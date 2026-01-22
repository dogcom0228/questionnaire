import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import CommonProperties from '../CommonProperties.vue'
import Input from '@/questionnaire/Components/ui/input/Input.vue'
import { Textarea } from '@/questionnaire/Components/ui/textarea'
import Switch from '@/questionnaire/Components/ui/switch/Switch.vue'
import Label from '@/questionnaire/Components/ui/label/Label.vue'

describe('CommonProperties.vue', () => {
    // Stub the components to isolate tests, or assume they work (integration test)
    // Here we'll do an integration-style test using the real UI components since they are simple wrappers
    // But to make it easier to query, we can rely on DOM elements or classes if available, or just use findComponent.

    const createWrapper = (questionData = {}) => {
        return mount(CommonProperties, {
            props: {
                question: {
                    id: 1,
                    type: 'text',
                    data: {
                        label: 'Initial Label',
                        description: 'Initial Description',
                        required: false,
                        ...questionData,
                    },
                },
            },
        })
    }

    it('renders all common fields', () => {
        const wrapper = createWrapper()
        expect(wrapper.findComponent(Input).exists()).toBe(true)
        expect(wrapper.findComponent(Textarea).exists()).toBe(true)
        expect(wrapper.findComponent(Switch).exists()).toBe(true)
    })

    it('initializes with question data', () => {
        const wrapper = createWrapper()

        const labelInput = wrapper.findComponent(Input)
        expect(labelInput.props('modelValue')).toBe('Initial Label')

        const descInput = wrapper.findComponent(Textarea)
        expect(descInput.props('modelValue')).toBe('Initial Description')

        const requiredSwitch = wrapper.findComponent(Switch)
        expect(requiredSwitch.props('checked')).toBe(false)
    })

    it('updates question data when inputs change', async () => {
        const question = {
            id: 1,
            type: 'text',
            data: {
                label: '',
                description: '',
                required: false,
            },
        }

        const wrapper = mount(CommonProperties, {
            props: { question },
        })

        await wrapper
            .findComponent(Input)
            .vm.$emit('update:modelValue', 'New Label')
        expect(question.data.label).toBe('New Label')

        await wrapper
            .findComponent(Textarea)
            .vm.$emit('update:modelValue', 'New Description')
        expect(question.data.description).toBe('New Description')

        await wrapper.findComponent(Switch).vm.$emit('update:checked', true)
        expect(question.data.required).toBe(true)
    })
})
