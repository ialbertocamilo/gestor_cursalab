<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <div v-if="options.action === 'validate_escuela_eliminar'">

            </div>
        </template>
    </DefaultDialog>
</template>

<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: {
            type: String,
            required: false
        },
        resource: {
            required: true
        },
    },
    data() {
        return {
            validateData: null,
            checkbox: false
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.checkbox = false
            // vue.validateData = null
        },
        resetSelects() {
            let vue = this
            vue.checkbox = false
        },
        confirmModal() {
            let vue = this
            let data = {
                checkbox: vue.checkbox
            }
            // console.log('CONFIRM DATA MODAL VALIDACION :: ',data)
            vue.$emit('onConfirm', data)
        },
        async loadData(validateData) {
            let vue = this
            // console.log('VALIDATE DATA :: ', validateData)
            vue.validateData = validateData

            return 0;
        },
        loadSelects() {
            let vue = this

        },
    },
}
</script>
