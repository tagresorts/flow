<template>
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">Form Builder</h1>
        <div class="mb-2">
            <input v-model="name" placeholder="Template name" class="border rounded p-2 w-full md:w-1/2" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h2 class="font-semibold mb-2">Form Schema (JSON)</h2>
                <textarea v-model="schemaJson" class="w-full h-64 border rounded p-2 font-mono text-xs"></textarea>
                <button @click="saveTemplate" class="mt-2 px-3 py-2 bg-indigo-600 text-white rounded">Save Template</button>
            </div>
            <div>
                <h2 class="font-semibold mb-2">Existing Templates</h2>
                <ul class="list-disc list-inside text-sm">
                    <li v-for="t in templates" :key="t.id">{{ t.name }}</li>
                </ul>
            </div>
        </div>
        <p v-if="status" class="mt-4 text-sm text-green-700">{{ status }}</p>
    </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

const name = ref('Untitled Form');
const schemaJson = ref('{\n  "fields": []\n}');
const templates = ref([]);
const status = ref('');

onMounted(loadTemplates);

async function loadTemplates() {
    const res = await axios.get('/builder/forms/templates');
    templates.value = res.data.data ?? res.data; // supports pagination or array
}

async function saveTemplate() {
    status.value = '';
    await axios.post('/builder/forms/templates', {
        name: name.value,
        json_schema: JSON.parse(schemaJson.value || '{}'),
    });
    status.value = 'Template saved';
    await loadTemplates();
}
</script>

