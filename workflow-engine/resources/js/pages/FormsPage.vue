<template>
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">Forms</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="font-semibold mb-2">Published Forms</h2>
                <ul class="divide-y">
                    <li v-for="f in forms" :key="f.id" class="py-3 cursor-pointer hover:bg-gray-50 px-2 rounded" @click="select(f)">
                        <div class="font-medium">{{ f.name }}</div>
                        <div class="text-xs text-gray-500">Workflow: {{ f.workflow?.name || '—' }}</div>
                    </li>
                </ul>
                <div v-if="forms.length === 0" class="text-gray-500 text-sm py-4">
                    No forms found. Create a form template in the <a href="/dashboard/builder/form" class="text-blue-600 hover:underline">Form Builder</a> and link it to a workflow.
                </div>
            </div>
            <div v-if="selected">
                <h2 class="font-semibold mb-2">Preview: {{ selected.name }}</h2>
                <div class="space-y-3">
                    <div v-for="fld in selected.fields" :key="fld.id">
                        <label class="block text-sm font-medium">{{ fld.label }}</label>
                        <input v-if="fld.type==='text'" class="mt-1 border rounded p-2 w-full" :placeholder="fld.default_value" />
                        <input v-else-if="fld.type==='number'" type="number" class="mt-1 border rounded p-2 w-full" :placeholder="fld.default_value" />
                        <input v-else-if="fld.type==='date'" type="date" class="mt-1 border rounded p-2 w-full" />
                        <select v-else-if="fld.type==='select'" class="mt-1 border rounded p-2 w-full">
                            <option v-for="opt in (fld.options || [])" :key="opt">{{ opt }}</option>
                        </select>
                        <div v-else-if="fld.type==='radio'" class="mt-1">
                            <label v-for="opt in (fld.options || [])" :key="opt" class="mr-3 text-sm">
                                <input type="radio" :name="fld.variable_name" class="mr-1" /> {{ opt }}
                            </label>
                        </div>
                        <div v-else-if="fld.type==='checkbox'" class="mt-1">
                            <label v-for="opt in (fld.options || [])" :key="opt" class="mr-3 text-sm">
                                <input type="checkbox" class="mr-1" /> {{ opt }}
                            </label>
                        </div>
                        <input v-else-if="fld.type==='file'" type="file" class="mt-1 border rounded p-2 w-full" />
                        <textarea v-else-if="fld.type==='textarea'" class="mt-1 border rounded p-2 w-full" :placeholder="fld.default_value"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

const forms = ref([]);
const selected = ref(null);

onMounted(load);

async function load() {
    const res = await axios.get('/forms');
    forms.value = res.data.data ?? res.data;
    selected.value = forms.value[0] || null;
}

function select(f) { selected.value = f; }
</script>

