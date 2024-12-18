<?php

namespace Bento\BentoStatamic\Listeners;

use Bentonow\BentoLaravel\Facades\Bento;
use Bentonow\BentoLaravel\DataTransferObjects\EventData;
use Illuminate\Support\Facades\Log;
use Statamic\Events\SubmissionCreated;
use Illuminate\Support\Facades\DB;
use Statamic\Facades\YAML;
use Illuminate\Support\Facades\File;

class FormSubmissionListener
{
    protected $useDatabase;

    public function __construct()
    {
        $this->useDatabase = config('statamic.database.enabled', false);
    }

    /**
     * Handle the submission created event
     */
    public function handle(SubmissionCreated $event)
    {
        try {
            $submission = $event->submission;
            $form = $submission->form();

            // Get the associated Bento event for this form
            $bentoEvent = $this->getFormBentoEvent($form->handle());

            if (!$bentoEvent) {
                return; // No Bento event configured for this form
            }

            // Get all form data
            $formData = $submission->data()->toArray();

            // Look for an email field in the submission
            $email = $this->findEmailField($formData);

            if (!$email) {
                Log::warning("Bento event not sent: No email field found in form submission for form: {$form->handle()}");
                return;
            }

            // Create the event data
            $eventData = collect([
                new EventData(
                    type: '$' . ltrim($bentoEvent, '$'), // Ensure $ prefix
                    email: $email,
                    fields: $formData
                )
            ]);

            // Track the event using the Bento SDK
            $response = Bento::trackEvent($eventData);

            if (!$response->successful()) {
                Log::error("Failed to send Bento event for form {$form->handle()}: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("Error processing Bento form event: " . $e->getMessage());
        }
    }

    /**
     * Get the configured Bento event for a form
     */
    protected function getFormBentoEvent($handle)
    {
        if ($this->useDatabase) {
            return DB::table('form_bento_events')
                ->where('form_handle', $handle)
                ->value('event_name');
        }

        $eventsPath = storage_path('bento/form_events.yaml');
        if (!File::exists($eventsPath)) {
            return null;
        }

        $events = YAML::parse(File::get($eventsPath)) ?? [];
        return $events[$handle] ?? null;
    }

    /**
     * Find an email field in the form data
     */
    protected function findEmailField(array $data)
    {
        // Common email field names
        $emailFields = ['email', 'email_address', 'user_email', 'contact_email'];

        // First check common email field names
        foreach ($emailFields as $field) {
            if (isset($data[$field])) {
                return $data[$field];
            }
        }

        // If not found, look for any field containing 'email'
        foreach ($data as $key => $value) {
            if (stripos($key, 'email') !== false && is_string($value)) {
                return $value;
            }
        }

        return null;
    }
}
