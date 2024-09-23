<?php

use Illuminate\Support\Facades\Route;
use FmcExample\UserPackage\Http\Controllers\UserController;

Route::post('export-users', [UserController::class, 'exportDataUsers']);
