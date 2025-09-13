<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar />
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center p-4 bg-white border-b">
                <div>
                    <!-- Header content, e.g., search bar -->
                </div>
                <div>
                    <!-- User menu -->
                    <form method="POST" action="/logout">
                        <input type="hidden" name="_token" :value="csrfToken">
                        <button type="submit" class="text-gray-600 hover:text-gray-800">Logout</button>
                    </form>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200">
                <div class="container mx-auto px-6 py-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from '@/components/Sidebar.vue';
import { ref } from 'vue';

const csrfToken = ref(document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
</script>
