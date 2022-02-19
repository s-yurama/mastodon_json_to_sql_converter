<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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
        $fileJson = Storage::get('outbox.json');
        
        $jsonObject = json_decode($fileJson, true);
        
        $body = '';
        foreach( $jsonObject->orderedItems as $item) {
            $body.= $item->id;
            $body.= "\n";
        }
        
        Storage::put('insert_status.sql', $body);
        
        return 0;
    }
}
