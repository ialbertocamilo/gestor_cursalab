<template>
    <DefaultAlertDialog :options="options"
                         @onCancel="onCancel"
                         @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <DefaultErrors :errors="errors"/>

            <p>La reunión finalizará para todos los asistentes y no podrá reanudarse.</p>

            <p>
                <strong>¿Está seguro de finalizar esta reunión?</strong>
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
        resetValidation(){

        },
        onConfirm() {
            let vue = this

            vue.errors = []

            let url = `${vue.options.base_endpoint}/${vue.resource.id}/finish`
            vue.$http.put(url)
                .then(({data}) => {
                    vue.showAlert(data.data.msg)
                    vue.$emit('onConfirm')
                }).catch((error) => {
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
