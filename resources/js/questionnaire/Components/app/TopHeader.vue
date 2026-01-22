<script setup>
    import { Link } from '@inertiajs/vue3'
    import { LogOut } from 'lucide-vue-next'
    import Button from '@/questionnaire/Components/ui/button/Button.vue'
</script>

<template>
    <header
        class="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
    >
        <div class="flex h-14 items-center px-6">
            <div class="mr-4 hidden md:flex">
                <Link
                    href="/"
                    class="mr-6 flex items-center space-x-2"
                >
                    <span class="hidden font-bold sm:inline-block">
                        Questionnaire App
                    </span>
                </Link>
            </div>
            <div
                class="flex flex-1 items-center justify-between space-x-2 md:justify-end"
            >
                <div class="w-full flex-1 md:w-auto md:flex-none"></div>
                <nav class="flex items-center gap-2">
                    <div class="text-sm font-medium">Admin User</div>
                    <Button
                        variant="ghost"
                        size="icon"
                        title="Log out"
                    >
                        <LogOut class="h-4 w-4" />
                    </Button>
                </nav>
            </div>
        </div>
    </header>
</template>
