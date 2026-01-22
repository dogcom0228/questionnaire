import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import CommonProperties from '../CommonProperties.vue'

describe('CommonProperties.vue', () => {
    // Define stubs
    const VTextField = {
        props: ['modelValue', 'label'],
        template:
            '<input class="v-text-field-stub" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    }

    const VTextarea = {
        props: ['modelValue', 'label'],
        template:
            '<textarea class="v-textarea-stub" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)"></textarea>',
    }

    const VSwitch = {
        props: ['modelValue', 'label'],
        template:
            '<input type="checkbox" class="v-switch-stub" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
    }

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
            global: {
                components: {
                    'v-text-field': VTextField,
                    'v-textarea': VTextarea,
                    'v-switch': VSwitch,
                },
            },
        })
    }

    it('renders all common fields', () => {
        const wrapper = createWrapper()
        expect(wrapper.find('.v-text-field-stub').exists()).toBe(true)
        expect(wrapper.find('.v-textarea-stub').exists()).toBe(true)
        expect(wrapper.find('.v-switch-stub').exists()).toBe(true)
    })

    it('initializes with question data', () => {
        const wrapper = createWrapper()

        // Now we can find the Stub component wrapper
        const labelInput = wrapper.findComponent(VTextField)
        expect(labelInput.props('modelValue')).toBe('Initial Label')

        const descInput = wrapper.findComponent(VTextarea)
        expect(descInput.props('modelValue')).toBe('Initial Description')

        const requiredSwitch = wrapper.findComponent(VSwitch)
        expect(requiredSwitch.props('modelValue')).toBe(false)
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
            global: {
                components: {
                    'v-text-field': VTextField,
                    'v-textarea': VTextarea,
                    'v-switch': VSwitch,
                },
            },
        })

        // Interact with the actual DOM elements inside the stubs
        await wrapper.find('.v-text-field-stub').setValue('New Label')
        expect(question.data.label).toBe('New Label')

        await wrapper.find('.v-textarea-stub').setValue('New Description')
        expect(question.data.description).toBe('New Description')

        await wrapper.find('.v-switch-stub').setValue(true)
        expect(question.data.required).toBe(true)
    })
})
