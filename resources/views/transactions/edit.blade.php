@extends('layouts.app')
@section('title', 'Edit Transaction')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card bg-dark text-white border-secondary">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <span>{{ __('Edit Transaction') }} #{{ $transaction->transaction_id }}</span>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-light">Cancel</a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.update', $transaction->transaction_id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="user_id" class="col-md-3 col-form-label text-md-end">{{ __('Assign User') }}</label>
                            <div class="col-md-6">
                                <select id="user_id" name="user_id" class="form-control bg-secondary text-white border-0 @error('user_id') is-invalid @enderror" required>
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $transaction->customer_id == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="overall_price" class="col-md-3 col-form-label text-md-end">{{ __('Overall Price ($)') }}</label>
                            <div class="col-md-6">
                                <input id="overall_price" type="number" step="0.01" 
                                       class="form-control bg-secondary text-white border-0 @error('overall_price') is-invalid @enderror" 
                                       name="overall_price" 
                                       value="{{ old('overall_price', $transaction->overall_price) }}" required>
                                @error('overall_price')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <hr class="border-secondary">

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label text-md-end">{{ __('Cart Items') }}</label>
                            <div class="col-md-8" id="cart-items">
                                @php
                                    // FIX: Directly use the array from the model
                                    $cart = $transaction->cart ?? [];
                                @endphp

                                @foreach($cart as $index => $item)
                                    <div class="row mb-2 cart-item">
                                        <div class="col-md-6">
                                            <label class="small text-muted">Product ID</label>
                                            <input type="number" name="cart[{{ $index }}][product_id]" 
                                                   class="form-control bg-secondary text-white border-0" 
                                                   value="{{ $item['product_id'] ?? $item['id'] }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Quantity</label>
                                            <input type="number" name="cart[{{ $index }}][qty]" 
                                                   class="form-control bg-secondary text-white border-0" 
                                                   value="{{ $item['qty'] ?? $item['quantity'] }}" required>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-8 offset-md-3">
                                <button type="button" class="btn btn-outline-info btn-sm" id="add-item">
                                    + Add Another Item
                                </button>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ __('Update Transaction') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize index based on current count so we don't overwrite existing items
    let index = {{ count($cart) }};

    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('cart-items');
        const newItem = document.createElement('div');
        newItem.classList.add('row', 'mb-2', 'cart-item', 'mt-3');
        
        // Note: Added classes bg-secondary text-white to match the theme
        newItem.innerHTML = `
            <div class="col-md-6">
                <input type="number" name="cart[${index}][product_id]" placeholder="Product ID" class="form-control bg-secondary text-white border-0" required>
            </div>
            <div class="col-md-4">
                <input type="number" name="cart[${index}][qty]" placeholder="Quantity" class="form-control bg-secondary text-white border-0" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.cart-item').remove()">x</button>
            </div>
        `;
        container.appendChild(newItem);
        index++;
    });
</script>
@endsection