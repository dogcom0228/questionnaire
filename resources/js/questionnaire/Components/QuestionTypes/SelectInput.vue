<template>
  <v-select
    v-model="modelValue"
    :items="question.options"
    :label="question.content"
    :hint="question.description"
    :error-messages="error"
    variant="outlined"
    persistent-hint
    clearable
  />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
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
</script>
