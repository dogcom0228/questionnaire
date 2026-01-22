import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import ChoiceProperties from '../ChoiceProperties.vue'
import Input from '@/questionnaire/Components/ui/input/Input.vue'
import Button from '@/questionnaire/Components/ui/button/Button.vue'

describe('ChoiceProperties.vue', () => {
    const createWrapper = (questionData = {}) => {
        return mount(ChoiceProperties, {
            props: {
                question: {
                    id: 1,
                    type: 'select',
                    data: {
                        options: [
                            { text: 'Option 1', value: 'Option 1' },
                            { text: 'Option 2', value: 'Option 2' },
                        ],
                        ...questionData,
                    },
                },
            },
        })
    }

    it('renders list of options', () => {
        const wrapper = createWrapper()
        const inputs = wrapper.findAllComponents(Input)
        // Two options mean two inputs
        expect(inputs).toHaveLength(2)
        expect(inputs[0].props('modelValue')).toBe('Option 1')
        expect(inputs[1].props('modelValue')).toBe('Option 2')
    })

    it('initializes options array if missing', () => {
        const question = {
            id: 1,
            type: 'select',
            data: {}, // No options
        }

        mount(ChoiceProperties, {
            props: { question },
        })

        expect(Array.isArray(question.data.options)).toBe(true)
    })

    it('adds an option', async () => {
        const question = {
            id: 1,
            type: 'select',
            data: { options: [] },
        }

        const wrapper = mount(ChoiceProperties, {
            props: { question },
        })

        // Find the "Add Option" button
        // It's the button that contains "Add Option" text
        const buttons = wrapper.findAllComponents(Button)
        const addBtn = buttons.find((btn) => btn.text().includes('Add Option'))

        expect(addBtn).toBeDefined()
        await addBtn.trigger('click')

        expect(question.data.options).toHaveLength(1)
        expect(question.data.options[0].text).toBe('New Option')
    })

    it('removes an option', async () => {
        const question = {
            id: 1,
            type: 'select',
            data: {
                options: [{ text: 'Option 1', value: 'Option 1' }],
            },
        }

        const wrapper = mount(ChoiceProperties, {
            props: { question },
        })

        // Find remove button (the one with sr-only text "Remove option" or just the first button that isn't Add Option)
        // In our template, each option row has a remove button.
        const buttons = wrapper.findAllComponents(Button)
        // The last button is "Add Option", others are remove buttons. Or filter by icon/content.
        // We can look for the button inside the loop.
        const removeBtns = buttons.filter(
            (btn) => !btn.text().includes('Add Option')
        )

        expect(removeBtns.length).toBeGreaterThan(0)
        await removeBtns[0].trigger('click')

        expect(question.data.options).toHaveLength(0)
    })

    it('updates option text', async () => {
        const question = {
            id: 1,
            type: 'select',
            data: {
                options: [{ text: 'Old', value: 'Old' }],
            },
        }

        const wrapper = mount(ChoiceProperties, {
            props: { question },
        })

        await wrapper.findComponent(Input).vm.$emit('update:modelValue', 'New')

        expect(question.data.options[0].text).toBe('New')
        expect(question.data.options[0].value).toBe('New')
    })
})
