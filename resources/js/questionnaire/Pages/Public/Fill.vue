<template>
  <PublicLayout :title="questionnaire.title">
    <v-container class="py-8">
      <v-row justify="center">
        <v-col cols="12" md="8" lg="6">
          <v-card>
            <v-card-title class="text-h5">{{ questionnaire.title }}</v-card-title>
            <v-card-subtitle v-if="questionnaire.description">
              {{ questionnaire.description }}
            </v-card-subtitle>

            <v-divider />

            <v-form @submit.prevent="submit" ref="formRef">
              <v-card-text>
                <div
                  v-for="(question, index) in questionnaire.questions"
                  :key="question.id"
                  :data-question-id="question.id"
                  class="mb-6"
                >
                  <div class="text-subtitle-1 font-weight-bold mb-2">
                    {{ index + 1 }}. {{ question.content }}
                    <span v-if="question.required" class="text-error">*</span>
                  </div>
                  <div v-if="question.description" class="text-caption text-grey mb-2">
                    {{ question.description }}
                  </div>

                  <QuestionRenderer
                    v-model="form.answers[question.id]"
                    :question="question"
                    :error="getErrorMessage(question.id)"
                  />
                </div>
              </v-card-text>

              <v-divider />

              <v-card-actions class="pa-4">
                <v-spacer />
                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  :loading="form.processing"
                  :disabled="!canSubmit"
                >
                  Submit Response
                </v-btn>
              </v-card-actions>
            </v-form>
          </v-card>

          <!-- Progress indicator for long forms -->
          <div v-if="questionnaire.questions.length > 5" class="mt-4 text-center text-grey">
            Question {{ currentQuestion }} of {{ questionnaire.questions.length }}
          </div>
        </v-col>
      </v-row>
    </v-container>
  </PublicLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { validateForm } from '../../Utils/validation';
// Use public layout located at resources/js/questionnaire/Layouts
import QuestionRenderer from '../../Components/QuestionRenderer.vue';
import PublicLayout from '../../Layouts/PublicLayout.vue';

const props = defineProps({
  questionnaire: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
});

const formRef = ref(null);
const validationErrors = ref({});

// Initialize answers object with empty values
const initialAnswers = {};
props.questionnaire.questions.forEach(q => {
  if (q.type === 'checkbox') {
    initialAnswers[q.id] = [];
  } else {
    initialAnswers[q.id] = '';
  }
});

const form = useForm({
  answers: initialAnswers,
});

const currentQuestion = ref(1);

const canSubmit = computed(() => {
  // Check if all required questions have answers
  return props.questionnaire.questions.every(q => {
    if (!q.required) return true;
    const answer = form.answers[q.id];
    if (Array.isArray(answer)) {
      return answer.length > 0;
    }
    return answer !== '' && answer !== null && answer !== undefined;
  });
});

const submit = () => {
  // Client-side validation
  const validation = validateForm(props.questionnaire.questions, form.answers);
  
  if (!validation.isValid) {
    validationErrors.value = validation.errors;
    
    // Scroll to first error
    const firstErrorField = Object.keys(validation.errors)[0];
    const errorElement = document.querySelector(`[data-question-id="${firstErrorField}"]`);
    if (errorElement) {
      errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    return;
  }

  // Clear validation errors
  validationErrors.value = {};

  // Submit form
  form.post(`/survey/${props.questionnaire.id}`, {
    preserveScroll: true,
    onError: (errors) => {
      if (import.meta.env?.DEV) {
        // eslint-disable-next-line no-console
        console.error('Submission errors:', errors);
      }
    },
  });
};

// Get error message for a question
const getErrorMessage = (questionId) => {
  return validationErrors.value[questionId] || form.errors[`answers.${questionId}`];
};
</script>
