<template>
    <DefaultAlertDialog :options="options"
                         @onCancel="onCancel"
                         @onConfirm="onConfirm"
    >
        <template v-slot:content> {{ options.contentText }}</template>
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
            resource: null
        }
    },
    methods: {
        onCancel() {
            let vue = this
            vue.$emit('onCancel',vue.resource)
        },
        resetValidation(){

        },
        onConfirm() {
            let vue = this
            let url = `${vue.options.base_endpoint}/${vue.resource.user_id}`
            vue.showLoader();
            vue.$http.patch(url)
                .then(({data}) => {
                    vue.showAlert(data.data.msg)
                    vue.hideLoader();
                    vue.$emit('onConfirm')
                })
        },
        loadData(resource) {
            let vue = this
            vue.resource = resource
        },
        loadSelects() {

        }
    }
}
</script>
