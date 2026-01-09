<template>
  <AdminLayout>
    <v-container>
      <v-row>
        <v-col cols="12">
          <div class="d-flex justify-space-between align-center mb-4">
            <h1 class="text-h4">Questionnaires</h1>
            <v-btn
              color="primary"
              prepend-icon="mdi-plus"
              :href="createUrl"
            >
              Create New
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <!-- Filters -->
      <v-row class="mb-4">
        <v-col cols="12" md="4">
          <v-text-field
            v-model="localFilters.search"
            label="Search"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            clearable
            @update:model-value="debounceSearch"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="localFilters.status"
            :items="statusOptions"
            label="Status"
            variant="outlined"
            density="compact"
            clearable
            @update:model-value="applyFilters"
          />
        </v-col>
      </v-row>

      <!-- Data Table -->
      <v-card>
        <v-data-table
          :headers="headers"
          :items="questionnaires.data"
          :items-per-page="15"
          class="elevation-1"
        >
          <template #item.status="{ item }">
            <v-chip
              :color="getStatusColor(item.status)"
              size="small"
            >
              {{ statuses[item.status] || item.status }}
            </v-chip>
          </template>

          <template #item.is_active="{ item }">
            <v-icon
              :color="item.is_active ? 'success' : 'grey'"
              :icon="item.is_active ? 'mdi-check-circle' : 'mdi-close-circle'"
            />
          </template>

          <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template #item.actions="{ item }">
            <v-btn
              icon="mdi-eye"
              size="small"
              variant="text"
              :href="showUrl(item.id)"
            />
            <v-btn
              icon="mdi-pencil"
              size="small"
              variant="text"
              :href="editUrl(item.id)"
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

      <!-- Delete Confirmation Dialog -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card>
          <v-card-title>Confirm Delete</v-card-title>
          <v-card-text>
            Are you sure you want to delete "{{ itemToDelete?.title }}"?
            This action cannot be undone.
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn @click="deleteDialog = false">Cancel</v-btn>
            <v-btn
              color="error"
              @click="deleteItem"
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
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '../Layouts/AdminLayout.vue';

const props = defineProps({
  questionnaires: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  statuses: {
    type: Object,
    default: () => ({
      draft: 'Draft',
      published: 'Published',
      closed: 'Closed',
    }),
  },
});

const localFilters = ref({ ...props.filters });
const deleteDialog = ref(false);
const itemToDelete = ref(null);
const deleting = ref(false);

const headers = [
  { title: 'Title', key: 'title', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Active', key: 'is_active', sortable: false },
  { title: 'Responses', key: 'responses_count', sortable: true },
  { title: 'Created', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

const statusOptions = computed(() => 
  Object.entries(props.statuses).map(([value, title]) => ({ value, title }))
);

const createUrl = '/questionnaire/admin/create';
const showUrl = (id) => `/questionnaire/admin/${id}`;
const editUrl = (id) => `/questionnaire/admin/${id}/edit`;

const getStatusColor = (status) => {
  const colors = {
    draft: 'grey',
    published: 'success',
    closed: 'error',
  };
  return colors[status] || 'grey';
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString();
};

let searchTimeout = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
};

const applyFilters = () => {
  router.get('/questionnaire/admin', localFilters.value, {
    preserveState: true,
    replace: true,
  });
};

const confirmDelete = (item) => {
  itemToDelete.value = item;
  deleteDialog.value = true;
};

const deleteItem = () => {
  if (!itemToDelete.value) return;
  
  deleting.value = true;
  router.delete(`/questionnaire/admin/${itemToDelete.value.id}`, {
    onFinish: () => {
      deleting.value = false;
      deleteDialog.value = false;
      itemToDelete.value = null;
    },
  });
};
</script>
