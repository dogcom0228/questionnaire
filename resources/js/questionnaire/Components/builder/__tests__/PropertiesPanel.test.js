import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import PropertiesPanel from '../PropertiesPanel.vue'
import CommonProperties from '../properties/CommonProperties.vue'
import TextProperties from '../properties/TextProperties.vue'
import ChoiceProperties from '../properties/ChoiceProperties.vue'

describe('PropertiesPanel.vue', () => {
    it('renders select message when no question provided', () => {
        const wrapper = mount(PropertiesPanel, {
            props: {
                question: null,
            },
        })
        expect(wrapper.text()).toContain('Select a question to edit properties')
    })

    it('renders CommonProperties when question provided', () => {
        const wrapper = mount(PropertiesPanel, {
            props: {
                question: { id: 1, type: 'text', data: {} },
            },
        })
        expect(wrapper.findComponent(CommonProperties).exists()).toBe(true)
    })

    it('renders TextProperties for text question types', () => {
        const wrapper = mount(PropertiesPanel, {
            props: {
                question: { id: 1, type: 'text', data: {} },
            },
        })
        expect(wrapper.findComponent(TextProperties).exists()).toBe(true)
        expect(wrapper.findComponent(ChoiceProperties).exists()).toBe(false)
    })

    it('renders ChoiceProperties for select question types', () => {
        const wrapper = mount(PropertiesPanel, {
            props: {
                question: { id: 1, type: 'select', data: {} },
            },
        })
        expect(wrapper.findComponent(ChoiceProperties).exists()).toBe(true)
        expect(wrapper.findComponent(TextProperties).exists()).toBe(false)
    })
})
