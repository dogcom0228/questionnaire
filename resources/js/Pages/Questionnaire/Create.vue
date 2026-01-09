<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1>Create Questionnaire</h1>
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

        <v-btn type="submit" color="success" :loading="form.processing">
          Save
        </v-btn>
      </v-form>
    </v-card>
  </v-container>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  title: '',
  description: '',
});

const submit = () => {
  form.post(route('questionnaires.store'));
};
</script>
