import { router } from '@inertiajs/vue3'
import route from 'ziggy-js'
import { ref } from 'vue'

/**
 * Composable for handling questionnaire operations
 */
export function useQuestionnaire() {
    const loading = ref(false)
    const error = ref(null)

    /**
     * Delete a questionnaire
     */
    const deleteQuestionnaire = (questionnaireId, options = {}) => {
        return new Promise((resolve, reject) => {
            if (
                !confirm(
                    options.confirmMessage ||
                        'Are you sure you want to delete this questionnaire? This action cannot be undone.'
                )
            ) {
                reject(new Error('User cancelled deletion'))
                return
            }

            loading.value = true
            error.value = null

            router.delete(
                route('questionnaire.admin.destroy', questionnaireId),
                {
                    onSuccess: () => {
                        loading.value = false
                        resolve()
                    },
                    onError: (errors) => {
                        loading.value = false
                        error.value = errors
                        reject(errors)
                    },
                    onFinish: () => {
                        loading.value = false
                    },
                }
            )
        })
    }

    /**
     * Publish a questionnaire
     */
    const publishQuestionnaire = (questionnaireId) => {
        return new Promise((resolve, reject) => {
            loading.value = true
            error.value = null

            router.post(
                route('questionnaire.admin.publish', questionnaireId),
                {},
                {
                    onSuccess: () => {
                        loading.value = false
                        resolve()
                    },
                    onError: (errors) => {
                        loading.value = false
                        error.value = errors
                        reject(errors)
                    },
                    onFinish: () => {
                        loading.value = false
                    },
                }
            )
        })
    }

    /**
     * Close a questionnaire
     */
    const closeQuestionnaire = (questionnaireId) => {
        return new Promise((resolve, reject) => {
            if (
                !confirm(
                    'Are you sure you want to close this questionnaire? It will no longer accept responses.'
                )
            ) {
                reject(new Error('User cancelled closing'))
                return
            }

            loading.value = true
            error.value = null

            router.post(
                route('questionnaire.admin.close', questionnaireId),
                {},
                {
                    onSuccess: () => {
                        loading.value = false
                        resolve()
                    },
                    onError: (errors) => {
                        loading.value = false
                        error.value = errors
                        reject(errors)
                    },
                    onFinish: () => {
                        loading.value = false
                    },
                }
            )
        })
    }

    return {
        loading,
        error,
        deleteQuestionnaire,
        publishQuestionnaire,
        closeQuestionnaire,
    }
}
