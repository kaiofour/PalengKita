@extends('layouts.app')

@section('content')
    <div class="container">
        @auth
            @if(Auth::user()->type === 'admin')
                <div>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-primary">Customers</a>
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-primary">Transactions</a>
                </div>
            @endif
        @endauth
    </div>
@endsection
