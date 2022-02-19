<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class dump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:outbox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'convert outbox.json to insert.sql';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return 0;
    }
}
