<template>
    <DefaultAlertDialog
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
        :width="(options.width)? options.width :'25vw'"
    >
        <template v-slot:content>
            <div class="bx_content" v-if="options.content_modal">
                <div class="bx_header">
                    <div class="img"><img src="/img/modal_alert.png"></div>
                    <div class="cont">
                        <span v-if="resource.active">{{ options.content_modal.inactive.title }}</span>
                        <span v-if="!resource.active">{{ options.content_modal.active.title }}</span>
                    </div>
                </div>
                <div class="bx_details">
                    <ul v-if="resource.active">
                        <li v-for="(item, index) in options.content_modal.inactive.details" :key="index">
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                    <ul v-if="!resource.active">
                        <li v-for="(item, index) in options.content_modal.active.details" :key="index">
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div v-else>
                <b>{{ options.contentText || contentText }}</b>
                <br>
                <br>
                El registro pasará a estar
                "{{ !resource.active ? 'Activo' : 'Inactivo' }}"
                <!--            {{ !resource.active ? '' : 'y los usuarios asignados no podrán verlo' }}-->
            </div>
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

            if (vue.customCallback) {
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

                    if(vue.options.ref != null && vue.options.ref != undefined && vue.options.ref != ""){
                        if(vue.options.ref =="UsuarioStatusModal"){
                            vue.queryStatus("usuarios", "actualiza_estado");
                        }
                    }
                })
                .catch((error) => {
                    if (error.http_code === 422){
                        vue.showAlert(error.message, 'warning')
                        vue.$emit('onConfirm', {})
                    }
                    vue.hideLoader()
                    vue.$emit('onError', error.data)
                })
        },
        loadData(resource) {
            let vue = this
            vue.resource = resource
            // console.log(vue.resource)
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
<style lang="scss">
.bx_header {
    display: flex;
    align-items: center;
}
.bx_header .cont span {
    color: #2A3649;
    font-size: 20px;
    font-family: "Nunito", sans-serif;
    font-weight: 700;
    margin-left: 29px;
    text-align: left;
    line-height: 25px;
}
.bx_details {
    border-top: 1px solid #D9D9D9;
    padding-top: 15px;
    margin-top: 20px;
    padding-left: 20px;
    padding-right: 20px;
}
.bx_details ul{
    margin-bottom: 0;
}
.bx_details ul li {
    text-align: left;
    font-family: "Nunito", sans-serif;
    font-size: 16px;
    font-weight: 400;
    line-height: 20px;
    color: #2A3649;
    position: relative;
    list-style: none;
    margin-bottom: 4px;
}
.bx_details ul li:before {
    content: '';
    position: absolute;
    height: 5px;
    width: 5px;
    background: black;
    left: -17px;
    top: 8px;
    border-radius: 50%;
}
</style>
