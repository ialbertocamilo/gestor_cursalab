<template>
    <v-tooltip
        :bottom="bottom"
        :top="top"
        :right="right"
        :left="left"
        attach
    >
        <template v-slot:activator="{ on, attrs }">
            <v-icon
                :small="small"
                :color="color"
                v-bind="attrs"
                v-on="on"
                v-text="icon"
            />
        </template>

        <span  style="" v-if="text" v-html="text" />

        <slot name="content"/>

    </v-tooltip>
</template>
<script>
export default {
    props: {
        icon: {
            type: String,
            default: 'mdi-information'
        },
        text: {
            type: String,
            required: false
        },
        color: {
            default: 'primary'
        },
        small: {
            type: Boolean,
            default: false
        },
        top: {
            type: Boolean,
            default: false
        },
        bottom: {
            type: Boolean,
            default: false
        },
        left: {
            type: Boolean,
            default: false
        },
        right: {
            type: Boolean,
            default: false
        },
        size: {
            type: String,
            default: ''
        },
    }
}
</script>
<style scoped>
    .v-tooltip__content.menuable__content__active {
        border-width: 1px !important;
        /* Fix position to show tooltip from right position */
        right: 0 !important;
        left: unset !important;
    }
</style>
