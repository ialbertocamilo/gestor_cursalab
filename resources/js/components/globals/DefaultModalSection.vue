<template>
    <v-card flat tile elevation="0" style="border-radius: 4px !important;">
        <v-card-title class="default-section-title d-flex align-items-center align-center" >
            <p class="default-section-title-p" v-text="title"/>
            <DefaultInfoTooltip
                v-if="tooltip"
                class="ml-1"
                :text="tooltip"
                :right="right"
                :bottom="!right"
                color="white"
                small
            />
        </v-card-title>
        <v-card-text class="default-section-content py-5 px-5">
            <slot name="content"/>
        </v-card-text>
    </v-card>
</template>
<script>
export default {
    props: {
        title: {
            type: String,
            default: 'Título de la sección'
        },
        tooltip: String,
        right: {
            type: Boolean,
            default: true
        },
    }
}
</script>
