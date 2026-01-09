<template>
  <v-app>
    <v-app-bar color="primary" density="compact">
      <v-app-bar-nav-icon @click="drawer = !drawer" />
      <v-toolbar-title>Questionnaire Admin</v-toolbar-title>
      <v-spacer />
      <v-btn icon="mdi-account" />
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" temporary>
      <v-list nav>
        <v-list-item
          prepend-icon="mdi-view-dashboard"
          title="Dashboard"
          :href="route('questionnaire.admin.index')"
        />
        <v-list-item
          prepend-icon="mdi-plus-circle"
          title="Create Questionnaire"
          :href="route('questionnaire.admin.create')"
        />
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <slot />
    </v-main>

    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3000"
    >
      {{ snackbar.message }}
      <template #actions>
        <v-btn variant="text" @click="snackbar.show = false">Close</v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const drawer = ref(false);
const snackbar = ref({
  show: false,
  message: '',
  color: 'success',
});

const page = usePage();

onMounted(() => {
  // Show flash messages
  if (page.props.flash?.success) {
    snackbar.value = {
      show: true,
      message: page.props.flash.success,
      color: 'success',
    };
  }
  if (page.props.flash?.error) {
    snackbar.value = {
      show: true,
      message: page.props.flash.error,
      color: 'error',
    };
  }
});

// Helper to generate routes
const route = (name, params = {}) => {
  // This is a simple implementation - in production, use ziggy or similar
  const routes = {
    'questionnaire.admin.index': '/questionnaire/admin',
    'questionnaire.admin.create': '/questionnaire/admin/create',
  };
  let url = routes[name] || '#';
  Object.entries(params).forEach(([key, value]) => {
    url = url.replace(`:${key}`, value);
  });
  return url;
};
</script>
