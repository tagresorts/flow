<template>
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">Pending Approvals</h1>

        <div class="flex flex-wrap items-end gap-3 mb-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Search</label>
                <input v-model="filters.q" class="border rounded p-2" placeholder="Search by workflow or request id" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Status</label>
                <select v-model="filters.status" class="border rounded p-2">
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div>
                <button @click="load()" class="px-3 py-2 bg-indigo-600 text-white rounded">Apply</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b text-left">Request</th>
                        <th class="py-2 px-4 border-b text-left">Workflow</th>
                        <th class="py-2 px-4 border-b text-left">Assigned</th>
                        <th class="py-2 px-4 border-b text-left">Status</th>
                        <th class="py-2 px-4 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="step in steps" :key="step.id">
                        <td class="py-2 px-4 border-b">#{{ step.request_id }}</td>
                        <td class="py-2 px-4 border-b">{{ step.request?.workflow?.name }}</td>
                        <td class="py-2 px-4 border-b">{{ new Date(step.created_at).toLocaleString() }}</td>
                        <td class="py-2 px-4 border-b">{{ step.status }}</td>
                        <td class="py-2 px-4 border-b">
                            <div class="flex items-center gap-2">
                                <button @click="approve(step)" class="px-2 py-1 bg-emerald-600 text-white rounded text-sm">Approve</button>
                                <button @click="reject(step)" class="px-2 py-1 bg-rose-600 text-white rounded text-sm">Reject</button>
                                <button @click="openReassign(step)" class="px-2 py-1 bg-slate-700 text-white rounded text-sm">Reassign</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-if="status" class="mt-3 text-sm text-green-700">{{ status }}</p>
    </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

const steps = ref([]);
const status = ref('');
const filters = ref({ q: '', status: 'Pending' });
const userOptions = ref([]);

onMounted(load);

async function load() {
    status.value = '';
    const res = await axios.get('/approvals', { params: { status: filters.value.status, q: filters.value.q } });
    steps.value = res.data.data ?? res.data;
}

async function approve(step) {
    status.value = '';
    await axios.post(`/approvals/${step.id}/approve`);
    status.value = 'Approved successfully';
    await load();
}

async function reject(step) {
    status.value = '';
    await axios.post(`/approvals/${step.id}/reject`);
    status.value = 'Rejected successfully';
    await load();
}

async function openReassign(step) {
    const res = await axios.get('/directory/users');
    userOptions.value = res.data;
    const to = prompt('Enter new assignee user ID (or choose):\n' + userOptions.value.map(u => `${u.id}: ${u.name} <${u.email}>`).join('\n'));
    if (to) {
        await axios.post(`/approvals/${step.id}/reassign`, { assigned_to: Number(to) });
        status.value = 'Reassigned successfully';
        await load();
    }
}
</script>

