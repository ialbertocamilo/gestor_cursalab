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
<!--            {{ !resource.active ? '' : 'y los usuarios asignados no podrán verlo' }}-->
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
        },
        customCallback: {
            type: Boolean,
            default: false
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

            if (vue.customCallback){
                vue.$emit('onConfirm')
                return;
            }

            vue.showLoader()

            let url = `${vue.options.base_endpoint}/${vue.resource.id}/status`

            url += !withValidations ? '?withValidations=1' : '?withValidations=0';

            vue.$http.post(url, {'_method': 'PUT'})
                .then(({data}) => {
                    let emitData = {
                        data: data,
                        confirmMethod: 'updateStatus'
                    }
                    vue.$emit('onConfirm', emitData)
                    vue.showAlert(data.data.msg, data.type)
                    vue.hideLoader()
                })
                .catch(({data}) => {
                    vue.hideLoader()
                    vue.$emit('onError', data)
                })
        },
        loadData(resource) {
            let vue = this
            vue.resource = resource
            console.log(vue.resource)
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
