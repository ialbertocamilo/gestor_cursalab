<template>
    <DefaultAlertDialog :options="options"
                         @onCancel="onCancel"
                         @onConfirm="onConfirm"
                         :width="width"
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
        },
        width: {
            default: '40vw'
        },
    },
    data() {
        return {
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
            let url = `${vue.options.base_endpoint}/${vue.resource.id}`
            vue.$http.patch(url)
                .then(({data}) => {
                    vue.showAlert(data.data.msg)
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
