<template>
    <v-progress-linear
        :value="localValue"
        :height="height"
        class="custom-static-progress-linear rounded-pill"
        :color="color"
        :background-color="backgroundColor"
    >
        <div class="d-flex justify-content-center" :style="{ 'width': text, 'min-width': '30px' }">
            <strong class="fstyle">{{ localValue }}</strong>
        </div>
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
        },
        backgroundColor: {
            default: 'primary',
            required: false
        },
        height: {
            default: 25,
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
<style lang="scss">
.fstyle {
    font-size: 10px;
    font-family: "Nunito", sans-serif;
    font-weight: 400;
    line-height: 1;
}
</style>
