<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\websiteController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\LocationController;


Route::post('/extract/website', [WebsiteController::class, 'extract']);
Route::post('/extract/domain', [DomainController::class, 'extract']);
Route::post('/extract/location', [LocationController::class, 'extract']);