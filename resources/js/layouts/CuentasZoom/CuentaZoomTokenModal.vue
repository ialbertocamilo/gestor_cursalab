<template>
    <DefaultAlertDialog :options="options"
                         @onCancel="onCancel"
                         @onConfirm="onConfirm"
                         width="30vw"
    >
        <template v-slot:content>
            <b>{{ options.contentText }}</b>
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
            resource: {active: null}
        }
    },
    methods: {
        onCancel() {
            let vue = this
            vue.$emit('onCancel')
        },
        onConfirm() {

            this.showLoader()

            let vue = this
            let url = `${vue.options.base_endpoint}/${vue.resource.id}/token`

            vue.$http.post(url, {'_method': 'PUT'})
                .then(({data}) => {
                    vue.$emit('onConfirm')
                    vue.showAlert(data.data.msg, data.type)
                    this.hideLoader()
                }).catch((error) => {
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
            vue.resource = resource
        },
        loadSelects() {

        },
        resetValidation() {
        },
    }
}
</script>
