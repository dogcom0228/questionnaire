<script setup>
    import { Link } from '@inertiajs/vue3'
    import { LayoutDashboard, FileText, Settings, Users } from 'lucide-vue-next'
    import { cn } from '@/lib/utils'
    import Button from '@/questionnaire/Components/ui/button/Button.vue'

    const navigation = [
        {
            name: 'Dashboard',
            href: '/admin/questionnaires',
            icon: LayoutDashboard,
            current: true,
        },
        // Placeholder links for future features
        { name: 'Responses', href: '#', icon: FileText, current: false },
        { name: 'Users', href: '#', icon: Users, current: false },
        { name: 'Settings', href: '#', icon: Settings, current: false },
    ]
</script>

<template>
    <div class="pb-12 min-h-screen w-64 border-r bg-background hidden md:block">
        <div class="space-y-4 py-4">
            <div class="px-3 py-2">
                <h2 class="mb-2 px-4 text-lg font-semibold tracking-tight">
                    Admin Panel
                </h2>
                <div class="space-y-1">
                    <Button
                        v-for="item in navigation"
                        :key="item.name"
                        as-child
                        variant="ghost"
                        class="w-full justify-start"
                        :class="cn(item.current && 'bg-secondary')"
                    >
                        <Link :href="item.href">
                            <component
                                :is="item.icon"
                                class="mr-2 h-4 w-4"
                            />
                            {{ item.name }}
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
