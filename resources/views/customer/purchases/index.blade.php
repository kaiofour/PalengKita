@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-white">Make a Purchase</h2>

    <div class="row">
        <!-- Products List -->
        <div class="col-md-8">
            <div class="card bg-dark text-white">
                <div class="card-header">Available Products</div>
                <div class="card-body">
                    <div id="products-list" class="row">
                        <!-- Products will be loaded here by JavaScript -->
                        <p>Loading products...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Bundle & Cart -->
        <div class="col-md-4">
            <div class="card bg-dark text-white mb-4">
                <div class="card-header">Current Bundle</div>
                <div class="card-body">
                    <ul id="current-bundle" class="list-group list-group-flush">
                        <li class="list-group-item bg-dark text-white" id="empty-bundle-message">No items in current bundle.</li>
                    </ul>
                    <div class="mt-3 text-end">
                        <button id="add-bundle-to-cart" class="btn btn-success" disabled>Add Bundle to Cart</button>
                    </div>
                </div>
            </div>

            <div class="card bg-dark text-white">
                <div class="card-header">Your Cart</div>
                <div class="card-body">
                    <ul id="customer-cart" class="list-group list-group-flush">
                        <li class="list-group-item bg-dark text-white" id="empty-cart-message">Your cart is empty.</li>
                    </ul>
                    <div class="mt-3 text-end">
                        <button id="checkout-button" class="btn btn-primary" disabled>Checkout</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let products = [];
    let currentBundle = {}; // { product_id: { product_name, quantity, price, overall_price } }
    let customerCart = []; // [ { bundle_id, items: { product_id: { product_name, quantity, price, overall_price } }, overall_price } ]
    let bundleCounter = 0;

    document.addEventListener('DOMContentLoaded', function() {
        fetchProducts(); 
        renderBundle();
        renderCart();

        document.getElementById('add-bundle-to-cart').addEventListener('click', addToCart);
        document.getElementById('checkout-button').addEventListener('click', checkout);
    });

    function fetchProducts() {
        fetch('{{ route('customer.api.products') }}')
            .then(response => response.json())
            .then(data => {
                products = data;
                renderProducts();
            })
            .catch(error => console.error('Error fetching products:', error));
    }

    function renderProducts() {
        const productsList = document.getElementById('products-list');
        productsList.innerHTML = '';

        if (products.length === 0) {
            productsList.innerHTML = '<p>No products available.</p>';
            return;
        }

        products.forEach(product => {
            const productCard = `
                <div class="col-md-6 mb-4">
                    <div class="card bg-secondary text-white h-100">
                        <div class="card-body">
                            <h5 class="card-title">${product.product_name}</h5>
                            <p class="card-text">Supplier: ${product.supplier}</p>
                            <p class="card-text">Price: $${product.price.toFixed(2)}</p>
                            <p class="card-text">Available: ${product.quantity}</p>
                            <div class="d-flex align-items-center">
                                <input type="number" class="form-control w-25 me-2" value="1" min="1" max="${product.quantity}" id="quantity-${product.product_id}">
                                <button class="btn btn-primary add-to-bundle-btn" data-product-id="${product.product_id}">Add to Bundle</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            productsList.innerHTML += productCard;
        });

        document.querySelectorAll('.add-to-bundle-btn').forEach(button => {
            button.addEventListener('click', addProductToBundle);
        });
    }

    function addProductToBundle(event) {
        const productId = event.target.dataset.productId;
        const quantityInput = document.getElementById(`quantity-${productId}`);
        const quantity = parseInt(quantityInput.value);
        const product = products.find(p => p.product_id === productId);

        if (!product || quantity <= 0 || quantity > product.quantity) {
            alert('Invalid quantity or product not found.');
            return;
        }

        if (currentBundle[productId]) {
            currentBundle[productId].quantity += quantity;
            currentBundle[productId].overall_price = currentBundle[productId].quantity * currentBundle[productId].price;
        } else {
            currentBundle[productId] = {
                product_name: product.product_name,
                quantity: quantity,
                price: product.price,
                overall_price: quantity * product.price
            };
        }
        renderBundle();
    }

    function renderBundle() {
        const bundleList = document.getElementById('current-bundle');
        bundleList.innerHTML = '';
        let totalBundlePrice = 0;
        let hasItems = false;

        for (const productId in currentBundle) {
            hasItems = true;
            const item = currentBundle[productId];
            totalBundlePrice += item.overall_price;
            const bundleItem = `
                <li class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">
                    ${item.product_name} (${item.quantity}x) - $${item.overall_price.toFixed(2)}
                    <div>
                        <button class="btn btn-sm btn-info me-1 update-bundle-quantity" data-product-id="${productId}" data-action="increase">+</button>
                        <button class="btn btn-sm btn-warning me-1 update-bundle-quantity" data-product-id="${productId}" data-action="decrease">-</button>
                        <button class="btn btn-sm btn-danger remove-from-bundle-btn" data-product-id="${productId}">Remove</button>
                    </div>
                </li>
            `;
            bundleList.innerHTML += bundleItem;
        }

        if (!hasItems) {
            bundleList.innerHTML = '<li class="list-group-item bg-dark text-white" id="empty-bundle-message">No items in current bundle.</li>';
            document.getElementById('add-bundle-to-cart').disabled = true;
        } else {
            bundleList.innerHTML += `
                <li class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">
                    <strong>Bundle Total:</strong>
                    <strong>$${totalBundlePrice.toFixed(2)}</strong>
                </li>
            `;
            document.getElementById('add-bundle-to-cart').disabled = false;
        }

        document.querySelectorAll('.remove-from-bundle-btn').forEach(button => {
            button.addEventListener('click', removeProductFromBundle);
        });
        document.querySelectorAll('.update-bundle-quantity').forEach(button => {
            button.addEventListener('click', updateBundleQuantity);
        });
    }

    function updateBundleQuantity(event) {
        const productId = event.target.dataset.productId;
        const action = event.target.dataset.action;
        const product = products.find(p => p.product_id === productId);

        if (!currentBundle[productId] || !product) return;

        if (action === 'increase') {
            if (currentBundle[productId].quantity < product.quantity) {
                currentBundle[productId].quantity++;
            }
        } else if (action === 'decrease') {
            if (currentBundle[productId].quantity > 1) {
                currentBundle[productId].quantity--;
            } else {
                // If quantity becomes 0, remove the item from the bundle
                delete currentBundle[productId];
            }
        }
        if (currentBundle[productId]) {
            currentBundle[productId].overall_price = currentBundle[productId].quantity * currentBundle[productId].price;
        }
        renderBundle();
    }

    function removeProductFromBundle(event) {
        const productId = event.target.dataset.productId;
        delete currentBundle[productId];
        renderBundle();
    }

    function addToCart() {
        if (Object.keys(currentBundle).length === 0) {
            alert('Bundle is empty!');
            return;
        }

        bundleCounter++;
        const bundleId = `bundle-${bundleCounter}`;
        let bundleOverallPrice = 0;
        for (const productId in currentBundle) {
            bundleOverallPrice += currentBundle[productId].overall_price;
        }

        customerCart.push({
            bundle_id: bundleId,
            items: { ...currentBundle }, // Deep copy
            overall_price: bundleOverallPrice
        });

        currentBundle = {}; // Clear current bundle after adding to cart
        renderBundle();
        renderCart();
    }

    function renderCart() {
        const cartList = document.getElementById('customer-cart');
        cartList.innerHTML = '';
        let totalCartPrice = 0;

        if (customerCart.length === 0) {
            cartList.innerHTML = '<li class="list-group-item bg-dark text-white" id="empty-cart-message">Your cart is empty.</li>';
            document.getElementById('checkout-button').disabled = true;
            return;
        }

        customerCart.forEach((bundle, index) => {
            totalCartPrice += bundle.overall_price;
            let bundleItemsHtml = '';
            for (const productId in bundle.items) {
                const item = bundle.items[productId];
                bundleItemsHtml += `<li>${item.product_name} (${item.quantity}x) - $${item.overall_price.toFixed(2)}</li>`;
            }

            const cartBundle = `
                <li class="list-group-item bg-dark text-white mb-2 border-bottom">
                    <strong>Bundle ${index + 1}</strong> (Total: $${bundle.overall_price.toFixed(2)})
                    <ul class="list-unstyled ms-3">
                        ${bundleItemsHtml}
                    </ul>
                    <div class="text-end">
                        <button class="btn btn-sm btn-danger remove-bundle-from-cart-btn" data-bundle-index="${index}">Remove Bundle</button>
                    </div>
                </li>
            `;
            cartList.innerHTML += cartBundle;
        });

        cartList.innerHTML += `
            <li class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">
                <strong>Cart Total:</strong>
                <strong>$${totalCartPrice.toFixed(2)}</strong>
            </li>
        `;
        document.getElementById('checkout-button').disabled = false;

        document.querySelectorAll('.remove-bundle-from-cart-btn').forEach(button => {
            button.addEventListener('click', removeBundleFromCart);
        });
    }

    function removeBundleFromCart(event) {
        const bundleIndex = parseInt(event.target.dataset.bundleIndex);
        customerCart.splice(bundleIndex, 1);
        renderCart();
    }

    function checkout() {
        if (customerCart.length === 0) {
            alert('Your cart is empty. Add items before checking out.');
            return;
        }

        // Simulate sending cart data to the external Inventory System via WebSockets
        console.log('Checking out with cart:', customerCart);
        alert('Checkout simulated! See console for cart data.');

        // Clear cart after simulated checkout
        customerCart = [];
        renderCart();
    }
</script>
@endsection
