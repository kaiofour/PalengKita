<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomersController;
use App\Http\Controllers\TransactionsController;


// test
Route::get('/test', [TransactionsController::class, 'test']);

// == TRANSACTION ==
// add
Route::post('/transactions/add', [TransactionsController::class, 'addTransaction']);
Route::get('/products/search', function (Request $request) {
    $q = $request->query('q', '');

    $res = Http::get('http://localhost:3000/api/products/search', [
        'q' => $q,
    ]);

    return response()->json($res->json(), $res->status());
});
