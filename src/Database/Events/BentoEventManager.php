<?php

namespace Bento\BentoStatamic\Database\Events;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Statamic\Facades\YAML;
use Statamic\Facades\File;
use Illuminate\Support\Facades\DB;

class BentoEventManager
{
    protected $useDatabase;
    protected $eventsPath;

    public function __construct()
    {
        $this->useDatabase = config('statamic.database.enabled', false);
        $this->eventsPath = storage_path('bento/events.yaml');
    }

    public function createTable()
    {
        if ($this->useDatabase && !Schema::hasTable('bento_form_events')) {
            Schema::create('bento_form_events', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        } elseif (!$this->useDatabase) {
            // Ensure storage directory exists
            if (!File::exists(dirname($this->eventsPath))) {
                File::makeDirectory(dirname($this->eventsPath), 0755, true);
            }

            // Create events file if it doesn't exist
            if (!File::exists($this->eventsPath)) {
                File::put($this->eventsPath, YAML::dump([]));
            }
        }
    }

    public function dropTable()
    {
        if ($this->useDatabase) {
            Schema::dropIfExists('bento_form_events');
        } else {
            if (File::exists($this->eventsPath)) {
                File::delete($this->eventsPath);
            }
        }
    }

    public function seedEvents()
    {
        $events = [
            // WooCommerce Events
            ['name' => '$OrderPlaced'],
            ['name' => '$OrderRefunded'],
            ['name' => '$OrderCancelled'],
            ['name' => '$OrderShipped'],

            // WooCommerce Subscriptions
            ['name' => '$SubscriptionCreated'],
            ['name' => '$SubscriptionActive'],
            ['name' => '$SubscriptionCancelled'],
            ['name' => '$SubscriptionExpired'],
            ['name' => '$SubscriptionOnHold'],
            ['name' => '$SubscriptionTrialEnded'],
            ['name' => '$SubscriptionRenewed'],

            // SureCart
            ['name' => '$CheckoutConfirmed'],

            // Easy Digital Downloads
            ['name' => '$DownloadPurchased'],
            ['name' => '$DownloadDownloaded'],
            ['name' => '$DownloadRefunded'],

            // LearnDash
            ['name' => '$CourseCompleted'],
            ['name' => '$LessonCompleted'],
            ['name' => '$TopicCompleted'],
            ['name' => '$QuizCompleted'],
            ['name' => '$EssayGraded'],
            ['name' => '$AssignmentApproved'],
            ['name' => '$AssignmentNewComment'],
            ['name' => '$UserEnrolledInCourse'],
            ['name' => '$UserEnrolledInGroup'],
            ['name' => '$UserPurchasedCourse'],
            ['name' => '$UserPurchasedGroup'],
            ['name' => '$UserEarnedNewCertificate'],
            ['name' => '$CourseNotCompleted'],
            ['name' => '$LessonNotCompleted'],
            ['name' => '$TopicNotCompleted'],
            ['name' => '$QuizNotCompleted'],
        ];

        if ($this->useDatabase) {
            foreach ($events as $event) {
                DB::table('bento_form_events')->insertOrIgnore([
                    'name' => $event['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            $existingEvents = YAML::parse(File::get($this->eventsPath)) ?? [];
            $existingNames = array_column($existingEvents, 'name');

            foreach ($events as $event) {
                if (!in_array($event['name'], $existingNames)) {
                    $existingEvents[] = $event;
                }
            }

            File::put($this->eventsPath, YAML::dump($existingEvents));
        }
    }

    public function getEvents()
    {
        if ($this->useDatabase) {
            return DB::table('bento_form_events')->get();
        }

        return collect(YAML::parse(File::get($this->eventsPath)) ?? []);
    }
}
