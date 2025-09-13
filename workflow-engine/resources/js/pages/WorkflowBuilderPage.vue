<template>
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">Workflow Builder</h1>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <!-- Palette -->
            <div class="lg:col-span-3 bg-gray-50 border rounded p-3">
                <h2 class="font-semibold mb-2">Nodes</h2>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <button @click="addNode('Start')" class="px-2 py-2 bg-white border rounded hover:bg-gray-100">Start</button>
                    <button @click="addNode('Approval')" class="px-2 py-2 bg-white border rounded hover:bg-gray-100">Approval</button>
                    <button @click="addNode('Condition')" class="px-2 py-2 bg-white border rounded hover:bg-gray-100">Condition</button>
                    <button @click="addNode('Parallel')" class="px-2 py-2 bg-white border rounded hover:bg-gray-100">Parallel</button>
                    <button @click="addNode('End')" class="px-2 py-2 bg-white border rounded hover:bg-gray-100">End</button>
                </div>

                <div class="mt-4">
                    <h2 class="font-semibold mb-2">Linked Form Template</h2>
                    <div class="flex items-center gap-3">
                        <select v-model="selectedTemplateId" class="border rounded p-2 w-full">
                            <option :value="null">-- Select Template --</option>
                            <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                        <button @click="saveFormTemplate" class="px-3 py-2 bg-slate-700 text-white rounded">Save</button>
                    </div>
                    <p v-if="visualMeta && visualMeta.form_template_id" class="text-xs text-gray-500 mt-2">Current: #{{ visualMeta.form_template_id }}</p>
                </div>
            </div>

            <!-- Canvas -->
            <div class="lg:col-span-6 relative border rounded bg-white" :style="{ minHeight: '480px', backgroundSize: '20px 20px', backgroundImage: 'linear-gradient(to right, #f3f4f6 1px, transparent 1px), linear-gradient(to bottom, #f3f4f6 1px, transparent 1px)' }">
                <div v-for="n in nodes" :key="n.id" :style="nodeStyle(n)" @click.stop="selectNode(n.id)" class="absolute border rounded shadow px-3 py-2 cursor-pointer select-none"
                    :class="selectedNodeId === n.id ? 'ring-2 ring-indigo-500' : 'bg-gray-50'">
                    <div class="text-xs text-gray-500">{{ n.type }}</div>
                    <div class="font-semibold">{{ n.label }}</div>
                </div>

                <!-- simple edges rendering as lines -->
                <svg class="absolute inset-0 pointer-events-none" :width="'100%'" :height="'100%'">
                    <line v-for="e in edges" :key="e.id" :x1="edgePoints(e).x1" :y1="edgePoints(e).y1" :x2="edgePoints(e).x2" :y2="edgePoints(e).y2" stroke="#94a3b8" stroke-width="2" marker-end="url(#arrow)" />
                    <defs>
                        <marker id="arrow" viewBox="0 0 10 10" refX="10" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 0 L 10 5 L 0 10 z" fill="#94a3b8" />
                        </marker>
                    </defs>
                </svg>
            </div>

            <!-- Properties -->
            <div class="lg:col-span-3 bg-gray-50 border rounded p-3 space-y-4">
                <div v-if="selectedNode">
                    <h2 class="font-semibold mb-2">Properties</h2>
                    <label class="block text-sm mb-2">Label
                        <input v-model="selectedNode.label" class="mt-1 border rounded p-1 w-full" />
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="block text-sm">Row
                            <input type="number" v-model.number="selectedNode.row" class="mt-1 border rounded p-1 w-full" />
                        </label>
                        <label class="block text-sm">Col
                            <input type="number" v-model.number="selectedNode.col" class="mt-1 border rounded p-1 w-full" />
                        </label>
                    </div>

                    <div v-if="selectedNode.type === 'Parallel'" class="mt-3">
                        <h3 class="font-medium mb-1">Parallel Settings</h3>
                        <label class="block text-sm">Completion policy
                            <select v-model="selectedNode.parallelPolicy" class="mt-1 border rounded p-1 w-full">
                                <option value="ALL">All branches must complete</option>
                                <option value="ANY">Any one branch completes</option>
                            </select>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">Use edge conditions below to enable/disable branches based on form variables.</p>
                    </div>

                    <div class="mt-3">
                        <h3 class="font-medium mb-1">Connect</h3>
                        <div class="flex items-center gap-2">
                            <select v-model="connectToId" class="border rounded p-1 w-full">
                                <option :value="null">-- to node --</option>
                                <option v-for="n in nodes.filter(x => x.id !== selectedNodeId)" :key="n.id" :value="n.id">{{ n.label }}</option>
                            </select>
                            <button @click="createEdge()" class="px-2 py-1 bg-indigo-600 text-white rounded text-sm">Add</button>
                        </div>
                        <div class="mt-2 space-y-2">
                            <div v-for="e in outgoingEdges(selectedNodeId)" :key="e.id" class="bg-white border rounded p-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <strong>→ {{ nodeById(e.toId)?.label }}</strong>
                                    <button @click="deleteEdge(e.id)" class="text-rose-600">Remove</button>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <label class="block">Label
                                        <input v-model="e.label" class="mt-1 border rounded p-1 w-full" placeholder="e.g. Path A" />
                                    </label>
                                    <label class="block">Condition (optional)
                                        <input v-model="e.condition" class="mt-1 border rounded p-1 w-full" placeholder="e.g. amount > 5000" />
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button @click="deleteNode(selectedNodeId)" class="px-3 py-2 bg-rose-600 text-white rounded">Delete Node</button>
                    </div>
                </div>

                <div>
                    <h2 class="font-semibold mb-2">Save & Version</h2>
                    <div class="space-y-2">
                        <button @click="saveVisualFromState" class="px-3 py-2 bg-slate-800 text-white rounded w-full">Save Visual</button>
                        <div>
                            <label class="flex items-center gap-2 text-sm mb-2"><input type="checkbox" v-model="activateVersion" /> Set Active</label>
                            <button @click="generateDefinitionAndSave" class="px-3 py-2 bg-emerald-600 text-white rounded w-full">Generate Version</button>
                        </div>
                    </div>
                </div>

                <details>
                    <summary class="cursor-pointer text-sm text-gray-600">Advanced (raw JSON)</summary>
                    <div class="mt-2 space-y-2">
                        <textarea v-model="visualConfigJson" class="w-full h-28 border rounded p-2 font-mono text-xs"></textarea>
                        <textarea v-model="definitionJson" class="w-full h-28 border rounded p-2 font-mono text-xs"></textarea>
                    </div>
                </details>
            </div>
        </div>

        <p v-if="status" class="mt-4 text-sm text-green-700">{{ status }}</p>
    </div>
</template>

<script setup>
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

const workflowId = ref(null);
const nodes = ref([]);
const edges = ref([]);
const selectedNodeId = ref(null);
const connectToId = ref(null);

const visualConfigJson = ref('{\n  "nodes": [],\n  "edges": []\n}');
const definitionJson = ref('{\n  "start": null,\n  "nodes": {}\n}');
const activateVersion = ref(false);
const status = ref('');
const templates = ref([]);
const selectedTemplateId = ref(null);
const visualMeta = ref(null);

onMounted(async () => {
    const res = await axios.post('/builder/workflows/ensure-default');
    workflowId.value = res.data.id;
    if (res.data.visual_config) {
        loadFromVisual(res.data.visual_config);
    }
    const t = await axios.get('/builder/forms/templates');
    templates.value = t.data.data ?? t.data;
});

function loadFromVisual(visual) {
    nodes.value = visual.nodes || [];
    edges.value = visual.edges || [];
    visualMeta.value = visual.meta || null;
    selectedTemplateId.value = visualMeta.value?.form_template_id ?? null;
    visualConfigJson.value = JSON.stringify(visual, null, 2);
}

function serializeVisual() {
    return {
        nodes: nodes.value,
        edges: edges.value,
        meta: visualMeta.value || {},
    };
}

async function saveVisualFromState() {
    status.value = '';
    const visual = serializeVisual();
    visualConfigJson.value = JSON.stringify(visual, null, 2);
    await axios.post(`/builder/workflows/${workflowId.value}/visual`, {
        visual_config: visual,
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

async function saveFormTemplate() {
    status.value = '';
    await axios.post(`/builder/workflows/${workflowId.value}/form-template`, {
        form_template_id: selectedTemplateId.value,
    });
    status.value = 'Linked form template saved';
}

// Visual helpers
function addNode(type) {
    nodes.value.push({
        id: crypto.randomUUID(),
        type,
        label: type,
        row: nodes.value.length, // simple stacking
        col: 0,
        config: {},
    });
}

function selectNode(id) {
    selectedNodeId.value = id;
}

function deleteNode(id) {
    nodes.value = nodes.value.filter(n => n.id !== id);
    edges.value = edges.value.filter(e => e.fromId !== id && e.toId !== id);
    if (selectedNodeId.value === id) selectedNodeId.value = null;
}

function nodeById(id) {
    return nodes.value.find(n => n.id === id) || null;
}

function outgoingEdges(id) {
    return edges.value.filter(e => e.fromId === id);
}

function createEdge() {
    if (!selectedNodeId.value || !connectToId.value) return;
    edges.value.push({ id: crypto.randomUUID(), fromId: selectedNodeId.value, toId: connectToId.value, label: '', condition: '' });
    connectToId.value = null;
}

function deleteEdge(id) {
    edges.value = edges.value.filter(e => e.id !== id);
}

function nodeStyle(n) {
    const top = (n.row ?? 0) * 90 + 16;
    const left = (n.col ?? 0) * 160 + 16;
    return { top: top + 'px', left: left + 'px', width: '140px' };
}

const selectedNode = computed(() => nodeById(selectedNodeId.value));

function generateDefinition() {
    const start = nodes.value.find(n => n.type === 'Start') || nodes.value[0] || null;
    const def = { start: start?.id || null, nodes: {} };
    for (const n of nodes.value) {
        const out = outgoingEdges(n.id).map(e => ({ to: e.toId, label: e.label || null, condition: e.condition || null }));
        const nodeDef = { type: n.type, next: out };
        if (n.type === 'Parallel') {
            nodeDef.policy = n.parallelPolicy || 'ALL'; // ALL or ANY
        }
        def.nodes[n.id] = nodeDef;
    }
    return def;
}

async function generateDefinitionAndSave() {
    const def = generateDefinition();
    definitionJson.value = JSON.stringify(def, null, 2);
    await saveVersion();
}
</script>

