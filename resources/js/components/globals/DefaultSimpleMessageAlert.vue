<template>
    <v-alert
        :outlined="outlined"
        :type="type"
        :dense="dense"
        class="mb-0"
    >
        <slot>
            <span v-html="msg"/>
        </slot>
    </v-alert>
</template>
<script>
export default {
    props: {
        msg: {
            type: String,
            required: true
        },
        outlined: {
            type: Boolean,
            default: true
        },
        dense: {
            type: Boolean,
            default: false
        },
        type: {
            type: String,
            default: "success"
        }
    }
}
</script>
