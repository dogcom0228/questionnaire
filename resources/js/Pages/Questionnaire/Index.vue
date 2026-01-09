<template>
  <v-container>
    <v-row>
      <v-col cols="12" class="d-flex justify-space-between align-center">
        <h1>Questionnaires</h1>
        <v-btn color="primary" :href="route('questionnaires.create')" tag="a">
          Create New
        </v-btn>
      </v-col>
    </v-row>

    <v-card class="mt-4">
      <v-table>
        <thead>
          <tr>
            <th class="text-left">Title</th>
            <th class="text-left">Status</th>
            <th class="text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in questionnaires.data" :key="item.id">
            <td>{{ item.title }}</td>
            <td>
              <v-chip :color="item.is_active ? 'success' : 'grey'">
                {{ item.is_active ? 'Active' : 'Inactive' }}
              </v-chip>
            </td>
            <td>
              <v-btn
                variant="text"
                color="primary"
                :href="route('questionnaires.show', item.id)"
                tag="a"
              >
                View
              </v-btn>
              <v-btn
                variant="text"
                color="info"
                :href="route('questionnaires.edit', item.id)"
                tag="a"
              >
                Edit
              </v-btn>
            </td>
          </tr>
        </tbody>
      </v-table>
    </v-card>
  </v-container>
</template>

<script setup>
defineProps({
  questionnaires: Object,
});
</script>
