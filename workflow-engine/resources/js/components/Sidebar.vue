<template>
    <aside :class="[
        'bg-gray-900 text-white h-screen p-4 transition-all duration-200 flex flex-col',
        collapsed ? 'w-20' : 'w-64'
    ]">
        <div class="flex items-center justify-between mb-6">
            <h2 v-if="!collapsed" class="text-xl font-bold">Workflow Engine</h2>
            <button @click="collapsed = !collapsed" class="p-2 rounded hover:bg-gray-800" :title="collapsed ? 'Expand' : 'Collapse'">
                <span v-if="collapsed">»</span>
                <span v-else>«</span>
            </button>
        </div>

        <nav class="flex-1">
            <ul>
                <li class="mb-2">
                    <a href="/dashboard" class="flex items-center gap-3 p-2 rounded hover:bg-gray-800" title="Dashboard">
                        <span>🏠</span>
                        <span v-if="!collapsed">Dashboard</span>
                    </a>
                </li>
                <li class="mb-2">
                    <a href="/ui/requests/new" class="flex items-center gap-3 p-2 rounded hover:bg-gray-800" title="New Request">
                        <span>➕</span>
                        <span v-if="!collapsed">New Request</span>
                    </a>
                </li>
                <li class="mb-2">
                    <a href="/ui/approvals" class="flex items-center gap-3 p-2 rounded hover:bg-gray-800" title="Approvals">
                        <span>✅</span>
                        <span v-if="!collapsed">Pending Approvals</span>
                    </a>
                </li>
                <li class="mb-2">
                    <a href="/ui/workflows" class="flex items-center gap-3 p-2 rounded hover:bg-gray-800" title="Workflows">
                        <span>⚙️</span>
                        <span v-if="!collapsed">Workflows</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="text-xs text-gray-400" v-if="!collapsed">
            v1.0
        </div>
    </aside>
</template>

<script setup>
import { ref } from 'vue';

const collapsed = ref(false);
</script>
