<template>
  <AdminLayout>
    <v-container>
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-4">
            <div>
              <h1 class="text-h4">Responses</h1>
              <p class="text-grey">{{ questionnaire.title }}</p>
            </div>
            <div class="d-flex gap-2">
              <v-btn
                variant="outlined"
                prepend-icon="mdi-arrow-left"
                :href="`/questionnaire/admin/${questionnaire.id}`"
              >
                Back to Details
              </v-btn>
              <v-btn
                color="primary"
                prepend-icon="mdi-download"
                @click="exportCsv"
                :loading="exporting"
              >
                Export CSV
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>

      <!-- Statistics Summary -->
      <v-row class="mb-4">
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-h3">{{ responses.total }}</div>
              <div class="text-grey">Total Responses</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-h3">{{ questionnaire.questions?.length || 0 }}</div>
              <div class="text-grey">Questions</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-body-1">{{ statistics.completion_rate || 0 }}%</div>
              <div class="text-grey">Completion Rate</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card>
            <v-card-text class="text-center">
              <div class="text-body-1">{{ statistics.avg_time || '-' }}</div>
              <div class="text-grey">Avg. Completion Time</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Responses Table -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-data-table
              :headers="headers"
              :items="responses.data"
              :items-per-page="15"
              class="elevation-1"
            >
              <template #item.respondent="{ item }">
                <span v-if="item.user">{{ item.user.name || item.user.email }}</span>
                <span v-else class="text-grey">Anonymous</span>
              </template>

              <template #item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
              </template>

              <template #item.actions="{ item }">
                <v-btn
                  icon="mdi-eye"
                  size="small"
                  variant="text"
                  @click="viewResponse(item)"
                />
                <v-btn
                  icon="mdi-delete"
                  size="small"
                  variant="text"
                  color="error"
                  @click="confirmDelete(item)"
                />
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>

      <!-- View Response Dialog -->
      <v-dialog v-model="viewDialog" max-width="700">
        <v-card v-if="selectedResponse">
          <v-card-title class="d-flex justify-space-between">
            <span>Response Details</span>
            <v-btn icon="mdi-close" variant="text" @click="viewDialog = false" />
          </v-card-title>
          <v-card-text>
            <v-list>
              <v-list-item
                v-for="question in questionnaire.questions"
                :key="question.id"
              >
                <v-list-item-title class="font-weight-bold">
                  {{ question.content }}
                </v-list-item-title>
                <v-list-item-subtitle>
                  {{ getAnswerForQuestion(selectedResponse, question.id) || 'No answer' }}
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>

            <v-divider class="my-4" />

            <div class="text-grey text-caption">
              <div>Submitted: {{ formatDate(selectedResponse.created_at) }}</div>
              <div v-if="selectedResponse.user">
                By: {{ selectedResponse.user.name || selectedResponse.user.email }}
              </div>
              <div v-if="selectedResponse.metadata?.ip_address">
                IP: {{ selectedResponse.metadata.ip_address }}
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-dialog>

      <!-- Delete Confirmation Dialog -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title>Confirm Delete</v-card-title>
          <v-card-text>
            Are you sure you want to delete this response? This action cannot be undone.
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn @click="deleteDialog = false">Cancel</v-btn>
            <v-btn
              color="error"
              @click="deleteResponse"
              :loading="deleting"
            >
              Delete
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '../Layouts/AdminLayout.vue';

const props = defineProps({
  questionnaire: {
    type: Object,
    required: true,
  },
  responses: {
    type: Object,
    required: true,
  },
  statistics: {
    type: Object,
    default: () => ({}),
  },
});

const viewDialog = ref(false);
const deleteDialog = ref(false);
const selectedResponse = ref(null);
const deleting = ref(false);
const exporting = ref(false);

const headers = [
  { title: '#', key: 'id', sortable: true },
  { title: 'Respondent', key: 'respondent', sortable: false },
  { title: 'Submitted', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString();
};

const viewResponse = (response) => {
  selectedResponse.value = response;
  viewDialog.value = true;
};

const getAnswerForQuestion = (response, questionId) => {
  const answer = response.answers?.find(a => a.question_id === questionId);
  if (!answer) return null;
  
  const value = answer.value;
  if (Array.isArray(value)) {
    return value.join(', ');
  }
  return value;
};

const confirmDelete = (response) => {
  selectedResponse.value = response;
  deleteDialog.value = true;
};

const deleteResponse = () => {
  if (!selectedResponse.value) return;
  
  deleting.value = true;
  router.delete(`/questionnaire/admin/${props.questionnaire.id}/responses/${selectedResponse.value.id}`, {
    onFinish: () => {
      deleting.value = false;
      deleteDialog.value = false;
      selectedResponse.value = null;
    },
  });
};

const exportCsv = () => {
  exporting.value = true;
  window.location.href = `/questionnaire/admin/${props.questionnaire.id}/export`;
  setTimeout(() => {
    exporting.value = false;
  }, 2000);
};
</script>
