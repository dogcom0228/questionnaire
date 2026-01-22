<template>
  <PublicLayout :title="questionnaire.title">
    <div class="py-8">
      <Card class="border-t-4 border-t-primary">
        <CardHeader>
          <CardTitle class="text-2xl font-bold">{{ questionnaire.title }}</CardTitle>
          <CardDescription v-if="questionnaire.description" class="text-base mt-2">
            {{ questionnaire.description }}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-8">
            <div
              v-for="(question, index) in questionnaire.questions"
              :key="question.id"
              :data-question-id="question.id"
              class="space-y-4"
            >
              <div class="space-y-2">
                <Label :class="{'text-destructive': getErrorMessage(question.id)}" class="text-base font-medium">
                  {{ index + 1 }}. {{ question.content }}
                  <span v-if="question.required" class="text-destructive">*</span>
                </Label>
                <div v-if="question.description" class="text-sm text-muted-foreground">
                  {{ question.description }}
                </div>
                
                <!-- Text Input -->
                <div v-if="question.type === 'text'">
                  <Input 
                    v-model="form.answers[question.id]" 
                    :placeholder="question.placeholder || 'Your answer'"
                    :class="{'border-destructive': getErrorMessage(question.id)}"
                  />
                </div>

                <!-- Multiple Choice (Radio) -->
                <div v-else-if="question.type === 'radio'" class="space-y-2">
                  <div 
                    v-for="(option, optIndex) in question.options" 
                    :key="optIndex"
                    class="flex items-center space-x-2"
                  >
                    <input 
                      type="radio" 
                      :id="`${question.id}-${optIndex}`" 
                      :name="`question-${question.id}`"
                      :value="option"
                      v-model="form.answers[question.id]"
                      class="h-4 w-4 border-primary text-primary focus:ring-primary"
                    />
                    <Label :for="`${question.id}-${optIndex}`" class="font-normal cursor-pointer">
                      {{ option }}
                    </Label>
                  </div>
                </div>

                <!-- Checkbox -->
                <div v-else-if="question.type === 'checkbox'" class="space-y-2">
                  <div 
                    v-for="(option, optIndex) in question.options" 
                    :key="optIndex"
                    class="flex items-center space-x-2"
                  >
                    <input 
                      type="checkbox" 
                      :id="`${question.id}-${optIndex}`" 
                      :value="option"
                      v-model="form.answers[question.id]"
                      class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    />
                    <Label :for="`${question.id}-${optIndex}`" class="font-normal cursor-pointer">
                      {{ option }}
                    </Label>
                  </div>
                </div>

                 <!-- Date Input -->
                 <div v-else-if="question.type === 'date'">
                  <Input 
                    type="date"
                    v-model="form.answers[question.id]" 
                    :class="{'border-destructive': getErrorMessage(question.id)}"
                  />
                </div>

                <!-- Textarea (Long Text) -->
                 <div v-else-if="question.type === 'textarea'">
                  <Textarea 
                    v-model="form.answers[question.id]" 
                    :placeholder="question.placeholder || 'Your answer'"
                    :class="{'border-destructive': getErrorMessage(question.id)}"
                    rows="4"
                  />
                </div>

                <div v-if="getErrorMessage(question.id)" class="text-sm text-destructive">
                  {{ getErrorMessage(question.id) }}
                </div>
              </div>
            </div>

            <div class="pt-6 border-t flex justify-end">
              <Button 
                type="submit" 
                size="lg" 
                :disabled="form.processing || !canSubmit"
              >
                {{ form.processing ? 'Submitting...' : 'Submit Response' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
      
      <!-- Progress indicator -->
      <div v-if="questionnaire.questions.length > 5" class="mt-4 text-center text-sm text-muted-foreground">
        {{ answeredCount }} of {{ questionnaire.questions.length }} questions answered
      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { validateForm } from '../../Utils/validation';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../../Components/ui/card';
import { Button } from '../../Components/ui/button/index.js';
import { Input } from '../../Components/ui/input/index.js';
import { Label } from '../../Components/ui/label/index.js';
import { Textarea } from '../../Components/ui/textarea';

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

const answeredCount = computed(() => {
  return props.questionnaire.questions.filter(q => {
     const answer = form.answers[q.id];
      if (Array.isArray(answer)) {
        return answer.length > 0;
      }
      return answer !== '' && answer !== null && answer !== undefined;
  }).length;
});

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
