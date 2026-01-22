<template>
    <AdminLayout>
        <v-container>
            <v-row>
                <v-col cols="12">
                    <div class="d-flex justify-space-between align-center mb-4">
                        <h1 class="text-h4">Questionnaires</h1>
                        <v-btn
                            color="primary"
                            prepend-icon="mdi-plus"
                            :href="createUrl"
                        >
                            Create New
                        </v-btn>
                    </div>
                </v-col>
            </v-row>

            <!-- Filters -->
            <v-row class="mb-4">
                <v-col
                    cols="12"
                    md="4"
                >
                    <v-text-field
                        v-model="localFilters.search"
                        label="Search"
                        prepend-inner-icon="mdi-magnify"
                        variant="outlined"
                        density="compact"
                        clearable
                        class="mb-2"
                        @update:model-value="debounceSearch"
                    />
                </v-col>
                <v-col
                    cols="12"
                    md="3"
                >
                    <v-select
                        v-model="localFilters.status"
                        :items="statusOptions"
                        label="Status"
                        variant="outlined"
                        density="compact"
                        clearable
                        class="mb-2"
                        @update:model-value="applyFilters"
                    />
                </v-col>
            </v-row>

            <!-- Data Table -->
            <v-card>
                <v-data-table
                    :headers="headers"
                    :items="questionnaires.data"
                    :items-per-page="15"
                    class="elevation-1"
                >
                    <template #no-data>
                        <v-empty-state
                            icon="mdi-clipboard-text-outline"
                            title="No questionnaires found"
                            text="Create your first questionnaire to get started collecting responses."
                            action-text="Create New"
                            @click:action="router.visit(createUrl)"
                        />
                    </template>

                    <template #:[`item.status`]="{ item }">
                        <v-chip
                            :color="getStatusColor(item.status)"
                            size="small"
                        >
                            {{ statuses[item.status] || item.status }}
                        </v-chip>
                    </template>

                    <template #:[`item.is_active`]="{ item }">
                        <v-icon
                            :color="item.is_active ? 'success' : 'grey'"
                            :icon="
                                item.is_active
                                    ? 'mdi-check-circle'
                                    : 'mdi-close-circle'
                            "
                        />
                    </template>

                    <template #:[`item.created_at`]="{ item }">
                        {{ formatDate(item.created_at) }}
                    </template>

                    <template #:[`item.actions`]="{ item }">
                        <v-tooltip
                            location="top"
                            text="View"
                        >
                            <template #activator="{ props }">
                                <v-btn
                                    v-bind="props"
                                    icon="mdi-eye"
                                    size="small"
                                    variant="tonal"
                                    color="info"
                                    class="mr-2"
                                    :href="showUrl(item.id)"
                                />
                            </template>
                        </v-tooltip>

                        <v-tooltip
                            location="top"
                            text="Edit"
                        >
                            <template #activator="{ props }">
                                <v-btn
                                    v-bind="props"
                                    icon="mdi-pencil"
                                    size="small"
                                    variant="tonal"
                                    color="success"
                                    class="mr-2"
                                    :href="editUrl(item.id)"
                                />
                            </template>
                        </v-tooltip>

                        <v-tooltip
                            location="top"
                            text="Delete"
                        >
                            <template #activator="{ props }">
                                <v-btn
                                    v-bind="props"
                                    icon="mdi-delete"
                                    size="small"
                                    variant="tonal"
                                    color="error"
                                    @click="confirmDelete(item)"
                                />
                            </template>
                        </v-tooltip>
                    </template>
                </v-data-table>
            </v-card>

            <!-- Delete Confirmation Dialog -->
            <v-dialog
                v-model="deleteDialog"
                max-width="400"
            >
                <v-card>
                    <v-card-title class="text-h5 d-flex align-center">
                        <v-icon
                            icon="mdi-alert"
                            color="warning"
                            class="mr-2"
                        />
                        Confirm Delete
                    </v-card-title>
                    <v-card-text>
                        Are you sure you want to delete "{{
                            itemToDelete?.title
                        }}"? This action cannot be undone.
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer />
                        <v-btn
                            variant="text"
                            @click="deleteDialog = false"
                        >
                            Cancel
                        </v-btn>
                        <v-btn
                            color="error"
                            variant="elevated"
                            @click="deleteItem"
                            :loading="deleting"
                        >
                            Delete
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-container>
    </AdminLayout>
</template>

<script setup>
    import { router } from '@inertiajs/vue3'
    import { computed, ref } from 'vue'
    import { debounce, formatDate as utilFormatDate } from '../../Utils/helpers'
    // Use shared admin layout located at resources/js/questionnaire/Layouts
    import AdminLayout from '../../Layouts/AdminLayout.vue'

    const props = defineProps({
        questionnaires: {
            type: Object,
            required: true,
        },
        filters: {
            type: Object,
            default: () => ({}),
        },
        statuses: {
            type: Object,
            default: () => ({
                draft: 'Draft',
                published: 'Published',
                closed: 'Closed',
            }),
        },
    })

    const localFilters = ref({ ...props.filters })
    const deleteDialog = ref(false)
    const itemToDelete = ref(null)
    const deleting = ref(false)

    const headers = [
        { title: 'Title', key: 'title', sortable: true },
        { title: 'Status', key: 'status', sortable: true },
        { title: 'Active', key: 'is_active', sortable: false },
        { title: 'Responses', key: 'responses_count', sortable: true },
        { title: 'Created', key: 'created_at', sortable: true },
        { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
    ]

    const statusOptions = computed(() =>
        Object.entries(props.statuses).map(([value, title]) => ({
            value,
            title,
        }))
    )

    const createUrl = '/questionnaire/admin/create'
    const showUrl = (id) => `/questionnaire/admin/${id}`
    const editUrl = (id) => `/questionnaire/admin/${id}/edit`

    const getStatusColor = (status) => {
        const colors = {
            draft: 'grey',
            published: 'success',
            closed: 'error',
        }
        return colors[status] || 'grey'
    }

    const formatDate = (date) => {
        return utilFormatDate(date)
    }

    // Use debounce helper for search
    const debounceSearch = debounce(() => {
        applyFilters()
    }, 500)

    const applyFilters = () => {
        router.get('/questionnaire/admin', localFilters.value, {
            preserveState: true,
            replace: true,
        })
    }

    const confirmDelete = (item) => {
        itemToDelete.value = item
        deleteDialog.value = true
    }

    const deleteItem = () => {
        if (!itemToDelete.value) return

        deleting.value = true
        router.delete(`/questionnaire/admin/${itemToDelete.value.id}`, {
            onFinish: () => {
                deleting.value = false
                deleteDialog.value = false
                itemToDelete.value = null
            },
            onError: (errors) => {
                if (import.meta.env?.DEV) {
                    // eslint-disable-next-line no-console
                    console.error('Failed to delete questionnaire:', errors)
                }
                deleting.value = false
            },
        })
    }
</script>
