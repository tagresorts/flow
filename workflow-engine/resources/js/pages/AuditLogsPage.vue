<template>
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">Audit Logs</h1>
        <div class="flex items-end gap-3 mb-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Search</label>
                <input v-model="q" class="border rounded p-2" placeholder="Action, user or workflow" />
            </div>
            <button @click="load" class="px-3 py-2 bg-indigo-600 text-white rounded">Search</button>
        </div>
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b text-left">When</th>
                    <th class="py-2 px-4 border-b text-left">User</th>
                    <th class="py-2 px-4 border-b text-left">Workflow</th>
                    <th class="py-2 px-4 border-b text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="log in rows" :key="log.id">
                    <td class="py-2 px-4 border-b">{{ new Date(log.timestamp).toLocaleString() }}</td>
                    <td class="py-2 px-4 border-b">{{ log.actor?.name }}</td>
                    <td class="py-2 px-4 border-b">{{ log.request?.workflow?.name }}</td>
                    <td class="py-2 px-4 border-b">{{ log.action }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

const q = ref('');
const rows = ref([]);

onMounted(load);

async function load() {
    const res = await axios.get('/audit/logs', { params: { q: q.value } });
    rows.value = res.data.data ?? res.data;
}
</script>

