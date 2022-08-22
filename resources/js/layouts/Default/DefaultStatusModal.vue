<template>
    <DefaultAlertDialog
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
        width="30vw"
    >
        <template v-slot:content>
            <b>{{ options.contentText || contentText }}</b>
            <br>
            <br>
            El registro pasará a estar
            "{{ !resource.active ? 'Activo' : 'Inactivo' }}"
            {{ !resource.active ? '' : 'y los usuarios asignados no podrán verlo' }}
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
            default: '¿Desea cambiar de estado a este registro?',
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
        onConfirm(withValidations = true) {
            let vue = this

            vue.showLoader()

            let url = `${vue.options.base_endpoint}/${vue.resource.id}/status`

            if (!withValidations) {
                url += '?withValidations=1'
            } else {
                url += '?withValidations=0'
            }

            vue.$http.post(url, {'_method': 'PUT'})
                .then(({data}) => {
                    let emitData = {
                        data: data,
                        confirmMethod: 'updateStatus'
                    }
                    vue.$emit('onConfirm', emitData)
                    vue.showAlert(data.data.msg, data.type)
                    // this.hideLoader()
                    vue.hideLoader()
                })
                // .catch((error) => {
                // if (error.response) {
                //     vue.$emit('onCancel')
                //     let data = error.response.data
                //     console.log('CATCH :: ' ,data)
                //     vue.showAlert(data.msg, data.type)
                //     // this.hideLoader()
                //     vue.hideLoader()
                // }
                .catch(({data}) => {
                    vue.hideLoader()
                    vue.$emit('onError', data)
                })
        },
        loadData(resource) {
            let vue = this
            vue.resource = resource
        }
        ,
        loadSelects() {

        }
        ,
        resetValidation() {
        }
        ,
    }
}
</script>
