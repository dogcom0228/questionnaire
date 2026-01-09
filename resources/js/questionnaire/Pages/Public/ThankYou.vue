<template>
  <PublicLayout title="Thank You">
    <v-container class="py-16">
      <v-row justify="center">
        <v-col cols="12" md="6" class="text-center">
          <v-icon size="120" color="success" class="mb-4">
            mdi-check-circle
          </v-icon>
          
          <h1 class="text-h3 mb-4">Thank You!</h1>
          
          <p class="text-body-1 text-grey mb-8">
            {{ message || 'Your response has been submitted successfully.' }}
          </p>

          <div class="d-flex justify-center gap-4">
            <v-btn
              v-if="allowAnotherResponse"
              color="primary"
              variant="outlined"
              :href="fillUrl"
            >
              Submit Another Response
            </v-btn>
            <v-btn
              href="/"
              variant="text"
            >
              Back to Home
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </PublicLayout>
</template>

<script setup>
import { computed } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';

const props = defineProps({
  questionnaire: {
    type: Object,
    required: true,
  },
  message: {
    type: String,
    default: null,
  },
  allowAnotherResponse: {
    type: Boolean,
    default: false,
  },
});

const fillUrl = computed(() => `/survey/${props.questionnaire.id}`);
</script>
