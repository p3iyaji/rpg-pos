<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    existingProducts: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'add-products']);

const products = ref([]);
const selectedProducts = ref([]);
const searchQuery = ref('');
const isLoading = ref(false);

const filteredProducts = computed(() => {
    const query = searchQuery.value.toLowerCase();
    return products.value.filter(product =>
        !isProductSelected(product.id) &&
        (product.name.toLowerCase().includes(query) ||
            product.sku.toLowerCase().includes(query)))
});

const fetchProducts = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/products');
        products.value = response.data.data;
    } catch (error) {
        console.error('Error fetching products:', error);
    } finally {
        isLoading.value = false;
    }
};

const isProductSelected = (id) => {
    return props.existingProducts.includes(id);
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
};

const close = () => {
    emit('close');
};

const addSelected = () => {
    emit('add-products', selectedProducts.value);
    close();
};

onMounted(() => {
    fetchProducts();
});
</script>

<template>
    <div class="modal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Products</h5>
                    <button type="button" class="btn-close" @click="close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" v-model="searchQuery" class="form-control" placeholder="Search products...">
                    </div>

                    <div v-if="isLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div v-else>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Current Stock</th>
                                    <th>Cost Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in filteredProducts" :key="product.id">
                                    <td>
                                        <input type="checkbox" v-model="selectedProducts" :value="product"
                                            :disabled="isProductSelected(product.id)">
                                    </td>
                                    <td>{{ product.name }}</td>
                                    <td>{{ product.sku }}</td>
                                    <td>{{ product.current_stock }}</td>
                                    <td>{{ formatCurrency(product.cost_price) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="filteredProducts.length === 0" class="text-center py-4 text-muted">
                            No products found
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="addSelected"
                        :disabled="selectedProducts.length === 0">
                        Add Selected ({{ selectedProducts.length }})
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
