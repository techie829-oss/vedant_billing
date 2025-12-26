import { defineStore } from 'pinia'
import { ref } from 'vue'
import client from '../api/client'
import { useAuthStore } from './auth'

export interface TeamMember {
    id: string
    name: string
    email: string
    role: string
    status: string
    joined_at: string
}

export const useTeamStore = defineStore('team', () => {
    const members = ref<TeamMember[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)

    const fetchMembers = async () => {
        const auth = useAuthStore()
        if (!auth.activeBusiness) return

        loading.value = true
        error.value = null
        try {
            const res = await client.get(`/businesses/${auth.activeBusiness.id}/members`)
            members.value = res.data
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to fetch team members'
            console.error(e)
        } finally {
            loading.value = false
        }
    }

    const inviteMember = async (email: string, role: string) => {
        const auth = useAuthStore()
        if (!auth.activeBusiness) return

        loading.value = true
        error.value = null
        try {
            const res = await client.post(`/businesses/${auth.activeBusiness.id}/members`, { email, role })
            await fetchMembers() // Refresh list
            return res.data.message as string
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to invite member'
            throw e
        } finally {
            loading.value = false
        }
    }

    const updateRole = async (userId: string, role: string) => {
        const auth = useAuthStore()
        if (!auth.activeBusiness) return

        loading.value = true
        error.value = null
        try {
            await client.put(`/businesses/${auth.activeBusiness.id}/members/${userId}`, { role })
            await fetchMembers()
            return true
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to update user role'
            throw e
        } finally {
            loading.value = false
        }
    }

    const removeMember = async (userId: string) => {
        const auth = useAuthStore()
        if (!auth.activeBusiness) return

        loading.value = true
        error.value = null
        try {
            await client.delete(`/businesses/${auth.activeBusiness.id}/members/${userId}`)
            await fetchMembers()
            return true
        } catch (e: any) {
            error.value = e.response?.data?.message || 'Failed to remove member'
            throw e
        } finally {
            loading.value = false
        }
    }

    return {
        members,
        loading,
        error,
        fetchMembers,
        inviteMember,
        updateRole,
        removeMember
    }
})
