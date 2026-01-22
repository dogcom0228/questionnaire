<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold tracking-tight">
                    Questionnaires
                </h1>
                <Button :as-child="true">
                    <a :href="createUrl">
                        <span class="mr-2">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-plus"
                            >
                                <path d="M5 12h14" />
                                <path d="M12 5v14" />
                            </svg>
                        </span>
                        Create New
                    </a>
                </Button>
            </div>

            <!-- Filters -->
            <div class="flex items-center space-x-2">
                <div class="relative w-full md:w-1/3">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground"
                    >
                        <circle
                            cx="11"
                            cy="11"
                            r="8"
                        />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <Input
                        v-model="localFilters.search"
                        placeholder="Search questionnaires..."
                        class="pl-8"
                        @input="debounceSearch"
                    />
                </div>
                <!-- Status Filter could go here -->
            </div>

            <Card>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[300px]">Title</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Active</TableHead>
                                <TableHead>Responses</TableHead>
                                <TableHead>Created</TableHead>
                                <TableHead class="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="item in questionnaires.data"
                                :key="item.id"
                            >
                                <TableCell class="font-medium">
                                    <a
                                        :href="showUrl(item.id)"
                                        class="hover:underline"
                                    >
                                        {{ item.title }}
                                    </a>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="item.status">
                                        {{
                                            statuses[item.status] || item.status
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center">
                                        <svg
                                            v-if="item.is_active"
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="text-green-500"
                                        >
                                            <path
                                                d="M22 11.08V12a10 10 0 1 1-5.93-9.14"
                                            />
                                            <polyline
                                                points="22 4 12 14.01 9 11.01"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="text-gray-400"
                                        >
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="10"
                                            />
                                            <path d="m15 9-6 6" />
                                            <path d="m9 9 6 6" />
                                        </svg>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    {{ item.responses_count }}
                                </TableCell>
                                <TableCell>
                                    {{ formatDate(item.created_at) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                class="h-8 w-8 p-0"
                                            >
                                                <span class="sr-only">
                                                    Open menu
                                                </span>
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="16"
                                                    height="16"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="h-4 w-4"
                                                >
                                                    <circle
                                                        cx="12"
                                                        cy="12"
                                                        r="1"
                                                    />
                                                    <circle
                                                        cx="19"
                                                        cy="12"
                                                        r="1"
                                                    />
                                                    <circle
                                                        cx="5"
                                                        cy="12"
                                                        r="1"
                                                    />
                                                </svg>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuLabel>
                                                Actions
                                            </DropdownMenuLabel>
                                            <DropdownMenuItem
                                                @click="
                                                    router.visit(
                                                        showUrl(item.id)
                                                    )
                                                "
                                            >
                                                View Details
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                @click="
                                                    router.visit(
                                                        editUrl(item.id)
                                                    )
                                                "
                                            >
                                                Edit Questionnaire
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                class="text-red-600"
                                                @click="confirmDelete(item)"
                                            >
                                                Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="questionnaires.data.length === 0">
                                <TableCell
                                    colspan="6"
                                    class="h-24 text-center"
                                >
                                    No questionnaires found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </Card>

            <!-- Delete Confirmation Dialog (Using native dialog or create a proper Dialog component later, for now we can stick with a simple implementation or reuse the v-dialog if we had Vuetify, but since we are removing Vuetify, let's use a standard browser confirm for speed or build a Shadcn Dialog) -->
            <!-- For now, using browser confirm for simplicity in this step, or could quickly scaffold a Dialog if needed. Let's use a custom modal implementation for now to keep it clean without Vuetify. -->

            <div
                v-if="deleteDialog"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            >
                <div
                    class="w-full max-w-md rounded-lg bg-background p-6 shadow-lg"
                >
                    <h3 class="text-lg font-semibold">Confirm Delete</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Are you sure you want to delete "{{
                            itemToDelete?.title
                        }}"? This action cannot be undone.
                    </p>
                    <div class="mt-4 flex justify-end space-x-2">
                        <Button
                            variant="outline"
                            @click="deleteDialog = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            variant="destructive"
                            @click="deleteItem"
                            :disabled="deleting"
                        >
                            {{ deleting ? 'Deleting...' : 'Delete' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
    import { router } from '@inertiajs/vue3'
    import { ref } from 'vue'
    import { debounce, formatDate as utilFormatDate } from '../../Utils/helpers'
    import AdminLayout from '../../Layouts/AdminLayout.vue'

    // UI Components
    import Button from '../../Components/ui/button/Button.vue'
    import Input from '../../Components/ui/input/Input.vue'
    import Card from '../../Components/ui/card/Card.vue'
    import Badge from '../../Components/ui/badge/Badge.vue'
    import {
        Table,
        TableBody,
        TableCaption,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '../../Components/ui/table'
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuSeparator,
        DropdownMenuTrigger,
    } from '../../Components/ui/dropdown-menu'

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

    const createUrl = '/questionnaire/admin/create'
    const showUrl = (id) => `/questionnaire/admin/${id}`
    const editUrl = (id) => `/questionnaire/admin/${id}/edit`

    const formatDate = (date) => {
        return utilFormatDate(date)
    }

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
                    console.error('Failed to delete questionnaire:', errors)
                }
                deleting.value = false
            },
        })
    }
</script>
