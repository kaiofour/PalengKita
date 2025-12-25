<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\Auth\AuthController;

Route::get('/', function () {
    return redirect()->route('signin');
});

// == AUTH ==
Route::get('/signup', [AuthController::class, 'signup'])->name('signup');
Route::post('/signup', [AuthController::class, 'store'])->name('signup.store');
Route::get('/signin', [AuthController::class, 'signin'])->name('signin');
Route::post('/signin', [AuthController::class, 'authenticate'])->name('signin.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// == AUTHENTICATED USERS (Customers & Admin) ==
Route::middleware(['auth'])->group(function () {
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    // == CUSTOMER MARKETPLACE (UPDATED) ==
    // 1. Shows the "Make Purchase" page
    Route::get('/purchases', [TransactionsController::class, 'marketplace'])->name('customer.purchases.index');
    
    // 2. Handles the Checkout button click
    Route::post('/customer/checkout', [TransactionsController::class, 'checkout'])->name('customer.checkout');
});

// == ADMIN: CUSTOMER MANAGEMENT ==
Route::middleware(['check.type:admin'])->group(function () {
    // add
    Route::get('/customers/create', [CustomersController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomersController::class, 'store'])->name('customers.store');

    // display (many)
    Route::get('/customers', [CustomersController::class, 'index'])->name('customers.index');

    // display (one)
    Route::get('/customers/{customer}', [CustomersController::class, 'displayCustomer'])->name('customers.displayCustomer');

    // edit
    Route::get('/customers/{customer}/edit', [CustomersController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomersController::class, 'update'])->name('customers.update');

    // delete
    Route::delete('/customers/{customer}', [CustomersController::class, 'delete'])->name('customers.delete');
});

// == ADMIN: TRANSACTION MANAGEMENT ==
Route::middleware(['auth'])->group(function () {
    Route::get('/transactions', [TransactionsController::class, 'index'])->name('transactions.index');

    // add
    Route::get('/transactions/create', [TransactionsController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionsController::class, 'store'])->name('transactions.store');

    // view one
    Route::get('/transactions/{transaction}', [TransactionsController::class, 'show'])->name('transactions.show');

    // edit
    Route::get('/transactions/{transaction}/edit', [TransactionsController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionsController::class, 'update'])->name('transactions.update');

    // delete
    Route::delete('/transactions/{transaction}', [TransactionsController::class, 'destroy'])->name('transactions.destroy');
});