@extends('layouts.app')
@section('title', 'Customer Details')
@section('content')
    <div class="container mt-4">
        <div clas="row">
            <div class="col-md-6 offset-3">
                <h1 class="text-white">Customer Details</h1>
                <div class="card bg-dark text-white mt-4">
                    <div class="card-body border border-success rounded">
                        <h5 class="card-title"><strong>Name:</strong> {{ $customer->name }} </h5>
                        <p class="card-text"><strong>Email:</strong> {{ $customer->email }} </p>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>

@endsection
