<?php

use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\LicenseController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VariationController;
use App\Http\Controllers\Api\V1\VersionController;
use Illuminate\Support\Facades\Route;

Route::get('customers/search', [CustomerController::class, 'search'])->name('api.customers.search');
Route::get('projects/search', [ProjectController::class, 'search'])->name('api.projects.search');

Route::apiResource('projects', ProjectController::class);
Route::apiResource('projects.versions', VersionController::class);
Route::apiResource('projects.variations', VariationController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('licenses', LicenseController::class);
Route::get('licenses/{license}/download', [LicenseController::class, 'download'])->name('licenses.download');
Route::post('licenses/{license}/reset', [LicenseController::class, 'reset'])->name('licenses.reset');
Route::apiResource('users', UserController::class);
