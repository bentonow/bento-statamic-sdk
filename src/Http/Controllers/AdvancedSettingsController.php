<?php

namespace Bento\BentoStatamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Statamic\Http\Controllers\Controller;

class AdvancedSettingsController extends Controller
{
    public function index()
    {
        $config = [
            'auto_user_sync' => config('bento.auto_user_sync', true),
            'inject_js' => config('bento.inject_js', false),
        ];

        return view('bento-statamic-sdk::advanced-settings', [
            'title' => 'Bento Advanced Settings',
            'bentoConfig' => json_encode($config),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'auto_user_sync' => 'required|boolean',
            'inject_js' => 'required|boolean',
        ]);

        try {
            // Get current env content
            $path = base_path('.env');
            $content = file_get_contents($path);

            // Update env values
            $this->updateEnvironmentFile([
                'BENTO_AUTO_USER_SYNC' => $validated['auto_user_sync'] ? 'true' : 'false',
                'BENTO_INJECT_JS' => $validated['inject_js'] ? 'true' : 'false'
            ]);

            return back()->with('success', 'Advanced settings updated successfully.');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
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
