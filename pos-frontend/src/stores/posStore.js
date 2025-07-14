import { defineStore } from 'pinia'
import axios from 'axios'

export const usePosStore = defineStore('pos', {
    state: () => ({
        products: [],
        categories: [],
        cart: [],
        customers: [],
        selectedCustomer: null,
        searchQuery: '',
        activeCategory: null,
        appliedProductDiscounts: {},
        appliedGeneralDiscount: null,
        baseUrl: import.meta.env.VITE_API_BASE_URL
    }),

    getters: {
        filteredProducts(state) {
            let filtered = state.products

            if (state.searchQuery) {
                const query = state.searchQuery.toLowerCase()
                filtered = filtered.filter(product =>
                    product.name.toLowerCase().includes(query) ||
                    product.barcode?.toLowerCase().includes(query)
                )
            }

            if (state.activeCategory) {
                filtered = filtered.filter(product => product.category_id === state.activeCategory)
            }

            return filtered
        },

        subtotal(state) {
            return state.cart.reduce((sum, item) => sum + item.product.price * item.quantity, 0)
        },

        productDiscounts(state) {
            return state.cart.reduce((sum, item) => {
                const discount = state.appliedProductDiscounts[item.product.id]
                if (discount) {
                    return sum + discount.calculateDiscount(item.product.price * item.quantity)
                }
                return sum
            }, 0)
        },

        generalDiscount(state) {
            if (!state.appliedGeneralDiscount) return 0
            const discountableAmount = this.subtotal - this.productDiscounts
            return state.appliedGeneralDiscount.calculateDiscount(discountableAmount)
        },

        total() {
            return this.subtotal - this.productDiscounts - this.generalDiscount
        }
    },

    actions: {
        async fetchProducts() {
            try {
                const response = await axios.get('/api/pos-products')
                this.products = response.data
            } catch (error) {
                console.error('Error fetching products:', error)
            }
        },

        async fetchCategories() {
            try {
                const response = await axios.get('/api/pos-categories')
                this.categories = response.data
            } catch (error) {
                console.error('Error fetching categories:', error)
            }
        },

        async fetchCustomers() {
            try {
                const response = await axios.get('/api/customers')
                this.customers = response.data.data
                const defaultCustomer = this.customers.find(c => c.id === 1)
                this.selectedCustomer = defaultCustomer || (this.customers.length > 0 ? this.customers[0] : null)
            } catch (error) {
                console.error('Error fetching customers:', error)
            }
        },

        addToCart(product) {
            const existingItem = this.cart.find(item => item.product.id === product.id)

            if (existingItem) {
                if (existingItem.quantity < product.quantity) {
                    existingItem.quantity++
                } else {
                    alert(`Only ${product.quantity} available in stock`)
                }
            } else {
                if (product.quantity > 0) {
                    this.cart.push({ product, quantity: 1 })
                } else {
                    alert('This product is out of stock')
                }
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1)
        },

        clearCart() {
            this.cart = []
            this.appliedProductDiscounts = {}
            this.appliedGeneralDiscount = null
        },

        async completeOrder(paymentData) {
            if (this.cart.length === 0) {
                alert('Your cart is empty')
                return
            }

            if (!this.selectedCustomer) {
                alert('Please select a customer before completing the order')
                return
            }

            try {
                const orderData = {
                    customer_id: this.selectedCustomer?.id || 1,
                    payment_method: paymentData.method,
                    amount_tendered: paymentData.amountTendered,
                    change_due: paymentData.changeDue,
                    items: this.cart.map(item => ({
                        product_id: item.product.id,
                        quantity: item.quantity,
                        price: item.product.price,
                        discount_id: this.appliedProductDiscounts[item.product.id]?.id || null
                    })),
                    subtotal: this.subtotal,
                    product_discounts: this.productDiscounts,
                    general_discount: this.generalDiscount,
                    total_amount: this.total
                }

                const response = await axios.post('/api/pos-orders', orderData)

                if (response.data.success) {
                    this.clearCart()
                    return { success: true, orderId: response.data.order_id }
                } else {
                    throw new Error(response.data.message || 'Error completing order')
                }
            } catch (error) {
                console.error('Error completing order:', error)
                throw error
            }
        },

        formatCurrency(amount) {
            const formattedAmount = new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount)

            return `₦${formattedAmount}`
        }
    }
})