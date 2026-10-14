<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeaseController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\UnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public: no login needed
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Any logged-in user (landlord, tenant or admin)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    // The policy decides who may view: the landlord or the tenant on it
    Route::get('/leases/{lease}', [LeaseController::class, 'show']);
});

// Landlords only
Route::middleware(['auth:sanctum', 'role:landlord'])->group(function () {
    Route::apiResource('properties', PropertyController::class);
    Route::apiResource('properties.units', UnitController::class)->shallow();

    Route::get('/leases', [LeaseController::class, 'index']);
    Route::post('/units/{unit}/leases', [LeaseController::class, 'store']);
    Route::post('/leases/{lease}/terminate', [LeaseController::class, 'terminate']);
});

// Tenants only
Route::middleware(['auth:sanctum', 'role:tenant'])->group(function () {
    Route::get('/my-leases', [LeaseController::class, 'myLeases']);
});