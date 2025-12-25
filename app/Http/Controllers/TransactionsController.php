<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TransactionsController extends Controller
{
    // ==========================================
    //      CUSTOMER MARKETPLACE & CHECKOUT
    // ==========================================

    /**
     * Display the purchase page for customers.
     */
    public function marketplace()
    {
        return view('customer.purchases.index');
    }

    /**
     * Handle the checkout process.
     * Updates Supabase stock and saves to local MySQL.
     */
    public function checkout(Request $request)
    {
        // 1. Ensure user is logged in
        if (!Auth::check()) {
            return response()->json(['error' => 'Your session has expired. Please sign in again.'], 401);
        }

        $cart = $request->input('cart');
        
        // 2. Fix the URL logic to prevent "Could not resolve host: rest"
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/');
        $apiKey = env('SUPABASE_ANON_KEY');
        
        if (empty($supabaseUrl) || empty($apiKey)) {
            Log::error("Checkout Error: Supabase credentials missing in .env file.");
            return response()->json(['error' => 'Server configuration error.'], 500);
        }

        $headers = [
            'apikey' => $apiKey,
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=minimal'
        ];

        $totalTransactionPrice = 0;
        $localCartData = [];

        try {
            foreach ($cart as $bundle) {
                foreach ($bundle['items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'];
                    
                    // A. Update Supabase Stock (So WebSockets update the UI)
                    // We combine the base URL and path properly here
                    $productResponse = Http::withHeaders($headers)
                        ->get($supabaseUrl . '/rest/v1/products?product_id=eq.' . $productId);
                    
                    if ($productResponse->successful() && !empty($productResponse->json())) {
                        $currentProduct = $productResponse->json()[0];
                        $newStock = max(0, $currentProduct['quantity'] - $item['quantity']);

                        Http::withHeaders($headers)
                            ->patch($supabaseUrl . '/rest/v1/products?product_id=eq.' . $productId, [
                                'quantity' => $newStock
                            ]);
                    }

                    // B. Prepare data for the local DB
                    $totalTransactionPrice += $item['overall_price'];
                    
                    $localCartData[] = [
                        'product_id' => (string)$productId,
                        'qty' => (string)$item['quantity']
                    ];
                }
            }

            // C. Save to Local DB
            Transaction::create([
                'customer_id'   => Auth::id(), 
                'overall_price' => $totalTransactionPrice,
                'cart'          => $localCartData, 
            ]);

            return response()->json(['message' => 'Checkout successful']);

        } catch (\Exception $e) {
            Log::error("Checkout Error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    //      ADMIN TRANSACTION MANAGEMENT
    // ==========================================

    // DISPLAY FORM
    public function create()
    {
        $users = User::where('type', 'customer')->get();
        return view('transactions.create', compact('users'));
    }

    // STORE TRANSACTION (Admin Manual)
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
            'cart'          => $validated['cart'],
        ]);

        return redirect()->route('transactions.index')
                        ->with('success', 'Transaction created successfully.');
    }

    // LIST TRANSACTIONS
    public function index()
    {
        $transactions = Transaction::orderByDesc('created_at')->paginate(5);
        return view('transactions.index', compact('transactions'));
    }

    // SHOW SINGLE TRANSACTION
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
            'cart'          => $validated['cart'],
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