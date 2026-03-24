import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
    const notifications = ref<Array<{ id: number, type: 'success' | 'error' | 'info' | 'warning', title: string, message: string }>>([])
    let nextId = 1

    const notify = (title: string, message: string, type: 'success' | 'error' | 'info' | 'warning' = 'info') => {
        const id = nextId++
        notifications.value.push({ id, type, title, message })

        // Auto remove after 5 seconds
        setTimeout(() => {
            remove(id)
        }, 5000)
    }

    const remove = (id: number) => {
        notifications.value = notifications.value.filter(n => n.id !== id)
    }

    return { notifications, notify, remove }
})
