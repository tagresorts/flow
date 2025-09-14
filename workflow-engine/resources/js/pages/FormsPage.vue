<template>
    <div class="bg-white p-6 rounded shadow">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-bold">Forms</h1>
            <button @click="showCreateForm = true" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Create New Form
            </button>
        </div>
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
                    No forms found. Create a form to get started.
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

        <!-- Create Form Modal -->
        <div v-if="showCreateForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <h2 class="text-xl font-bold mb-4">Create New Form</h2>
                
                <form @submit.prevent="createForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Form Name</label>
                        <input v-model="newForm.name" type="text" required class="w-full border rounded p-2" placeholder="Enter form name">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea v-model="newForm.description" class="w-full border rounded p-2" placeholder="Enter form description"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Workflow</label>
                        <select v-model="newForm.workflow_id" required class="w-full border rounded p-2">
                            <option value="">Select a workflow</option>
                            <option v-for="workflow in workflows" :key="workflow.id" :value="workflow.id">
                                {{ workflow.name }}
                            </option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Form Fields</label>
                        <div class="space-y-3">
                            <div v-for="(field, index) in newForm.fields" :key="index" class="border rounded p-3">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium">Field {{ index + 1 }}</h4>
                                    <button type="button" @click="removeField(index)" class="text-red-600 text-sm">Remove</button>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <input v-model="field.label" type="text" placeholder="Field Label" class="border rounded p-2" required>
                                    <select v-model="field.type" class="border rounded p-2" required>
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                        <option value="select">Select</option>
                                        <option value="radio">Radio</option>
                                        <option value="checkbox">Checkbox</option>
                                        <option value="file">File</option>
                                        <option value="textarea">Textarea</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <input v-model="field.variable_name" type="text" placeholder="Variable Name" class="border rounded p-2" required>
                                    <input v-model="field.default_value" type="text" placeholder="Default Value" class="border rounded p-2">
                                </div>
                                <div class="mt-2">
                                    <label class="flex items-center">
                                        <input v-model="field.is_required" type="checkbox" class="mr-2">
                                        Required field
                                    </label>
                                </div>
                            </div>
                            <button type="button" @click="addField" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">
                                Add Field
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showCreateForm = false" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Create Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

const forms = ref([]);
const selected = ref(null);
const showCreateForm = ref(false);
const workflows = ref([]);

const newForm = ref({
    name: '',
    description: '',
    workflow_id: '',
    fields: [
        {
            label: '',
            type: 'text',
            variable_name: '',
            default_value: '',
            is_required: false
        }
    ]
});

onMounted(() => {
    load();
    loadWorkflows();
});

async function load() {
    try {
        const res = await axios.get('/forms');
        forms.value = res.data.data ?? res.data;
        selected.value = forms.value[0] || null;
    } catch (error) {
        console.error('Error loading forms:', error);
    }
}

async function loadWorkflows() {
    try {
        const res = await axios.get('/workflows');
        workflows.value = res.data.data ?? res.data;
    } catch (error) {
        console.error('Error loading workflows:', error);
    }
}

function select(f) { 
    selected.value = f; 
}

function addField() {
    newForm.value.fields.push({
        label: '',
        type: 'text',
        variable_name: '',
        default_value: '',
        is_required: false
    });
}

function removeField(index) {
    if (newForm.value.fields.length > 1) {
        newForm.value.fields.splice(index, 1);
    }
}

async function createForm() {
    try {
        await axios.post('/forms', newForm.value);
        showCreateForm.value = false;
        resetNewForm();
        await load(); // Reload forms list
    } catch (error) {
        console.error('Error creating form:', error);
        alert('Error creating form: ' + (error.response?.data?.message || error.message));
    }
}

function resetNewForm() {
    newForm.value = {
        name: '',
        description: '',
        workflow_id: '',
        fields: [
            {
                label: '',
                type: 'text',
                variable_name: '',
                default_value: '',
                is_required: false
            }
        ]
    };
}
</script>

