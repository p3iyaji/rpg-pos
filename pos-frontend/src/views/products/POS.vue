<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// State
const products = ref([])
const categories = ref([])
const cart = ref([])
const searchQuery = ref('')
const activeCategory = ref(null)
const discountCode = ref('')
const appliedDiscount = ref(null)
const discountError = ref(null)

const baseUrl = import.meta.env.VITE_API_BASE_URL;


// Computed
const filteredProducts = computed(() => {
    let filtered = products.value

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(product =>
            product.name.toLowerCase().includes(query) ||
            product.barcode?.toLowerCase().includes(query)
        )
    }

    if (activeCategory.value) {
        filtered = filtered.filter(product => product.category_id === activeCategory.value)
    }

    return filtered
})

const subtotal = computed(() =>
    cart.value.reduce((sum, item) => sum + item.product.price * item.quantity, 0)
)

const discountAmount = computed(() => {
    if (!appliedDiscount.value) return 0
    return appliedDiscount.value.calculateDiscount(subtotal.value)
})

const total = computed(() =>
    subtotal.value - discountAmount.value
)

// Methods
const fetchProducts = async () => {
    try {
        const response = await axios.get('/api/pos-products')
        products.value = response.data
    } catch (error) {
        console.error('Error fetching products:', error)
    }
}

const fetchCategories = async () => {
    try {
        const response = await axios.get('/api/pos-categories')
        categories.value = response.data
    } catch (error) {
        console.error('Error fetching categories:', error)
    }
}

const addToCart = (product) => {
    const existingItem = cart.value.find(item => item.product.id === product.id)

    if (existingItem) {
        if (existingItem.quantity < product.quantity) {
            existingItem.quantity++
        } else {
            alert(`Only ${product.quantity} available in stock`)
        }
    } else {
        if (product.quantity > 0) {
            cart.value.push({ product, quantity: 1 })
        } else {
            alert('This product is out of stock')
        }
    }
}

const increaseQuantity = (index) => {
    const item = cart.value[index]
    if (item.quantity < item.product.quantity) {
        item.quantity++
    } else {
        alert(`Only ${item.product.quantity} available in stock`)
    }
}

const decreaseQuantity = (index) => {
    const item = cart.value[index]
    if (item.quantity > 1) {
        item.quantity--
    } else {
        removeItem(index)
    }
}

const removeItem = (index) => {
    cart.value.splice(index, 1)
    if (cart.value.length === 0) {
        appliedDiscount.value = null
    }
}

const clearCart = () => {
    cart.value = []
    appliedDiscount.value = null
    discountError.value = null
}

const filterByCategory = (categoryId) => {
    activeCategory.value = categoryId
}

const clearCategoryFilter = () => {
    activeCategory.value = null
}

const applyDiscount = async () => {
    discountError.value = null

    if (!discountCode.value) {
        discountError.value = 'Please enter a discount code'
        return
    }

    try {
        const response = await axios.get(`/api/pos-discounts/validate?code=${discountCode.value}&amount=${subtotal.value}&quantity=${cart.value.reduce((sum, item) => sum + item.quantity, 0)}`)

        if (response.data.valid) {
            appliedDiscount.value = response.data.discount
        } else {
            discountError.value = response.data.message || 'Discount is not applicable'
        }
    } catch (error) {
        discountError.value = error.response?.data?.message || 'Error applying discount'
        console.error('Error applying discount:', error)
    }
}

const completeOrder = async () => {
    if (cart.value.length === 0) {
        alert('Your cart is empty')
        return
    }

    try {
        const orderData = {
            items: cart.value.map(item => ({
                product_id: item.product.id,
                quantity: item.quantity,
                price: item.product.price
            })),
            discount_id: appliedDiscount.value?.id || null,
            total_amount: total.value
        }

        await axios.post('/api/pos-orders', orderData)
        alert('Order completed successfully!')
        clearCart()
    } catch (error) {
        console.error('Error completing order:', error)
        alert('Error completing order. Please try again.')
    }
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'GBP'
    }).format(amount)
}


const scrollCategories = (direction) => {
    const container = this.$refs.categoriesContainer;
    const scrollAmount = 200; // Adjust this value as needed
    container.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
// Lifecycle
onMounted(() => {
    fetchProducts()
    fetchCategories()
})


</script>


<template>
    <div class="min-h-screen bg-gray-100">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl text-teal-800 font-bold text-center mb-8">
                Point of Sale System
            </h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- product selection area starts -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                    <div clas="mb-6">
                        <input v-model="searchQuery" type="text" placeholder="Search products..." class="w-full p-3 border border-teal-300 rounded-ls focus:outline-none focus:ring-2
                             focus:ring-teal-500">
                    </div>

                    <div class="mb-6 relative px-5">
                        <!-- Left arrow (shown when scrollable to left) -->
                        <button
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 bg-teal-800 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-md hover:bg-teal-600 transition"
                            @click="scrollCategories(-1)">
                            &larr;
                        </button>

                        <!-- Right arrow (shown when scrollable to right) -->
                        <button
                            class="absolute right-0 top-1/2 transform -translate-y-1/2 z-10 bg-teal-800 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-md hover:bg-teal-600 transition"
                            @click="scrollCategories(1)">
                            &rarr;
                        </button>

                        <div ref="categoriesContainer"
                            class="flex space-x-2 p-2 overflow-x-auto pb-2 mt-5 mb-5 border border-teal-800 shadow-lg scrollbar-hide">
                            <button v-for="category in categories" :key="category.id"
                                @click="filterByCategory(category.id)"
                                :class="{ 'bg-teal-800 text-white': activeCategory === category.id }"
                                class="px-4 py-2 rounded-full text-white text-sm text-semibold bg-teal-700 hover:bg-teal-300 transition whitespace-nowrap">
                                {{ category.name }}
                            </button>
                            <button @click="clearCategoryFilter"
                                :class="{ 'bg-teal-800 text-white': activeCategory === null }"
                                class="px-4 py-2 rounded-full text-white text-sm text-semibold bg-teal-700 hover:bg-teal-300 transition whitespace-nowrap">
                                All
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div v-for="product in filteredProducts" :key="product.id" @click="addToCart(product)" class="bg-white border border-teal-200 rounded-lg overflow-hidden shadow-sm
                        hover:shadow-md transition cursor-pointer">
                            <div class="h-40 bg-teal-100 flex items-center justify-center overflow-hidden">
                                <img v-if="product.image"
                                    :src="product.image ? `${baseUrl}/storage/${product.image}` : '/images/default-food.png'"
                                    :alt="product.name" class="object-cover h-full w-full">
                                <div v-else class="text-teal-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="p-3">
                                <h3 clas="font-semibold text-teal-800 truncate">{{ product.name }}</h3>
                                <p class="text-teal-800 font-bold">{{ formatCurrency(product.price) }}</p>
                                <p v-if="product.quantity <= 5" class="text-xs text-red-500">
                                    Only {{ product.quantity }} left
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- product selection area ends -->

                <!-- Cart starts -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-4">Current Order</h2>
                    <div v-if="cart.length === 0" class="text-teal-500 text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p>Your cart is empty</p>
                        <p class="text-sm">Select products to add to your order</p>
                    </div>

                    <div v-else>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-teal-700 mb-1">Discount code</label>
                            <div class="flex">
                                <input v-model="discountCode" type="text" placeholder="Enter discount code"
                                    class="flex-1 p-2 border border-teal-300 rounded-l-lg focus:outline-none focus:ring-1 focus:ring-teal-500">
                                <button @click="applyDiscount" class="bg-teal-800 text-white px-4 py-2 rounded-r-lg hoaver:bg-blue-700
                                 transition">
                                    Apply
                                </button>
                            </div>
                            <p v-if="appliedDiscount" class="text-teal-800 text-sm mt-1">
                                Discount applied: {{ appliedDiscount.name }} (-{{ formatCurrency(discountAmount) }})
                            </p>
                            <p v-if="discountError" class="text-red-600 text-sm mt-1">{{ discountError }}</p>
                        </div>
                        <div class="border-b border-teal-200 pb-2 mb-4">
                            <div v-for="(item, index) in cart" :key="index"
                                class="flex items-center py-2 border-b border-teal-100">
                                <div class="w-8 h-8 bg-teal-100 rounded-md overflow-hidden mr-3 flex-shrink-0">
                                    <img v-if="item.product.image" :src="`${baseUrl}/storage/${item.product.image}`"
                                        :alt="item.product.name" class="object-cover h-full w-full">
                                    <div v-else class="text-teal-400 h-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-medium text-teal-800">{{ item.product.name }}</h3>
                                        <div class="flex items-center justify-between mt-1">
                                            <div class="flex items-center">
                                                <!-- Quantity controls -->
                                                <button @click.stop="decreaseQuantity(index)"
                                                    class="w-6 h-6 flex items-center justify-center bg-teal-200 rounded hover:bg-teal-300">
                                                    -
                                                </button>
                                                <span class="mx-2 w-6 text-center">{{ item.quantity }}</span>
                                                <button @click.stop="increaseQuantity(index)"
                                                    class="w-6 h-6 flex items-center justify-center bg-teal-200 rounded hover:bg-teal-300">
                                                    +
                                                </button>
                                                <!-- Remove button -->
                                                <button @click.stop="removeItem(index)"
                                                    class="ml-2 text-red-500 hover:text-red-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <!-- Price display -->
                                            <span class="font-medium text-teal-800">
                                                {{ formatCurrency(item.product.price * item.quantity) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2 mb-6">
                                <div class="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span>{{ formatCurrency(subtotal) }}</span>
                                </div>
                                <div v-if="appliedDiscount" class="flex justify-between text-teal-800">
                                    <span>{{ formatCurrency(subtotal) }}</span>
                                </div>
                                <div v-if="appliedDiscount" class="flex justify-between text-green-600">
                                    <span>Discount ({{ appliedDiscount.name }}):</span>
                                    <span>-{{ formatCurrency(discountAmount) }}</span>
                                </div>
                                <div class="flex justify-between font-bold text-lg border-t border-gray-200 pt-2 mt-2">
                                    <span>Total:</span>
                                    <span>{{ formatCurrency(total) }}</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <button @click="completeOrder" class="w-full bg-teal-800 text-white py-3 rounded-lg *:font-bold
                                hover:bg-teal-700 transition">
                                    Complete Order
                                </button>
                                <button @click="clearCart"
                                    class="w-full bg-gray-200 text-gray-800 py-3 rounded-lg font-bold hover:bg-gray-300 transition">
                                    Clear Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>