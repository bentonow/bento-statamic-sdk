<?php

use Illuminate\Database\Migrations\Migration;
use Bento\BentoStatamic\Database\Seeders\Events\BentoEventManager;

return new class extends Migration
{
    protected $eventManager;

    public function __construct()
    {
        $this->eventManager = new BentoEventManager();
    }

    public function up()
    {
        $this->eventManager->createTable();
    }

    public function down()
    {
        $this->eventManager->dropTable();
    }
};
