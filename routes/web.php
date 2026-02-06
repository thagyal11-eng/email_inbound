<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $emails = \Illuminate\Support\Facades\DB::table('emails')->orderByDesc('created_at')->get();
    return view('emails', ['emails' => $emails]);
});
