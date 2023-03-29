<template>
    <v-breadcrumbs :items="breadcrumbs" :large="large" class="default-breadcrumb">
        <template v-slot:divider>
            <v-icon v-text="'mdi-chevron-right'"/>
        </template>
        <template v-slot:item="{ item }">
            <div class="d-flex flex-column">
                <a class="default-breadcrumb-title"
                   href="#"
                   v-text="item.title"
                   @click="navigate(item)"
                />
            </div>
        </template>
    </v-breadcrumbs>
</template>
<script>
export default {
    props: {
        breadcrumbs: {
            type: Array,
            required: true
        },
        large: {
            type: Boolean,
            default: false
        }
    },
    data: () => ({}),
    methods: {
        navigate(breadcrumb) {
            if (breadcrumb.disabled) return
            window.location.href = breadcrumb.href;
        }
    }
}
</script>
