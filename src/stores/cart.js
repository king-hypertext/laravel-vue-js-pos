import { defineStore, storeToRefs } from 'pinia';
import { ref, computed, watch } from 'vue';
import { useProductsStore } from './products';

export const useCartStore = defineStore('cart', () => {
    // State
    const _cart = ref(JSON.parse(localStorage.getItem('cart')) || []);
    const cart = computed(() => _cart.value);

    // Watch cart for changes and save to local storage
    watch(
        _cart,
        (newCart) => {
            localStorage.setItem('cart', JSON.stringify(newCart));
        },
        { deep: true }
    );
    const productList = useProductsStore();
    const { productsList } = storeToRefs(productList);
    // Getters
    const cartList = computed(() => {
        return _cart.value.map(({ productId, quantity }) => {
            const product = productsList.value.find(({ id }) => id === productId);
            return product
                ? {
                    id: product.id,
                    name: product.name,
                    price: product.current_price?.price,
                    quantity,
                    totalPrice: (product.current_price?.price * quantity).toFixed(2), // Calculate total price
                }
                : null;
        }).filter(Boolean); // Filter out any null items
    });
    //   computed(() => Object.values(cart.value));
    const cartTotal = computed(() => {
        // console.log('cart list:', cartList.value);
        
        return Number(cartList.value.reduce((total, item) => total + item?.price * item.quantity, 0.00)).toFixed(2);
    });
    const itemsCount = computed(() => {
        return cartList.value.length ?? 0;
    });

    // Actions
    function addItem(productId) {

        const product = productsList.value.find(({ id }) => id === productId);

        if (!product || product.quantity <= 0) {
            console.warn('Product is out of stock or does not exist.');
            return;
        }

        const existingItem = _cart.value.find(({ productId: id }) => id === productId);

        if (existingItem) {
            // Increment quantity if already in _cart and stock allows
            if (existingItem.quantity < product.quantity) {
                existingItem.quantity++;
            } else {
                console.warn('Not enough stock available.');
            }
        } else {
            // Add new product to the _cart
            _cart.value.push({ productId, quantity: 1 });
        }
    }

    function removeItem(productId) {
        _cart.value = _cart.value.filter(({ productId: id }) => id !== productId);
    }

    function clearCart() {
        _cart.value = []; // Clear the cart
        localStorage.setItem('cart', JSON.stringify(_cart.value)); // Update localStorage
        console.log('Cart has been cleared.');
    }

    function increaseQuantity(productId) {

        const cartItem = _cart.value.find(({ productId: id }) => id === productId);

        const product = productsList.value.find(({ id }) => id === productId);

        if (cartItem && product && cartItem.quantity < product.quantity) {
            cartItem.quantity++;
        } else {
            console.warn('Not enough stock available.');
        }
    }

    function decreaseQuantity(productId) {
        const cartItem = _cart.value.find(({ productId: id }) => id === productId);

        if (cartItem && cartItem.quantity > 1) {
            cartItem.quantity--;
        } else if (cartItem) {
            // Remove item if quantity is 1
            removeItem(productId);
        }
    }

    return {
        cart,
        cartList,
        cartTotal,
        itemsCount,
        addItem,
        removeItem,
        clearCart,
        increaseQuantity,
        decreaseQuantity,
    };
});