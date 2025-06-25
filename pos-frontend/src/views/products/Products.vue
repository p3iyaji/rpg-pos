<script setup>
import AppLayout from '@/components/AppLayout.vue';
import { onMounted } from 'vue';

onMounted(() => {
    document.title = 'Products List'
})


</script>

<template>
    <AppLayout>
        <h2 class="text-purple-600">Products List</h2>

    </AppLayout>
</template>