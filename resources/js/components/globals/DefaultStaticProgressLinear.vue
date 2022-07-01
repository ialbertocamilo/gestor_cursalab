<template>
    <v-progress-linear
        :value="localValue"
        height="25"
        class="custom-static-progress-linear rounded-pill"
        :color="color"
    >
        <strong>{{ localValue }}</strong>
    </v-progress-linear>
</template>

<script>
export default {
    props: {
        text: {
            required: true
        },
        color: {
            default: 'primary',
            required: false
        }
    },
    data() {
        return {
            localValue: null
        }
    },
    created() {
        if (this.text) {
            this.localValue = this.text // set initial value
        }
    },
    watch: {
        text(val) {
            this.localValue = val // watch change from parent component
        }
    },
}
</script>
