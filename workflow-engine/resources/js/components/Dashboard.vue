<template>
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-4">My Requests</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">Request ID</th>
                        <th class="py-2 px-4 border-b">Workflow</th>
                        <th class="py-2 px-4 border-b">Status</th>
                        <th class="py-2 px-4 border-b">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- In a real application, this would be populated from an API -->
                    <tr v-for="request in requests" :key="request.id">
                        <td class="py-2 px-4 border-b">{{ request.id }}</td>
                        <td class="py-2 px-4 border-b">{{ request.workflow }}</td>
                        <td class="py-2 px-4 border-b">
                            <span :class="statusClass(request.status)" class="px-2 py-1 rounded-full text-xs font-semibold">
                                {{ request.status }}
                            </span>
                        </td>
                        <td class="py-2 px-4 border-b">{{ request.createdAt }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const requests = ref([
    { id: 1, workflow: 'Purchase Request', status: 'Approved', createdAt: '2025-09-12' },
    { id: 2, workflow: 'Leave Request', status: 'Pending', createdAt: '2025-09-13' },
    { id: 3, workflow: 'Purchase Request', status: 'Rejected', createdAt: '2025-09-11' },
]);

const statusClass = (status) => {
    switch (status) {
        case 'Approved':
            return 'bg-green-200 text-green-800';
        case 'Pending':
            return 'bg-yellow-200 text-yellow-800';
        case 'Rejected':
            return 'bg-red-200 text-red-800';
        default:
            return 'bg-gray-200 text-gray-800';
    }
};
</script>
