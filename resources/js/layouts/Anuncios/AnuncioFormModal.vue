<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="anuncioForm">
                <DefaultErrors :errors="errors" />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.modules"
                            multiple
                            return-object
                            v-model="resource.module_ids"
                            label="Módulo"
                            :rules="rules.module_ids"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.nombre"
                            label="Nombre"
                            :rules="rules.nombre"
                            maxlength="120"
                            :max="120"
                            hint="Máximo 120 caracteres"
                            emojiable
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex-- justify-content-center">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputImagen"
                            v-model="resource.imagen"
                            label="Imagen (500x350px)"
                            :file-types="['image']"
                            @onSelect="setFile($event, resource, 'imagen')"
                        />
                    </v-col>

                    <v-col cols="6" class="d-flex-- justify-content-center">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputPDF"
                            v-model="resource.archivo"
                            label="PDF"
                            :file-types="['pdf']"
                            @onSelect="setFile($event, resource, 'archivo')"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.destinos"
                            v-model="resource.destino"
                            label="Destino"
                            return-object
                            :rules="rules.destinos"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
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
                            v-model="resource.publish_date"
                            label="Fecha de inicio"
                        />
                    </v-col>
                    <v-col cols="6">
                        <DefaultInputDate
                            clearable
                            :referenceComponent="'modalDateFilter2'"
                            :options="modalDateFilter2"
                            v-model="resource.end_date"
                            label="Fecha de finalización"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultRichText
                            clearable
                            v-model="resource.contenido"
                            label="Contenido"
                            :rules="rules.contenido"
                        />
                    </v-col>
                </v-row>

                <v-row align="start" align-content="center">
                    <v-col cols="4" class="--d-flex --justify-content-start">
                        <DefaultToggle v-model="resource.active" />
                    </v-col>
                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>
import DefaultRichText from "../../components/globals/DefaultRichText";
import moment from "moment";

const fields = [
    "nombre",
    "active",
    "destino",
    "link",
    "module_ids",
    "contenido",
    "publish_date",
    "end_date"
];

const file_fields = ["imagen", "archivo"];

export default {
    components: { DefaultRichText },
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
                nombre: "",
                imagen: null,
                file_imagen: null,
                archivo: null,
                file_archivo: null,
                destino: null,
                link: null,
                module_ids: [],
                destinos: null,
                active: true,
                publish_date: null,
                end_date: null,
                contenido: ""
            },
            resource: {},
            selects: {
                modules: [],
                destinos: []
            },

            rules: {
                module_ids: this.getRules(["required"]),
                nombre: this.getRules(["required", "max:100"]),
                imagen: this.getRules(["required"]),
                contenido: this.getRules(["required"])
                // dni: this.getRules(['required', 'number'])
            },

            modalDateFilter1: {
                open: false
            },

            modalDateFilter2: {
                open: false
            }
        };
    },
    methods: {
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        }
        ,
        resetValidation() {
            let vue = this
            // if (vue.options.action !== 'edit'){
            vue.$refs.inputPDF.removeAllFilesFromDropzone()
            vue.$refs.inputImagen.removeAllFilesFromDropzone()
            // vue.resource.contenido = ""
            // }
            vue.$refs.anuncioForm.resetValidation()
        }
        ,
        confirmModal() {

            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('anuncioForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id
                        ? `${base}/${vue.resource.id}/update`
                        : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            if (validateForm && vue.isValid()) {

                let formData = vue.getMultipartFormData(
                    method, vue.resource, fields, file_fields
                );

                vue.$http
                   .post(url, formData)
                   .then(({data}) => {

                        vue.queryStatus("anuncios", "crear_anuncio");
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')

                   }).catch((error) => {

                       if (error && error.errors)
                            vue.errors = error.errors

                        if (error.response.data.msg) {
                            vue.showAlert(error.response.data.msg, 'warning')
                        }
                   })
            }

            this.hideLoader()
        }
        ,
        resetSelects() {
            let vue = this
            vue.selects.modules = []
            vue.selects.destinos = []
        }
        ,
        async loadData(resource) {

            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource
                        ? `${base}/${resource.id}/edit`
                        : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                vue.selects.modules = data.data.modules
                vue.selects.destinos = data.data.destinos

                if (resource) {
                    vue.resource = Object.assign({}, data.data.announcement);

                    if (vue.resource.publish_date)
                        vue.resource.publish_date = vue.resource.publish_date.substring(0, 10)

                    if (vue.resource.end_date)
                        vue.resource.end_date = vue.resource.end_date.substring(0, 10)
                }
            })

            return 0;
        }
        ,
        isValid() {

            let valid = true;
            let errors = [];
            let formData = this.getMultipartFormData(
                '', this.resource, fields, file_fields
            );

            // Validation: At least one module should be selected

            if (this.resource.module_ids.length === 0) {
                errors.push({
                    message: 'No hay ningún módulo seleccionado'
                })
                valid = false;
            }

            // Validation: imagen has been set or not,
            // from file or from gallery

            if (!formData.get('file_imagen') && !this.resource.imagen) {
                errors.push({
                    message: 'No ha seleccionado ninguna imágen'
                })
                valid = false;
            }

            // Validation: When a link has been set,
            // check whether is valid or not

            if (this.resource.link) {

                if (!this.isValidHttpUrl(this.resource.link)) {
                    errors.push({
                        message: 'El link no es una URL válida'
                    })
                    valid = false;
                }
            }

            // Validation: contenido is required

            if (!this.resource.contenido) {

                errors.push({
                    message: 'El contenido es requerido'
                })
                valid = false;
            }

            if (!valid) {
                this.errors = errors;
            }

            return valid;
        }
        ,
        isValidHttpUrl(string) {
            let url;

            try {
                url = new URL(string);
            } catch (_) {
                return false;
            }

            return url.protocol === "http:" || url.protocol === "https:";
        }
        ,
        loadSelects() {
            let vue = this
        },
    }
}
</script>
