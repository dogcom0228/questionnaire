<template>
    <AdminLayout>
        <!-- Builder Layout: Fixed height minus header/padding -->
        <div class="flex h-[calc(100vh-64px)] overflow-hidden">
            <Toolbox />
            <BuilderCanvas />
            <PropertiesPanel />
        </div>
    </AdminLayout>
</template>

<script setup>
    import { onMounted } from 'vue'
    import AdminLayout from '../../Layouts/AdminLayout.vue'
    import Toolbox from '../../Components/builder/Toolbox.vue'
    import BuilderCanvas from '../../Components/builder/BuilderCanvas.vue'
    import PropertiesPanel from '../../Components/builder/PropertiesPanel.vue'
    import { useBuilder } from '../../Composables/useBuilder'

    const props = defineProps({
        questionnaire: {
            type: Object,
            required: true,
        },
        questionTypes: {
            type: Array,
            default: () => [],
        },
    })

    const builder = useBuilder()

    onMounted(() => {
        // Initialize store with questionnaire data
        builder.initialize(props.questionnaire)
    })
</script>
