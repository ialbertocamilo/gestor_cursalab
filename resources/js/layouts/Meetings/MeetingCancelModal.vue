<template>
    <DefaultAlertDialog :options="options"
                        @onCancel="onCancel"
                        @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <DefaultErrors :errors="errors"/>

            <p>La reunión se cancelará para todos los asistentes.</p>

            <p>
                <strong>¿Está seguro de cancelar esta reunión?</strong>
            </p>

        </template>
    </DefaultAlertDialog>
</template>

<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            errors: [],
            resource: null
        }
    },
    methods: {
        onCancel() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {

        },
        onConfirm() {
            let vue = this
            vue.showLoader()
            vue.errors = []

            let url = `${vue.options.base_endpoint}/${vue.resource.id}/cancel`
            vue.$http.put(url)
                .then(({data}) => {
                    vue.showAlert(data.data.msg)
                    vue.$emit('onConfirm')
                    vue.hideLoader()
                }).catch((error) => {
                vue.hideLoader()

                if (error && error.errors)
                    vue.errors = error.errors
            })
        },
        loadData(resource) {
            let vue = this

            vue.errors = []
            vue.resource = resource
        },
        loadSelects() {

        }
    }
}
</script>
