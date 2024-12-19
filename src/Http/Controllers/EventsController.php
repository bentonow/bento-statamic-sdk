<?php

namespace Bento\BentoStatamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\YAML;
use Statamic\Facades\File;
use Statamic\Http\Controllers\Controller;
use Statamic\Facades\Form;
use Bentonow\BentoLaravel\Facades\Bento;

class EventsController extends Controller
{
    protected $useDatabase;
    protected $eventsPath;
    protected $syncTagsPath;

    public function __construct()
    {
        $this->useDatabase = config('statamic.database.enabled', false);
        $this->eventsPath = storage_path('bento/events.yaml');
        $this->syncTagsPath = storage_path('bento/sync_tags.yaml');
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

    /**
     * Get all available Bento tags
     */
    public function getTags()
    {
        try {
            $response = Bento::getTags();

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to fetch tags from Bento API'
                ], 500);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to fetch Bento tags: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch tags'
            ], 500);
        }
    }

    /**
     * Get currently selected sync tags
     */
    /**
     * Get currently selected sync tags
     */
    public function getSyncTags()
    {
        try {
            if ($this->useDatabase) {
                $tags = DB::table('bento_sync_tags')
                    ->select('tag_name')
                    ->get()
                    ->pluck('tag_name')
                    ->toArray();
            } else {
                if (!File::exists($this->syncTagsPath)) {
                    File::put($this->syncTagsPath, YAML::dump([]));
                    return response()->json([]);
                }

                $tags = YAML::parse(File::get($this->syncTagsPath)) ?? [];
                $tags = array_column($tags, 'tag_name');
            }

            return response()->json($tags);
        } catch (\Exception $e) {
            Log::error('Failed to fetch sync tags: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch sync tags'
            ], 500);
        }
    }

    /**
     * Add a new sync tag
     */
    public function addSyncTag(Request $request)
    {
        $validated = $request->validate([
            'tag' => 'required|string|max:255'
        ]);

        try {
            if ($this->useDatabase) {
                DB::table('bento_sync_tags')->insertOrIgnore([
                    'tag_name' => $validated['tag'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $tags = $this->getYamlSyncTags();

                if (!in_array($validated['tag'], array_column($tags, 'tag_name'))) {
                    $tags[] = [
                        'tag_name' => $validated['tag'],
                        'created_at' => now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString(),
                    ];
                    File::put($this->syncTagsPath, YAML::dump($tags));
                }
            }

            return response()->json([
                'message' => 'Tag added successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add sync tag: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to add tag'
            ], 500);
        }
    }

    /**
     * Remove a sync tag
     */
    public function removeSyncTag(Request $request)
    {
        $validated = $request->validate([
            'tag' => 'required|string|max:255'
        ]);

        try {
            if ($this->useDatabase) {
                DB::table('bento_sync_tags')
                    ->where('tag_name', $validated['tag'])
                    ->delete();
            } else {
                $tags = $this->getYamlSyncTags();
                $tags = array_filter($tags, function ($tag) use ($validated) {
                    return $tag['tag_name'] !== $validated['tag'];
                });
                File::put($this->syncTagsPath, YAML::dump(array_values($tags)));
            }

            return response()->json([
                'message' => 'Tag removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove sync tag: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to remove tag'
            ], 500);
        }
    }

    /**
     * Helper method to get sync tags from YAML file
     */
    protected function getYamlSyncTags()
    {
        if (!File::exists($this->syncTagsPath)) {
            File::put($this->syncTagsPath, YAML::dump([]));
            return [];
        }

        return YAML::parse(File::get($this->syncTagsPath)) ?? [];
    }
}
