<template>
    <div class="min-h-screen bg-background font-sans antialiased flex flex-col">
        <TopHeader />
        <div class="flex flex-1 overflow-hidden">
            <Sidebar />
            <main class="flex-1 overflow-y-auto bg-muted/20 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
    import Sidebar from '@/questionnaire/Components/app/Sidebar.vue'
    import TopHeader from '@/questionnaire/Components/app/TopHeader.vue'
</script>
