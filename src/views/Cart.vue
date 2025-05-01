<script setup>
import { useCartStore } from '@/stores/cart'
import { useSaleStore } from '@/stores/sales'
import { truncateText } from '@/utils'
import { faMinus, faPlus, faSpinner, faTimes } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { storeToRefs } from 'pinia'
import { ref } from 'vue'

const cartStore = useCartStore();
const { cartList, cartTotal, itemsCount } = storeToRefs(cartStore) // Use the 'cartList' getter for enriched cart data
const saleStore = useSaleStore();
const { isCheckoutLoading } = storeToRefs(saleStore)

const customer = ref('');
const paymentMethod = ref('cash');
const checkOut = () => {
    const data = {
        items: cartList.value,
        total: cartTotal.value,
        customer: customer.value,
        payment_method: paymentMethod.value
    };
    saleStore.addSale(data);
}
</script>

<template>
    <div class="cart-container">
        <h4 class="text-center">Cart ({{ itemsCount }})</h4>
        <table class="table cart-table align-middle table-striped" v-if="itemsCount > 0">
            <thead>
                <tr class="text-uppercase fw-bold px-1">
                    <th class="first-th">*</th>
                    <th>item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in cartList" :key="item.id">
                    <td class="first-td">
                        <button type="button" class="btn btn-link btn-sm p-1"
                            @click="cartStore.removeItem(item.id)">
                            <FontAwesomeIcon :icon="faTimes" class="text-warning" />
                        </button>
                    </td>
                    <td :title="item.name">{{ truncateText(item.name).toLocaleUpperCase() }}</td>
                    <td class="price">{{ Number(item.price).toFixed(2) }}</td>
                    <td>
                        <div class="d-flex" style="width: min-content">
                            <button class="btn btn-link btn-sm p-1" type="button"
                                @click="cartStore.decreaseQuantity(item.id)">
                                <FontAwesomeIcon :icon="faMinus" />
                            </button>
                            <input readonly v-model.number="item.quantity" autocomplete="off"
                                class="form-control form-control-sm text-center" style="width: 50px" />
                            <button class="btn btn-link btn-sm p-1" type="button"
                                @click="cartStore.increaseQuantity(item.id)">
                                <FontAwesomeIcon :icon="faPlus" />
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td class="total">total</td>
                    <td class="price total">{{ cartTotal }}</td>
                    <td>

                    </td>
                </tr>
            </tfoot>
        </table>
        <p class="text-center" v-else>Your cart is empty.</p>
    </div>
    <form v-if="itemsCount > 0" class="cart-footer mb-3 px-3" @submit.prevent="checkOut">
        <div class="mb-3">
            <label for="customer-name" class="form-label">Customer Name
                <span class="text-danger">*</span>
            </label>
            <input v-model="customer" type="text" class="form-control" required />
        </div>
        <div class="mb-3">
            <div class="mb-3">
                <label for="payment_mode" class="form-label">Payment mode</label>
                <select class="form-select form-select-lg" v-model="paymentMethod" name="payment_mode" id="payment_mode"
                    required>
                    <option value="cash">Cash </option>
                    <option value="momo">Momo </option>
                </select>
            </div>
        </div>
        <div class="d-flex justify-content-between my-1">
            <button type="submit" class="btn btn-success" :disabled="isCheckoutLoading">
                <span v-if="isCheckoutLoading">
                    <FontAwesomeIcon :icon="faSpinner" spin size="xl" class="me-1" />
                    processing...
                </span>
                <span v-if="!isCheckoutLoading" class="">checkout</span>
            </button>
        </div>
    </form>
</template>

<style scoped>
.cart-footer {
    position: relative;
    top: 55px;
    right: 0;
    width: 100%;
}

.cart-container {
    position: -webkit-sticky;
    position: sticky;
    right: 0;
    top: 5px;
    max-height: calc(100% - 355px);
    overflow: auto;
    padding-left: 10px;
}

/* 
@media (max-width: 800px) {
    .cart-footer {
        width: 350px;
    }
} */

.first-th,
.first-td {
    flex-direction: column;
    justify-content: start;
    align-items: center;
    max-width: 50px;
    text-align: left;
}

.total {
    font-weight: 500;
    font-size: 1.25rem;
    text-transform: uppercase;
}

.price {
    color: #333;
    font-size: 1.05rem;
}

.price::before {
    content: '¢';
}
</style>
