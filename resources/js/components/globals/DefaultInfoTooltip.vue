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
        <span v-html="text"/>
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
            required: true
        },
        color: {
            default: 'primary'
        },
        small: {
            type: Boolean,
            default: false
        },top: {
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
    }
}
</script>
