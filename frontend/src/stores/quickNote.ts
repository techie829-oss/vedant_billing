import { defineStore } from 'pinia'
import client from '../api/client'
import type { ParsedItem } from '../utils/smartParser'

export interface QuickNote {
    id: string
    business_id: string
    user_id: string
    type: 'order_receipt' | 'hisab'
    title: string
    description?: string
    content: ParsedItem[]
    total_amount: number
    created_at: string
}

export const useQuickNoteStore = defineStore('quickNote', {
    state: () => ({
        notes: [] as QuickNote[],
        loading: false,
    }),
    actions: {
        async fetchNotes() {
            this.loading = true
            try {
                const response = await client.get('/quick-notes')
                this.notes = response.data.data
            } catch (error) {
                console.error('Failed to fetch notes', error)
            } finally {
                this.loading = false
            }
        },
        async saveNote(note: Partial<QuickNote>) {
            this.loading = true
            try {
                const response = await client.post('/quick-notes', note)
                this.notes.unshift(response.data)
                return response.data
            } catch (error) {
                console.error('Failed to save note', error)
                throw error
            } finally {
                this.loading = false
            }
        },
        async deleteNote(id: string) {
            try {
                await client.delete(`/quick-notes/${id}`)
                this.notes = this.notes.filter(n => n.id !== id)
            } catch (error) {
                console.error('Failed to delete note', error)
                throw error
            }
        }
    }
})
