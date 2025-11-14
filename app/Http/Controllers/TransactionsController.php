<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;

class TransactionsController extends Controller
{
    // DISPLAY FORM
    public function create()
    {
        $users = User::where('type', 'customer')->get();
        return view('transactions.create', compact('users'));
    }

    //STORE TRANSACTION
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => 'required|integer|exists:users,id',
            'overall_price' => 'required|numeric|min:0',
            'cart'          => 'required|array',
            'cart.*.product_id' => 'required|integer|min:1',
            'cart.*.qty'        => 'required|integer|min:1',
        ]);

        Transaction::create([
            'customer_id'   => $validated['user_id'], 
            'overall_price' => $validated['overall_price'],
            'cart'          => json_encode($validated['cart']),
        ]);

        return redirect()->route('transactions.index')
                        ->with('success', 'Transaction created successfully.');
    }

    //LIST TRANSACTIONS
    public function index()
    {
        $transactions = Transaction::orderByDesc('created_at')->paginate(5);
        return view('transactions.index', compact('transactions'));
    }

    //SHOW SINGLE TRANSACTION
    public function show(Transaction $transaction)
    {
        return view('transactions.transaction', compact('transaction'));
    }

    // EDIT FORM
    public function edit(Transaction $transaction)
    {
        $users = User::where('type', 'customer')->get();
        return view('transactions.edit', compact('transaction', 'users'));
    }

    // UPDATE TRANSACTION
    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'user_id'       => 'required|integer|exists:users,id',
            'overall_price' => 'required|numeric|min:0',
            'cart'          => 'required|array',
            'cart.*.product_id' => 'required|integer|min:1',
            'cart.*.qty'        => 'required|integer|min:1',
        ]);

        $transaction->update([
            'customer_id'   => $validated['user_id'], 
            'overall_price' => $validated['overall_price'],
            'cart'          => json_encode($validated['cart']),
        ]);

        return redirect()->route('transactions.index')
                         ->with('success', 'Transaction updated successfully.');
    }

    // DELETE TRANSACTION
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->route('transactions.index')
                         ->with('success', 'Transaction deleted successfully.');
    }

}
