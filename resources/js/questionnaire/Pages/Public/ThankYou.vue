<template>
  <PublicLayout title="Thank You">
    <v-container class="py-8 fill-height">
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="12"
          md="8"
          lg="6"
        >
          <v-card class="text-center pa-8">
            <v-icon
              icon="mdi-check-circle"
              color="success"
              size="64"
              class="mb-4"
            ></v-icon>

            <v-card-title class="text-h4 mb-2">
              Thank You!
            </v-card-title>

            <v-card-text class="text-body-1">
              <p class="mb-4">
                {{
                  message ||
                  'Your response has been recorded successfully.'
                }}
              </p>

              <div
                v-if="allowAnotherResponse && fillUrl"
                class="mt-6"
              >
                <v-btn
                  :href="fillUrl"
                  color="primary"
                  variant="flat"
                  size="large"
                >
                  Submit Another Response
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </PublicLayout>
</template>

<script setup>
import { computed } from 'vue';
import PublicLayout from '../../Layouts/PublicLayout.vue';

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
