<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\PurchaseController;


Route::get('/', function () {
    return redirect()->route('signin');
});

// == AUTH ==
Route::get('/signup', [AuthController::class, 'signup'])->name('signup');
Route::post('/signup', [AuthController::class, 'store'])->name('signup.store');
Route::get('/signin', [AuthController::class, 'signin'])->name('signin');
Route::post('/signin', [AuthController::class, 'authenticate'])->name('signin.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    // == CUSTOMER PURCHASES ==
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('customer.purchases.index');
    Route::get('/api/products', [PurchaseController::class, 'getProducts'])->name('customer.api.products');
});

// == CUSTOMER ==
Route::middleware(['check.type:admin'])->group(function () {
    // add
    Route::get('/customers/create', [CustomersController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomersController::class, 'store'])->name('customers.store');

    // display (many)
    Route::get('/customers', [CustomersController::class, 'index'])->name('customers.index');

    // diaply (one)
    Route::get('/customers/{customer}', [CustomersController::class, 'displayCustomer'])->name('customers.displayCustomer');

    // edit
    Route::get('/customers/{customer}/edit', [CustomersController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomersController::class, 'update'])->name('customers.update');

    // delete
    Route::delete('/customers/{customer}', [CustomersController::class, 'delete'])->name('customers.delete');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/transactions', [TransactionsController::class, 'index'])->name('transactions.index');
});
