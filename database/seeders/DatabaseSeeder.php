<?php

namespace Database\Seeders\Bento;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            BentoFormEventsSeeder::class
        ]);
    }
}
