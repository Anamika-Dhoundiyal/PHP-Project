// Cart functionality for FreshMart

// Get cart from localStorage
function getCart() {
    return JSON.parse(localStorage.getItem('cart') || '[]');
}

// Save cart to localStorage
function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

// Add item to cart
function addToCart(productId, productName, price, image = '') {
    const cart = getCart();
    
    // Check if product already exists in cart
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: parseFloat(price),
            image: image,
            quantity: 1
        });
    }
    
    saveCart(cart);
    showToast('Product added to cart successfully!', 'success');
}

// Remove item from cart
function removeFromCart(productId) {
    let cart = getCart();
    cart = cart.filter(item => item.id !== productId);
    saveCart(cart);
    showToast('Product removed from cart', 'info');
}

// Update item quantity
function updateQuantity(productId, quantity) {
    const cart = getCart();
    const item = cart.find(item => item.id === productId);
    
    if (item) {
        if (quantity <= 0) {
            removeFromCart(productId);
        } else {
            item.quantity = quantity;
            saveCart(cart);
        }
    }
}

// Calculate cart total
function calculateTotal() {
    const cart = getCart();
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// Display cart items
function displayCart() {
    const cart = getCart();
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalElement = document.getElementById('cart-total');
    
    if (cartItemsContainer) {
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<p>Your cart is empty</p>';
            if (cartTotalElement) {
                cartTotalElement.textContent = '₹0.00';
            }
            return;
        }
        
        let html = '';
        cart.forEach(item => {
            html += `
                <div class="cart-item">
                    <img src="${item.image}" alt="${item.name}" style="width: 50px; height: 50px;">
                    <div class="item-details">
                        <h6>${item.name}</h6>
                        <p>₹${item.price.toFixed(2)}</p>
                    </div>
                    <div class="item-quantity">
                        <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                        <span>${item.quantity}</span>
                        <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                    </div>
                    <div class="item-total">
                        ₹${(item.price * item.quantity).toFixed(2)}
                    </div>
                    <button onclick="removeFromCart(${item.id})" class="btn-remove">×</button>
                </div>
            `;
        });
        
        cartItemsContainer.innerHTML = html;
        if (cartTotalElement) {
            cartTotalElement.textContent = '₹' + calculateTotal().toFixed(2);
        }
    }
}

// Clear cart
function clearCart() {
    localStorage.removeItem('cart');
    updateCartCount();
    showToast('Cart cleared', 'info');
}

// Update cart count in navbar
function updateCartCount() {
    const cart = getCart();
    const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
    
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        if (element) {
            element.textContent = cartCount;
        }
    });
}

// Show toast notification
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container');
    const toastTemplate = document.getElementById('toast-template');
    
    if (toastContainer && toastTemplate) {
        const toast = toastTemplate.cloneNode(true);
        toast.style.display = 'block';
        toast.id = '';
        
        const toastMessage = toast.querySelector('.toast-message');
        if (toastMessage) {
            toastMessage.textContent = message;
        }
        
        // Set icon based on type
        const icon = toast.querySelector('.toast-header i');
        if (icon) {
            icon.className = type === 'success' ? 'fas fa-check-circle text-success me-2' : 
                             type === 'info' ? 'fas fa-info-circle text-info me-2' : 
                             'fas fa-exclamation-circle text-danger me-2';
        }
        
        toastContainer.appendChild(toast);
        
        // Initialize Bootstrap toast
        if (typeof bootstrap !== 'undefined') {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remove toast element after it's hidden
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }
    }
}

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    
    // If on cart page, display cart items
    if (document.getElementById('cart-items')) {
        displayCart();
    }
});