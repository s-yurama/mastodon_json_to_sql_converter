<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ConvertOutbox extends Command
{
    const ACCOUNT_ID = '107821726044655681';
    
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

    protected $mediaAttachments = [];
    protected $statusesTags = [];
    protected $tags = [];
    
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
        $this->buildSqlInsertTags();
        $this->buildSqlInsertStatusesTags();
        
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
        
        $account_id = self::ACCOUNT_ID;
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
            
            // hash tag part recovery
            $text = preg_replace('/<a href=\"https:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+\" class=\"mention hashtag\" rel=\"tag\">/u', '', $text);
            $text = str_replace('#<span>', '#', $text);
            
            // URL part recovery
            //$text = preg_replace("/(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/u", '', $text);
            $text = str_replace('<a href="', ' ', $text);
            $text = str_replace('" rel="nofollow noopener" target="_blank">', '', $text);
            $text = preg_replace('/<span class="invisible">https:\/\/[a-zA-Z0-9\-\.]*<\/span><span class="[a-zA-Z0-9]*">[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+<\/span><span class="invisible">[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]*<\/span><\/a>/u', ' ', $text);
            
            $text = str_replace('</span></a>', ' ', $text);

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
            
            foreach($item->object->tag as $tag) {
                $this->tags[] = [
                    'status_id'           => $id,
                    'name'                => preg_replace('/^#/u', '', $tag->name),
                    'created_at'          => $created_at,
                    'updated_at'          => $updated_at,
                    'id'                  => 0,
                    'usable'              => 'NULL',
                    'trendable'           => 'NULL',
                    'listable'            => 'NULL',
                    'reviewed_at'         => 'NULL',
                    'requested_review_at' => 'NULL',
                    'last_status_at'      => 'NULL',
                    'max_score'           => 'NULL',
                    'max_score_at'        => 'NULL',
                ];
            }

            foreach($item->object->attachment as $attachment) {
                $this->mediaAttachments[] = [
                    'status_id'                     => $id,
                    'file_file_name'                => basename($attachment->url),
                    'file_content_type'             => $attachment->mediaType,
                    'file_file_size'                => null, // required to made manually.
                    'file_updated_at'               => $created_at,
                    'remote_url'                    => 'NULL',
                    'created_at'                    => $created_at,
                    'updated_at'                    => $created_at,
                    'shortcode'                     => 'NULL',
                    'type'                          => 0,
                    'file_meta'                     => null, // required to made manually.
                    'account_id'                    => $account_id,
                    'id'                            => 'timestamp_id(\'media_attachments\')', // recreated
                    'description'                   => '',
                    'scheduled_status_id'           => 'NULL',
                    'blurhash'                      => $attachment->blurhash,
                    'processing'                    => 2,
                    'file_storage_schema_version'   => 1,
                    'thumbnail_file_name'           => 'NULL',
                    'thumbnail_content_type'        => 'NULL',
                    'thumbnail_file_size'           => 'NULL',
                    'thumbnail_updated_at'          => 'NULL',
                    'thumbnail_remote_url'          => 'NULL',
                ];
            }

            $body .= <<<__SQL_VALUES__
VALUES (
   {$id},
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
     * build insert SQL for tags table
     */
    private function buildSqlInsertTags() {
        $body = '';

        $columnNames = [
            'name',
            'created_at',
            'updated_at',
            'id',
            'usable',
            'trendable',
            'listable',
            'reviewed_at',
            'requested_review_at',
            'last_status_at',
            'max_score',
            'max_score_at',
        ];
        
        $id = 0;
        $existsNames = [];
        foreach($this->tags as $tag) {
            $isExistsAlready = false;
            foreach($existsNames as $existsName){
                 if ($existsName ===  $tag['name']) {
                     $isExistsAlready = true;
                     break;
                 }
            }
            
            if ($isExistsAlready) {
                continue;
            }
            
            $id++;
            
            $this->statusesTags[] = [
                'status_id' => $tag['status_id'],
                'tag_id' => $id,
            ];
            
            $existsNames[] = $tag['name'];
            
            $body .= 'INSERT INTO tags';
            $body .= "\n";
            
            $columns = '('. implode(', ', $columnNames). ') ';
            $body.= $columns;   
            $body .= "\n";
            
            $name = $tag['name'];
            $created_at = $tag['created_at'];
            $updated_at = $tag['created_at'];
            //$id = $tag['id'];
            $usable = $tag['usable'];
            $trendable = $tag['trendable'];
            $listable = $tag['listable'];
            $reviewed_at = $tag['reviewed_at'];
            $requested_review_at = $tag['requested_review_at'];
            $last_status_at = $tag['last_status_at'];
            $max_score = $tag['max_score'];
            $max_score_at = $tag['max_score_at'];
        
            $body .= <<<__SQL_VALUES__
VALUES (
    '{$name}',
    '{$created_at}',
    '{$updated_at}',
    {$id},
    {$usable},
    {$trendable},
    {$listable},
    {$reviewed_at},
    {$requested_review_at},
    {$last_status_at},
    {$max_score},
    {$max_score_at}
);
__SQL_VALUES__;
            $body .= "\n\n";
        }
        
        Storage::put('sql/insert_tags.sql', $body);
    }

    /**
     * build insert SQL for status_tags table
     */
    private function buildSqlInsertStatusesTags() {
        $body = '';

        $columnNames = [
            'status_id',
            'tag_id',
        ];
        
        foreach($this->statusesTags as $statusesTag) {               
            $body .= 'INSERT INTO statuses_tags';
            $body .= "\n";
            
            $columns = '('. implode(', ', $columnNames). ') ';
            $body.= $columns;   
            $body .= "\n";
            $body .= <<<__SQL_VALUES__
VALUES (
    {$statusesTag['status_id']},
    {$statusesTag['tag_id']}
);
__SQL_VALUES__;
            $body .= "\n\n";
        }
        
        Storage::put('sql/insert_statuses_tags.sql', $body);
    }
}
