<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\DB;

class SyncEmails extends Command
{
    protected $signature = 'email:sync';
    protected $description = 'Connect to Gmail and save emails from specific senders';

    public function handle()
    {
        $this->info('Connecting to Gmail...');

        // 1. Define your allowed senders here
        $allowedSenders = [
            'thagyal11@gmail.com',
            'sunilthagyal60@gmail.com',
            'sahil@gmail.com'
        ];

        try {
            $client = Client::account('default');
            $client->connect();

            $folder = $client->getFolder('INBOX');
            $this->info('Connected to INBOX.');

            $since = now()->subDay()->format('d-M-Y'); 
            $this->info("Fetching all emails since: $since ...");

            // 2. Fetch ALL emails from yesterday (broad search)
            // We do NOT filter by 'from' here, we filter in the loop below.
            $messages = $folder->query()->since($since)->get();
            $this->info("Scanning " . $messages->count() . " recent emails...");

            foreach ($messages as $message) {
                $senderEmail = $message->getFrom()[0]->mail;

                // 3. PHP FILTER: strictly check if sender is in our allowed list
                if (!in_array($senderEmail, $allowedSenders)) {
                    // If not in the list, skip silently
                    continue; 
                }

                // Check if already saved
                if (DB::table('emails')->where('message_id', $message->getMessageId())->exists()) {
                    $this->comment("Skipped (Existing): " . $message->getSubject());
                    continue;
                }

                // Save to DB
                DB::table('emails')->insert([
                    'subject'    => $message->getSubject() ?? 'No Subject',
                    'from_email' => $senderEmail,
                    'from_name'  => $message->getFrom()[0]->personal,
                    'body'       => $message->getTextBody() ?? 'No Text Content',
                    'message_id' => $message->getMessageId(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->info("Imported: " . $message->getSubject() . " (From: $senderEmail)");
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}