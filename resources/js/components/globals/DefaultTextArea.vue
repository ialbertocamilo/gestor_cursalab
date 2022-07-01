<template>
    <v-textarea
        outlined
        :label="label"
        :placeholder="placeholder"
        :value="localText"
        :rules="rules"
        :clearable="clearable"
        :disabled="disabled"
        @input="updateValue"
        hide-details="auto"
        :rows="rows"
    >
        <template v-slot:label v-if="showRequired">
            {{ label }}<RequiredFieldSymbol/>
        </template>
    </v-textarea>
</template>
<script>
export default {
    props: {
        label: {
            type: String,
            required: true
        },
        value: {
            required: true
        },
        placeholder: {
            required: false,
            default: ''
        },
        rules: {
            type: Object | Array
        },
        showRequired: {
            type: Boolean,
            default: false,
        },
        clearable: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        rows: {
            default: 3
        }

    },
    data() {
        return {
            localText: null
        }
    },
    created() {
        if (this.value) {
            this.localText = this.value // set initial value
        }
    },
    watch: {
        value(val) {
            this.localText = val // watch change from parent component
        }
    },
    methods: {
        updateValue(value) {
            let vue = this
            vue.$emit('input', value || null)
        },
    }
}
</script>
