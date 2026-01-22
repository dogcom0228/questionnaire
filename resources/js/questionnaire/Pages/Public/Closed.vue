<template>
  <PublicLayout title="Questionnaire Closed">
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
              icon="mdi-lock-clock"
              color="grey"
              size="64"
              class="mb-4"
            ></v-icon>

            <v-card-title class="text-h4 mb-2">
              Questionnaire Closed
            </v-card-title>

            <v-card-text class="text-body-1">
              <p class="mb-4">
                {{
                  message ||
                  'This questionnaire is no longer accepting responses.'
                }}
              </p>

              <v-alert
                v-if="reason"
                type="info"
                variant="tonal"
                class="mt-4 text-left"
              >
                {{ reason }}
              </v-alert>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </PublicLayout>
</template>

<script setup>
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
  reason: {
    type: String,
    default: null,
  },
});
</script>
