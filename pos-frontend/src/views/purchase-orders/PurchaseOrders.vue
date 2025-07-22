<script setup>
defineOptions({
    name: 'PurchaseOrdersView'
})
</script>

<template>
    <div class="purchase-orders-view">
        <router-view />
    </div>
</template>

<style scoped>
.purchase-orders-view {
    @apply h-full w-full;
}
</style>