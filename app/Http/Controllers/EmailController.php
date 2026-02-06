<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Mime\Email; // Required for Message-ID access

class EmailController extends Controller
{
   public function send(Request $request)
    {
        $request->validate([
            'to_email' => 'required|email',
            'subject'  => 'required',
            'body'     => 'required',
        ]);

        $recipient = $request->to_email;
        $subject   = $request->subject;
        $body      = $request->body;
        $parentId  = $request->parent_id;

        // Generate Custom ID
        $uniqueId = uniqid(time());
        $customMessageId = "{$uniqueId}@gmail.com"; 

        $replyToMessageId = null;
        if ($parentId) {
            $parentEmail = DB::table('emails')->find($parentId);
            if ($parentEmail) {
                $replyToMessageId = $parentEmail->message_id;
            }
        }

        try {
            // ATTEMPT TO SEND VIA SMTP
            Mail::send([], [], function ($message) use ($recipient, $subject, $body, $replyToMessageId, $customMessageId) {
                $message->to($recipient)
                        ->subject($subject)
                        ->html($body);

                $message->getHeaders()->addIdHeader('Message-ID', $customMessageId);

                if ($replyToMessageId) {
                    $message->getHeaders()->addTextHeader('In-Reply-To', $replyToMessageId);
                    $message->getHeaders()->addTextHeader('References', $replyToMessageId);
                }
            });

        } catch (\Exception $e) {
            // IF SMTP FAILS: RETURN THE ERROR TO THE USER
            return back()->with('error', 'SMTP Error: ' . $e->getMessage());
        }

        // IF SUCCESSFUL, SAVE TO DB
        DB::table('emails')->insert([
            'parent_id'  => $parentId,
            'type'       => 'outgoing',
            'subject'    => $subject,
            'from_email' => config('mail.from.address'),
            'from_name'  => config('mail.from.name'),
            'body'       => $body,
            'message_id' => $customMessageId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Email sent successfully!');
    }
}