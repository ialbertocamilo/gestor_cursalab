<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="anuncioForm">

                <DefaultErrors :errors="errors" />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelect clearable
                                       :items="selects.modules"
                                       v-model="resource.modules"
                                       label="Módulos"
                                       multiple
                                       return-object
                                       :rules="rules.modules"
                                       :count-show-values="3"
                                       :show-select-all="false"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.nombre"
                                      label="Nombre"
                                      :rules="rules.nombre"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex-- justify-content-center">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputImagen"
                            v-model="resource.imagen"
                            label="Imagen"
                            :file-types="['image']"
                            @onSelect="setFile($event, resource, 'imagen')"/>
                    </v-col>

                    <v-col cols="6" class="d-flex-- justify-content-center">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputPDF"
                            v-model="resource.archivo"
                            label="PDF"
                            :file-types="['pdf']"
                            @onSelect="setFile($event, resource, 'archivo')"/>
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect clearable
                                       :items="selects.destinos"
                                       v-model="resource.destino"
                                       label="Destino"
                                       return-object
                                       :rules="rules.destinos"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.link"
                                      label="Link"
                                      :rules="rules.link"
                        />
                    </v-col>
                </v-row>
                <v-row>
                     <v-col cols="6">
                        <DefaultInputDate
                            clearable
                            :referenceComponent="'modalDateFilter1'"
                            :options="modalDateFilter1"
                            v-model="resource.publication_starts_at"
                            label="Fecha inicio"
                        />
                    </v-col>

                    <v-col cols="6">
                        <DefaultInputDate
                            clearable
                            :referenceComponent="'modalDateFilter2'"
                            :options="modalDateFilter2"
                            v-model="resource.publication_ends_at"
                            label="Fecha fin"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultRichText
                                      v-model="resource.content"
                                      label="Contenido"
                        />
                    </v-col>

                </v-row>

                <v-row align="start" align-content="center">
                    <v-col cols="4" class="--d-flex --justify-content-start">
                        <DefaultToggle v-model="resource.estado" />
                    </v-col>
                </v-row>
            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

import DefaultRichText from "../../components/globals/DefaultRichText";

const fields = ['nombre', 'estado', 'destino', 'link', 'publication_starts_at', 'publication_ends_at', 'modules'];
const file_fields = ['imagen', 'archivo'];

export default {
    components: {DefaultRichText},
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
            resourceDefault: {
                id: null,
                config_id: null,
                nombre: '',
                imagen: null,
                file_imagen: null,
                archivo: null,
                file_archivo: null,
                destino: null,
                link: null,
                modules: [],
                destinos: null,
                estado: true,
                publication_starts_at: null,
                publication_ends_at: null,
            },
            resource: {},
            selects: {
                modules: [],
                destinos: [],
            },

            rules: {
                modules: this.getRules(['required']),
                nombre: this.getRules(['required', 'max:100']),
                imagen: this.getRules(['required']),
                // dni: this.getRules(['required', 'number'])
            },

            modalDateFilter1: {
                open: false,
            },

            modalDateFilter2: {
                open: false,
            },
        }
    },
    methods: {
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.$refs.anuncioForm.resetValidation()
        },
        confirmModal() {
            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('anuncioForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            // if (validateForm && validateSelectedModules) {
            if (validateForm ) {

                let formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);

                vue.$http.post(url, formData)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                    }).catch((error) => {
                        if (error && error.errors)
                            vue.errors = error.errors
                    })
            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.modules = []
            vue.selects.destinos = []
            vue.removeFileFromDropzone(vue.resource.imagen, 'inputImagen')
            vue.removeFileFromDropzone(vue.resource.archivo, 'inputArchivo')
        },
        async loadData(resource) {
            let vue = this

            vue.errors = []
            
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                vue.selects.modules = data.data.modules
                vue.selects.destinos = data.data.destinos

                if (resource) {
                    vue.resource = data.data.anuncio
                }
            })

            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
