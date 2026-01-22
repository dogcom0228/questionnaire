<template>
  <PublicLayout title="Thank You">
    <div class="py-8 flex items-center justify-center min-h-[50vh]">
      <Card class="w-full max-w-lg text-center p-8">
        <div class="flex justify-center mb-6">
          <div class="rounded-full bg-green-100 p-3">
            <CheckCircle class="h-16 w-16 text-green-600" />
          </div>
        </div>
        
        <h1 class="text-3xl font-bold mb-2">Thank You!</h1>
        
        <p class="text-lg text-muted-foreground mb-8">
          {{ message || 'Your response has been recorded successfully.' }}
        </p>

        <div v-if="allowAnotherResponse && fillUrl">
          <a :href="fillUrl">
            <Button size="lg">
              Submit Another Response
            </Button>
          </a>
        </div>
      </Card>
    </div>
  </PublicLayout>
</template>

<script setup>
import { computed } from 'vue';
import { CheckCircle } from 'lucide-vue-next';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import { Card } from '../../Components/ui/card';
import { Button } from '../../Components/ui/button';

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
