<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WebsiteController;


Route::post('/extract/website', [WebsiteController::class, 'extract']);