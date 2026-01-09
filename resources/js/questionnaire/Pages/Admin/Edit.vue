<template>
  <AdminLayout>
    <v-container>
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-4">
            <h1 class="text-h4">Edit: {{ questionnaire.title }}</h1>
            <div class="d-flex gap-2">
              <v-btn
                variant="outlined"
                prepend-icon="mdi-arrow-left"
                href="/questionnaire/admin"
              >
                Back to List
              </v-btn>
              <v-btn
                variant="outlined"
                prepend-icon="mdi-eye"
                :href="`/questionnaire/admin/${questionnaire.id}`"
              >
                View Details
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <v-form @submit.prevent="submit" ref="formRef">
        <v-row>
          <!-- Basic Info -->
          <v-col cols="12" md="8">
            <v-card class="mb-4">
              <v-card-title>Basic Information</v-card-title>
              <v-card-text>
                <v-text-field
                  v-model="form.title"
                  label="Title"
                  :error-messages="form.errors.title"
                  required
                  variant="outlined"
                />
                <v-textarea
                  v-model="form.description"
                  label="Description"
                  :error-messages="form.errors.description"
                  variant="outlined"
                  rows="3"
                />
              </v-card-text>
            </v-card>

            <!-- Questions -->
            <v-card class="mb-4">
              <v-card-title class="d-flex justify-space-between align-center">
                <span>Questions</span>
                <v-btn
                  color="primary"
                  size="small"
                  @click="addQuestion"
                  :disabled="questionnaire.status !== 'draft'"
                >
                  <v-icon left>mdi-plus</v-icon>
                  Add Question
                </v-btn>
              </v-card-title>
              <v-card-text>
                <v-alert
                  v-if="questionnaire.status !== 'draft'"
                  type="warning"
                  variant="tonal"
                  class="mb-4"
                >
                  Questions can only be modified when the questionnaire is in draft status.
                </v-alert>

                <v-expansion-panels v-model="openPanels">
                  <draggable
                    v-model="form.questions"
                    item-key="id"
                    handle=".drag-handle"
                    @end="updateQuestionOrder"
                    :disabled="questionnaire.status !== 'draft'"
                  >
                    <template #item="{ element, index }">
                      <v-expansion-panel :value="index">
                        <v-expansion-panel-title>
                          <div class="d-flex align-center w-100">
                            <v-icon class="drag-handle mr-2 cursor-move">mdi-drag</v-icon>
                            <span class="mr-2">{{ index + 1 }}.</span>
                            <span class="text-truncate flex-grow-1">
                              {{ element.content || 'New Question' }}
                            </span>
                            <v-chip size="x-small" class="mr-2">
                              {{ getQuestionTypeName(element.type) }}
                            </v-chip>
                            <v-chip v-if="element.required" color="error" size="x-small">
                              Required
                            </v-chip>
                          </div>
                        </v-expansion-panel-title>
                        <v-expansion-panel-text>
                          <QuestionEditor
                            v-model="form.questions[index]"
                            :question-types="questionTypes"
                            :index="index"
                            :disabled="questionnaire.status !== 'draft'"
                            @remove="removeQuestion(index)"
                          />
                        </v-expansion-panel-text>
                      </v-expansion-panel>
                    </template>
                  </draggable>
                </v-expansion-panels>

                <div v-if="form.questions.length === 0" class="text-center py-8 text-grey">
                  <v-icon size="64" color="grey-lighten-1">mdi-help-circle-outline</v-icon>
                  <p class="mt-2">No questions yet. Click "Add Question" to get started.</p>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Settings -->
          <v-col cols="12" md="4">
            <v-card class="mb-4">
              <v-card-title>Settings</v-card-title>
              <v-card-text>
                <v-select
                  v-model="form.duplicate_submission_strategy"
                  :items="duplicateStrategyOptions"
                  label="Duplicate Submission"
                  variant="outlined"
                />
                <v-switch
                  v-model="form.requires_auth"
                  label="Require Login"
                  color="primary"
                />
                <v-text-field
                  v-model.number="form.submission_limit"
                  label="Submission Limit"
                  type="number"
                  min="0"
                  variant="outlined"
                  hint="Leave empty for unlimited"
                  persistent-hint
                />
              </v-card-text>
            </v-card>

            <v-card class="mb-4">
              <v-card-title>Schedule</v-card-title>
              <v-card-text>
                <v-text-field
                  v-model="form.starts_at"
                  label="Start Date"
                  type="datetime-local"
                  variant="outlined"
                />
                <v-text-field
                  v-model="form.ends_at"
                  label="End Date"
                  type="datetime-local"
                  variant="outlined"
                />
              </v-card-text>
            </v-card>

            <v-btn
              type="submit"
              color="primary"
              block
              size="large"
              :loading="form.processing"
            >
              Save Changes
            </v-btn>
          </v-col>
        </v-row>
      </v-form>
    </v-container>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
// Use shared admin layout located at resources/js/questionnaire/Layouts
import AdminLayout from '../../Layouts/AdminLayout.vue';
import QuestionEditor from '../../Components/QuestionEditor.vue';
import draggable from 'vuedraggable';

const props = defineProps({
  questionnaire: {
    type: Object,
    required: true,
  },
  questionTypes: {
    type: Array,
    default: () => [],
  },
  duplicateStrategies: {
    type: Object,
    default: () => ({}),
  },
});

const formRef = ref(null);
const openPanels = ref([]);

const form = useForm({
  title: props.questionnaire.title,
  description: props.questionnaire.description || '',
  requires_auth: props.questionnaire.requires_auth || false,
  submission_limit: props.questionnaire.submission_limit || null,
  duplicate_submission_strategy: props.questionnaire.duplicate_submission_strategy || 'allow_multiple',
  starts_at: props.questionnaire.starts_at ? formatDateForInput(props.questionnaire.starts_at) : null,
  ends_at: props.questionnaire.ends_at ? formatDateForInput(props.questionnaire.ends_at) : null,
  questions: props.questionnaire.questions || [],
});

const duplicateStrategyOptions = computed(() =>
  Object.entries(props.duplicateStrategies).map(([value, title]) => ({ value, title }))
);

function formatDateForInput(dateString) {
  const date = new Date(dateString);
  return date.toISOString().slice(0, 16);
}

const getQuestionTypeName = (typeId) => {
  const type = props.questionTypes.find(t => t.identifier === typeId);
  return type?.name || typeId;
};

let questionCounter = form.questions.length;
const addQuestion = () => {
  form.questions.push({
    id: `new_${++questionCounter}`,
    type: 'text',
    content: '',
    description: '',
    options: [],
    required: false,
    order: form.questions.length,
    settings: {},
  });
  openPanels.value = [form.questions.length - 1];
};

const removeQuestion = (index) => {
  form.questions.splice(index, 1);
  updateQuestionOrder();
};

const updateQuestionOrder = () => {
  form.questions.forEach((q, i) => {
    q.order = i;
  });
};

const submit = () => {
  form.put(`/questionnaire/admin/${props.questionnaire.id}`);
};
</script>

<style scoped>
.cursor-move {
  cursor: move;
}
</style>
