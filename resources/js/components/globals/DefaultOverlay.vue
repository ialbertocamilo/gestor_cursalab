<template>
    <v-overlay
        :absolute="absolute"
        :value="value"
        :opacity="opacity"
        :style="{'border-radius': borderRadius}"
    >
        <slot name="content">
            <div class="d-flex justify-content-center">
                <v-progress-circular indeterminate size="64"></v-progress-circular>
            </div>
        </slot>
    </v-overlay>
</template>
<script>
export default {
    props: {
        value: {
            type: Object | Array,
            required: true
        },
        absolute: {
            type: Boolean,
            default: false
        },
        opacity: {
            default: 0.35
        },
        borderRadius: {
            default: "5px"
        }
    }
}
</script>
