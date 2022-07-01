<template>
    <DefaultAlertDialog :options="options"
                        @onCancel="onCancel"
                        @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <DefaultErrors :errors="errors"/>

            <p>La reunión se dará por iniciada y será redireccionado a la web o aplicación de la reunión para su
                inicio.</p>

            <p>
                <strong>¿Está seguro de iniciar esta reunión?</strong>
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
            vue.showAlert()
            vue.errors = []

            let url = `${vue.options.base_endpoint}/${vue.resource.id}/start`
            vue.$http.put(url)
                .then(({data}) => {
                    vue.showAlert(data.data.msg)

                    // console.log('startMeeting data')
                    // console.log(data)

                    let link = data.data.url

                    window.open(link, '_blank');

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
