<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
                   no-padding-card-text
    >
        <template v-slot:content>

            <v-row justify="space-around" class="mt-8 mx-1">
                <v-col cols="12">

                    <DefaultErrors :errors="errors"/>

                    <template >

                        <v-alert
                          border="top"
                          colored-border
                          elevation="2"
                          color="primary"
                          class="pb-0"
                        >
                            <small><strong>IMPORTANTE</strong></small>

                            <ul class="mt-3">
                                <li><small>Se restaurará la contraseña del usuario a su forma original (documento).</small></li>
                                <li><small>El usuario deberá actualizar su contraseña al iniciar sesión.</small></li>
                                <li><small>Recuerde que si el usuario cuenta con un correo, el proceso de restauración de contraseña puede realizarse desde la web/aplicación por parte del mismo usuario.</small></li>
                            </ul>
                        </v-alert>

                    </template>
                </v-col>
            </v-row>
        </template>
    </DefaultDialog>

</template>

<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            errors: [],
            resource: {},
        }
    },
    methods: {
        closeModal() {
            let vue = this;
            vue.$emit('onCancel')
        },
        confirmModal() {

            // console.log(action)

            let vue = this

            this.showLoader()

            console.log('vue.resource')
            console.log(vue.resource)


            let base = `${vue.options.base_endpoint}`
            let url =`${base}/${vue.resource.id}/reset-password`;


            // let formData = vue.getMultipartFormData('POST', vue.resource, fields);

            vue.$http.post(url, null)
            // vue.$http.post(url, {'modulos_carreras' : vue.resource.modulos_carreras, '_method' : method})
                .then(({data}) => {
                    vue.showAlert(data.data.msg)

                        // if (action == 'reset_total') {
                        //     this.hideLoader()
                        // } else {
                        //     this.loadData(vue.resource)
                            // vue.closeModal()
                        this.hideLoader()
                        // }
                        // if (action === 'reset_x_tema')
                        //     vue.queryStatus("usuarios", "reinicia_tema");

                        vue.$emit('onConfirm')
                    })
                    .catch((error) => {
                        vue.hideLoader()

                        if (error && error.errors)
                            vue.errors = error.errors
                    })

        },
        resetValidation() {
            let vue = this
            // vue.resetFormValidation('cursosForm')
            // vue.resetFormValidation('totalForm')
            // vue.resetFormValidation('temasForm')
        }
        ,
        loadData(resource) {

            let vue = this
            
            console.log('resource')
            console.log(resource)

            vue.resource = resource



            // let url = `${vue.options.base_endpoint}/${resource.id}/reset`

            // vue.$http.get(url).then(({data}) => {
            //     vue.selects.temas = data.data.topics
            //     vue.selects.cursos = data.data.courses
            //     vue.resource = data.data.user
            //     vue.resetValidation()
            // });
        },
        loadSelects() {

        }
    }
}
</script>
<!-- 
<style>
    .simple-table .v-data-table__wrapper{
        min-height: 150px; max-height: 450px;
        overflow-x: auto;
        overflow-y: auto;
    }
</style>
 -->