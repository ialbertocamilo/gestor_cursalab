<template>
    <DefaultAlertDialog :options="options"
                         @onCancel="onCancel"
                         @onConfirm="onConfirm"
                         width="35vw"
    >
        <template v-slot:content>
            
            <DefaultErrors :errors="errors" />

            <strong>{{ options.contentText || contentText }}</strong>
            <br>
            <br>
            El registro se eliminará de la base de datos y no podrá recuperarse.
        </template>
    </DefaultAlertDialog>
</template>

<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        contentText: {
            type: String,
            required: false,
            default: '¿Desea eliminar este registro?',
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
        onConfirm() {

            let vue = this

            vue.errors = []

            this.showLoader()

            let url = `${vue.options.base_endpoint}/${vue.resource.id}/destroy`

            vue.$http.post(url, {'_method': 'DELETE'})
                .then(({data}) => {
                    vue.$emit('onConfirm')
                    vue.showAlert(data.data.msg, data.type)
                    this.hideLoader()
                }).catch((error) => {

                    if (error && error.errors)
                        vue.errors = error.errors

                    if (error && error.error)
                        vue.errors.push({error: error.error})

                    if (error.response)
                    {
                        vue.$emit('onCancel')
                        let data = error.response.data
                        vue.showAlert(data.msg, data.type)
                        this.hideLoader()
                    }
                })
        },
        loadData(resource) {
            let vue = this

            vue.errors = []
            vue.resource = resource
        },
        loadSelects() {

        },
        resetValidation() {
        },
    }
}
</script>
