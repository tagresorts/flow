<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Open Requests</div>
                <div class="text-2xl font-bold">{{ summary.open }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Approved</div>
                <div class="text-2xl font-bold">{{ summary.approved }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Rejected</div>
                <div class="text-2xl font-bold">{{ summary.rejected }}</div>
            </div>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <div class="flex items-end gap-3 mb-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Search</label>
                    <input v-model="filters.q" class="border rounded p-2" placeholder="ID or Workflow name" />
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Status</label>
                    <select v-model="filters.status" class="border rounded p-2">
                        <option value="">All</option>
                        <option>Pending</option>
                        <option>In Progress</option>
                        <option>Approved</option>
                        <option>Rejected</option>
                    </select>
                </div>
                <button @click="loadRequests" class="px-3 py-2 bg-indigo-600 text-white rounded">Apply</button>
            </div>
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b text-left">ID</th>
                        <th class="py-2 px-4 border-b text-left">Workflow</th>
                        <th class="py-2 px-4 border-b text-left">Status</th>
                        <th class="py-2 px-4 border-b text-left">Created</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td class="py-2 px-4 border-b">#{{ r.id }}</td>
                        <td class="py-2 px-4 border-b">{{ r.workflow?.name }}</td>
                        <td class="py-2 px-4 border-b">{{ r.status }}</td>
                        <td class="py-2 px-4 border-b">{{ new Date(r.created_at).toLocaleString() }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

const summary = ref({ open: 0, approved: 0, rejected: 0 });
const filters = ref({ q: '', status: '' });
const rows = ref([]);

onMounted(async () => {
    await Promise.all([loadSummary(), loadRequests()]);
});

async function loadSummary() {
    const res = await axios.get('/dashboard/summary');
    summary.value = res.data;
}

async function loadRequests() {
    const res = await axios.get('/dashboard/requests', { params: filters.value });
    rows.value = res.data.data ?? res.data;
}
</script>

