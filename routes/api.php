<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CompanyInformationController;


Route::post('/extract/website', [WebsiteController::class, 'extract']);
Route::post('/extract/domain', [DomainController::class, 'extract']);
Route::post('/extract/location', [LocationController::class, 'extract']);
Route::get('/company-information', [CompanyInformationController::class, 'show']);    