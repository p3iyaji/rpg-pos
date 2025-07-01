<script setup>
import AppLayout from '@/components/AppLayout.vue';
import { onMounted } from 'vue';

onMounted(() => {
    document.title = 'Dashboard - RPG-Pos'
})


</script>

<template>

    <AppLayout>

        <Head title="Dashboard" />
        <div class="p-4 bg-white border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">

            <h2 class="text-teal-600">Dashboard</h2>
        </div>

    </AppLayout>

</template>