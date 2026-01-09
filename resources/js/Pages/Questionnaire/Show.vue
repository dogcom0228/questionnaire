<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between">
          <h1>{{ questionnaire.title }}</h1>
          <v-btn color="secondary" :href="route('questionnaires.index')" tag="a">
            Back
          </v-btn>
        </div>
        <p class="text-subtitle-1">{{ questionnaire.description }}</p>
      </v-col>
    </v-row>

    <v-divider class="my-4"></v-divider>

    <v-row>
      <v-col cols="12">
        <h3>Questions</h3>
        
        <!-- Add Question Form -->
        <v-card class="mb-4 pa-4" variant="outlined">
            <h4>Add New Question</h4>
            <v-form @submit.prevent="addQuestion">
                <v-select
                    v-model="questionForm.type"
                    :items="['text', 'textarea', 'radio', 'checkbox']"
                    label="Question Type"
                    required
                ></v-select>
                
                <v-text-field
                    v-model="questionForm.content"
                    label="Question Text"
                    required
                ></v-text-field>

                <div v-if="['radio', 'checkbox'].includes(questionForm.type)">
                     <v-textarea
                        v-model="optionsInput"
                        label="Options (one per line)"
                        hint="Enter each option on a new line"
                        persistent-hint
                     ></v-textarea>
                </div>

                <v-checkbox v-model="questionForm.required" label="Required"></v-checkbox>
                
                <v-btn type="submit" color="primary" :loading="questionForm.processing">Add Question</v-btn>
            </v-form>
        </v-card>

        <!-- List Questions -->
        <v-card v-for="question in questionnaire.questions" :key="question.id" class="mb-3">
          <v-card-title class="d-flex justify-space-between">
             <span>{{ question.content }}</span>
             <v-chip size="small">{{ question.type }}</v-chip>
          </v-card-title>
          <v-card-text>
            <div v-if="question.options && question.options.length">
                <ul>
                    <li v-for="opt in question.options" :key="opt">{{ opt }}</li>
                </ul>
            </div>
             <div v-if="question.required" class="text-caption text-red">Required</div>
          </v-card-text>
          <v-card-actions>
             <v-btn color="error" size="small" @click="deleteQuestion(question.id)">Delete</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
  questionnaire: Object,
});

const optionsInput = ref('');
const questionForm = useForm({
    type: 'text',
    content: '',
    options: [],
    required: false,
    order: 0
});

watch(optionsInput, (newVal) => {
    questionForm.options = newVal.split('\n').filter(line => line.trim() !== '');
});

const addQuestion = () => {
    questionForm.post(route('questionnaires.questions.store', props.questionnaire.id), {
        onSuccess: () => {
            questionForm.reset();
            optionsInput.value = '';
        }
    });
};

const deleteQuestion = (id) => {
    if(confirm('Are you sure?')) {
        router.delete(route('questionnaires.questions.destroy', [props.questionnaire.id, id]));
    }
}
</script>
