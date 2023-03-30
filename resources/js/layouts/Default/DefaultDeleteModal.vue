<template>
    <DefaultAlertDialog :options="options"
                         @onCancel="onCancel"
                         @onConfirm="onConfirm"
                        :width="(options.width)? options.width :'25vw'"
    >
        <template v-slot:content>

            <DefaultErrors :errors="errors" />

            <div class="bx_content" v-if="options.content_modal">
                <div class="bx_header">
                    <div class="img"><img src="/img/modal_alert.png"></div>
                    <div class="cont">
                        <span>{{ options.content_modal.delete.title }}</span>
                    </div>
                </div>
                <div class="bx_details">
                    <ul>
                        <li v-for="(item, index) in options.content_modal.delete.details" :key="index">
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div v-else>
                <strong>{{ options.contentText || contentText }}</strong>
                <br>
                <br>
                El registro se eliminará de la base de datos y no podrá recuperarse.
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
