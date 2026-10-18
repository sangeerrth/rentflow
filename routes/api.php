<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\LeaseController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\UnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FakeGatewayController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RazorpayWebhookController;

// Public: no login needed
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle']);


// Any logged-in user (the policies decide who may view each record)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/leases/{lease}', [LeaseController::class, 'show']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
});

// Landlords only
Route::middleware(['auth:sanctum', 'role:landlord'])->group(function () {
    Route::apiResource('properties', PropertyController::class);
    Route::apiResource('properties.units', UnitController::class)->shallow();

    Route::get('/leases', [LeaseController::class, 'index']);
    Route::post('/units/{unit}/leases', [LeaseController::class, 'store']);
    Route::post('/leases/{lease}/terminate', [LeaseController::class, 'terminate']);

    Route::get('/invoices', [InvoiceController::class, 'index']);
});

// Tenants only
Route::middleware(['auth:sanctum', 'role:tenant'])->group(function () {
    Route::get('/my-leases', [LeaseController::class, 'myLeases']);
    Route::get('/my-invoices', [InvoiceController::class, 'myInvoices']);
    Route::post('/invoices/{invoice}/pay', [PaymentController::class, 'createOrder']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);

    if (app()->environment('local') && config('services.payment_gateway') === 'fake') {
        Route::post('/dev/fake-gateway/pay', [FakeGatewayController::class, 'pay']);
    }
});