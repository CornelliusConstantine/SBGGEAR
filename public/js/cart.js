/**
 * SBGEAR E-commerce Cart Functionality
 * Handles cart operations like add, update, remove items and cart persistence
 */

class ShoppingCart {
    constructor() {
        this.token = localStorage.getItem('token') || '';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Get the current user's cart
     * @returns {Promise} Promise object with cart data
     */
    async getCart() {
        try {
            const response = await fetch('/api/cart', {
                headers: this.getHeaders()
            });

            if (!response.ok) {
                if (response.status === 401) {
                    this.handleUnauthorized();
                    return null;
                }
                const data = await response.json();
                throw new Error(data.message || 'An error occurred while fetching cart');
            }

            return await response.json();
        } catch (error) {
            console.error('Error fetching cart:', error);
            throw error;
        }
    }

    /**
     * Add an item to the cart
     * @param {number} productId - The product ID to add
     * @param {number} quantity - Quantity to add
     * @returns {Promise} Promise object with updated cart data
     */
    async addItem(productId, quantity) {
        try {
            const response = await fetch('/api/cart', {
                method: 'POST',
                headers: this.getHeaders(),
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            });

            if (!response.ok) {
                if (response.status === 401) {
                    this.handleUnauthorized();
                    return null;
                }
                const data = await response.json();
                throw new Error(data.message || 'An error occurred while adding item to cart');
            }

            const result = await response.json();
            this.updateCartCount(result.cart);
            return result;
        } catch (error) {
            console.error('Error adding item to cart:', error);
            throw error;
        }
    }

    /**
     * Update a cart item's quantity
     * @param {number} itemId - The cart item ID to update
     * @param {number} quantity - New quantity
     * @returns {Promise} Promise object with updated cart data
     */
    async updateItem(itemId, quantity) {
        try {
            const response = await fetch(`/api/cart/${itemId}`, {
                method: 'PUT',
                headers: this.getHeaders(),
                body: JSON.stringify({
                    quantity: quantity
                })
            });

            if (!response.ok) {
                if (response.status === 401) {
                    this.handleUnauthorized();
                    return null;
                }
                const data = await response.json();
                throw new Error(data.message || 'An error occurred while updating cart item');
            }

            const result = await response.json();
            this.updateCartCount(result.cart);
            return result;
        } catch (error) {
            console.error('Error updating cart item:', error);
            throw error;
        }
    }

    /**
     * Remove an item from the cart
     * @param {number} itemId - The cart item ID to remove
     * @returns {Promise} Promise object with updated cart data
     */
    async removeItem(itemId) {
        try {
            const response = await fetch(`/api/cart/${itemId}`, {
                method: 'DELETE',
                headers: this.getHeaders()
            });

            if (!response.ok) {
                if (response.status === 401) {
                    this.handleUnauthorized();
                    return null;
                }
                const data = await response.json();
                throw new Error(data.message || 'An error occurred while removing cart item');
            }

            const result = await response.json();
            this.updateCartCount(result.cart);
            return result;
        } catch (error) {
            console.error('Error removing cart item:', error);
            throw error;
        }
    }

    /**
     * Clear all items from the cart
     * @returns {Promise} Promise object with empty cart data
     */
    async clearCart() {
        try {
            const response = await fetch('/api/cart', {
                method: 'DELETE',
                headers: this.getHeaders()
            });

            if (!response.ok) {
                if (response.status === 401) {
                    this.handleUnauthorized();
                    return null;
                }
                const data = await response.json();
                throw new Error(data.message || 'An error occurred while clearing cart');
            }

            const result = await response.json();
            this.updateCartCount(result.cart);
            return result;
        } catch (error) {
            console.error('Error clearing cart:', error);
            throw error;
        }
    }

    /**
     * Check if a product has sufficient stock
     * @param {number} productId - The product ID to check
     * @param {number} quantity - Quantity to check
     * @returns {Promise} Promise object with stock status
     */
    async checkStock(productId, quantity) {
        try {
            const response = await fetch(`/api/products/${productId}`, {
                headers: this.getHeaders()
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'An error occurred while checking stock');
            }

            const product = await response.json();
            return {
                available: product.stock >= quantity,
                stock: product.stock,
                message: product.stock < quantity ? `Only ${product.stock} items available` : ''
            };
        } catch (error) {
            console.error('Error checking stock:', error);
            throw error;
        }
    }

    /**
     * Update the cart count in the navbar
     * @param {Object} cart - Cart object with items array
     */
    updateCartCount(cart) {
        const countElement = document.getElementById('cart-count');
        if (!countElement) return;

        const itemCount = cart && cart.items ? cart.items.length : 0;
        countElement.textContent = itemCount;
        countElement.style.display = itemCount > 0 ? 'inline-block' : 'none';
    }

    /**
     * Get common headers for API requests
     * @returns {Object} Headers object
     */
    getHeaders() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken,
            'Authorization': `Bearer ${this.token}`
        };
    }

    /**
     * Handle unauthorized responses (user not logged in)
     */
    handleUnauthorized() {
        // Clear token
        localStorage.removeItem('token');
        
        // Redirect to login if we're not already on the login page
        if (!window.location.pathname.includes('/login')) {
            window.location.href = `/login?redirect=${encodeURIComponent(window.location.href)}`;
        }
    }

    /**
     * Show a toast notification for cart actions
     * @param {string} type - 'success' or 'error'
     * @param {string} message - Message to display
     */
    showNotification(type, message) {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '5';
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        const toastId = `toast-${Date.now()}`;
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';

        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${icon} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        // Initialize and show toast
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();

        // Remove toast after it's hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
}

// Initialize cart instance
const cart = new ShoppingCart();

// Export for use in other scripts
window.ShoppingCart = ShoppingCart;
window.cart = cart; 