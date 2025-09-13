<template>
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">Form Builder</h1>

        <div class="mb-4 flex items-center gap-3">
            <input v-model="name" placeholder="Template name" class="border rounded p-2 w-full md:w-1/3" />
            <button @click="saveTemplate" class="px-3 py-2 bg-indigo-600 text-white rounded">Save Template</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <div class="lg:col-span-3 bg-gray-50 border rounded p-3">
                <h2 class="font-semibold mb-2">Fields</h2>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <button v-for="f in palette" :key="f.type" @click="addField(f)" class="px-2 py-2 bg-white border rounded hover:bg-gray-100">{{ f.label }}</button>
                </div>
            </div>

            <div class="lg:col-span-6 min-h-[400px] bg-gray-50 border rounded p-3">
                <h2 class="font-semibold mb-2">Canvas</h2>
                <div v-if="fields.length === 0" class="text-gray-500 text-sm">Click a field to add it to the form.</div>
                <ul class="space-y-3">
                    <li v-for="(field, idx) in fields" :key="field.id" class="bg-white border rounded p-3">
                        <div class="flex items-center justify-between">
                            <div class="font-medium">{{ idx+1 }}. {{ field.label }} <span class="text-xs text-gray-400">({{ field.type }})</span></div>
                            <div class="flex items-center gap-2">
                                <button @click="select(idx)" class="text-indigo-600 text-sm">Edit</button>
                                <button @click="remove(idx)" class="text-rose-600 text-sm">Remove</button>
                            </div>
                        </div>
                        <div v-if="selectedIndex === idx" class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <label class="block">Label
                                <input v-model="field.label" class="mt-1 border rounded p-1 w-full" />
                            </label>
                            <label class="block">Variable Name
                                <input v-model="field.variable_name" class="mt-1 border rounded p-1 w-full" />
                            </label>
                            <label class="block">Required
                                <input type="checkbox" v-model="field.is_required" class="ml-2 align-middle" />
                            </label>
                            <label class="block" v-if="['select','radio','checkbox'].includes(field.type)">Options (comma-separated)
                                <input v-model="field.optionsText" class="mt-1 border rounded p-1 w-full" />
                            </label>
                            <label class="block">Default Value
                                <input v-model="field.default_value" class="mt-1 border rounded p-1 w-full" />
                            </label>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="lg:col-span-3 bg-gray-50 border rounded p-3">
                <h2 class="font-semibold mb-2">Preview</h2>
                <div class="space-y-3">
                    <div v-for="f in fields" :key="f.id">
                        <label class="block text-sm font-medium">{{ f.label }}</label>
                        <input v-if="f.type==='text'" class="mt-1 border rounded p-2 w-full" :placeholder="f.default_value" />
                        <input v-else-if="f.type==='number'" type="number" class="mt-1 border rounded p-2 w-full" :placeholder="f.default_value" />
                        <input v-else-if="f.type==='date'" type="date" class="mt-1 border rounded p-2 w-full" />
                        <select v-else-if="f.type==='select'" class="mt-1 border rounded p-2 w-full">
                            <option v-for="opt in (f.optionsText?.split(',').map(o=>o.trim()).filter(Boolean) || [])" :key="opt">{{ opt }}</option>
                        </select>
                        <div v-else-if="f.type==='radio'" class="mt-1">
                            <label v-for="opt in (f.optionsText?.split(',').map(o=>o.trim()).filter(Boolean) || [])" :key="opt" class="mr-3 text-sm">
                                <input type="radio" :name="f.variable_name" class="mr-1" /> {{ opt }}
                            </label>
                        </div>
                        <div v-else-if="f.type==='checkbox'" class="mt-1">
                            <label v-for="opt in (f.optionsText?.split(',').map(o=>o.trim()).filter(Boolean) || [])" :key="opt" class="mr-3 text-sm">
                                <input type="checkbox" class="mr-1" /> {{ opt }}
                            </label>
                        </div>
                        <input v-else-if="f.type==='file'" type="file" class="mt-1 border rounded p-2 w-full" />
                        <textarea v-else-if="f.type==='textarea'" class="mt-1 border rounded p-2 w-full" :placeholder="f.default_value"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <p v-if="status" class="mt-4 text-sm text-green-700">{{ status }}</p>
    </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

const name = ref('Untitled Form');
const fields = ref([]);
const selectedIndex = ref(-1);
const status = ref('');
const templates = ref([]);

const palette = [
    { type: 'text', label: 'Text' },
    { type: 'textarea', label: 'Textarea' },
    { type: 'number', label: 'Number' },
    { type: 'date', label: 'Date' },
    { type: 'select', label: 'Select' },
    { type: 'radio', label: 'Radio' },
    { type: 'checkbox', label: 'Checkbox' },
    { type: 'file', label: 'File Upload' },
];

function addField(proto) {
    fields.value.push({
        id: crypto.randomUUID(),
        type: proto.type,
        label: proto.label,
        variable_name: proto.type + '_' + (fields.value.length + 1),
        is_required: false,
        optionsText: '',
        default_value: '',
    });
}

function select(idx) {
    selectedIndex.value = idx;
}

function remove(idx) {
    fields.value.splice(idx, 1);
    if (selectedIndex.value === idx) selectedIndex.value = -1;
}

onMounted(loadTemplates);

async function loadTemplates() {
    const res = await axios.get('/builder/forms/templates');
    templates.value = res.data.data ?? res.data;
}

async function saveTemplate() {
    status.value = '';
    const jsonSchema = {
        fields: fields.value.map(f => ({
            type: f.type,
            label: f.label,
            variable_name: f.variable_name,
            is_required: f.is_required,
            options: (f.optionsText?.split(',').map(o=>o.trim()).filter(Boolean)) || [],
            default_value: f.default_value,
        })),
    };
    await axios.post('/builder/forms/templates', {
        name: name.value,
        json_schema: jsonSchema,
    });
    status.value = 'Template saved';
    await loadTemplates();
}
</script>

