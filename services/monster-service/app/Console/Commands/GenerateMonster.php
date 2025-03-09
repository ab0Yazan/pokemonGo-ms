<?php

namespace App\Console\Commands;

use App\Actions\GenerateMonsterAction;
use Illuminate\Console\Command;

class GenerateMonster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monster:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monsters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new GenerateMonsterAction)->execute();
    }
}
