/**
 * Debounce function - delays execution until after a specified time has elapsed
 * since the last time it was invoked.
 *
 * @param {Function} func - The function to debounce
 * @param {number} wait - The number of milliseconds to delay
 * @returns {Function} - The debounced function
 */
export function debounce(func, wait = 300) {
    let timeout

    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout)
            func(...args)
        }

        clearTimeout(timeout)
        timeout = setTimeout(later, wait)
    }
}

/**
 * Throttle function - ensures a function is called at most once in a specified time period
 *
 * @param {Function} func - The function to throttle
 * @param {number} limit - The number of milliseconds to wait between calls
 * @returns {Function} - The throttled function
 */
export function throttle(func, limit = 300) {
    let inThrottle

    return function executedFunction(...args) {
        if (!inThrottle) {
            func(...args)
            inThrottle = true
            setTimeout(() => (inThrottle = false), limit)
        }
    }
}

/**
 * Format date to locale string
 *
 * @param {string|Date} date - The date to format
 * @param {string} locale - The locale to use (default: 'en-US')
 * @returns {string} - The formatted date
 */
export function formatDate(date, locale = 'en-US') {
    if (!date) return ''

    const dateObj = typeof date === 'string' ? new Date(date) : date

    return dateObj.toLocaleDateString(locale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

/**
 * Format datetime to locale string
 *
 * @param {string|Date} datetime - The datetime to format
 * @param {string} locale - The locale to use (default: 'en-US')
 * @returns {string} - The formatted datetime
 */
export function formatDateTime(datetime, locale = 'en-US') {
    if (!datetime) return ''

    const dateObj = typeof datetime === 'string' ? new Date(datetime) : datetime

    return dateObj.toLocaleString(locale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

/**
 * Safely parse JSON
 *
 * @param {string} jsonString - The JSON string to parse
 * @param {any} defaultValue - The default value to return if parsing fails
 * @returns {any} - The parsed object or default value
 */
export function safeJsonParse(jsonString, defaultValue = null) {
    try {
        return JSON.parse(jsonString)
    } catch (e) {
        if (typeof import.meta !== 'undefined' && import.meta.env?.DEV) {
            // eslint-disable-next-line no-console
            console.error('Failed to parse JSON:', e)
        }
        return defaultValue
    }
}
