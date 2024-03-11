<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="encuestaForm">

                <DefaultErrors :errors="errors" />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.titulo"
                            label="Título"
                            :rules="rules.titulo"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.tipos"
                            v-model="resource.anonima"
                            label="Tipo"
                            return-object
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultAutocomplete
                            clearable
                            :items="selects.secciones"
                            v-model="resource.type_id"
                            return-object
                            label="Sección"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex-- justify-content-center c-dropzone">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputImagen"
                            v-model="resource.imagen"
                            label="Imagen"
                            :file-types="['image']"
                            @onSelect="setFile($event, resource, 'imagen')"/>
                    </v-col>
                </v-row>

                <v-row align="start" align-content="center">
                    <v-col cols="4" class="d-flex justify-content-start">
                        <DefaultToggle v-model="resource.active" @onChange="modalStatusEdit"/>
                    </v-col>
                </v-row>
            </v-form>
            <DialogConfirm
                :ref="encuestaUpdateStatusModal.ref"
                v-model="encuestaUpdateStatusModal.open"
                :options="encuestaUpdateStatusModal"
                width="408px"
                title="Cambiar de estado al curso"
                subtitle="¿Está seguro de cambiar de estado al curso?"
                @onConfirm="encuestaUpdateStatusModal.open = false"
                @onCancel="closeModalStatusEdit"
            />
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['type_id', 'anonima', 'titulo', 'active', 'imagen'];
const file_fields = ['imagen'];
import DialogConfirm from "../../components/basicos/DialogConfirm";


export default {
    components: {DialogConfirm},
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
                titulo: '',
                imagen: null,
                anonima: 0,
                type_id: [],
                // secciones: [],
                active: null,
            },
            resource: {},
            selects: {
                tipos: [],
                secciones: [],
            },

            rules: {
                titulo: this.getRules(['required', 'max:100']),
            },
            encuestaUpdateStatusModal: {
                ref: 'encuestaUpdateStatusModal',
                title: 'Actualizar Encuesta',
                contentText: '¿Desea actualizar este registro?',
                open: false,
                endpoint: '',
                title_modal: 'Cambiar de estado a las <b>encuestas</b>',
                type_modal: 'status',
                status_item_modal: null,
                content_modal: {
                    inactive: {
                        title: '¡Estás a punto de desactivar una encuesta!',
                        details: [
                            'Los usuarios no la podrán visualizar en la plataforma ni cursos.',
                        ],
                    },
                    active: {
                        title: '¡Estás a punto de activar una encuesta!',
                        details: [
                            'Los usuarios la podrán visualizar en la plataforma o cursos.',
                        ]
                    }
                },
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
            // if (vue.options.action !== 'edit') {
            vue.$refs.inputImagen.removeAllFilesFromDropzone()
            // }
            vue.$refs.encuestaForm.resetValidation()
        },
        confirmModal() {

            let vue = this
            vue.errors = []
            const validateForm = vue.validateForm('encuestaForm')
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

                vue.$http.post(url, formData)
                    .then(({data}) => {
                        vue.queryStatus("encuesta", "crear_encuestas");
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()
                    }).catch((error) => {

                        if (error && error.errors)
                            vue.errors = error.errors

                        if (error.response.data.msg) {
                            vue.showAlert(error.response.data.msg, 'warning')
                        }
                    })
            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this
            // Selects independientes

            vue.selects.tipos = []
            vue.selects.secciones = []
            vue.removeFileFromDropzone(vue.resource.imagen, 'inputImagen')
        },
        async loadData(resource) {
            let vue = this
            vue.errors = []
            vue.$nextTick(() => {
                vue.resource = Object.assign(
                    {}, vue.resource, vue.resourceDefault
                )
            })

            // let url = `${vue.options.base_endpoint}/${resource ? `${resource.id}/search` : `form-selects`}`
            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.tipos = data.data.tipos
                    vue.selects.secciones = data.data.secciones

                    if (resource) {
                        vue.resource = Object.assign({}, data.data.poll)
                    }
                })
            return 0;
        },
        isValid() {

            let valid = true;
            let errors = [];
            let formData = this.getMultipartFormData(
                '', this.resource, fields, file_fields
            );

            // Validate whether imagen has been set or not,
            // from file or from gallery

            if (!formData.get('file_imagen') && !this.resource.imagen) {
                errors.push({
                    message: 'No ha seleccionado ninguna imágen'
                })
                valid = false;
            }

            if (!valid) {
                this.errors = errors;
            }

            return valid;
        }
        ,
        loadSelects() {
            let vue = this
            // if (vue.resource.modulo && vue.resource.modulo.id)
            //     vue.loadBoticas()

            // if (vue.resource.botica && vue.resource.botica.criterio)
            //     vue.loadCarreras()
        },
        closeModalStatusEdit(){
            let vue = this
            vue.encuestaUpdateStatusModal.open = false
            vue.resource.active = !vue.resource.active
        },
        modalStatusEdit(){
            let vue = this
            const edit = vue.options.action === 'edit'
            if(edit){
                vue.encuestaUpdateStatusModal.open = true
                vue.encuestaUpdateStatusModal.status_item_modal = !vue.resource.active
            }
        },
    }
}
</script>
<style lang="scss">
.c-dropzone .dropzone .dz-preview.dz-file-preview .dz-details .dz-inf,
.c-dropzone .dropzone .dz-preview.dz-image-preview .dz-details .dz-inf {
    display: none;
}
.c-dropzone .dropzone .dz-preview.dz-file-preview .dz-image,
.c-dropzone .dropzone .dz-preview.dz-image-preview .dz-image {
    margin-top: 36px;
}
</style>
