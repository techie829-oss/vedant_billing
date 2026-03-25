<template>
    <AppLayout>
        <div class="p-fluid">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0">Team Management</h1>
                    <p class="text-gray-500 mt-1">Manage access control and organizational roles for your business.</p>
                </div>
                <div>
                    <Button label="Invite Team Member" icon="pi pi-user-plus" @click="openInviteModal" />
                </div>
            </div>

            <!-- Members Data Table -->
            <Card class="border-none shadow-sm overflow-hidden">
                <template #content>
                    <DataTable :value="teamStore.members" :loading="teamStore.loading" dataKey="id" 
                        responsiveLayout="stack" breakpoint="960px">
                        
                        <template #empty>No team members found.</template>

                        <Column header="Member">
                            <template #body="{ data }">
                                <div class="flex items-center gap-3">
                                    <Avatar :label="data.name.charAt(0).toUpperCase()" shape="circle" class="bg-primary-100 text-primary font-bold" />
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900">{{ data.name }}</span>
                                        <span class="text-xs text-gray-500">{{ data.email }}</span>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column field="role" header="Role" sortable style="width: 150px">
                            <template #body="{ data }">
                                <Tag :value="data.role.toUpperCase()" :severity="getRoleSeverity(data.role)" rounded />
                            </template>
                        </Column>

                        <Column header="Management / Actions">
                            <template #body="{ data }">
                                <div v-if="canManage(data)" class="flex items-center gap-2">
                                    <Select :modelValue="data.role" :options="roleOptions" optionLabel="label" optionValue="value" 
                                        size="small" class="w-32" @change="(e) => updateRole(data, e.value)" />
                                    <Button icon="pi pi-key" severity="warn" text rounded v-tooltip.top="'Reset Password'" @click="resetMemberPassword(data)" />
                                    <Button icon="pi pi-user-minus" severity="danger" text rounded v-tooltip.top="'Remove Member'" @click="removeMember(data)" />
                                </div>
                                <span v-else class="text-gray-400 italic text-xs">Self / Owner restricted</span>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Invite Member Dialog -->
        <Dialog v-model:visible="showModal" header="Invite New Member" :modal="true" :style="{ width: '450px' }">
            <div class="flex flex-col gap-6 pt-4">
                <div v-if="!successMessage" class="flex flex-col gap-4">
                    <p class="text-gray-500">Enter the email address of the person you want to invite. They will receive an invitation to join your business organization.</p>
                    
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">Email Address *</label>
                        <InputText v-model="inviteForm.email" type="email" placeholder="colleague@company.com" autofocus />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm">Assign Role</label>
                        <Select v-model="inviteForm.role" :options="roleOptions" optionLabel="label" optionValue="value" />
                    </div>

                    <Message v-if="inviteError" severity="error" size="small">{{ inviteError }}</Message>
                </div>

                <div v-else class="flex flex-col items-center text-center gap-4 py-4">
                    <i class="pi pi-check-circle text-6xl text-green-500"></i>
                    <h2 class="text-xl font-bold">Invite Sent!</h2>
                    <p class="text-gray-600">{{ successMessage }}</p>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 w-full font-mono text-sm break-all">
                        Please copy and share the temporary password with them manually if they don't receive the email.
                    </div>
                </div>
            </div>
            <template #footer>
                <Button v-if="!successMessage" label="Cancel" text severity="secondary" @click="closeModal" />
                <Button v-if="!successMessage" label="Send Invitation" icon="pi pi-send" :loading="inviting" @click="submitInvite" />
                <Button v-else label="Got it" icon="pi pi-check" @click="closeModal" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import { useTeamStore } from '../../stores/team'
import { useAuthStore } from '../../stores/auth'

// PrimeVue
import Card from 'primevue/card'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Avatar from 'primevue/avatar'
import Select from 'primevue/select'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'

const teamStore = useTeamStore()
const authStore = useAuthStore()

const showModal = ref(false)
const inviting = ref(false)
const inviteError = ref<string | null>(null)
const successMessage = ref<string | null>(null)

const inviteForm = ref({ email: '', role: 'staff' })

const roleOptions = [
    { label: 'Admin', value: 'admin' },
    { label: 'Staff', value: 'staff' },
    { label: 'Accountant', value: 'accountant' }
]

onMounted(() => { teamStore.fetchMembers() })

const getRoleSeverity = (role: string) => {
    switch (role) {
        case 'owner': return 'success'
        case 'admin': return 'info'
        case 'accountant': return 'warn'
        default: return 'secondary'
    }
}

const openInviteModal = () => {
    inviteForm.value = { email: '', role: 'staff' }
    inviteError.value = null
    successMessage.value = null
    showModal.value = true
}

const closeModal = () => { showModal.value = false }

const submitInvite = async () => {
    if (!inviteForm.value.email) return
    inviting.value = true
    inviteError.value = null
    try {
        const msg = await teamStore.inviteMember(inviteForm.value.email, inviteForm.value.role)
        if (msg && msg.includes('Temporary password')) successMessage.value = msg
        else closeModal()
        await teamStore.fetchMembers()
    } catch (e: any) {
        inviteError.value = e.response?.data?.message || 'Failed to invite user.'
    } finally { inviting.value = false }
}

const canManage = (member: any) => {
    if (member.email === authStore.user?.email) return false
    return member.role !== 'owner'
}

const updateRole = async (member: any, newRole: string) => {
    if (!confirm(`Change role to ${newRole.toUpperCase()}?`)) return
    try { await teamStore.updateRole(member.id, newRole) } 
    catch (e) { alert('Update failed') }
}

const resetMemberPassword = async (member: any) => {
    if (!confirm(`Reset password for ${member.name}? The new password will be shown once.`)) return
    try {
        const pass = await teamStore.resetPassword(member.id)
        if (pass) alert(`Reset Successful.\n\nNew Password: ${pass}\n\nPlease share this securely.`);
    } catch (e: any) { alert(e.response?.data?.message || 'Failed') }
}

const removeMember = async (member: any) => {
    if (!confirm(`Remove ${member.name} from organization?`)) return
    try { await teamStore.removeMember(member.id) } 
    catch (e) { alert('Removal failed') }
}
</script>
