<template>
    <div>
        <DefaultDialog
            :options="options"
            :width="width"
            @onCancel="closeModal"
            @onConfirm="confirmModal"
            content-class="br-dialog"
        >
            <template v-slot:content>
                <v-form ref="projectForm">
                    <p>La subida de datos seran asignados a todos los usuarios segmentados en el proceso de inducción.</p>
                    <v-row justify="space-around">
                        <v-col cols="12">
                            <v-text-field
                                outlined
                                dense
                                auto-grow
                                hide-details="auto"
                                v-model="resource.title"
                                :class="{'border-error': resource.hasErrors}"
                                label="Título"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="12">
                            <DefaultTextArea label="Nombre de la subida de datos" v-model="resource.upload_name" />
                        </v-col>
                    </v-row>
                    <div class="d-flex justify-space-between align-center my-4">
                        <p class="p-0 m-0 text-bold">Lista de Recursos</p>
                        <div>
                            <v-btn class="mt-1 ml-1" color="primary" elevation="0" outlined
                                :disabled="resources.length >= constraints.max_quantity_upload_files"
                                @click="openSelectPreviewMultimediaModal">
                                Multimedia
                            </v-btn>
                            <v-btn class="mt-1 ml-1" color="primary" elevation="0" outlined
                                :disabled="resources.length >= constraints.max_quantity_upload_files" @click="openFileInput">
                                Escritorio
                            </v-btn>
                            <input ref="uploader_input_file" class="d-none" type="file" @change="subirArchivo">
                        </div>
                    </div>
                    <span>Añade una lista de recursos para que los usuarios puedan descargar. Peso máximo
                        {{ constraints.max_size_upload_files }} MB</span>
                    <v-row class="mt-4">
                        <ul style="width: 100%;">
                            <div class="d-flex justify-space-between" v-for="(resource, index) in resources" :key="index">
                                <li v-text="getTitle(resource)"></li>
                                <v-icon @click="deleteResource(index)">mdi mdi-close</v-icon>
                            </div>
                        </ul>
                    </v-row>

                    <DefaultModalSectionExpand
                        title="Avanzado"
                        :expand="sections.showSectionAdvanced"
                        class="my-4 bg_card_none"
                    >
                        <template slot="content">

                            <v-row>
                                <v-col cols="12" class="p-0">
                                    <div class="mt-2 mb-1">
                                        <span class="text_default">Tipo de confirmación de recepción de documentos</span>
                                    </div>
                                    <div>
                                        <div>
                                            <v-radio-group v-model="resource.reception" row>
                                                <v-radio label="Sin confirmar" value="auto"></v-radio>
                                                <v-radio label="Por firma" value="manual"></v-radio>
                                            </v-radio-group>
                                        </div>
                                    </div>
                                    <div>
                                        <v-row>
                                            <v-col cols="6">
                                                <DefaultAutocomplete
                                                    dense
                                                    label="Indicar actividad requisito"
                                                    v-model="resource.requirement"
                                                    :items="selects.requirement_list"
                                                    item-text="name"
                                                    item-value="code"
                                                    :openUp="true"
                                                />
                                            </v-col>
                                        </v-row>
                                    </div>
                                </v-col>
                            </v-row>

                        </template>
                    </DefaultModalSectionExpand>

                </v-form>
                <SelectMultimedia :ref="modalPreviewMultimedia.ref" :options="modalPreviewMultimedia" :custom-filter="[]"
                    width="85vw" @onClose="closeSelectPreviewMultimediaModal" @onConfirm="onSelectMediaPreview" />
            </template>
        </DefaultDialog>
        <!-- === MODAL ALERT STORAGE === -->
        <GeneralStorageModal
            :ref="modalGeneralStorageOptions.ref"
            :options="modalGeneralStorageOptions"
            width="45vw"
            @onCancel="closeFormModal(modalGeneralStorageOptions)"
            @onConfirm="closeFormModal(modalGeneralStorageOptions),
                        openFormModal(modalGeneralStorageEmailSendOptions, null, 'status', 'Solicitud enviada')"
        />
        <!-- MODAL ALMACENAMIENTO -->

        <!-- MODAL EMAIL ENVIADO -->
        <GeneralStorageEmailSendModal
            :ref="modalGeneralStorageEmailSendOptions.ref"
            :options="modalGeneralStorageEmailSendOptions"
            width="35vw"
            @onCancel="closeFormModal(modalGeneralStorageEmailSendOptions)"
            @onConfirm="closeFormModal(modalGeneralStorageEmailSendOptions)"
        />
        <!-- MODAL EMAIL ENVIADO -->

        <!-- === MODAL ALERT STORAGE === -->
        <DefaultStorageAlertModal
            :ref="modalAlertStorageOptions.ref"
            :options="modalAlertStorageOptions"
            width="25vw"
            @onCancel="closeFormModal(modalAlertStorageOptions)"
            @onConfirm="openFormModal(modalGeneralStorageOptions, null, 'status', 'Aumentar mi plan'),
                        closeFormModal(modalAlertStorageOptions)"
        />
    </div>
</template>

<script>
import SelectMultimedia from '../../forms/SelectMultimedia.vue'

import DefaultStorageAlertModal from '../../../layouts/Default/DefaultStorageAlertModal.vue';
import GeneralStorageEmailSendModal from '../../../layouts/General/GeneralStorageEmailSendModal.vue';
import GeneralStorageModal from '../../../layouts/General/GeneralStorageModal.vue';

export default {
    components: { SelectMultimedia,DefaultStorageAlertModal,GeneralStorageEmailSendModal,GeneralStorageModal },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            sections: {
                showSectionAdvanced: {status: false},
            },
            errors: [],
            resourceDefault: {
                id: null,
                model_id: null,
                model_type: null,
                indications: '',
                course_name: '',
                count_file: 0
            },
            resource: {
                id: null,
                model_id: null,
                model_type: null,
                indications: '',
                course_name: '',
                count_file: 0
            },
            selects: {
                courses: [],
                requirement_list: [{'code':'none', 'name': 'Sin requisito'}]
            },
            rules: {
                required: this.getRules(['required']),
            },
            modalPreviewMultimedia: {
                ref: 'modalSelectPreviewMultimedia',
                open: false,
                title: 'Buscar multimedia',
                confirmLabel: 'Seleccionar',
                cancelLabel: 'Cerrar'
            },
            resources: [],
            search: null,
            isLoading: false,
            debounceTimer: null,
            file: null,
            edit_resource: false,
            constraints: {
                max_quantity_upload_files: 6,
                max_size_upload_files: 25,
            },
            fileSelected: null,
            modalAlertStorageOptions: {
                ref: 'AlertStorageModal',
                open: false,
                showCloseIcon: true,
                base_endpoint: '/general',
                confirmLabel:'Solicitar',
                persistent: true,
            },
            modalGeneralStorageOptions: {
                ref: 'GeneralStorageModal',
                open: false,
                showCloseIcon: true,
                base_endpoint: '/general',
                confirmLabel:'Enviar',
                persistent: true
            },
            modalGeneralStorageEmailSendOptions: {
                ref: 'GeneralStorageEmailSendModal',
                open: false,
                showCloseIcon: true,
                hideCancelBtn: true,
                confirmLabel:'Entendido',
                persistent: false
            },
        };
    },
    watch: {
        search(val) {
            // Items have already been loaded
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }
            this.debounceTimer = setTimeout(() => {
                this.searchCourses(val);
            }, 800);
        },
    },
    methods: {
        closeModal() {
            let vue = this
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        }
        ,
        resetValidation() {
            let vue = this
            vue.$refs.projectForm.resetValidation()
            vue.$refs.projectForm.reset()
        }
        ,
        async confirmModal() {

            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('projectForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = edit
                ? `${base}/${vue.resource.id}/update`
                : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';
            console.log(url);
            if (validateForm) {
                const formData = vue.createFormData();
                await vue.$http
                    .post(url, formData)
                    .then(({ data }) => {

                        if(!data.data.still_has_storage){
                            vue.showAlert(data.data.msg,'warning')
                            vue.openFormModal(vue.modalAlertStorageOptions, null, null, 'Alerta de almacenamiento');
                            return '';
                        }
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                    }).catch((error) => {
                        if (error && error.errors) {
                            const errors = error.errors ? error.errors : error;
                            vue.show_http_errors(errors);
                        }
                    })
            }

            this.hideLoader()
        }
        ,
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {

            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            if(vue.options.create_from_course_list){
                vue.$nextTick(() => {
                    vue.selects.courses.push(vue.options.course);
                    vue.resource.model_id = vue.options.model_id;
                    vue.resource.model_type = 'App\\Models\\Stage';
                    vue.search = vue.options.course.name;
                })
                console.log(1);
            }else{
                if (resource) {
                    let url = `${base}/edit/${resource.id}`
                    await vue.$http.get(url).then(({ data }) => {
                        vue.resource.id = data.data.id;
                        vue.resource.model_id = data.data.model_id;
                        vue.resource.model_type = data.data.model_type;
                        vue.resource.indications = data.data.indications;
                        vue.resource.course_name = data.data.course_name;
                        vue.resource.count_file = data.data.count_file;
                        vue.resources = data.data.resources;
                        vue.selects.courses.push(data.data.course);
                    })
                    console.log(2);
                } else {
                    console.log(3);
                    vue.resource.id = null;
                    vue.resource.model_id = vue.options.model_id;
                    vue.resource.model_type = 'App\\Models\\Stage';
                    vue.resource.indications = null;
                    vue.resource.course_name = null;
                    vue.resource.count_file = null;
                    vue.resources = [];
                    vue.selects.courses = [];
                }
            }

            return 0;
        },
        async loadSelects()
        {
            let vue = this;
            let url = `${vue.options.base_endpoint}/form-selects`

            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.requirement_list = data.data.requirements
                })
        },
        async searchCourses(value) {
            let vue = this;
            // if (this.selects.courses.length > 0) return
            if (vue.options.action === 'edit' || vue.options.create_from_course_list) return
            // Items have already been requested
            if (vue.isLoading) return
            vue.isLoading = true
            let url = `${vue.options.base_endpoint}/get-selects?type=search-course&q=${value}`
            await vue.$http.get(url)
                .then(({ data }) => {
                    console.log(data);
                    vue.selects.courses = data.data;
                }).catch((error) => {
                    if (error && error.errors)
                        vue.errors = error.errors
                }).finally(() => (vue.isLoading = false))
        },
        openFileInput() {
            this.$refs.uploader_input_file.click();
        },
        deleteResource(index) {
            this.resources.splice(index, 1);
        },
        createFormData() {
            let vue = this;
            let formData = new FormData();
            const resources_file = vue.resources.filter(r => r.type_resource == 'file');
            resources_file.map(rf => {
                formData.append("files[]", rf);
            })
            const resources_media = vue.resources.filter(r => r.type_resource == 'media');
            if (resources_media.length > 0) {
                resources_media.forEach((rm, index) => {
                    const keys = Object.keys(rm);
                    keys.forEach(k => {
                        formData.append(`multimedias[${index}][${k}]`, resources_media[index][k]);
                    })
                })
            }

            vue.resource.model_id = vue.options.model_id;
            vue.resource.model_type = 'App\\Models\\Stage';

            const keys_project = Object.keys(vue.resource);
            keys_project.forEach(k => {
                formData.append(`project[${k}]`, vue.resource[k]);
            });
            return formData;
        },
        getTitle(resource) {
            if (resource.type_resource == 'file') {
                return `${resource.title} (${resource.filesize} MB)`;
            }
            return (resource.size) ? `${resource.title} (${resource.size} MB)` : resource.title;
        },
        openSelectPreviewMultimediaModal() {
            let vue = this
            vue.modalPreviewMultimedia.open = true
            if (vue.$refs[vue.modalPreviewMultimedia.ref]) {
                vue.$refs[vue.modalPreviewMultimedia.ref].getData()
            }
        },
        onSelectMediaPreview(media) {
            let vue = this;
            console.log(media);
            if (!media) {
                vue.showAlert('Seleccione un multimedia.', 'warning')
                return true;
            }
            if (this.resources.find(r => r.id && r.id == media.id)) {
                vue.showAlert('Este recurso ya ha sido seleccionado.', 'warning')
                return true;
            }
            if (media.formattedSize.includes('MB')) {
                media.size = media.formattedSize.replace(' MB', '');
            }
            if (media.size > vue.constraints.max_size_upload_files) {
                vue.showAlert(`El limite máximo por archivo es de ${vue.constraints.max_size_upload_files} MB`, 'warning')
                return true;
            }
            vue.closeSelectPreviewMultimediaModal();
            media.type_resource = 'media';
            this.resources.push(media);
        },
        closeSelectPreviewMultimediaModal() {
            let vue = this
            vue.modalPreviewMultimedia.open = false
        },
        subirArchivo(e) {
            const vue = this;
            let file = e.target.files[0];
            file.filesize = vue.roundTwoDecimal(file.size / 1024 ** 2);
            if (file.filesize > vue.constraints.max_size_upload_files) {
                vue.showAlert(`El limite máximo por archivo es de ${vue.constraints.max_size_upload_files} MB`, 'warning');
                return true;
            }
            file.type_resource = 'file';
            file.title = file.name;
            vue.resources.push(file);
        }
    }
}
</script>
