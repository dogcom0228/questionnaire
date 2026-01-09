<template>
  <PublicLayout title="Already Submitted">
    <v-container class="py-16">
      <v-row justify="center">
        <v-col cols="12" md="6" class="text-center">
          <v-icon size="120" color="warning" class="mb-4">
            mdi-alert-circle
          </v-icon>
          
          <h1 class="text-h3 mb-4">Already Submitted</h1>
          
          <p class="text-body-1 text-grey mb-8">
            {{ message || 'You have already submitted a response to this survey.' }}
          </p>

          <v-btn href="/" color="primary">
            Back to Home
          </v-btn>
        </v-col>
      </v-row>
    </v-container>
  </PublicLayout>
</template>

<script setup>
// Use public layout located at resources/js/questionnaire/Layouts
import PublicLayout from '../../Layouts/PublicLayout.vue';

defineProps({
  questionnaire: {
    type: Object,
    default: null,
  },
  message: {
    type: String,
    default: null,
  },
});
</script>
