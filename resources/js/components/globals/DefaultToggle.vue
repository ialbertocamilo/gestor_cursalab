<template>
    <v-switch
        class="default-toggle"
        inset
        :label="pre_label ? pre_label+' '+new_label : new_label"
        hide-details="auto"
        v-model="localSwitch"
        @change="updateValue"
        :disabled="disabled"
    ></v-switch>
</template>

<script>
export default {
    props: {
        value: {
            type: Boolean | Number,
            required: true
        },
        label: {
            type: String,
            required: false
        },
        type: {
            type: String,
            default: 'active'
        },
        noLabel: {
            type: Boolean,
            default: false
        },
        disabled:{
            type: Boolean,
            default: false
        },
        activeLabel:{
            type: String,
            default: 'Activo'
        },
        inactiveLabel:{
            type: String,
            default: 'Inactivo'
        },
        pre_label: {
            type: String,
            required: false
        },
    },
    data() {
        return {
            localSwitch: false
        }
    },
    computed: {
        new_label: function () {
            if (this.noLabel) return ''
            let value = this.label

            if (this.type === 'active') {
                value = this.value ? this.activeLabel : this.inactiveLabel
            }

            return value
        }
    },
    created() {
        if (this.value) {
            this.localSwitch = this.value // set initial value
        }
    },
    watch: {
        value(val) {
            this.localSwitch = val // watch change from parent component
        }
    },
    methods: {
        updateValue(value) {
            let vue = this
            vue.$emit('input', value)
            vue.$emit('onChange',value)
        }
    }
}
</script>
