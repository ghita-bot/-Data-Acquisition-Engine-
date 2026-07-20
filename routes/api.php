<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\websiteController;
use App\Http\Controllers\DomainController;


Route::post('/extract/website', [WebsiteController::class, 'extract']);
Route::post('/extract/domain', [DomainController::class, 'extract']);