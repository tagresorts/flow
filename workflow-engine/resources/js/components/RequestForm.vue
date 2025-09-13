<template>
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-4">Submit a New Request</h2>
        <form @submit.prevent="submitRequest">
            <div class="mb-4">
                <label for="workflow" class="block text-gray-700 font-bold mb-2">Workflow</label>
                <select id="workflow" v-model="selectedWorkflow" class="w-full px-3 py-2 border rounded">
                    <option disabled value="">Please select one</option>
                    <!-- In a real application, this would be populated from an API -->
                    <option value="1">Purchase Request</option>
                    <option value="2">Leave Request</option>
                </select>
            </div>

            <!-- Dynamic form fields would be rendered here based on the selected workflow -->
            <div class="mb-4">
                <label for="amount" class="block text-gray-700 font-bold mb-2">Amount</label>
                <input type="number" id="amount" v-model="formData.amount" class="w-full px-3 py-2 border rounded">
            </div>

            <div class="mb-4">
                <label for="reason" class="block text-gray-700 font-bold mb-2">Reason</label>
                <textarea id="reason" v-model="formData.reason" class="w-full px-3 py-2 border rounded"></textarea>
            </div>

            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Submit
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const selectedWorkflow = ref('');
const formData = ref({
    amount: 0,
    reason: '',
});

const submitRequest = () => {
    // In a real application, this would make an API call to submit the request
    console.log('Submitting request for workflow:', selectedWorkflow.value);
    console.log('Form data:', formData.value);
    alert('Request submitted!');
};
</script>
