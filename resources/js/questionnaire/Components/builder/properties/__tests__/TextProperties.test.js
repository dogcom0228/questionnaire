import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import TextProperties from '../TextProperties.vue'

describe('TextProperties.vue', () => {
    const VTextField = {
        props: ['modelValue', 'label'],
        template:
            '<input class="v-text-field-stub" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    }

    const createWrapper = (questionData = {}) => {
        return mount(TextProperties, {
            props: {
                question: {
                    id: 1,
                    type: 'text',
                    data: {
                        placeholder: 'Initial Placeholder',
                        ...questionData,
                    },
                },
            },
            global: {
                components: {
                    'v-text-field': VTextField,
                },
            },
        })
    }

    it('renders placeholder field', () => {
        const wrapper = createWrapper()
        expect(wrapper.find('.v-text-field-stub').exists()).toBe(true)
    })

    it('initializes with question data', () => {
        const wrapper = createWrapper()
        const input = wrapper.findComponent(VTextField)
        expect(input.props('modelValue')).toBe('Initial Placeholder')
    })

    it('updates question data when input changes', async () => {
        const question = {
            id: 1,
            type: 'text',
            data: {
                placeholder: '',
            },
        }

        const wrapper = mount(TextProperties, {
            props: { question },
            global: {
                components: {
                    'v-text-field': VTextField,
                },
            },
        })

        await wrapper.find('.v-text-field-stub').setValue('New Placeholder')
        expect(question.data.placeholder).toBe('New Placeholder')
    })
})
