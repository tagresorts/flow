<template>
    <div class="bg-white p-6 rounded shadow">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-bold">Forms Management</h1>
            <button @click="openCreateModal" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Create New Form
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="font-semibold mb-2">All Forms</h2>
                <div v-if="forms.length === 0" class="text-gray-500 text-sm py-4">
                    No forms found. Create your first form to get started.
                </div>
                <ul v-else class="divide-y">
                    <li v-for="f in forms" :key="f.id" class="py-3 cursor-pointer hover:bg-gray-50 px-2 rounded" @click="select(f)" :class="{ 'bg-blue-50 border-l-4 border-blue-400': selected?.id === f.id }">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="font-medium">{{ f.name }}</div>
                                <div class="text-xs text-gray-500">
                                    Workflow: {{ f.workflow?.name || 'No workflow' }}
                                    <span v-if="!f.is_active" class="ml-2 text-orange-600">(Inactive)</span>
                                </div>
                                <div class="text-xs text-gray-400">{{ f.fields?.length || 0 }} fields</div>
                            </div>
                            <div class="flex gap-1 ml-2">
                                <button @click.stop="editForm(f)" class="text-blue-600 hover:text-blue-800 text-sm px-2 py-1">Edit</button>
                                <button @click.stop="deleteForm(f)" class="text-red-600 hover:text-red-800 text-sm px-2 py-1">Delete</button>
                            </div>
                        </div>
                    </li>
                </ul>
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

        <!-- Form Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <h2 class="text-xl font-bold mb-4">{{ editingForm ? 'Edit Form' : 'Create New Form' }}</h2>
                
                <form @submit.prevent="saveForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Form Name</label>
                            <input v-model="formData.name" type="text" required class="w-full border rounded p-2" placeholder="Enter form name">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Workflow (Optional)</label>
                            <select v-model="formData.workflow_id" class="w-full border rounded p-2">
                                <option value="">No workflow</option>
                                <option v-for="workflow in workflows" :key="workflow.id" :value="workflow.id">
                                    {{ workflow.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea v-model="formData.description" class="w-full border rounded p-2" placeholder="Enter form description"></textarea>
                    </div>
                    
                    <div class="flex items-center">
                        <input v-model="formData.is_active" type="checkbox" id="is_active" class="mr-2">
                        <label for="is_active" class="text-sm font-medium">Active</label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Form Fields</label>
                        <div class="space-y-3">
                            <div v-for="(field, index) in formData.fields" :key="index" class="border rounded p-3">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium">Field {{ index + 1 }}</h4>
                                    <button type="button" @click="removeField(index)" class="text-red-600 text-sm px-2 py-1" :disabled="formData.fields.length === 1">Remove</button>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
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
                                    <input v-model="field.variable_name" type="text" placeholder="Variable Name" class="border rounded p-2" required>
                                    <input v-model="field.default_value" type="text" placeholder="Default Value" class="border rounded p-2">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
                                    <div class="flex items-center">
                                        <input v-model="field.is_required" type="checkbox" class="mr-2">
                                        <label class="text-sm">Required field</label>
                                    </div>
                                    <div v-if="['select','radio','checkbox'].includes(field.type)">
                                        <input v-model="field.optionsText" type="text" placeholder="Options (comma-separated)" class="border rounded p-2 w-full">
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="addField" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">
                                Add Field
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            {{ editingForm ? 'Update Form' : 'Create Form' }}
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
const showModal = ref(false);
const editingForm = ref(null);
const workflows = ref([]);

const formData = ref({
    name: '',
    description: '',
    workflow_id: '',
    is_active: true,
    fields: [
        {
            label: '',
            type: 'text',
            variable_name: '',
            default_value: '',
            is_required: false,
            optionsText: ''
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
        if (forms.value.length > 0 && !selected.value) {
            selected.value = forms.value[0];
        }
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

function openCreateModal() {
    editingForm.value = null;
    resetFormData();
    showModal.value = true;
}

function editForm(form) {
    editingForm.value = form;
    formData.value = {
        name: form.name,
        description: form.description || '',
        workflow_id: form.workflow_id || '',
        is_active: form.is_active,
        fields: form.fields?.map(field => ({
            label: field.label,
            type: field.type,
            variable_name: field.variable_name,
            default_value: field.default_value || '',
            is_required: field.is_required,
            optionsText: Array.isArray(field.options) ? field.options.join(', ') : ''
        })) || [{
            label: '',
            type: 'text',
            variable_name: '',
            default_value: '',
            is_required: false,
            optionsText: ''
        }]
    };
    showModal.value = true;
}

function addField() {
    formData.value.fields.push({
        label: '',
        type: 'text',
        variable_name: '',
        default_value: '',
        is_required: false,
        optionsText: ''
    });
}

function removeField(index) {
    if (formData.value.fields.length > 1) {
        formData.value.fields.splice(index, 1);
    }
}

async function saveForm() {
    try {
        const dataToSend = {
            ...formData.value,
            fields: formData.value.fields.map(field => ({
                label: field.label,
                type: field.type,
                variable_name: field.variable_name,
                default_value: field.default_value,
                is_required: field.is_required,
                options: field.optionsText ? field.optionsText.split(',').map(o => o.trim()).filter(Boolean) : []
            }))
        };

        if (editingForm.value) {
            await axios.put(`/forms/${editingForm.value.id}`, dataToSend);
        } else {
            await axios.post('/forms', dataToSend);
        }

        closeModal();
        await load();
    } catch (error) {
        console.error('Error saving form:', error);
        alert('Error saving form: ' + (error.response?.data?.message || error.message));
    }
}

async function deleteForm(form) {
    if (confirm(`Are you sure you want to delete "${form.name}"?`)) {
        try {
            await axios.delete(`/forms/${form.id}`);
            await load();
            if (selected.value?.id === form.id) {
                selected.value = forms.value[0] || null;
            }
        } catch (error) {
            console.error('Error deleting form:', error);
            alert('Error deleting form: ' + (error.response?.data?.message || error.message));
        }
    }
}

function closeModal() {
    showModal.value = false;
    editingForm.value = null;
    resetFormData();
}

function resetFormData() {
    formData.value = {
        name: '',
        description: '',
        workflow_id: '',
        is_active: true,
        fields: [
            {
                label: '',
                type: 'text',
                variable_name: '',
                default_value: '',
                is_required: false,
                optionsText: ''
            }
        ]
    };
}
</script>

