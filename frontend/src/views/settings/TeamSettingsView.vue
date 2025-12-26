<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="mb-8 md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold text-gray-900">Team Management</h2>
                    <p class="mt-1 text-sm text-gray-500">Manage access and roles for your business organization.</p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <button @click="openInviteModal" type="button"
                        class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Invite Member
                    </button>
                </div>
            </div>

            <!-- Members List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul role="list" class="divide-y divide-gray-200">
                    <li v-for="member in teamStore.members" :key="member.id">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center truncate">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold uppercase">
                                        {{ member.name.charAt(0) }}
                                    </div>
                                    <div class="ml-4 truncate">
                                        <p class="text-sm font-medium text-indigo-600 truncate">{{ member.name }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ member.email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                                        :class="{
                                            'bg-green-100 text-green-800': member.role === 'owner',
                                            'bg-blue-100 text-blue-800': member.role === 'admin',
                                            'bg-gray-100 text-gray-800': member.role === 'staff',
                                            'bg-yellow-100 text-yellow-800': member.role === 'accountant'
                                        }">
                                        {{ member.role }}
                                    </span>

                                    <!-- Actions -->
                                    <div v-if="canManage(member)" class="relative flex items-center space-x-2">
                                        <select @change="updateRole(member, $event)" :value="member.role"
                                            class="block w-full pl-3 pr-10 py-1 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            <option value="admin">Admin</option>
                                            <option value="staff">Staff</option>
                                            <option value="accountant">Accountant</option>
                                        </select>
                                        <button @click="removeMember(member)"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium">Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li v-if="teamStore.members.length === 0 && !teamStore.loading"
                        class="px-4 py-8 text-center text-gray-500">
                        No team members found.
                    </li>
                </ul>
            </div>
        </div>

        <!-- Invite Modal -->
        <div v-if="showModal" class="relative z-50 py-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <!-- Modal Panel -->
            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                        <div>
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Invite Team
                                    Member</h3>
                                <div class="mt-2">
                                    <p v-if="!successMessage" class="text-sm text-gray-500">
                                        Enter the email address. If they don't have an account, one will be created for
                                        them.
                                    </p>
                                    <div v-else class="rounded-md bg-green-50 p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-green-800 text-left">Success</h3>
                                                <div class="mt-2 text-sm text-green-700 text-left">
                                                    <p>{{ successMessage }}</p>
                                                </div>
                                                <div class="mt-4">
                                                    <div class="-mx-2 -my-1.5 flex">
                                                        <button @click="closeModal" type="button"
                                                            class="rounded-md bg-green-50 px-2 py-1.5 text-sm font-medium text-green-800 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50">Dismiss</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="inviteError" class="mt-2 text-sm text-red-600">{{ inviteError }}</div>
                                </div>
                            </div>

                            <form v-if="!successMessage" @submit.prevent="submitInvite" class="mt-5 space-y-4">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email
                                        Address</label>
                                    <input type="email" v-model="inviteForm.email" required
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="user@example.com">
                                </div>
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                                    <select id="role" v-model="inviteForm.role"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="admin">Admin</option>
                                        <option value="staff">Staff</option>
                                        <option value="accountant">Accountant</option>
                                    </select>
                                </div>

                                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                    <button type="submit" :disabled="inviting"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                                        {{ inviting ? 'Inviting...' : 'Send Invite' }}
                                    </button>
                                    <button type="button" @click="closeModal"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import { useTeamStore } from '../../stores/team'
import { useAuthStore } from '../../stores/auth'

const teamStore = useTeamStore()
const authStore = useAuthStore()

const showModal = ref(false)
const inviting = ref(false)
const inviteError = ref<string | null>(null)
const inviteForm = ref({
    email: '',
    role: 'staff'
})

onMounted(() => {
    teamStore.fetchMembers()
})

const successMessage = ref<string | null>(null)

const openInviteModal = () => {
    showModal.value = true
    inviteForm.value = { email: '', role: 'staff' }
    inviteError.value = null
    successMessage.value = null
}

const closeModal = () => {
    showModal.value = false
    successMessage.value = null // reset
}

const submitInvite = async () => {
    inviting.value = true
    inviteError.value = null
    successMessage.value = null
    try {
        const msg = await teamStore.inviteMember(inviteForm.value.email, inviteForm.value.role)
        if (msg && msg.includes('Temporary password')) {
            successMessage.value = msg
        } else {
            closeModal()
        }
    } catch (e: any) {
        inviteError.value = e.response?.data?.message || 'Failed to invite user.'
    } finally {
        inviting.value = false
    }
}

const canManage = (member: any) => {
    // Current user cannot manage themselves here (UI safety, backend enforced too)
    if (member.email === authStore.user?.email) return false
    // Only owners can manage everyone, or Admins can manage Staff/Accountants (backend check)
    // For simplicity, let's just show controls if user is not Owner
    return member.role !== 'owner'
}

const updateRole = async (member: any, event: Event) => {
    const newRole = (event.target as HTMLSelectElement).value
    if (!confirm(`Are you sure you want to change role to ${newRole}?`)) {
        // Reset select if cancelled - tricky with v-bind, but simple page refresh fixes it or manual reset
        (event.target as HTMLSelectElement).value = member.role
        return
    }

    try {
        await teamStore.updateRole(member.id, newRole)
    } catch (e) {
        alert('Failed to update role')
    }
}

const removeMember = async (member: any) => {
    if (!confirm(`Are you sure you want to remove ${member.name}?`)) return
    try {
        await teamStore.removeMember(member.id)
    } catch (e) {
        alert('Failed to remove member')
        console.error(e)
    }
}
</script>
