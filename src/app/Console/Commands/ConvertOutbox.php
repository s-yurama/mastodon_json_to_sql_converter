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

        $this->buildSqlInsertStatus($jsonObject->orderedItems);
        $this->buildSqlInsertMediaAttachments();
        $this->buildSqlInsertStatusTags();
        $this->buildSqlInsertTags();
        
        return 0;
    }
    
    /**
     * build insert SQL for statuses table
     * @param type $items
     */
    private function buildSqlInsertStatus($items) {
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
        
        foreach( $items as $item) {
            $body .= 'INSERT INTO statuses';
            $body .= "\n";
            
            $columns = '('. implode(', ', $columnNames). ') ';
            $body.= $columns;   
            $body .= "\n";
            
            $uri = $item->object->id;
            $id = basename($item->object->id); // as ID column in posgresql db side
            $text = html_entity_decode($item->object->content);
            $text = str_replace('&apos;', '\'', $text);
            $text = str_replace('<p>', '', $text);
            $text = str_replace('</p>', "\n", $text);
            $text = str_replace('<br />', "\n", $text);
            
            // URL part recovery
            //$text = preg_replace("/(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/u", '', $text);
            $text = str_replace('<a href="', ' ', $text);
            $text = str_replace('" rel="nofollow noopener" target="_blank">', '', $text);
            $text = preg_replace('/<span class="invisible">https:\/\/<\/span><span class="">[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+<\/span><span class="invisible"><\/span><\/a>/u', ' ', $text);

            // end of </p> will be converted to \n, then remove it.
            $text = preg_replace("/\n$/u", '', $text);
            $text = preg_replace("/\s$/u", '', $text);

            $text = str_replace('\'', '\'\'', $text);
            
            // escape for text
            //$text = pg_escape_string(null, $text);
            
            $created_at = date('Y/m/d H:i:s.v', strtotime($item->object->published));
            $updated_at = date('Y/m/d H:i:s.v', strtotime($item->object->published));
            $in_reply_to_id = 'NULL';
            $reblog_of_id = 'NULL';
            $url = 'NULL';
            $sensitive = $item->object->sensitive ? 'true' : 'false';
            $visibility = 1;
            $spoiler_text = '';
            $reply = 'false';
            $language = 'ja';
            $conversation_id++;
            $local = 'true';
            
            $application_id = 'NULL';
            $in_reply_to_account_id = 'NULL';
            $poll_id = 'NULL';
            $deleted_at = 'NULL';
            $edited_at = 'NULL';

            $body .= <<<__SQL_VALUES__
VALUES (
   timestamp_id('statuses'),
   '{$uri}',
   '{$text}',
   '{$created_at}',
   '{$updated_at}',
   {$in_reply_to_id},
   {$reblog_of_id},
   {$url},
   {$sensitive},
   {$visibility},
   '{$spoiler_text}',
   {$reply},
   '{$language}',
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
    }
    
    /**
     * build insert SQL for media_attachments table
     */
    private function buildSqlInsertMediaAttachments() {
        
    }

    /**
     * build insert SQL for status_tags table
     */
    private function buildSqlInsertStatusTags() {
        
    }

    /**
     * build insert SQL for tags table
     */
    private function buildSqlInsertTags() {
        
    }
}
