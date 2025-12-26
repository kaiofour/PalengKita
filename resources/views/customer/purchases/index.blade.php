@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-white">Make a Purchase</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="card bg-dark text-white border-secondary">
                <div class="card-header border-secondary">Available Products</div>
                <div class="card-body">
                    <div id="products-list" class="row">
                        <p class="text-white">Connecting to Live Inventory...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-dark text-white mb-4 border-secondary">
                <div class="card-header border-secondary">Current Bundle</div>
                <div class="card-body">
                    <ul id="current-bundle" class="list-group list-group-flush">
                        <li class="list-group-item bg-dark text-white" id="empty-bundle-message">No items in current bundle.</li>
                    </ul>
                    <div class="mt-3 text-end">
                        <button id="add-bundle-to-cart" class="btn btn-success w-100" disabled>Add Bundle to Cart</button>
                    </div>
                </div>
            </div>

            <div class="card bg-dark text-white border-secondary">
                <div class="card-header border-secondary">Your Cart</div>
                <div class="card-body">
                    <ul id="customer-cart" class="list-group list-group-flush">
                        <li class="list-group-item bg-dark text-white" id="empty-cart-message">Your cart is empty.</li>
                    </ul>
                    <div class="mt-3 text-end">
                        <button 
                            id="checkout-button" 
                            type="button" 
                            class="btn btn-primary w-100" 
                            disabled
                        >
                            Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let products = [];
    let currentBundle = {}; 
    let customerCart = []; 
    let bundleCounter = 0;

    document.addEventListener('DOMContentLoaded', function() {
        connectToWebSocket();
        renderBundle();
        renderCart();

        document.getElementById('add-bundle-to-cart').addEventListener('click', addToCart);
        // FIX: Pass the event object so we can stop default behavior
        document.getElementById('checkout-button').addEventListener('click', (e) => checkout(e));
    });

    // 1. WEBSOCKET CONNECTION
    function connectToWebSocket() {
        const ws = new WebSocket('ws://localhost:8080');

        ws.onopen = () => {
            console.log("Connected to PalengkePro Live Inventory");
        };

        ws.onmessage = (event) => {
            try {
                const liveProducts = JSON.parse(event.data);
                console.log("Stock Update Received:", liveProducts);
                products = liveProducts;
                renderProducts();
            } catch (e) {
                console.error("Error parsing WebSocket data:", e);
            }
        };

        ws.onclose = () => {
            console.log("Disconnected. Reconnecting...");
            setTimeout(connectToWebSocket, 3000);
        };
    }

    // 2. RENDER PRODUCTS
    function renderProducts() {
        const productsList = document.getElementById('products-list');
        productsList.innerHTML = '';

        if (!products || products.length === 0) {
            productsList.innerHTML = '<p class="text-white ms-3">Waiting for stock data...</p>';
            return;
        }

        products.forEach(product => {
            const id = product.product_id || product.id;
            const name = product.product_name || product.name;
            const qty = product.quantity;
            const price = parseFloat(product.price);

            const productCard = `
                <div class="col-md-6 mb-4">
                    <div class="card bg-secondary text-white h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">${name}</h5>
                            <p class="card-text mb-1 small">Supplier: ${product.supplier || 'N/A'}</p>
                            <p class="card-text mb-1">Price: $${price.toFixed(2)}</p>
                            <p class="card-text fw-bold ${qty > 0 ? 'text-info' : 'text-danger'}">
                                Available: ${qty}
                            </p>
                            <div class="d-flex align-items-center mt-3">
                                <input type="number" class="form-control w-25 me-2 bg-dark text-white border-0" 
                                    value="1" min="1" max="${qty}" id="quantity-${id}">
                                <button class="btn btn-primary btn-sm flex-grow-1 add-to-bundle-btn" 
                                    data-product-id="${id}"
                                    ${qty <= 0 ? 'disabled' : ''}>
                                    ${qty <= 0 ? 'Out of Stock' : 'Add to Bundle'}
                                </button>
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

    // 3. BUNDLE LOGIC
    function addProductToBundle(event) {
        const btn = event.currentTarget;
        const productId = btn.getAttribute('data-product-id');
        const quantityInput = document.getElementById(`quantity-${productId}`);
        
        if (!quantityInput) {
            console.error(`Missing input for ID: quantity-${productId}`);
            return;
        }

        const quantity = parseInt(quantityInput.value);
        const product = products.find(p => (p.product_id || p.id) == productId);

        if (!product || quantity <= 0 || quantity > product.quantity) {
            alert('Check your quantity! It might be more than what is available.');
            return;
        }

        if (currentBundle[productId]) {
            currentBundle[productId].quantity += quantity;
            currentBundle[productId].overall_price = currentBundle[productId].quantity * parseFloat(product.price);
        } else {
            currentBundle[productId] = {
                product_id: productId,
                product_name: product.product_name || product.name,
                quantity: quantity,
                price: parseFloat(product.price),
                overall_price: quantity * parseFloat(product.price)
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
            
            bundleList.innerHTML += `
                <li class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center border-secondary small">
                    <div>
                        <strong>${item.product_name}</strong><br>
                        ${item.quantity} x $${item.price.toFixed(2)}
                    </div>
                    <button class="btn btn-sm btn-outline-danger border-0 remove-from-bundle-btn" data-product-id="${productId}">×</button>
                </li>
            `;
        }

        if (!hasItems) {
            bundleList.innerHTML = '<li class="list-group-item bg-dark text-white border-0">No items in bundle.</li>';
            document.getElementById('add-bundle-to-cart').disabled = true;
        } else {
            bundleList.innerHTML += `<li class="list-group-item bg-secondary text-white text-end fw-bold">Bundle Total: $${totalBundlePrice.toFixed(2)}</li>`;
            document.getElementById('add-bundle-to-cart').disabled = false;
        }

        document.querySelectorAll('.remove-from-bundle-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                delete currentBundle[e.currentTarget.getAttribute('data-product-id')];
                renderBundle();
            });
        });
    }

    function addToCart() {
        if (Object.keys(currentBundle).length === 0) return;

        bundleCounter++;
        let bundleOverallPrice = 0;
        let itemsArray = [];

        for (const key in currentBundle) {
            bundleOverallPrice += currentBundle[key].overall_price;
            itemsArray.push(currentBundle[key]);
        }

        customerCart.push({
            bundle_id: `bundle-${bundleCounter}`,
            items: itemsArray,
            overall_price: bundleOverallPrice
        });

        currentBundle = {};
        renderBundle();
        renderCart();
    }

    function renderCart() {
        const cartList = document.getElementById('customer-cart');
        cartList.innerHTML = '';
        let totalCartPrice = 0;

        if (customerCart.length === 0) {
            cartList.innerHTML = '<li class="list-group-item bg-dark text-white border-0">Your cart is empty.</li>';
            document.getElementById('checkout-button').disabled = true;
            return;
        }

        customerCart.forEach((bundle, index) => {
            totalCartPrice += bundle.overall_price;
            const itemsHtml = bundle.items.map(i => `<li>${i.product_name} (${i.quantity}x)</li>`).join('');

            cartList.innerHTML += `
                <li class="list-group-item bg-dark text-white mb-2 border-secondary">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong>Bundle ${index + 1}</strong>
                        <span>$${bundle.overall_price.toFixed(2)}</span>
                    </div>
                    <ul class="ms-1 text-info small list-unstyled">${itemsHtml}</ul>
                    <div class="text-end">
                        <button class="btn btn-sm text-danger p-0 small remove-cart-bundle" data-index="${index}">Remove Bundle</button>
                    </div>
                </li>
            `;
        });

        cartList.innerHTML += `<li class="list-group-item bg-primary text-white text-end fw-bold mt-2">Grand Total: $${totalCartPrice.toFixed(2)}</li>`;
        document.getElementById('checkout-button').disabled = false;

        document.querySelectorAll('.remove-cart-bundle').forEach(btn => {
            btn.addEventListener('click', (e) => {
                customerCart.splice(e.currentTarget.getAttribute('data-index'), 1);
                renderCart();
            });
        });
    }

    // 4. CHECKOUT (FIXED FOR POST METHOD)
    async function checkout(event) {
        // Prevent default form submission (stops GET request error)
        if (event) event.preventDefault();

        if (customerCart.length === 0) return;

        const checkoutBtn = document.getElementById('checkout-button');
        const originalText = checkoutBtn.innerText;
        checkoutBtn.disabled = true;
        checkoutBtn.innerText = "Processing...";

        try {
            const response = await fetch("{{ route('customer.checkout') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ cart: customerCart })
            });

            // Parse result to see error message
            const result = await response.json();

            if (response.ok) {
                alert("Checkout Successful!");
                customerCart = []; 
                renderCart(); 
            } else {
                console.error("Checkout Failed:", result);
                alert("Checkout Failed: " + (result.error || "Unknown error"));
            }
        } catch (error) {
            console.error("Network Error:", error);
            alert("Connection error. Is the server running?");
        } finally {
            checkoutBtn.disabled = false;
            checkoutBtn.innerText = originalText;
        }
    }
</script>
@endsection