<?php

use Illuminate\Support\Facades\Route;

// Redirect root to admin
Route::get('/', function () {
    return redirect('/admin');
});
