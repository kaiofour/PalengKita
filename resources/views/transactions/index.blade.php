@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4 text-white">Transactions List</h2>

        <div class="container">
            {{-- <a href="{{ route('transactions.signup') }}" class="btn btn-outline-info mb-3">Add Transaction</a> --}}
            <a href="{{ url('/home') }}" class="btn btn-outline-info mb-3">Back</a>
        </div>

        @session('success')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ $value }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endsession

        <table class="table table-bordered table-dark table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Transaction</th>
                    <th>Cart</th>
                    <th>Overall Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_id }}</td>
                        <td>{{ $transaction->transaction_name }}</td>
                        <td>
                            @php
                                $cart = json_decode($transaction->cart, true);
                            @endphp

                            <ul>
                                @foreach($cart as $item)
                                    <li id="{{ $item['bundle_id'] }}">
                                        Name: {{ $item['product_id'] }} ({{ $item['quantity'] }}x) = ${{ $item['overall_price'] }}
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td>{{ $transaction->overall_price }}</td>
                        <td>
                            {{-- <a href="{{ route('transactions.displaytransaction', $transaction->transaction_id) }}" class="btn btn-outline-warning">View</a>
                            <a href="{{ route('transactions.edit', $transaction->transaction_id) }}" class="btn btn-outline-info">Edit</a>
                            <form action="{{ route('transactions.delete', $transaction->transaction_id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="btn btn-outline-danger"
                                onClick="return confirm('Are you sure?')"
                            >Delete</button>
                            </form> --}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No Transactions Found!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-end mt-4">
            {{$transactions->links('vendor.pagination.bootstrap-5-dark')}}
        </div>

    </div>
@endsection
