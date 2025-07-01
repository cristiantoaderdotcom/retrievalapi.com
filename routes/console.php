<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\ProcessEmailInboxes;
use App\Console\Commands\GenerateKBEmbeddings;

Schedule::command(ProcessEmailInboxes::class)->everyMinute();
Schedule::command(GenerateKBEmbeddings::class)->everyMinute();