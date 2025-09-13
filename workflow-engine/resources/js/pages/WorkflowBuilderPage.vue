<template>
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">Workflow Builder</h1>
        <div class="mb-4 text-sm text-gray-600">Builder canvas placeholder. Save will persist visual config and version JSON.</div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h2 class="font-semibold mb-2">Visual Config (JSON)</h2>
                <textarea v-model="visualConfigJson" class="w-full h-56 border rounded p-2 font-mono text-xs"></textarea>
                <button @click="saveVisual" class="mt-2 px-3 py-2 bg-indigo-600 text-white rounded">Save Visual</button>
            </div>
            <div>
                <h2 class="font-semibold mb-2">Definition JSON (Executable)</h2>
                <textarea v-model="definitionJson" class="w-full h-56 border rounded p-2 font-mono text-xs"></textarea>
                <div class="mt-2 flex items-center gap-2">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="activateVersion" /> Set Active</label>
                    <button @click="saveVersion" class="px-3 py-2 bg-emerald-600 text-white rounded">Create Version</button>
                </div>
            </div>
        </div>
        <p v-if="status" class="mt-4 text-sm text-green-700">{{ status }}</p>
    </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

const workflowId = ref(null);
const visualConfigJson = ref('{\n  "nodes": []\n}');
const definitionJson = ref('{\n  "start": "node-1",\n  "nodes": {}\n}');
const activateVersion = ref(false);
const status = ref('');

onMounted(async () => {
    const res = await axios.post('/builder/workflows/ensure-default');
    workflowId.value = res.data.id;
    if (res.data.visual_config) {
        visualConfigJson.value = JSON.stringify(res.data.visual_config, null, 2);
    }
});

async function saveVisual() {
    status.value = '';
    await axios.post(`/builder/workflows/${workflowId.value}/visual`, {
        visual_config: JSON.parse(visualConfigJson.value || '{}'),
    });
    status.value = 'Visual config saved';
}

async function saveVersion() {
    status.value = '';
    await axios.post(`/builder/workflows/${workflowId.value}/versions`, {
        definition_json: JSON.parse(definitionJson.value || '{}'),
        is_active: activateVersion.value,
    });
    status.value = 'Version created';
}
</script>

