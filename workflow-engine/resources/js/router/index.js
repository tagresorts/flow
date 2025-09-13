import { createRouter, createWebHistory } from 'vue-router';

// Pages
import DashboardPage from '@/pages/DashboardPage.vue';
import ApprovalsPage from '@/pages/ApprovalsPage.vue';
import WorkflowsPage from '@/pages/WorkflowsPage.vue';
import FormsPage from '@/pages/FormsPage.vue';
import WorkflowBuilderPage from '@/pages/WorkflowBuilderPage.vue';
import FormBuilderPage from '@/pages/FormBuilderPage.vue';
import NewRequestPage from '@/pages/NewRequestPage.vue';
import AuditLogsPage from '@/pages/AuditLogsPage.vue';
import AdminUsersPage from '@/pages/AdminUsersPage.vue';
import AdminSmtpPage from '@/pages/AdminSmtpPage.vue';

const routes = [
    { path: '/dashboard', name: 'dashboard', component: DashboardPage },
    { path: '/dashboard/approvals', name: 'approvals', component: ApprovalsPage },
    { path: '/dashboard/workflows', name: 'workflows', component: WorkflowsPage },
    { path: '/dashboard/forms', name: 'forms', component: FormsPage },
    { path: '/dashboard/builder/workflow', name: 'workflow-builder', component: WorkflowBuilderPage },
    { path: '/dashboard/builder/form', name: 'form-builder', component: FormBuilderPage },
    { path: '/dashboard/requests/new', name: 'request-new', component: NewRequestPage },
    { path: '/dashboard/audit', name: 'audit', component: AuditLogsPage },
    { path: '/dashboard/admin/users', component: AdminUsersPage },
    { path: '/dashboard/admin/smtp', component: AdminSmtpPage },
    { path: '/dashboard/:pathMatch(.*)*', redirect: { name: 'dashboard' } },
];

const router = createRouter({
    history: createWebHistory('/'),
    routes,
});

export default router;

