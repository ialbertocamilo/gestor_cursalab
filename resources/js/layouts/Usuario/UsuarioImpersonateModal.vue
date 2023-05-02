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
                                <li><small>Se permitirá el acceso única y exclusivamente a la sesión del usuario con el fin de supervisar el correcto funcionamiento de éste.</small></li>
                                <li><small>Toda acción está siendo registrada para los reportes correspondientes.</small></li>
                                <li><small>Finalice la sesión al terminar su uso.</small></li>
                                <!-- <li><small>Recuerde que si el usuario cuenta con un correo, el proceso de restauración de contraseña puede realizarse desde la web/aplicación por parte del mismo usuario.</small></li> -->
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
            
            vue.resource = resource
        },
        loadSelects() {

        }
    }
}
</script>
