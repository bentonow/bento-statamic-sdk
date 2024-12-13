<?php

namespace Bento\BentoStatamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class ConfigController extends CpController
{
    public function index()
    {
        return view('bento-statamic::config', [
            'title' => 'Bento Configuration',
            'enabled' => config('bento.enabled', false),
            'site_uuid' => config('bento.site_uuid', ''),
            'publishable_key' => config('bento.publishable_key', ''),
            'secret_key' => config('bento.secret_key', ''),
            'admin_email' => User::current()->email(),
            'mail_from_address' => env('MAIL_FROM_ADDRESS', '')
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'site_uuid' => 'required_if:enabled,1|string',
            'publishable_key' => 'required_if:enabled,1|string',
            'secret_key' => 'required_if:enabled,1|string',
        ]);

        try {
            $this->updateEnvironmentFile([
                'BENTO_ENABLED' => $validated['enabled'] ? 'true' : 'false',
                'BENTO_SITE_UUID' => $validated['site_uuid'],
                'BENTO_PUBLISHABLE_KEY' => $validated['publishable_key'],
                'BENTO_SECRET_KEY' => $validated['secret_key'],
                'MAIL_MAILER' => $validated['enabled'] ? 'bento' : 'log'
            ]);

            $this->updateRuntimeConfig($validated);

            return back()->with('success', 'Bento configuration updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update Bento configuration: ' . $e->getMessage());
            return back()->with('error', 'Failed to update configuration: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updateEmail(Request $request)
    {
        $validated = $request->validate([
            'from_email' => 'required|email'
        ]);

        try {
            $this->updateEnvironmentFile([
                'MAIL_FROM_ADDRESS' => $validated['from_email']
            ]);

            config(['mail.from.address' => $validated['from_email']]);
            Artisan::call('config:clear');

            return back()->with('success', 'Email configuration updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update email configuration: ' . $e->getMessage());
            return back()->with('error', 'Failed to update email configuration: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'from_email' => 'required|email'
        ]);

        try {
            $adminEmail = User::current()->email();

            Mail::raw('This is a test email from your Bento configuration in Statamic.', function ($message) use ($request, $adminEmail) {
                $message->from($request->from_email)
                    ->to($adminEmail)
                    ->subject('Bento Test Email');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send test email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function updateRuntimeConfig($validated)
    {
        config([
            'bento.enabled' => (bool)$validated['enabled'],
            'bento.site_uuid' => $validated['site_uuid'],
            'bento.publishable_key' => $validated['publishable_key'],
            'bento.secret_key' => $validated['secret_key']
        ]);

        Artisan::call('config:clear');
    }

    protected function updateEnvironmentFile(array $data)
    {
        $path = base_path('.env');

        if (!file_exists($path)) {
            throw new \Exception('.env file not found');
        }

        $content = file_get_contents($path);

        foreach ($data as $key => $value) {
            // Escape any quotes in the value
            $value = str_replace('"', '\\"', $value);

            // Wrap the value in quotes if it contains spaces
            if (str_contains($value, ' ')) {
                $value = '"' . $value . '"';
            }

            if (preg_match("/^{$key}=/m", $content)) {
                // Update existing variable
                $content = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $content
                );
            } else {
                // Add new variable
                $content .= PHP_EOL . "{$key}={$value}";
            }
        }

        if (file_put_contents($path, $content) === false) {
            throw new \Exception('Unable to write to .env file');
        }

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path);
        }
    }
}
