<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */

Route::group([

    'middleware' => 'api',
    'prefix' => 'v1/auth',

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);

});

Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'v1',
], function ($router) {

    // user
    Route::group([
        'prefix' => 'users',
    ], function ($router) {
        Route::get('/', [UserController::class, 'index'])->withoutMiddleware(['format.response']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // department
    Route::group([
        'middleware' => ['role:admin'],
        'prefix' => 'departments',
    ], function ($router) {
        Route::get('/', [DepartmentController::class, 'index'])->withoutMiddleware(['format.response']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/{id}', [DepartmentController::class, 'show']);
        Route::put('/{id}', [DepartmentController::class, 'update']);
        Route::delete('/{id}', [DepartmentController::class, 'destroy']);

        // map and unmap
        Route::post('/map', [DepartmentController::class, 'map']);
        Route::post('/unmap', [DepartmentController::class, 'unmap']);

        // get positions by department
        Route::get('/{id}/positions', [DepartmentController::class, 'positions']);
    });

    // position
    Route::group([
        'prefix' => 'positions',
    ], function ($router) {
        Route::get('/', [PositionController::class, 'index'])->withoutMiddleware(['format.response']);
        Route::post('/', [PositionController::class, 'store']);
        Route::get('/{id}', [PositionController::class, 'show']);
        Route::put('/{id}', [PositionController::class, 'update']);
        Route::delete('/{id}', [PositionController::class, 'destroy']);
    });

    // employee
    Route::group([
        'prefix' => 'employees',
    ], function ($router) {
        Route::get('/', [EmployeeController::class, 'index'])->withoutMiddleware(['format.response']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('/{id}', [EmployeeController::class, 'show']);
        Route::post('/{id}', [EmployeeController::class, 'update']);
        Route::delete('/{id}', [EmployeeController::class, 'destroy']);
    });

    // customer
    Route::group([
        'prefix' => 'customers',
    ], function ($router) {
        Route::get('/', [CustomerController::class, 'index'])->withoutMiddleware(['format.response']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::delete('/{id}', [CustomerController::class, 'destroy']);
    });

    // applicant
    Route::group([
        'prefix' => 'applicants',
    ], function ($router) {
        Route::get('/', [ApplicantController::class, 'index'])->withoutMiddleware(['format.response']);
        Route::post('/', [ApplicantController::class, 'store']);
        Route::get('/{id}', [ApplicantController::class, 'show']);
        Route::post('/{id}', [ApplicantController::class, 'update']);
        Route::delete('/{id}', [ApplicantController::class, 'destroy']);
    });

    // bank account
    Route::group([
        'prefix' => 'bank-accounts',
    ], function ($router) {
        Route::get('/', [BankAccountController::class, 'index'])->withoutMiddleware(['format.response']);
        Route::post('/', [BankAccountController::class, 'store']);
        Route::get('/{id}', [BankAccountController::class, 'show']);
        Route::put('/{id}', [BankAccountController::class, 'update']);
        Route::delete('/{id}', [BankAccountController::class, 'destroy']);
    });

    // vendor
    Route::group([
        'prefix' => 'vendors',
    ], function ($router) {
        Route::get('/', [VendorController::class, 'index'])->withoutMiddleware(['format.response']);
        Route::post('/', [VendorController::class, 'store']);
        Route::get('/{id}', [VendorController::class, 'show']);
        Route::put('/{id}', [VendorController::class, 'update']);
        Route::delete('/{id}', [VendorController::class, 'destroy']);
    });

    // invoice
    Route::group([
        'prefix' => 'invoices',
    ], function ($router) {
        Route::get('/', [InvoiceController::class, 'index'])->withoutMiddleware(['format.response']);
        Route::post('/', [InvoiceController::class, 'store']);
        Route::get('/{id}', [InvoiceController::class, 'show']);
        Route::put('/{id}', [InvoiceController::class, 'update']);
        Route::delete('/{id}', [InvoiceController::class, 'destroy']);
    });

});
