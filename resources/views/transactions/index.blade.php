@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">Transaction History</h2>
        {{-- Only Admin sees the manual add button --}}
        @if(auth()->check() && auth()->user()->type === 'admin')
            <a href="{{ route('transactions.create') }}" class="btn btn-success">Add New Transaction</a>
        @endif
        <a href="{{ url('/home') }}" class="btn btn-outline-info">Back to Home</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card bg-dark text-white border-secondary">
        <div class="card-body">
            <table class="table table-dark table-hover table-striped border-secondary">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer ID</th>
                        <th>Items Purchased</th>
                        <th>Overall Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_id }}</td>
                            <td>{{ $transaction->customer_id }}</td>
                            <td>
                                <ul class="mb-0 ps-3 small">
                                @php
                                    $cart = $transaction->cart;

                                    // support old rows where cart was JSON string
                                    if (is_string($cart)) {
                                        $cart = json_decode($cart, true) ?? [];
                                    }
                                @endphp

                                @if(is_array($cart))
                                    @foreach($cart as $item)
                                        @php
                                            // support both product_id and id
                                            $pid = trim((string)($item['product_id'] ?? $item['id'] ?? ''));
                                            $name = $pid !== '' ? ($productMap[$pid] ?? 'Unknown Product') : 'Unknown Product';
                                            $qty  = $item['qty'] ?? $item['quantity'] ?? 0;
                                        @endphp

                                        <li>
                                            <strong>{{ $name }}</strong><br>
                                            <span class="text-info">Qty: {{ $qty }}</span>
                                            <div class="text-muted small">ID: {{ $pid }}</div>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="text-danger">Invalid Cart Data</li>
                                @endif
                                </ul>

                            </td>
                            <td>${{ number_format($transaction->overall_price, 2) }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-warning btn-sm">View</a>
                                    <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-outline-info btn-sm">Edit</a>

                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No Transactions Found!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end mt-4">
                {{ $transactions->links('vendor.pagination.bootstrap-5-dark') }}
            </div>
        </div>
    </div>
</div>
@endsection
