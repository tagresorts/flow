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
                    <button @click="addNode('Merge')" class="px-2 py-2 bg-white border rounded hover:bg-gray-100">Merge</button>
                    <button @click="addNode('End')" class="px-2 py-2 bg-white border rounded hover:bg-gray-100">End</button>
                </div>

                <div class="mt-3 text-xs text-gray-600">
                    <p class="mb-1"><strong>Tip:</strong> Use Parallel to split into multiple approvals, then connect them back to a Merge (or End). Parallel policy controls ALL vs ANY completion.</p>
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
                <div v-for="n in nodes" :key="n.id" :style="nodeStyle(n)" @mousedown.stop="startDrag(n, $event)" @click.stop="selectNode(n.id)" class="absolute border rounded shadow px-3 py-2 cursor-move select-none"
                    :class="selectedNodeId === n.id ? 'ring-2 ring-indigo-500' : 'bg-gray-50'">
                    <div class="text-xs text-gray-500">{{ n.type }}</div>
                    <div class="font-semibold">{{ n.label }}</div>
                    <div class="text-[10px] text-gray-400">{{ shortId(n.id) }}</div>
                </div>

                <!-- simple edges rendering as lines -->
                <svg class="absolute inset-0 pointer-events-none" :width="'100%'" :height="'100%'">
                    <line v-for="e in edges" :key="e.id" :x1="edgePoints(e).x1" :y1="edgePoints(e).y1" :x2="edgePoints(e).x2" :y2="edgePoints(e).y2" :stroke="edgeStroke(e)" :stroke-dasharray="e.condition ? '4 4' : null" :stroke-width="edgeWidth(e)" marker-end="url(#arrow)" />
                    <line v-if="rubberBand" :x1="rubberBand.x1" :y1="rubberBand.y1" :x2="rubberBand.x2" :y2="rubberBand.y2" stroke="#6366f1" stroke-dasharray="4 4" stroke-width="2" />
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

                    <div v-if="selectedNode.type === 'Approval'" class="mt-3">
                        <h3 class="font-medium mb-2">Approvers</h3>
                        <div class="text-xs text-gray-500 mb-2">Choose by user or role; configure rule & SLA.</div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <select v-model="approverSelectorMode" class="border rounded p-1">
                                    <option value="users">Users</option>
                                    <option value="roles">Roles</option>
                                </select>
                                <button @click="openApproverPicker" class="px-2 py-1 bg-slate-700 text-white rounded text-sm">Add</button>
                            </div>
                            <ul class="text-xs space-y-1">
                                <li v-for="(a, ai) in (selectedNode.approvers || [])" :key="ai" class="flex items-center justify-between bg-white border rounded p-1">
                                    <span>{{ a.type }}: {{ a.name || a.id }}</span>
                                    <button @click="selectedNode.approvers.splice(ai,1)" class="text-rose-600">Remove</button>
                                </li>
                            </ul>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="block text-sm">Rule
                                    <select v-model="(selectedNode.approvalRule)" class="mt-1 border rounded p-1 w-full">
                                        <option value="ALL">All must approve</option>
                                        <option value="ANY">Any one can approve</option>
                                        <option value="MAJORITY">Majority</option>
                                    </select>
                                </label>
                                <label class="block text-sm">SLA (hours)
                                    <input type="number" v-model.number="(selectedNode.slaHours)" class="mt-1 border rounded p-1 w-full" />
                                </label>
                            </div>
                            <div>
                                <h4 class="font-medium mb-1">Notifications</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="block text-sm">Email To
                                        <input v-model="selectedNode.notifyEmail" placeholder="user@example.com" class="mt-1 border rounded p-1 w-full" />
                                    </label>
                                    <label class="block text-sm">Webhook URL
                                        <input v-model="selectedNode.webhookUrl" placeholder="https://..." class="mt-1 border rounded p-1 w-full" />
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h3 class="font-medium mb-1">Connect</h3>
                        <div class="flex items-center gap-2">
                            <template v-if="selectedNode?.type === 'Parallel'">
                                <select multiple v-model="connectToIds" class="border rounded p-1 w-full h-20">
                                    <option v-for="n in nodes.filter(x => x.id !== selectedNodeId)" :key="n.id" :value="n.id">{{ n.label }} [{{ shortId(n.id) }}]</option>
                                </select>
                            </template>
                            <template v-else>
                                <select v-model="connectToId" class="border rounded p-1 w-full">
                                    <option :value="null">-- to node --</option>
                                    <option v-for="n in nodes.filter(x => x.id !== selectedNodeId)" :key="n.id" :value="n.id">{{ n.label }} [{{ shortId(n.id) }}]</option>
                                </select>
                            </template>
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
                                    <label class="block">Mode
                                        <select v-model="e.condMode" class="mt-1 border rounded p-1 w-full">
                                            <option value="builder">Builder</option>
                                            <option value="advanced">Advanced</option>
                                        </select>
                                    </label>
                                </div>
                                <div v-if="e.condMode !== 'advanced'" class="grid grid-cols-3 gap-2 mt-2">
                                    <label class="block">Field
                                        <select v-model="e.cbField" class="mt-1 border rounded p-1 w-full">
                                            <option :value="null">-- choose --</option>
                                            <option v-for="f in formFields" :key="f.variable_name" :value="f.variable_name">{{ f.label }} ({{ f.variable_name }})</option>
                                        </select>
                                    </label>
                                    <label class="block">Operator
                                        <select v-model="e.cbOp" class="mt-1 border rounded p-1 w-full">
                                            <option value=">">></option>
                                            <option value=">=">>=</option>
                                            <option value="<"><</option>
                                            <option value="<="><=</option>
                                            <option value="==">=</option>
                                            <option value="!=">!=</option>
                                            <option value="contains">contains</option>
                                            <option value="startsWith">startsWith</option>
                                            <option value="endsWith">endsWith</option>
                                            <option value="regex">regex</option>
                                            <option value="between">between</option>
                                            <option value="in">in</option>
                                            <option value="notIn">not in</option>
                                        </select>
                                    </label>
                                    <label class="block">Value
                                        <input v-model="e.cbValue" class="mt-1 border rounded p-1 w-full" placeholder="e.g. 5000 or foo,bar" />
                                    </label>
                                    <label v-if="e.cbOp==='between'" class="block">And
                                        <input v-model="e.cbValue2" class="mt-1 border rounded p-1 w-full" placeholder="e.g. 10000" />
                                    </label>
                                    <div class="col-span-3 text-right">
                                        <button @click="applyBuiltCondition(e)" class="px-2 py-1 bg-slate-600 text-white rounded text-xs">Apply</button>
                                    </div>
                                </div>
                                <div v-else class="mt-2">
                                    <label class="block">Expression
                                        <input v-model="e.condition" class="mt-1 border rounded p-1 w-full" placeholder="e.g. amount > 5000 && department == 'HR'" />
                                    </label>
                                    <p class="text-[10px] text-gray-500 mt-1">Use AND/OR, parentheses, and functions: contains(x,y), startsWith(x,y), endsWith(x,y), regex(x,pattern).</p>
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
const connectToIds = ref([]);
let dragging = null; // { id, offsetX, offsetY }
const rubberBand = ref(null);

const visualConfigJson = ref('{\n  "nodes": [],\n  "edges": []\n}');
const definitionJson = ref('{\n  "start": null,\n  "nodes": {}\n}');
const activateVersion = ref(false);
const status = ref('');
const templates = ref([]);
const selectedTemplateId = ref(null);
const visualMeta = ref(null);
const approverSelectorMode = ref('users');
const formFields = ref([]);

onMounted(async () => {
    const res = await axios.post('/builder/workflows/ensure-default');
    workflowId.value = res.data.id;
    if (res.data.visual_config) {
        loadFromVisual(res.data.visual_config);
    }
    const t = await axios.get('/builder/forms/templates');
    templates.value = t.data.data ?? t.data;
    // load form fields of linked template (if any via published form)
    if (workflowId.value) {
        const formRes = await axios.get(`/workflows/${workflowId.value}/form`);
        formFields.value = formRes.data?.fields || [];
    }
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
        approvers: type === 'Approval' ? [] : undefined,
        approvalRule: type === 'Approval' ? 'ALL' : undefined,
        slaHours: type === 'Approval' ? 48 : undefined,
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
    if (selectedNode.value?.type === 'Parallel' && connectToIds.value && connectToIds.value.length) {
        for (const to of connectToIds.value) {
            edges.value.push({ id: crypto.randomUUID(), fromId: selectedNodeId.value, toId: to, label: '', condition: '', condMode: 'builder' });
        }
        connectToIds.value = [];
    } else {
        edges.value.push({ id: crypto.randomUUID(), fromId: selectedNodeId.value, toId: connectToId.value, label: '', condition: '', condMode: 'builder' });
        connectToId.value = null;
    }
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
        if (n.type === 'Approval') {
            nodeDef.approval = {
                rule: n.approvalRule || 'ALL',
                slaHours: n.slaHours ?? null,
                approvers: (n.approvers || []).map(a => ({ type: a.type, id: a.id, name: a.name || null })),
                notifyEmail: n.notifyEmail || null,
                webhookUrl: n.webhookUrl || null,
            };
        }
        def.nodes[n.id] = nodeDef;
    }
    return def;
}

async function openApproverPicker() {
    if (approverSelectorMode.value === 'users') {
        const res = await axios.get('/directory/users');
        const pick = prompt('Pick user id to add as approver:\n' + res.data.map(u => `${u.id}: ${u.name} <${u.email}>`).join('\n'));
        if (pick && selectedNode.value) selectedNode.value.approvers.push({ type: 'user', id: Number(pick), name: res.data.find(u => u.id === Number(pick))?.name });
    } else {
        const res = await axios.get('/directory/roles');
        const pick = prompt('Pick role id to add as approver:\n' + res.data.map(r => `${r.id}: ${r.name}`).join('\n'));
        if (pick && selectedNode.value) selectedNode.value.approvers.push({ type: 'role', id: Number(pick), name: res.data.find(r => r.id === Number(pick))?.name });
    }
}

async function generateDefinitionAndSave() {
    const def = generateDefinition();
    definitionJson.value = JSON.stringify(def, null, 2);
    await saveVersion();
}

// Edge visuals
function edgeStroke(e) {
    const from = nodeById(e.fromId);
    if (from?.type === 'Parallel') return '#2563eb'; // blue for split
    if (from?.type === 'Condition') return '#16a34a'; // green for condition
    return '#94a3b8';
}

// Dragging logic and rubber-band connector
function startDrag(n, evt) {
    selectedNodeId.value = n.id;
    const rect = evt.currentTarget.parentElement.getBoundingClientRect();
    const nodeTop = (n.row ?? 0) * 90 + 16;
    const nodeLeft = (n.col ?? 0) * 160 + 16;
    dragging = {
        id: n.id,
        startX: evt.clientX,
        startY: evt.clientY,
        offsetX: evt.clientX - (rect.left + nodeLeft),
        offsetY: evt.clientY - (rect.top + nodeTop),
    };
    window.addEventListener('mousemove', onDragMove);
    window.addEventListener('mouseup', onDragEnd);
    // hold Ctrl to start rubber-band
    if (evt.ctrlKey) {
        rubberBand.value = { x1: evt.clientX - rect.left, y1: evt.clientY - rect.top, x2: evt.clientX - rect.left, y2: evt.clientY - rect.top, fromId: n.id };
    }
}

function onDragMove(evt) {
    const canvas = document.querySelector('.lg\\:col-span-6.relative');
    if (!canvas) return;
    const rect = canvas.getBoundingClientRect();
    if (dragging) {
        const x = evt.clientX - rect.left - dragging.offsetX;
        const y = evt.clientY - rect.top - dragging.offsetY;
        const node = nodeById(dragging.id);
        if (node) {
            node.col = Math.max(0, Math.round((x - 16) / 160));
            node.row = Math.max(0, Math.round((y - 16) / 90));
        }
    }
    if (rubberBand.value) {
        rubberBand.value.x2 = evt.clientX - rect.left;
        rubberBand.value.y2 = evt.clientY - rect.top;
    }
}

function onDragEnd(evt) {
    window.removeEventListener('mousemove', onDragMove);
    window.removeEventListener('mouseup', onDragEnd);
    if (rubberBand.value) {
        // hit-test end node
        const canvas = document.querySelector('.lg\\:col-span-6.relative');
        const rect = canvas.getBoundingClientRect();
        const endX = evt.clientX - rect.left;
        const endY = evt.clientY - rect.top;
        const to = nodes.value.find(n => {
            const ns = nodeStyle(n);
            const left = parseInt(ns.left);
            const top = parseInt(ns.top);
            const right = left + 140;
            const bottom = top + 56; // approx height
            return endX >= left && endX <= right && endY >= top && endY <= bottom;
        });
        if (to && rubberBand.value.fromId && to.id !== rubberBand.value.fromId) {
            edges.value.push({ id: crypto.randomUUID(), fromId: rubberBand.value.fromId, toId: to.id, label: '', condition: '' });
        }
    }
    dragging = null;
    rubberBand.value = null;
}

function edgeWidth(e) {
    const from = nodeById(e.fromId);
    return from?.type === 'Parallel' ? 3 : 2;
}

function shortId(id) {
    return (id || '').toString().slice(0, 6);
}

function applyBuiltCondition(e) {
    if (!e.cbField || !e.cbOp) {
        e.condition = '';
        return;
    }
    const v = (e.cbValue || '').trim();
    const v2 = (e.cbValue2 || '').trim();
    const field = e.cbField;
    const wrap = (x) => (isNaN(Number(x)) ? `'${x}'` : x);
    switch (e.cbOp) {
        case '>': e.condition = `${field} > ${wrap(v)}`; break;
        case '>=': e.condition = `${field} >= ${wrap(v)}`; break;
        case '<': e.condition = `${field} < ${wrap(v)}`; break;
        case '<=': e.condition = `${field} <= ${wrap(v)}`; break;
        case '==': e.condition = `${field} == ${wrap(v)}`; break;
        case '!=': e.condition = `${field} != ${wrap(v)}`; break;
        case 'contains': e.condition = `contains(${field}, ${wrap(v)})`; break;
        case 'startsWith': e.condition = `startsWith(${field}, ${wrap(v)})`; break;
        case 'endsWith': e.condition = `endsWith(${field}, ${wrap(v)})`; break;
        case 'regex': e.condition = `regex(${field}, ${wrap(v)})`; break;
        case 'between': e.condition = `${field} >= ${wrap(v)} && ${field} <= ${wrap(v2)}`; break;
        case 'in': e.condition = `in(${field}, [${(v||'').split(',').map(s=>wrap(s.trim())).join(', ')}])`; break;
        case 'notIn': e.condition = `!in(${field}, [${(v||'').split(',').map(s=>wrap(s.trim())).join(', ')}])`; break;
        default: e.condition = '';
    }
}
</script>

