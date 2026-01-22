import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import TextProperties from '../TextProperties.vue'
import Input from '@/questionnaire/Components/ui/input/Input.vue'

describe('TextProperties.vue', () => {
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
        })
    }

    it('renders placeholder field', () => {
        const wrapper = createWrapper()
        expect(wrapper.findComponent(Input).exists()).toBe(true)
    })

    it('initializes with question data', () => {
        const wrapper = createWrapper()
        const input = wrapper.findComponent(Input)
        expect(input.props('modelValue')).toBe('Initial Placeholder')
    })

    it('updates question data when input changes', async () => {
        const question = {
            id: 1,
            type: 'text',
            data: {
                placeholder: 'Initial Placeholder',
            },
        }

        const wrapper = mount(TextProperties, {
            props: { question },
        })

        await wrapper
            .findComponent(Input)
            .vm.$emit('update:modelValue', 'New Placeholder')
        expect(question.data.placeholder).toBe('New Placeholder')
    })
})
