<template>
    <AuthenticatedLayout>
        <RouterView />
    </AuthenticatedLayout>
    
    
</template>

<script setup>
import { RouterView } from 'vue-router';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

