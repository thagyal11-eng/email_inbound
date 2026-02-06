<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncEmails extends Command
{
    protected $signature = 'email:sync';
    protected $description = 'Sync Inbox AND Sent items (to catch phone replies)';

    public function handle()
    {
        // 1. We MUST include YOUR email here now, 
        // otherwise the script will ignore your phone replies.
        $allowedSenders = [
            'thagyal11@gmail.com',
            'sunilthagyal60@gmail.com',
            'sahil@gmail.com',
            'sunilkumar.mind2web@gmail.com', // <--- ADDED BACK
            'jailal.mind2web@gmail.com'
        ];

        // 2. Scan both locations
        $folders = ['INBOX', '[Gmail]/Sent Mail']; 

        $client = Client::account('default');
        $client->connect();

        foreach($folders as $folderName) {
            try {
                $folder = $client->getFolder($folderName);
                $this->info("---------------------------------");
                $this->info("📂 Scanning: $folderName");
            } catch (\Exception $e) {
                continue;
            }

            $since = now()->subDay()->format('d-M-Y');
            $messages = $folder->query()->since($since)->get();

            foreach ($messages as $message) {
                $senderEmail = $message->getFrom()[0]->mail;

                // Filter: Is this sender allowed?
                if (!in_array($senderEmail, $allowedSenders)) {
                    continue; 
                }

                // DUPLICATE CHECK (Critical):
                // If we sent this from the Website, it's already in the DB.
                // This check ensures we don't save it twice.
                if (DB::table('emails')->where('message_id', $message->getMessageId())->exists()) {
                    // $this->comment("Skipped duplicate: " . $message->getSubject());
                    continue;
                }

                // --- THREADING LOGIC ---
                $inReplyToId = $message->getInReplyTo();
                if (is_array($inReplyToId)) $inReplyToId = $inReplyToId[0] ?? null;

                $parentId = null;
                if ($inReplyToId) {
                    $parentEmail = DB::table('emails')->where('message_id', $inReplyToId)->first();
                    if ($parentEmail) {
                        $parentId = $parentEmail->id;
                        $this->info("   🔗 Linked reply to: " . $parentEmail->subject);
                    }
                }

                // Determine Type: If it's in Sent Mail, it's outgoing.
                $type = (strpos($folderName, 'Sent') !== false) ? 'outgoing' : 'incoming';

                // Double check: If the sender is ME, force type to outgoing
                if ($senderEmail == 'sunilkumar.mind2web@gmail.com') {
                    $type = 'outgoing';
                }

                DB::table('emails')->insert([
                    'parent_id'  => $parentId,
                    'type'       => $type,
                    'subject'    => $message->getSubject() ?? 'No Subject',
                    'from_email' => $senderEmail,
                    'from_name'  => $message->getFrom()[0]->personal,
                    'body'       => $message->getTextBody() ?? 'No Text Content',
                    'message_id' => $message->getMessageId(),
                    'created_at' => Carbon::parse($message->getDate()), 
                    'updated_at' => now(),
                ]);

                $this->info("Saved ($type): " . $message->getSubject());
            }
        }
    }
}