<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1>Edit Questionnaire</h1>
      </v-col>
    </v-row>

    <v-card class="mt-4 pa-4">
      <v-form @submit.prevent="submit">
        <v-text-field
          v-model="form.title"
          label="Title"
          :error-messages="form.errors.title"
          required
        ></v-text-field>

        <v-textarea
          v-model="form.description"
          label="Description"
          :error-messages="form.errors.description"
        ></v-textarea>

        <v-checkbox
          v-model="form.is_active"
          label="Active"
          :error-messages="form.errors.is_active"
        ></v-checkbox>

        <v-btn type="submit" color="success" :loading="form.processing">
          Update
        </v-btn>
      </v-form>
    </v-card>
  </v-container>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  questionnaire: Object,
});

const form = useForm({
  title: props.questionnaire.title,
  description: props.questionnaire.description,
  is_active: !!props.questionnaire.is_active,
});

const submit = () => {
  form.put(route('questionnaires.update', props.questionnaire.id));
};
</script>
