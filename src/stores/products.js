import { defineStore } from 'pinia';

import { isBefore, parseISO, addDays } from 'date-fns';
import api from '@/config/axios-config';

const LOW_STOCK_THRESHOLD = 5;
export const useProductsStore = defineStore('products', {
    state: () => ({
        products: [],
        isLoading: false,
        errors: null,
        isUpdating: false,
        isAdding: false,
        isEditing: false,
        isDeleting: {}
    }),

    getters: {
        productsList: (state) => state.products,
        productsCount: (state) => state.products.length,
        findProduct: (state) => (id) => state.products.find((product) => product.id === id),

        lowStockProducts: (state) => state.products.filter((product) => product.quantity <= LOW_STOCK_THRESHOLD),
        inStockProducts: (state) => state.products.filter((product) => product.quantity > 0),
        inStockProductsCount: (state) => state.products.filter((product) => product.quantity > 0).length,
        outOfStockProducts: (state) => state.products.filter((product) => product.quantity === 0),
        outOfStockProductsCount: (state) => state.products.filter((product) => product.quantity === 0).length,

        expiredProducts: (state) => 
            state.products.filter((product) => product.expiry_date && isBefore(parseISO(product.expiry_date), new Date())),
        expiredProductsCount: (state) =>
            state.products.filter((product) => product.expiry_date && isBefore(parseISO(product.expiry_date), new Date())).length,

        aboutToExpiredProducts: (state) => {
            const expiryThresholdDays = 7; // Define how many days before expiry is considered "about to expire"
            return state.products.filter((product) => {
                if (product.expiry_date) {
                    const expiry_date = parseISO(product.expiry_date);
                    const thresholdDate = addDays(new Date(), expiryThresholdDays);
                    return isBefore(expiry_date, thresholdDate) && isBefore(new Date(), expiry_date);
                }
                return false;
            });
        },
        aboutToExpiredProductsCount: (state) => {
            return this.aboutToExpiredProducts.length
        },

        productsAmount: (state) => state.products.reduce(
            (sum, product) => sum + ((Number(product.quantity) === 0 ? Number(product.quantity) + 1 : Number(product.quantity)) * product.current_price?.price), 0),

        inStockProductsAmount: (state) => {
            return state.products.filter((product) => Number(product.quantity) > 0).reduce((sum, product) => {
                const quantity = Number(product.quantity) || 0; // Ensure quantity is numeric
                const price = Number(product.current_price?.price) || 0; // Ensure price is numeric
                return sum + (quantity * price);
            }, 0.00);
        },
    },

    actions: {
        async fetchProducts() {
            this.isLoading = true;
            this.errors = null;
            try {
                const response = await api.get('/products'); // Replace '/products' with your actual API endpoint
                // console.log(response.data);
                this.products = response.data.data; // Assuming the API returns an array of products
            } catch (error) {
                this.errors = error.response?.data?.message || 'Failed to fetch products.';
                console.error('Fetch products error:', error);
            } finally {
                this.isLoading = false;
            }
        },

        async addProduct(product = {}) {
            this.isAdding = true;
            this.errors = null;
            try {
                const response = await api.post('/products', product); // Replace '/products' with your actual API endpoint
                this.products.unshift(response.data.data); // Assuming the API returns the newly created product
            } catch (error) {
                this.errors = error.response?.data?.errors || 'Failed to add product.';
                console.error('Add product error:', error);
                throw error; // Re-throw to handle in the component
            } finally {
                this.isAdding = false;
            }
        },

        async updateProduct(productId, productData = {}) {
            this.isEditing = true;
            this.errors = null;
            if(!productId) return;
            try {
                const response = await api.put(`/products/${productId}`, productData); // Replace '/products' with your actual API endpoint
                const index = this.products.findIndex(product => product.id === productId);
                if (index !== -1) {
                    this.products[index] = response.data.data;
                }
            } catch (error) {
                this.errors = error.response?.data?.errors || 'Failed to update product.';
                console.error('Update product error:', error);
                throw error; // Re-throw to handle in the component
            } finally {
                this.isEditing = false;
            }
        },

        async deleteProduct(productId) {
            this.isDeleting[productId] = true;
            this.errors = null;
            try {
              await api.delete(`/products/${productId}`); // Replace '/products' with your actual API endpoint
              this.products = this.products.filter((product) => product.id !== productId);
            } catch (error) {
              this.errors = error.response?.data?.message || 'Failed to delete product.';
              console.error('Delete product error:', error);
              throw error; // Re-throw to handle in the component
            } finally {
              this.isDeleting[productId] = false;
            }
        },

        async increaseStock(productId, quantity) {
            const product = this.products.find((p) => p.id === productId);
            this.errors = null;
            this.isUpdating = true;
            try {
                const response = await api.put(`/products/update-quantity/${productId}`, { quantity });
                product.quantity = response.data.quantity;
                return response.data;
            } catch (error) {
                this.errors = error.response?.data?.errors || 'Failed to update product.';
                console.error('Update product error:', error);
                throw error; // Re-throw to handle in the component
            } finally {
                this.isUpdating = false;
            }
        },

        async decreaseStock(productId, quantity) {
            const product = this.products.find((p) => p.id === productId);
            if (product && product.quantity >= quantity) {
                product.quantity -= quantity;
            } else if (product) {
                this.errors = `Not enough stock for product ${product.name}.`;
            } else {
                this.errors = `Product with ID ${productId} not found.`;
            }
            // You might want to call an API endpoint to update the stock on the server as well
            // await api.patch(`/products/${productId}/decrease-stock`, { quantity });
        },
    },
}
);