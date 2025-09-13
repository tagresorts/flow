<template>
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">SMTP Settings</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm">Preset</label>
                <select v-model="preset" class="border rounded p-2 w-full mt-1" @change="applyPreset">
                    <option value="custom">Custom</option>
                    <option value="m365">Microsoft 365</option>
                    <option value="gmail">Gmail</option>
                </select>

                <label class="block text-sm mt-3">Host</label>
                <input v-model="form.host" class="border rounded p-2 w-full mt-1" />

                <label class="block text-sm mt-3">Port</label>
                <input v-model.number="form.port" type="number" class="border rounded p-2 w-full mt-1" />

                <label class="block text-sm mt-3">Encryption</label>
                <select v-model="form.encryption" class="border rounded p-2 w-full mt-1">
                    <option :value="null">None</option>
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                </select>

                <label class="block text-sm mt-3">Username</label>
                <input v-model="form.username" class="border rounded p-2 w-full mt-1" />

                <label class="block text-sm mt-3">Password</label>
                <input v-model="form.password" type="password" class="border rounded p-2 w-full mt-1" />

                <label class="block text-sm mt-3">From Address</label>
                <input v-model="form.from_address" class="border rounded p-2 w-full mt-1" />

                <label class="block text-sm mt-3">From Name</label>
                <input v-model="form.from_name" class="border rounded p-2 w-full mt-1" />

                <label class="inline-flex items-center mt-3 text-sm"><input type="checkbox" v-model="form.is_active" class="mr-2" /> Active</label>

                <div class="mt-4 flex items-center gap-2">
                    <button @click="save" class="px-3 py-2 bg-indigo-600 text-white rounded">Save</button>
                    <button @click="sendTest" class="px-3 py-2 bg-slate-700 text-white rounded">Send Test</button>
                </div>

                <p v-if="status" class="text-sm text-green-700 mt-2">{{ status }}</p>
            </div>
            <div>
                <h2 class="font-semibold mb-2">Recent Settings</h2>
                <ul class="text-sm divide-y">
                    <li v-for="s in settings" :key="s.id" class="py-2">{{ s.provider }} - {{ s.host }} : {{ s.port }} ({{ s.is_active ? 'active' : 'inactive' }})</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios'
import { onMounted, ref } from 'vue'

const preset = ref('custom')
const form = ref({ provider: 'custom', host: '', port: 587, encryption: 'tls', username: '', password: '', from_address: '', from_name: '', is_active: true })
const status = ref('')
const settings = ref([])

const presets = {
  m365: { host: 'smtp.office365.com', port: 587, encryption: 'tls' },
  gmail: { host: 'smtp.gmail.com', port: 587, encryption: 'tls' },
  custom: { host: '', port: 587, encryption: 'tls' }
}

function applyPreset() {
  const p = presets[preset.value]
  form.value.provider = preset.value
  form.value.host = p.host
  form.value.port = p.port
  form.value.encryption = p.encryption
}

async function load() {
  const res = await axios.get('/admin/mail-settings')
  settings.value = res.data.data ?? res.data
}

async function save() {
  status.value = ''
  const res = await axios.post('/admin/mail-settings', form.value)
  status.value = 'Saved'
  await load()
}

async function sendTest() {
  const to = prompt('Send test email to:')
  if (!to) return
  await axios.post('/admin/mail-settings/test', { to })
  status.value = 'Test sent'
}

onMounted(load)
</script>

