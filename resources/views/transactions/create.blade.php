@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Add New Transaction') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf

                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                            </ul>
                        </div>
                        @endif

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
                                <input
                                id="overall_price"
                                type="number"
                                step="0.01"
                                class="form-control"
                                name="overall_price"
                                readonly
                                >
                                @error('overall_price')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <!-- Cart Items -->
                        <div class="row mb-3">
                        <label class="col-md-2 col-form-label text-md-end">{{ __('Cart Items') }}</label>

                        <div class="col-md-8" id="cart-items">
                            <div class="row g-2 align-items-center mb-2 cart-item">
                            <div class="col-md-7">
                                <select name="cart[0][product_id]" class="form-control product-select" required style="width:100%">
                                <option value="">Search product...</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <input type="number" name="cart[0][qty]" placeholder="Qty" class="form-control" min="1" required>
                            </div>

                            <div class="col-md-2 d-grid">
                                <button type="button" class="btn btn-outline-danger remove-item" disabled>Remove</button>
                            </div>
                            </div>
                        </div>
                        </div>

                        <!-- Add More Button -->
                        <div class="row mb-3">
                            <div class="col-md-3 offset-md-2">
                                <button type="button" class="btn btn-secondary" id="add-item">+ Add Another Item</button>
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

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let index = 1;

    function initProductSelect($el) {
        $el.select2({
            openOnFocus: true,
            placeholder: "Search product...",
            allowClear: true,
            width: "100%",
            ajax: {
            url: "/api/products/search",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return { q: params.term || "" };
            },
            processResults: function (data) {
                return {
                results: (data || []).map(p => ({
                    id: p.product_id,
                    text: `${p.product_name}`,
                    price: p.price
                })),
                };
            },
            cache: true
            },
            minimumInputLength: 0
        });

        $el.on("select2:select", function (e) {
            const price = e.params.data.price || 0;
            const row = e.target.closest(".cart-item");

            row.dataset.price = price;
            calculateOverallPrice();
        });

        $el.on("select2:clear", function () {
            const row = this.closest(".cart-item");
            delete row.dataset.price;
            calculateOverallPrice();
        });
    }

    $(document).ready(function () {
    initProductSelect($(".product-select").first());
    });

    document.addEventListener("click", function (e) {
        if (!e.target.classList.contains("remove-item")) return;

        const row = e.target.closest(".cart-item");
        if (!row) return;

        const sel = row.querySelector(".product-select");
        if (sel) $(sel).select2("destroy");

        row.remove();
    });

    document.addEventListener("input", function (e) {
        if (e.target.name && e.target.name.includes("[qty]")) {
            calculateOverallPrice();
        }
    });

    document.getElementById("add-item").addEventListener("click", function () {
    const container = document.getElementById("cart-items");

    const newItem = document.createElement("div");
    newItem.classList.add("row", "g-2", "align-items-center", "mb-2", "cart-item");

        newItem.innerHTML = `
        <div class="col-md-7">
            <select name="cart[${index}][product_id]" class="form-control product-select" required style="width:100%">
            <option value="">Search product...</option>
            </select>
        </div>

        <div class="col-md-3">
            <input type="number" name="cart[${index}][qty]" placeholder="Qty" class="form-control" min="1" required>
        </div>

        <div class="col-md-2 d-grid">
            <button type="button" class="btn btn-outline-danger remove-item">Remove</button>
        </div>
        `;

    container.appendChild(newItem);
    initProductSelect($(newItem).find(".product-select"));
    index++;
    });

    function calculateOverallPrice() {
        let total = 0;

        document.querySelectorAll(".cart-item").forEach(row => {
            const price = parseFloat(row.dataset.price || 0);
            const qtyInput = row.querySelector('input[name$="[qty]"]');
            const qty = parseFloat(qtyInput?.value || 0);

            total += price * qty;
        });

        document.getElementById("overall_price").value = total.toFixed(2);
    }

</script>
@endpush
