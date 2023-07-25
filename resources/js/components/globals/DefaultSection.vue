<template>
    <v-card flat tile elevation="0" style="border-radius: 4px !important;">

        <v-subheader class="mt-4 px-0">
            <strong>{{ title }}</strong>
        </v-subheader>

        <!-- <DefaultInfoTooltip
            v-if="tooltip"
            class="ml-1"
            :text="tooltip"
            right
            color="white"
        /> -->

        <v-divider class="mt-0"/>

        <slot name="content"/>
        
    </v-card>
</template>
<script>
export default {
    props: {
        title: {
            type: String,
            default: 'Título de la sección'
        },
        tooltip: String
    }
}
</script>
