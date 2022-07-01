<template>
    <DefaultAlertDialog :options="options"
                         @onCancel="onCancel"
                         @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <p>El evento finalizará para todos los asistentes y no podrá reanudarse.</p>

            <p>
                <strong>¿Está seguro de finalizar este evento?</strong>
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
            let url = `${vue.options.base_endpoint}/${vue.resource.id}/finish`
            vue.$http.post(url)
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
