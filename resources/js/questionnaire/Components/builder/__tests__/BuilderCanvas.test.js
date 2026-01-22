import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import BuilderCanvas from '../BuilderCanvas.vue'

// Mock useBuilder
vi.mock('@questionnaire/Composables/useBuilder', () => ({
    useBuilder: () => ({
        form: {
            questions: [],
        },
        selectQuestion: vi.fn(),
        selectedQuestionId: { value: null },
    }),
}))

// Mock draggable component since it might be complex to test with JSDOM
// We can just check if it renders and if v-model is bound
vi.mock('vuedraggable', () => ({
    default: {
        name: 'draggable',
        template: '<div><slot></slot><slot name="footer"></slot></div>',
        props: ['modelValue', 'itemKey'],
    },
}))

describe('BuilderCanvas.vue', () => {
    it('renders placeholder when no questions', () => {
        const wrapper = mount(BuilderCanvas)
        expect(wrapper.text()).toContain('Drag or Click to add questions')
    })
})
