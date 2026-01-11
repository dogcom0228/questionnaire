<template>
    <AdminLayout>
        <v-container>
            <v-row>
                <v-col cols="12">
                    <div class="d-flex justify-space-between align-center mb-4">
                        <div>
                            <h1 class="text-h4">{{ questionnaire.title }}</h1>
                            <v-chip
                                :color="getStatusColor(questionnaire.status)"
                                size="small"
                                class="mt-1"
                            >
                                {{ questionnaire.status }}
                            </v-chip>
                        </div>
                        <div class="d-flex gap-2">
                            <v-btn
                                variant="outlined"
                                prepend-icon="mdi-arrow-left"
                                href="/questionnaire/admin"
                            >
                                Back
                            </v-btn>
                            <v-btn
                                color="primary"
                                prepend-icon="mdi-pencil"
                                :href="`/questionnaire/admin/${questionnaire.id}/edit`"
                            >
                                Edit
                            </v-btn>
                        </div>
                    </div>
                </v-col>
            </v-row>

            <!-- Status Actions -->
            <v-row class="mb-4">
                <v-col cols="12">
                    <v-card>
                        <v-card-text class="d-flex gap-4 align-center">
                            <div class="flex-grow-1">
                                <strong>Status:</strong>
                                {{ questionnaire.status }}
                                <span
                                    v-if="questionnaire.published_at"
                                    class="ml-4 text-grey"
                                >
                                    Published:
                                    {{ formatDate(questionnaire.published_at) }}
                                </span>
                            </div>
                            <v-btn
                                v-if="questionnaire.status === 'draft'"
                                color="success"
                                @click="publish"
                                :loading="publishing"
                            >
                                Publish
                            </v-btn>
                            <v-btn
                                v-if="questionnaire.status === 'published'"
                                color="error"
                                variant="outlined"
                                @click="close"
                                :loading="closing"
                            >
                                Close
                            </v-btn>
                            <v-btn
                                v-if="questionnaire.status === 'published'"
                                color="primary"
                                variant="outlined"
                                :href="publicUrl"
                                target="_blank"
                            >
                                <v-icon left>mdi-open-in-new</v-icon>
                                View Public Page
                            </v-btn>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Statistics -->
            <v-row class="mb-4">
                <v-col
                    cols="12"
                    md="3"
                >
                    <v-card>
                        <v-card-text class="text-center">
                            <div class="text-h3">
                                {{ statistics.total_responses }}
                            </div>
                            <div class="text-grey">Total Responses</div>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col
                    cols="12"
                    md="3"
                >
                    <v-card>
                        <v-card-text class="text-center">
                            <div class="text-h3">
                                {{ questionnaire.questions?.length || 0 }}
                            </div>
                            <div class="text-grey">Questions</div>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col
                    cols="12"
                    md="3"
                >
                    <v-card>
                        <v-card-text class="text-center">
                            <div class="text-body-1">
                                {{
                                    formatDate(statistics.first_response_at) ||
                                    '-'
                                }}
                            </div>
                            <div class="text-grey">First Response</div>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col
                    cols="12"
                    md="3"
                >
                    <v-card>
                        <v-card-text class="text-center">
                            <div class="text-body-1">
                                {{
                                    formatDate(statistics.last_response_at) ||
                                    '-'
                                }}
                            </div>
                            <div class="text-grey">Last Response</div>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Questions Preview -->
            <v-row>
                <v-col cols="12">
                    <v-card>
                        <v-card-title
                            class="d-flex justify-space-between align-center"
                        >
                            <span>Questions</span>
                            <v-btn
                                size="small"
                                variant="outlined"
                                :href="`/questionnaire/admin/${questionnaire.id}/responses`"
                            >
                                View All Responses
                            </v-btn>
                        </v-card-title>
                        <v-card-text>
                            <v-list>
                                <v-list-item
                                    v-for="(
                                        question, index
                                    ) in questionnaire.questions"
                                    :key="question.id"
                                >
                                    <template #prepend>
                                        <v-avatar
                                            color="primary"
                                            size="32"
                                        >
                                            {{ index + 1 }}
                                        </v-avatar>
                                    </template>
                                    <v-list-item-title>
                                        {{ question.content }}
                                    </v-list-item-title>
                                    <v-list-item-subtitle>
                                        <v-chip
                                            size="x-small"
                                            class="mr-1"
                                        >
                                            {{ question.type }}
                                        </v-chip>
                                        <v-chip
                                            v-if="question.required"
                                            size="x-small"
                                            color="error"
                                        >
                                            Required
                                        </v-chip>
                                    </v-list-item-subtitle>
                                </v-list-item>
                            </v-list>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </AdminLayout>
</template>

<script setup>
    import { ref, computed } from 'vue'
    import { router } from '@inertiajs/vue3'
    // Use shared admin layout located at resources/js/questionnaire/Layouts
    import AdminLayout from '../../Layouts/AdminLayout.vue'

    const props = defineProps({
        questionnaire: {
            type: Object,
            required: true,
        },
        statistics: {
            type: Object,
            default: () => ({
                total_responses: 0,
                first_response_at: null,
                last_response_at: null,
            }),
        },
        questionTypes: {
            type: Array,
            default: () => [],
        },
    })

    const publishing = ref(false)
    const closing = ref(false)

    const publicUrl = computed(() => `/survey/${props.questionnaire.id}`)

    const getStatusColor = (status) => {
        const colors = {
            draft: 'grey',
            published: 'success',
            closed: 'error',
        }
        return colors[status] || 'grey'
    }

    const formatDate = (date) => {
        if (!date) return null
        return new Date(date).toLocaleDateString()
    }

    const publish = () => {
        publishing.value = true
        router.post(
            `/questionnaire/admin/${props.questionnaire.id}/publish`,
            {},
            {
                onFinish: () => (publishing.value = false),
            }
        )
    }

    const close = () => {
        closing.value = true
        router.post(
            `/questionnaire/admin/${props.questionnaire.id}/close`,
            {},
            {
                onFinish: () => (closing.value = false),
            }
        )
    }
</script>
