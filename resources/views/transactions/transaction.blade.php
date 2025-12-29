@extends('layouts.app')
@section('title', 'Transaction Details')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-6 offset-3">

            <h1 class="text-white">Transaction Details</h1>

            <div class="card bg-dark text-white mt-4">
                <div class="card-body border border-success rounded">

                    <h5 class="card-title">
                        <strong>Transaction ID:</strong> {{ $transaction->transaction_id }}
                    </h5>

                    <p class="card-text">
                        <strong>Customer ID:</strong>
                        {{ $transaction->customer_id }}
                    </p>

                    <p class="card-text">
                        <strong>Overall Price:</strong> 
                        ${{ number_format($transaction->overall_price, 2) }}
                    </p>

                    {{-- 
                        ✅ FIXED: Removed json_decode. 
                        We use $transaction->cart directly because the Model casts it to an array.
                    --}}
                    <p class="card-text"><strong>Cart:</strong></p>

                    <ul class="list-group bg-dark">
                        {{-- Safety check to ensure cart is not null --}}
                        @if(is_array($transaction->cart) || is_object($transaction->cart))
                            @foreach($transaction->cart as $item)
                                <li class="list-group-item bg-secondary text-white">
                                    Product ID: {{ $item['product_id'] ?? $item['id'] ?? 'N/A' }}  
                                    <br>
                                    Quantity: ({{ $item['qty'] ?? $item['quantity'] ?? 0 }}x)
                                </li>
                            @endforeach
                        @else
                            <li class="list-group-item bg-danger text-white">
                                Error: Invalid cart data.
                            </li>
                        @endif
                    </ul>

                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary mt-3">
                        Back to List
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection