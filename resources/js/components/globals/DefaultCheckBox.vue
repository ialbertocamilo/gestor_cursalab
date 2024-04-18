<template>
    <v-checkbox
        class="default-checkbox"
        v-model="localBoolean"
        :label="localBoolean ? labelTrue : labelFalse"
        @change="updateValue"
        :disabled="disabled"
    ></v-checkbox>
</template>

<script>
export default {
    props: {
        value: {
            type: Object | Number,
            required: true
        },
        labelTrue: {
            type: String,
            default: 'Activo'
        },
        labelFalse: {
            type: String,
            default: 'Inactivo'
        },
        disabled:{
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            localBoolean: false,
        }
    },
    created() {
        if (this.value) {
            this.localBoolean = this.value // set initial value
        }
    },
    watch: {
        value(val) {
            this.localBoolean = val // watch change from parent component
        }
    },
    methods: {
        updateValue(value) {
            let vue = this
            vue.$emit('input', value || null)
            vue.$emit('onChange')
        }
    }
}
</script>
