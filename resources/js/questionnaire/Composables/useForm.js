import { useForm as useInertiaForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

/**
 * Enhanced form composable with validation and error handling
 */
export function useForm(initialData = {}) {
    const form = useInertiaForm(initialData)
    const clientErrors = ref({})
    const isValidating = ref(false)

    /**
     * Validate a single field
     */
    const validateField = (field, value, rules) => {
        if (!rules || rules.length === 0) return null

        for (const rule of rules) {
            if (rule.required && !value) {
                return rule.message || `${field} is required`
            }

            if (rule.min && value && value.length < rule.min) {
                return (
                    rule.message ||
                    `${field} must be at least ${rule.min} characters`
                )
            }

            if (rule.max && value && value.length > rule.max) {
                return (
                    rule.message ||
                    `${field} must not exceed ${rule.max} characters`
                )
            }

            if (rule.email && value && !isValidEmail(value)) {
                return rule.message || 'Please enter a valid email address'
            }

            if (rule.pattern && value && !rule.pattern.test(value)) {
                return rule.message || `${field} format is invalid`
            }

            if (rule.custom && typeof rule.custom === 'function') {
                const customError = rule.custom(value)
                if (customError) return customError
            }
        }

        return null
    }

    /**
     * Validate all fields
     */
    const validate = (validationRules = {}) => {
        isValidating.value = true
        clientErrors.value = {}

        Object.keys(validationRules).forEach((field) => {
            const error = validateField(
                field,
                form[field],
                validationRules[field]
            )
            if (error) {
                clientErrors.value[field] = error
            }
        })

        isValidating.value = false
        return Object.keys(clientErrors.value).length === 0
    }

    /**
     * Get all errors (client + server)
     */
    const allErrors = computed(() => {
        return { ...clientErrors.value, ...form.errors }
    })

    /**
     * Clear specific error
     */
    const clearError = (field) => {
        delete clientErrors.value[field]
        form.clearErrors(field)
    }

    /**
     * Clear all errors
     */
    const clearAllErrors = () => {
        clientErrors.value = {}
        form.clearErrors()
    }

    return {
        form,
        clientErrors,
        allErrors,
        isValidating,
        validate,
        validateField,
        clearError,
        clearAllErrors,
    }
}

/**
 * Simple email validation
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
}
