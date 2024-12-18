<?php

namespace Bento\BentoStatamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\YAML;
use Statamic\Facades\File;
use Statamic\Http\Controllers\Controller;
use Statamic\Facades\Form;

class EventsController extends Controller
{
    protected $useDatabase;
    protected $eventsPath;

    public function __construct()
    {
        $this->useDatabase = config('statamic.database.enabled', false);
        $this->eventsPath = storage_path('bento/events.yaml');
    }

    public function index()
    {
        if ($this->useDatabase) {
            $events = DB::table('bento_form_events')->get();
        } else {
            $events = $this->getEventsFromFile();
            // Ensure each event has an id
            $events = array_map(function ($event, $index) {
                if (!isset($event['id'])) {
                    $event['id'] = $index + 1;
                }
                return $event;
            }, $events, array_keys($events));

            // Save the updated events with IDs back to file
            $this->saveEventsToFile($events);
        }

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if ($this->useDatabase) {
            $id = DB::table('bento_form_events')->insertGetId($validated);
            $event = DB::table('bento_form_events')->find($id);
        } else {
            $events = $this->getEventsFromFile();

            // Find the highest existing ID
            $maxId = 0;
            foreach ($events as $event) {
                if (isset($event['id']) && $event['id'] > $maxId) {
                    $maxId = $event['id'];
                }
            }

            // Create new event with incremented ID
            $validated['id'] = $maxId + 1;
            $events[] = $validated;
            $this->saveEventsToFile($events);
            $event = $validated;
        }

        return response()->json($event);
    }


    public function destroy($id)
    {
        try {
            if ($this->useDatabase) {
                DB::table('bento_form_events')->where('id', $id)->delete();
            } else {
                $events = $this->getEventsFromFile();
                $events = array_values(array_filter($events, function ($event) use ($id) {
                    return isset($event['id']) && $event['id'] != $id;
                }));
                $this->saveEventsToFile($events);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to delete event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function getEventsFromFile()
    {
        if (!File::exists($this->eventsPath)) {
            File::put($this->eventsPath, YAML::dump([]));
            return [];
        }

        $events = YAML::parse(File::get($this->eventsPath)) ?? [];

        // Ensure each event has an ID
        $events = array_map(function ($event, $index) {
            if (!isset($event['id'])) {
                $event['id'] = $index + 1;
            }
            return $event;
        }, $events, array_keys($events));

        return $events;
    }

    protected function saveEventsToFile($events)
    {
        // Ensure proper array values before saving
        $events = array_values($events);
        File::put($this->eventsPath, YAML::dump($events));
    }

    public function getForms()
    {
        $forms = Form::all()->map(function ($form) {
            return [
                'handle' => $form->handle(),
                'title' => $form->title(),
                'bento_event' => $this->getFormBentoEvent($form->handle())
            ];
        });

        return response()->json($forms);
    }

    public function updateFormEvent(Request $request, $handle)
    {
        try {
            $validated = $request->validate([
                'event' => 'nullable|string'
            ]);

            $this->saveFormBentoEvent($handle, $validated['event']);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update form event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update form event: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function getFormBentoEvent($handle)
    {
        if ($this->useDatabase) {
            return DB::table('form_bento_events')
                ->where('form_handle', $handle)
                ->value('event_name');
        }

        $events = YAML::parse(File::get(storage_path('bento/form_events.yaml'))) ?? [];
        return $events[$handle] ?? null;
    }

    protected function saveFormBentoEvent($handle, $event)
    {
        if ($this->useDatabase) {
            DB::table('form_bento_events')
                ->updateOrInsert(
                    ['form_handle' => $handle],
                    ['event_name' => $event]
                );
        } else {
            $events = YAML::parse(File::get(storage_path('bento/form_events.yaml'))) ?? [];
            $events[$handle] = $event;
            File::put(storage_path('bento/form_events.yaml'), YAML::dump($events));
        }
    }
}
