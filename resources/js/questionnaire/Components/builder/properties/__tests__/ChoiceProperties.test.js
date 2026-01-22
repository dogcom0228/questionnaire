import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import ChoiceProperties from '../ChoiceProperties.vue'

describe('ChoiceProperties.vue', () => {
    const VTextField = {
        props: ['modelValue', 'label'],
        template:
            '<input class="v-text-field-stub" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    }

    // Simplified VBtn stub - relies on attribute fallthrough for @click
    const VBtn = {
        template:
            '<button type="button" class="v-btn-stub"><slot></slot></button>',
    }

    const VIcon = {
        template: '<span class="v-icon-stub"><slot></slot></span>',
    }

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
            global: {
                components: {
                    'v-text-field': VTextField,
                    'v-btn': VBtn,
                    'v-icon': VIcon,
                },
            },
        })
    }

    it('renders list of options', () => {
        const wrapper = createWrapper()
        const inputs = wrapper.findAll('.v-text-field-stub')
        expect(inputs).toHaveLength(2)
        expect(inputs[0].element.value).toBe('Option 1')
        expect(inputs[1].element.value).toBe('Option 2')
    })

    it('initializes options array if missing', () => {
        const question = {
            id: 1,
            type: 'select',
            data: {}, // No options
        }

        mount(ChoiceProperties, {
            props: { question },
            global: {
                components: {
                    'v-text-field': VTextField,
                    'v-btn': VBtn,
                    'v-icon': VIcon,
                },
            },
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
            global: {
                components: {
                    'v-text-field': VTextField,
                    'v-btn': VBtn,
                    'v-icon': VIcon,
                },
            },
        })

        // Find the add button (assuming it has text 'Add Option')
        const addBtn = wrapper
            .findAll('.v-btn-stub')
            .find((w) => w.text().includes('Add Option'))
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
            global: {
                components: {
                    'v-text-field': VTextField,
                    'v-btn': VBtn,
                    'v-icon': VIcon,
                },
            },
        })

        // Find remove button
        const removeBtns = wrapper
            .findAll('.v-btn-stub')
            .filter((w) => !w.text().includes('Add Option'))
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
            global: {
                components: {
                    'v-text-field': VTextField,
                    'v-btn': VBtn,
                    'v-icon': VIcon,
                },
            },
        })

        await wrapper.find('.v-text-field-stub').setValue('New')

        expect(question.data.options[0].text).toBe('New')
        expect(question.data.options[0].value).toBe('New')
    })
})
