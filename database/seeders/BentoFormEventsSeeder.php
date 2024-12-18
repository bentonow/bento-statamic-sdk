<?php

namespace Database\Seeders\Bento;

use Illuminate\Database\Seeder;
use Bento\BentoStatamic\Database\Events\BentoEventManager;

class BentoFormEventsSeeder extends Seeder
{
    protected $eventManager;

    public function __construct()
    {
        $this->eventManager = new BentoEventManager();
    }

    public function run()
    {
        $this->eventManager->seedEvents();
    }
}
