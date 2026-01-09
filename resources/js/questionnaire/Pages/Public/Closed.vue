<template>
  <PublicLayout title="Survey Closed">
    <v-container class="py-16">
      <v-row justify="center">
        <v-col cols="12" md="6" class="text-center">
          <v-icon size="120" color="grey" class="mb-4">
            mdi-calendar-remove
          </v-icon>
          
          <h1 class="text-h3 mb-4">Survey Closed</h1>
          
          <p class="text-body-1 text-grey mb-8">
            {{ message || 'This survey is no longer accepting responses.' }}
          </p>

          <div v-if="reason" class="mb-8">
            <v-alert type="info" variant="tonal">
              {{ reason }}
            </v-alert>
          </div>

          <v-btn href="/" color="primary">
            Back to Home
          </v-btn>
        </v-col>
      </v-row>
    </v-container>
  </PublicLayout>
</template>

<script setup>
import PublicLayout from '../Layouts/PublicLayout.vue';

defineProps({
  questionnaire: {
    type: Object,
    default: null,
  },
  message: {
    type: String,
    default: null,
  },
  reason: {
    type: String,
    default: null,
  },
});
</script>
