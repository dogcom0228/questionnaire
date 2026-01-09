<template>
  <component
    :is="componentMap[question.type] || 'TextInput'"
    v-model="modelValue"
    :question="question"
    :error="error"
  />
</template>

<script setup>
import { computed, markRaw } from 'vue';

// Import all question type components
import TextInput from './QuestionTypes/TextInput.vue';
import TextareaInput from './QuestionTypes/TextareaInput.vue';
import RadioInput from './QuestionTypes/RadioInput.vue';
import CheckboxInput from './QuestionTypes/CheckboxInput.vue';
import SelectInput from './QuestionTypes/SelectInput.vue';
import NumberInput from './QuestionTypes/NumberInput.vue';
import DateInput from './QuestionTypes/DateInput.vue';

const props = defineProps({
  modelValue: {
    type: [String, Number, Array, Boolean],
    default: '',
  },
  question: {
    type: Object,
    required: true,
  },
  error: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue']);

const modelValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

// Map question types to components
const componentMap = {
  text: markRaw(TextInput),
  textarea: markRaw(TextareaInput),
  radio: markRaw(RadioInput),
  checkbox: markRaw(CheckboxInput),
  select: markRaw(SelectInput),
  number: markRaw(NumberInput),
  date: markRaw(DateInput),
};
</script>
