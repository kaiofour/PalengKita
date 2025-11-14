@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Add New Transaction') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf

                        <!-- Assign User -->
                        <div class="row mb-3">
                            <label for="user_id" class="col-md-2 col-form-label text-md-end">{{ __('Assign User') }}</label>
                            <div class="col-md-4">
                                <select id="user_id" name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <!-- Overall Price -->
                        <div class="row mb-3">
                            <label for="overall_price" class="col-md-2 col-form-label text-md-end">{{ __('Overall Price') }}</label>
                            <div class="col-md-4">
                                <input id="overall_price" type="number" step="0.01" class="form-control @error('overall_price') is-invalid @enderror" name="overall_price" value="{{ old('overall_price') }}" required>
                                @error('overall_price')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <!-- Cart Items -->
                        <div class="row mb-3">
                            <label class="col-md-2 col-form-label text-md-end">{{ __('Cart Items') }}</label>
                            <div class="col-md-8" id="cart-items">
                                <div class="row mb-2 cart-item">
                                    <div class="col-md-6">
                                        <input type="number" name="cart[0][product_id]" placeholder="Product ID" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" name="cart[0][qty]" placeholder="Quantity" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add More Button -->
                        <div class="row mb-3">
                            <div class="col-md-2 offset-md-2">
                                <button type="button" class="btn btn-secondary" id="add-item">Add Another Item</button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-2">
                                <button type="submit" class="btn btn-primary">{{ __('Add Transaction') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let index = 1;
document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('cart-items');
    const newItem = document.createElement('div');
    newItem.classList.add('row', 'mb-2', 'cart-item');
    newItem.innerHTML = `
        <div class="col-md-6">
            <input type="number" name="cart[${index}][product_id]" placeholder="Product ID" class="form-control" required>
        </div>
        <div class="col-md-4">
            <input type="number" name="cart[${index}][qty]" placeholder="Quantity" class="form-control" required>
        </div>
    `;
    container.appendChild(newItem);
    index++;
});
</script>
@endsection
