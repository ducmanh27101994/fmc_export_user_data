<?php

use Illuminate\Support\Facades\Route;
use FmcExample\GoogleLogin\Http\Controllers\GoogleLoginController;

Route::middleware(['web'])->group(function () {
    Route::get('auth/google', [GoogleLoginController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [GoogleLoginController::class, 'googleCallback']);
});
