<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router';
import axios from 'axios'
import Calculator from '@/components/Calculator.vue';
import Swal from 'sweetalert2'

import PaymentMethod from '@/components/PaymentMethod.vue';

// State
const products = ref([])
const categories = ref([])
const cart = ref([])
const searchQuery = ref('')
const activeCategory = ref(null)
const discountCode = ref('')
const appliedDiscount = ref(null)
const discountError = ref(null)
const categoriesContainer = ref(null);

const appliedProductDiscounts = ref({});
const appliedGeneralDiscount = ref(null);

const baseUrl = import.meta.env.VITE_API_BASE_URL;

const router = useRouter();

const showCalculator = ref(false);
const isFullScreen = ref(false);

const toggleCalculator = () => {
    showCalculator.value = !showCalculator.value;
}

const toggleFullScreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(error => {
            console.error(`Error attempting to enable full screen: ${error.message}`)
        })
        isFullScreen.value = true;
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen()
            isFullScreen.value = false;
        }
    }
}

//customer handling area
const customers = ref([]);
const selectedCustomer = ref(null);
const showCustomerModal = ref(false);
const newCustomer = ref({
    name: '',
    phone: '',
    email: '',
    address: ''
});

const fetchCustomers = async () => {
    try {
        const response = await axios.get('/api/customers');
        customers.value = response.data.data;
        // Set default customer (ID 1) if exists
        const defaultCustomer = customers.value.find(c => c.id === 1);
        selectedCustomer.value = defaultCustomer || (customers.value.length > 0 ? customers.value[0] : null);
    } catch (error) {
        console.error('Error fetching customers:', error);
    }
};

const addNewCustomer = async () => {
    try {
        const response = await axios.post('/api/customers', newCustomer.value);
        customers.value.push(response.data);
        selectedCustomer.value = response.data;
        showCustomerModal.value = false;
        // Reset form
        newCustomer.value = { name: '', phone: '', email: '', address: '' };
        await fetchCustomers();

    } catch (error) {
        console.error('Error adding customer:', error);
    }
};

const openCustomerModal = () => {
    showCustomerModal.value = true;
};

const handleCalculatorValue = (value) => {
    searchQuery.value = value;
};

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
);

const productDiscounts = computed(() => {
    return cart.value.reduce((sum, item) => {
        const discount = appliedProductDiscounts.value[item.product.id];
        if (discount) {
            return sum + discount.calculateDiscount(item.product.price * item.quantity);
        }
        return sum;
    }, 0);
});

const generalDiscount = computed(() => {
    if (!appliedGeneralDiscount.value) return 0;
    const discountableAmount = subtotal.value - productDiscounts.value;
    return appliedGeneralDiscount.value.calculateDiscount(discountableAmount);
});

const discountAmount = computed(() => {
    if (!appliedDiscount.value) return 0
    return appliedDiscount.value.calculateDiscount(subtotal.value)
})

const totalDiscount = computed(() =>
    productDiscounts.value + generalDiscount.value
);

const total = computed(() =>
    subtotal.value - productDiscounts.value - generalDiscount.value
);

// Methods


const applyProductDiscount = async (productId) => {
    const product = cart.value.find(item => item.product.id === productId)?.product;
    if (!product) return;

    try {
        const response = await axios.get('/api/pos-discounts/validate', {
            params: {
                code: discountCode.value,
                product_id: productId,
                amount: product.price,
                quantity: 1
            }
        });

        if (response.data.valid && response.data.scope === 'product') {
            const discount = response.data.discount;
            discount.calculateDiscount = (amount) => {
                if (discount.type === 'fixed') return Math.min(discount.value, amount);
                if (discount.type === 'percentage') return amount * (discount.value / 100);
                return 0;
            };

            appliedProductDiscounts.value[productId] = discount;
            discountCode.value = '';
        }
    } catch (error) {
        console.error(error);
    }
};

const applyGeneralDiscount = async () => {
    try {
        const response = await axios.get('/api/pos-discounts/validate', {
            params: {
                code: discountCode.value,
                amount: subtotal.value,
                quantity: cart.value.reduce((sum, item) => sum + item.quantity, 0)
            }
        });

        if (response.data.valid && response.data.scope === 'general') {
            const discount = response.data.discount;
            discount.calculateDiscount = (amount) => {
                if (discount.type === 'fixed') return Math.min(discount.value, amount);
                if (discount.type === 'percentage') return amount * (discount.value / 100);
                return 0;
            };

            appliedGeneralDiscount.value = discount;
            discountCode.value = '';
        }
    } catch (error) {
        console.error(error);
    }
};

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
            cart.value.push({ product, quantity: 1, originalQuantity: product.quantity });
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
    discountError.value = null;
    appliedDiscount.value = null;

    if (!discountCode.value) {
        discountError.value = 'Please enter a discount code';
        return;
    }

    try {
        // First try to apply as general discount
        let response = await axios.get('/api/pos-discounts/validate', {
            params: {
                code: discountCode.value,
                amount: subtotal.value,
                quantity: cart.value.reduce((sum, item) => sum + item.quantity, 0)
            }
        });

        if (response.data.valid) {
            const discount = response.data.discount;

            // Add calculation method
            discount.calculateDiscount = (amount) => {
                if (discount.type === 'fixed') {
                    return Math.min(discount.value, amount);
                } else if (discount.type === 'percentage') {
                    return amount * (discount.value / 100);
                }
                return 0;
            };

            // Apply based on scope
            if (response.data.scope === 'product') {
                // If it's a product discount but no product specified, try each product
                if (!response.data.applicable_to?.product_id) {
                    discountError.value = 'This discount requires selecting a specific product';
                    return;
                }
                appliedProductDiscounts.value[response.data.applicable_to.product_id] = discount;
            } else {
                appliedGeneralDiscount.value = discount;
            }

            discountCode.value = '';
            discountError.value = null;
        } else {
            // If general discount failed, try for each product in cart
            for (const item of cart.value) {
                response = await axios.get('/api/pos-discounts/validate', {
                    params: {
                        code: discountCode.value,
                        product_id: item.product.id,
                        amount: item.product.price * item.quantity,
                        quantity: item.quantity
                    }
                });

                if (response.data.valid && response.data.scope === 'product') {
                    const discount = response.data.discount;
                    discount.calculateDiscount = (amount) => {
                        if (discount.type === 'fixed') return Math.min(discount.value, amount);
                        if (discount.type === 'percentage') return amount * (discount.value / 100);
                        return 0;
                    };

                    appliedProductDiscounts.value[item.product.id] = discount;
                    discountCode.value = '';
                    discountError.value = null;
                    return; // Exit after first successful application
                }
            }

            // If we get here, no valid application was found
            discountError.value = response.data.message || 'Discount is not applicable to any products';
        }
    } catch (error) {
        discountError.value = error.response?.data?.message || 'Error applying discount';
        console.error('Error applying discount:', error);
    }
};

const getProductName = (productId) => {
    const item = cart.value.find(item => item.product.id === productId);
    return item ? item.product.name : '';
};

const getProductTotal = (productId) => {
    const item = cart.value.find(item => item.product.id === productId);
    return item ? item.product.price * item.quantity : 0;
};

const removeProductDiscount = (productId) => {
    delete appliedProductDiscounts.value[productId];
};

const removeGeneralDiscount = () => {
    appliedGeneralDiscount.value = null;
};

const handlePaymentCompleted = (paymentData) => {
    completeOrder(paymentData);
};

const completeOrder = async (paymentData) => {
    if (cart.value.length === 0) {
        alert('Your cart is empty');
        return;
    }

    if (!selectedCustomer.value) {
        alert('Please select a customer before completing the order');
        return;
    }

    try {
        const orderData = {
            customer_id: selectedCustomer.value?.id || 1,
            payment_method: paymentData.method,
            amount_tendered: paymentData.amountTendered,
            change_due: paymentData.changeDue,
            items: cart.value.map(item => ({
                product_id: item.product.id,
                quantity: item.quantity,
                price: item.product.price,
                discount_id: appliedProductDiscounts.value[item.product.id]?.id || null
            })),
            subtotal: subtotal.value,
            product_discounts: productDiscounts.value || 0,
            general_discount: generalDiscount.value || 0,
            total_amount: total.value
        }

        console.log(orderData);

        const response = await axios.post('/api/pos-orders', orderData);

        if (response.data.success) {
            await Swal.fire({
                title: 'Success!',
                text: 'Order completed successfully!',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#1e40af',
                timer: 3000,
                timerProgressBar: true,
                didClose: () => {
                    resetPosState();
                }
            })
        } else {
            await Swal.fire({
                title: 'Error!',
                text: response.data.message || 'Error completing order',
                icon: 'error',
                confirmButtonText: 'Try Again',
                confirmButtonColor: '#dc2626'
            })
        }
    } catch (error) {
        console.error('Error completing order:', error);
        if (error.response) {
            console.error('Response data:', error.response.data);
            console.error('Response status:', error.response.status);
        }
        alert(error.response?.data?.message || 'Error completing order. Please try again.');
    }
}

// Add this new method to reset the POS state
const resetPosState = () => {
    // Clear the cart
    cart.value = [];

    // Reset all discounts
    appliedProductDiscounts.value = {};
    appliedGeneralDiscount.value = null;
    discountCode.value = '';
    discountError.value = null;

    // Reset search and filters
    searchQuery.value = '';
    activeCategory.value = null;

    // Refresh product data to get updated stock quantities
    fetchProducts();

    // If you're using categories that might change
    fetchCategories();

    fetchCustomers();

}

const formatCurrency = (amount) => {
    const formattedAmount = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)

    return `₦${formattedAmount}`;
}


const scrollCategories = (direction) => {
    if (categoriesContainer.value) {
        const scrollAmount = 200;
        categoriesContainer.value.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
};

const goBack = () => {
    router.go(-1);
}

// Lifecycle
onMounted(() => {
    fetchProducts()
    fetchCategories()
    fetchCustomers()
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
                    <div class="mb-6">

                        <div class="flex justify-between items-center mt-2">
                            <div class="flex space-x-2">
                                <button @click="toggleCalculator"
                                    class="p-2 bg-teal-800 text-white rounded-lg hover:bg-teal-700 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z" />
                                    </svg>
                                </button>
                                <button @click="toggleFullScreen"
                                    class="p-2 bg-teal-800 text-white rounded-lg hover:bg-teal-700 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                </button>
                            </div>

                            <button @click="goBack" type="button"
                                class="p-2 bg-teal-800 text-white rounded-lg hover:bg-teal-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div clas="mb-6">
                        <input v-model="searchQuery" type="text" placeholder="Search products..." class="w-full p-3 border border-teal-300 rounded-md focus:outline-none focus:ring-2
                             focus:ring-teal-500">
                    </div>



                    <div class="mb-6 relative">
                        <!-- Left arrow (shown when scrollable to left) -->
                        <button
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 bg-white text-teal-800 border border-teal-800 rounded-full w-8 h-8 flex items-center justify-center shadow-md hover:bg-teal-600 transition"
                            @click="scrollCategories(-1)">
                            &larr;
                        </button>

                        <!-- Right arrow (shown when scrollable to right) -->
                        <button
                            class="absolute right-0 top-1/2 transform -translate-y-1/2 z-10 bg-white text-teal-800 border border-teal-800 rounded-full w-8 h-8 flex items-center justify-center shadow-md hover:bg-teal-600 transition"
                            @click="scrollCategories(1)">
                            &rarr;
                        </button>

                        <div ref="categoriesContainer"
                            class="flex rounded-md p-2 space-x-2 p-2 overflow-x-auto pb-2 mt-5 mb-5 border border-teal-800 shadow-lg scrollbar-hide">
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
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-teal-700">Customer</label>
                            <button @click="openCustomerModal" class="text-sm text-teal-600 hover:text-teal-800">
                                + Add New Customer
                            </button>
                        </div>
                        <select v-model="selectedCustomer" class="w-full p-2 border border-teal-300 rounded-lg">
                            <option v-for="customer in customers" :key="customer.id" :value="customer">
                                {{ customer.name }} ({{ customer.phone }})
                            </option>
                        </select>
                    </div>
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
                        <!-- Discount application section -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-teal-700 mb-1">Discount code</label>

                            <!-- Product selection for discount -->
                            <select v-model="selectedProductForDiscount"
                                class="w-full mb-2 p-2 border border-teal-300 rounded-lg">
                                <option :value="null">Apply to entire order</option>
                                <option v-for="item in cart" :key="item.product.id" :value="item.product.id">
                                    Apply to {{ item.product.name }} only
                                </option>
                            </select>

                            <div class="flex">
                                <input v-model="discountCode" type="text" placeholder="Enter discount code"
                                    class="flex-1 p-2 border border-teal-300 rounded-l-lg focus:outline-none focus:ring-1 focus:ring-teal-500">
                                <button @click="applyDiscount"
                                    class="bg-teal-800 text-white px-4 py-2 rounded-r-lg hover:bg-teal-700 transition">
                                    Apply
                                </button>
                            </div>
                            <p v-if="discountError" class="text-red-600 text-sm mt-1">{{ discountError }}</p>
                        </div>

                        <!-- Applied discounts display -->
                        <div v-if="Object.keys(appliedProductDiscounts).length > 0 || appliedGeneralDiscount"
                            class="mb-4">
                            <h3 class="text-sm font-medium text-teal-700 mb-2">Applied Discounts</h3>

                            <!-- Product discounts -->
                            <div v-for="(discount, productId) in appliedProductDiscounts" :key="productId"
                                class="flex justify-between items-center mb-1">
                                <div>
                                    <span class="text-green-600">Product Discount:</span>
                                    <span class="ml-1">{{ getProductName(productId) }} - {{ discount.name }}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-600 mr-2">-{{
                                        formatCurrency(discount.calculateDiscount(getProductTotal(productId))) }}</span>
                                    <button @click="removeProductDiscount(productId)"
                                        class="text-red-500 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- General discount -->
                            <div v-if="appliedGeneralDiscount" class="flex justify-between items-center">
                                <div>
                                    <span class="text-green-600">Order Discount:</span>
                                    <span class="ml-1">{{ appliedGeneralDiscount.name }}</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-600 mr-2">-{{ formatCurrency(generalDiscount) }}</span>
                                    <button @click="removeGeneralDiscount" class="text-red-500 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="border-b border-teal-200 pb-2 mb-4">
                            <div v-for="(item, index) in cart" :key="index"
                                class="flex items-center py-2 border-b border-teal-100">
                                <div class="w-16 h-16 bg-teal-100 rounded-md overflow-hidden mr-3 flex-shrink-0">
                                    <img v-if="item.product.image" :src="`${baseUrl}/storage/${item.product.image}`"
                                        :alt="item.product.name" class="object-cover h-full w-full">
                                    <div v-else class="text-teal-400 h-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>

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

                            <!-- Order summary -->
                            <div class="space-y-2 mb-6">
                                <!-- Subtotal -->
                                <div class="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span>{{ formatCurrency(subtotal) }}</span>
                                </div>

                                <!-- Product Discounts -->
                                <div v-if="productDiscounts > 0" class="flex justify-between text-green-600">
                                    <span>Product Discounts:</span>
                                    <span>-{{ formatCurrency(productDiscounts) }}</span>
                                </div>

                                <!-- General Discount -->
                                <div v-if="generalDiscount > 0" class="flex justify-between text-green-600">
                                    <span>Order Discount:</span>
                                    <span>-{{ formatCurrency(generalDiscount) }}</span>
                                </div>

                                <!-- Total -->
                                <div class="flex justify-between font-bold text-lg border-t border-gray-200 pt-2 mt-2">
                                    <span>Total:</span>
                                    <span>{{ formatCurrency(total) }}</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <PaymentMethod :total-amount="total" @payment-completed="handlePaymentCompleted" />

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
        <!-- Customer Modal -->
        <div v-if="showCustomerModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">Add New Customer</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input v-model="newCustomer.name" type="text" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input v-model="newCustomer.phone" type="text" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input v-model="newCustomer.email" type="email" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea v-model="newCustomer.address" class="w-full p-2 border rounded"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 mt-4">
                    <button @click="showCustomerModal = false" class="px-4 py-2 bg-gray-200 rounded-lg">
                        Cancel
                    </button>
                    <button @click="addNewCustomer" class="px-4 py-2 bg-teal-800 text-white rounded-lg">
                        Save Customer
                    </button>
                </div>
            </div>
        </div>
        <!-- Calculator Popup -->
        <Calculator v-if="showCalculator" @close="showCalculator = false" @apply="handleCalculatorValue" />
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

.payment-method {
    transition: all 0.2s ease;
}

.payment-method.selected {
    background-color: #1e40af;
    color: white;
}

.payment-method:not(.selected):hover {
    background-color: #e5e7eb;
}
</style>