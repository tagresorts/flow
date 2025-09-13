<template>
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">Users & Roles</h1>
        <div class="flex gap-4">
            <div class="flex-1">
                <table class="min-w-full">
                    <thead><tr>
                        <th class="py-2 px-4 border-b text-left">Name</th>
                        <th class="py-2 px-4 border-b text-left">Email</th>
                        <th class="py-2 px-4 border-b text-left">Actions</th>
                    </tr></thead>
                    <tbody>
                        <tr v-for="u in users" :key="u.id">
                            <td class="py-2 px-4 border-b">{{ u.name }}</td>
                            <td class="py-2 px-4 border-b">{{ u.email }}</td>
                            <td class="py-2 px-4 border-b">
                                <div class="flex items-center gap-2">
                                    <select v-model="roleToAssign" class="border rounded p-1">
                                        <option v-for="r in roles" :key="r.name" :value="r.name">{{ r.name }}</option>
                                    </select>
                                    <button @click="assign(u)" class="px-2 py-1 bg-indigo-600 text-white rounded text-sm">Assign</button>
                                    <button @click="revoke(u)" class="px-2 py-1 bg-rose-600 text-white rounded text-sm">Revoke</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <p v-if="status" class="text-sm text-green-700 mt-2">{{ status }}</p>
    </div>
</template>

<script setup>
import axios from 'axios'
import { onMounted, ref } from 'vue'

const users = ref([])
const roles = ref([])
const roleToAssign = ref('Admin')
const status = ref('')

async function load() {
  const [u, r] = await Promise.all([
    axios.get('/admin/users'),
    axios.get('/directory/roles'),
  ])
  users.value = u.data.data ?? u.data
  roles.value = (r.data.data ?? r.data).map(x => ({ name: x.name }))
}

async function assign(u) {
  status.value = ''
  await axios.post(`/admin/users/${u.id}/roles/assign`, { role: roleToAssign.value })
  status.value = 'Role assigned'
}

async function revoke(u) {
  status.value = ''
  await axios.post(`/admin/users/${u.id}/roles/revoke`, { role: roleToAssign.value })
  status.value = 'Role revoked'
}

onMounted(load)
</script>

