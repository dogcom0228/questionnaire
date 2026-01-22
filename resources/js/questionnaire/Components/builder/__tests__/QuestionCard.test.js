import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import QuestionCard from '../QuestionCard.vue'

// Mock the useBuilder composable
vi.mock('@questionnaire/Composables/useBuilder', () => ({
    useBuilder: () => ({
        selectedQuestionId: { value: 'q1' },
        removeQuestion: vi.fn(),
    }),
}))

describe('QuestionCard.vue', () => {
    const mockQuestion = {
        id: 'q1',
        type: 'text',
        label: 'Test Question',
        data: {},
    }

    it('renders the question label', () => {
        const wrapper = mount(QuestionCard, {
            props: {
                question: mockQuestion,
                index: 0,
            },
        })
        expect(wrapper.text()).toContain('Test Question')
    })

    it('applies selected styles when question is selected', () => {
        const wrapper = mount(QuestionCard, {
            props: {
                question: mockQuestion,
                index: 0,
            },
        })
        // Based on instructions: If selected, add `ring-2 ring-primary`
        expect(wrapper.classes()).toContain('ring-2')
        expect(wrapper.classes()).toContain('ring-primary')
    })

    it('renders input preview based on type', () => {
        const wrapper = mount(QuestionCard, {
            props: {
                question: mockQuestion,
                index: 0,
            },
        })
        // Should verify that some form of input or placeholder is rendered
        // Since we haven't implemented Input.vue interaction yet, checking for the existence is tricky
        // but we can check if it renders the type specific content.
        // For now let's just assume we will render an input element.
        expect(wrapper.find('input').exists()).toBe(true)
    })
})
