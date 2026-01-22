import { ref, reactive } from 'vue'

// Singleton state
const form = ref({
    title: '',
    description: '',
    questions: [],
})

const selectedQuestionId = ref(null)
const isDragging = ref(false)

export function useBuilder() {
    function initialize(data) {
        form.value = {
            title: data.title || '',
            description: data.description || '',
            questions: data.questions || [],
        }
    }

    function addQuestion(type) {
        const id = crypto.randomUUID()
        const newQuestion = {
            id,
            type,
            title: 'New Question',
            options: [],
            // Add other default properties here as needed
        }
        form.value.questions.push(newQuestion)
    }

    function selectQuestion(id) {
        selectedQuestionId.value = id
    }

    function updateQuestion(id, data) {
        const question = form.value.questions.find((q) => q.id === id)
        if (question) {
            Object.assign(question, data)
        }
    }

    function removeQuestion(id) {
        const index = form.value.questions.findIndex((q) => q.id === id)
        if (index !== -1) {
            form.value.questions.splice(index, 1)
        }
        if (selectedQuestionId.value === id) {
            selectedQuestionId.value = null
        }
    }

    function reorderQuestions(newIndex, oldIndex) {
        const question = form.value.questions[oldIndex]
        form.value.questions.splice(oldIndex, 1)
        form.value.questions.splice(newIndex, 0, question)
    }

    function reset() {
        form.value = {
            title: '',
            description: '',
            questions: [],
        }
        selectedQuestionId.value = null
        isDragging.value = false
    }

    return {
        form,
        selectedQuestionId,
        isDragging,
        initialize,
        addQuestion,
        selectQuestion,
        updateQuestion,
        removeQuestion,
        reorderQuestions,
        reset,
    }
}
