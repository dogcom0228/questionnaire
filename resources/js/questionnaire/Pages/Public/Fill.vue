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
                    :error="form.errors[`answers.${question.id}`]"
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
import { ref, computed, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Use public layout located at resources/js/questionnaire/Layouts
import PublicLayout from '../../Layouts/PublicLayout.vue';
import QuestionRenderer from '../../Components/QuestionRenderer.vue';

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
  form.post(`/survey/${props.questionnaire.id}/submit`, {
    preserveScroll: true,
  });
};
</script>
