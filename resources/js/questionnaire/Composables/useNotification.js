import { ref } from 'vue'

/**
 * Composable for handling notifications/alerts
 */
export function useNotification() {
    const notifications = ref([])
    let nextId = 1

    /**
     * Show a notification
     */
    const show = (message, type = 'info', duration = 5000) => {
        const id = nextId++
        const notification = {
            id,
            message,
            type, // 'success', 'error', 'warning', 'info'
            visible: true,
        }

        notifications.value.push(notification)

        if (duration > 0) {
            setTimeout(() => {
                remove(id)
            }, duration)
        }

        return id
    }

    /**
     * Show success notification
     */
    const success = (message, duration = 5000) => {
        return show(message, 'success', duration)
    }

    /**
     * Show error notification
     */
    const error = (message, duration = 7000) => {
        return show(message, 'error', duration)
    }

    /**
     * Show warning notification
     */
    const warning = (message, duration = 6000) => {
        return show(message, 'warning', duration)
    }

    /**
     * Show info notification
     */
    const info = (message, duration = 5000) => {
        return show(message, 'info', duration)
    }

    /**
     * Remove a notification
     */
    const remove = (id) => {
        const index = notifications.value.findIndex((n) => n.id === id)
        if (index > -1) {
            notifications.value.splice(index, 1)
        }
    }

    /**
     * Clear all notifications
     */
    const clearAll = () => {
        notifications.value = []
    }

    return {
        notifications,
        show,
        success,
        error,
        warning,
        info,
        remove,
        clearAll,
    }
}
