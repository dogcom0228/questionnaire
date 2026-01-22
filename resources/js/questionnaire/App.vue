<template>
    <component
        :is="currentComponent"
        v-bind="currentProps"
    />
</template>

<script setup>
    import { computed, ref, onMounted } from 'vue'
    import AdminIndex from './Pages/Admin/Index.vue'
    import AdminCreate from './Pages/Admin/Create.vue'
    import AdminEdit from './Pages/Admin/Edit.vue'
    import AdminShow from './Pages/Admin/Show.vue'
    import PublicFill from './Pages/Public/Fill.vue'
    import PublicThankYou from './Pages/Public/ThankYou.vue'
    import PublicClosed from './Pages/Public/Closed.vue'

    const props = defineProps(['view', 'questionnaireId', 'initialData'])

    const currentComponent = computed(() => {
        const views = {
            'admin.index': AdminIndex,
            'admin.create': AdminCreate,
            'admin.edit': AdminEdit,
            'admin.show': AdminShow,
            'public.fill': PublicFill,
            'public.thankyou': PublicThankYou,
            'public.closed': PublicClosed,
        }
        return views[props.view] || null
    })

    const currentProps = computed(() => {
        return {
            id: props.questionnaireId ? parseInt(props.questionnaireId) : null,
            ...JSON.parse(props.initialData || '{}')
        }
    })
</script>
