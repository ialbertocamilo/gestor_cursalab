<template>
    <v-breadcrumbs :items="breadcrumbs" :large="large" class="default-breadcrumb">
        <template v-slot:divider>
            <v-icon v-text="'mdi-chevron-double-right'"/>
        </template>
        <template v-slot:item="{ item }">
            <div class="d-flex flex-column position-relative pt-2">
                <a class="default-breadcrumb-title default-breadcrumb-cursor-none btn_b"
                   href="#"
                   v-text="item.title"/>
                <v-tooltip bottom v-if="item.text" attach>
                    <template v-slot:activator="{ on, attrs }">
                        <span
                            class="default-breadcrumb-text cursor-pointer"
                            v-bind="attrs"
                            v-on="on"
                            @click="navigate(item)"
                        >{{item.text}}</span>
                        </template>
                    <span  v-text="item.text"/>
                </v-tooltip>
                <span v-else>&nbsp;</span>
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
