<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ConvertOutbox extends Command
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
        try {
            $fileJson = Storage::get('json/outbox.json');
        } catch (\ContractFileNotFoundException $e) {
            echo 'json file not found. please put outbox.json under ./storage/app/json/';
            return 0;
        }

        $jsonObject = json_decode($fileJson, false);

        $body = '';
                
        $columnNames = [
            'id',
            'uri',
            'text',
            'created_at',
            'updated_at',
            'in_reply_to_id',
            'reblog_of_id',
            'url',
            'sensitive',
            'visibility',
            'spoiler_text',
            'reply',
            'language',
            'conversation_id',
            'local',
            'account_id',
            'application_id',
            'in_reply_to_account_id',
            'poll_id',
            'deleted_at',
            'edited_at',
        ];
        
        $account_id = 'reserved_account_id_here';
        $conversation_id = 0;
        
        foreach( $jsonObject->orderedItems as $item) {
            $body .= 'INSERT INTO statuses';
            $body .= "\n";
            
            $columns = '('. implode(', ', $columnNames). ') ';
            $body.= $columns;   
            $body .= "\n";
            
            $uri = $item->id;
            $timestamp = basename($item->object->id); // as ID column in posgresql db side
            $text = str_replace('<p>', '', $item->object->content);
            $text = str_replace('</p>', "\n", $text);
            $text = str_replace('<br />', "\n", $text);
            $text = preg_replace("/\n$/u", '', $text);
            $created_at = $timestamp;
            $updated_at = $timestamp;
            $in_reply_to_id = 'NULL';
            $reblog_of_id = 'NULL';
            $url = 'NULL';
            $sensitive = (string)($item->object->sensitive);
            $visibility = 1;
            $spoiler_text = '';
            $reply = false;
            $language = 'ja';
            $conversation_id++;
            $local = true;
            
            $application_id = 'NULL';
            $in_reply_to_account_id = 'NULL';
            $poll_id = 'NULL';
            $deleted_at = 'NULL';
            $edited_at = 'NULL';

            $body .= <<<__SQL_VALUES__
VALUES (
   {$timestamp},
   "{$uri}",
   "{$text}",
   {$created_at},
   {$updated_at},
   {$in_reply_to_id},
   {$reblog_of_id},
   "{$url}",
   {$sensitive},
   {$visibility}
   {$spoiler_text},
   {$reply},
   "{$language}",
   {$conversation_id},
   {$local},
   {$account_id},
   {$application_id},
   {$in_reply_to_account_id},
   {$poll_id},
   {$deleted_at},
   {$edited_at}
);
__SQL_VALUES__;
            $body .= "\n\n";
        }
        
        Storage::put('sql/insert_status.sql', $body);
        
        return 0;
    }
}
