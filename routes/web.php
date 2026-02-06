<?php

use App\Http\Controllers\EmailController;
use App\Models\Email;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Fetch only top-level emails, but include all their children recursively
    $emails = Email::whereNull('parent_id')
                   ->with('replies')
                   ->orderByDesc('created_at')
                   ->get();

    return view('emails', ['emails' => $emails]);
});

Route::post('/send-email', [EmailController::class, 'send'])->name('email.send');