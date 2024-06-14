<template>
    <section class="section-list">
        <v-card flat elevation="0">
            <v-card-title>
                Temas: {{ topic_id ? 'Editar' : 'Crear' }}
            </v-card-title>
        </v-card>
        <!--        <DefaultDivider/>-->
        <br>
        <v-card flat elevation="0">
            <v-card-text>
                <v-form ref="TemaForm">
                    <v-row justify="center">
                        <v-col cols="8">
                            <DefaultInput
                                dense
                                label="Nombre"
                                show-required
                                placeholder="Ingrese un nombre"
                                v-model="resource.name"
                                :rules="rules.name"
                                counter="120"
                                emojiable
                            />
                            <DefaultAutocomplete
                                dense
                                label="Requisito"
                                placeholder="Seleccione un requisito"
                                v-model="resource.topic_requirement_id"
                                :items="selects.requisitos"
                                clearable
                                item-text="name"
                            />
                            <fieldset class="editor mt-2">
                                <legend>Descripción y/u objetivos</legend>
                                <editor
                                    api-key="dph7cfjyhfkb998no53zdbcbwxvxtge2o84f02zppo4eix1g"
                                    v-model="resource.content"
                                    :init="{
                                        deprecation_warnings: false,
                                        content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                        height: 185,
                                        menubar: false,
                                        language: 'es',
                                        force_br_newlines : true,
                                        force_p_newlines : false,
                                        forced_root_block : '',
                                        plugins: ['lists image preview anchor', 'code', 'paste','link'],
                                        toolbar:
                                            'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify  |bullist numlist | image | preview |code | link | customButton ',
                                        images_upload_handler: images_upload_handler,
                                        setup: function (editor) {
                                            editor.ui.registry.addButton('customButton', {
                                                text: getIconText(), // Ruta de la imagen para el botón personalizado
                                                tooltip: 'Generar descripción con IA', // Texto que se muestra cuando se pasa el ratón sobre la imagen
                                                onAction: function (_) {
                                                    generateIaDescription();
                                                },
                                            });
                                        }
                                    }"
                                />
                                <v-progress-linear
                                    indeterminate
                                    color="primary"
                                    v-if="loading_description"
                                ></v-progress-linear>
                            </fieldset>
                        </v-col>
                        <v-col cols="4">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputLogo"
                                v-model="resource.imagen"
                                label="Imagen (500x350px)"
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'imagen')"/>
                        </v-col>
                    </v-row>

                    <DefaultModalSection
                        title="Método de evaluación"
                        class="my-5"
                    >
                        <template slot="content">

                            <v-row justify="center">
                                <v-col cols="4">
                                    <DefaultSelect
                                        dense
                                        show-required
                                        label="Tema evaluable"
                                        v-model="resource.assessable"
                                        :items="selects.assessable"
                                        @onChange="validateTipoEv"
                                    />
                                    <!-- :rules="rules.assessable" -->
                                </v-col>

                                <v-col cols="4">
                                    <DefaultSelect
                                        dense
                                        :show-required="resource.assessable === 1"
                                        label="Tipo de evaluación"
                                        v-model="resource.type_evaluation_id"
                                        :items="selects.evaluation_types"
                                        :rules="resource.assessable === 1 ? rules.tipo_ev : []"
                                        :disabled="resource.assessable === '0' || !resource.assessable"
                                        @onChange="showAlertEvaluacion"
                                    />
                                </v-col>

                                <v-col cols="4">
                                    <DefaultSelect
                                        v-show="showActiveResults"
                                        clearable
                                        dense
                                        :items="selects.qualification_types"
                                        item-text="name"
                                        return-object
                                        show-required
                                        v-model="resource.qualification_type"
                                        label="Sistema de calificación"
                                        :rules="rules.qualification_type_id"
                                    />
                                </v-col>

                            </v-row>
                            <DefaultSection
                                v-if="showActiveResults"
                                title="Resultados de evaluación"
                                class="mt-4"
                            >
                                <template slot="content">
                                    <v-row justify="center">
                                        <v-col cols="2" class="d-flex justify-content-center align-items-center">
                                            <DefaultToggle
                                                v-model="resource.active_results"
                                            />
                                        </v-col>

                                        <v-col cols="10">
                                            * Al activar resultados se visualizarán las respuestas ingresadas (correctas e incorrectas) en la aplicación del usuario al realizar una evaluación.
                                        </v-col>

                                    </v-row>

                                </template>
                            </DefaultSection>
                        </template>
                    </DefaultModalSection>


                    <DefaultModalSection
                        title="Recursos multimedia"
                        class="my-5"
                    >
                        <template slot="content">
                            <v-row justify="center">
                                <v-col cols="12">

                                    <table class="table table-hover table-multimedia">
                                        <!-- <thead class="--bg-default-primary">
                                        <tr>
                                            <th class="text-left" v-text="'Tipo'"/>
                                            <th class="text-left"
                                                style="max-width: 25% !important;     justify-content: right !important;"
                                                v-text="'Título'"/>
                                            <th class="text-center" v-text="'Archivo'"/>
                                            <th class="text-center" v-text="'¿Embebido?'"/>
                                            <th class="text-center" v-text="'¿Descargable?'"/>
                                            <th class="text-center" v-text="'Eliminar'"/>
                                        </tr>
                                        </thead> -->
                                        <draggable
                                            v-model="resource.media"
                                            group="multimedias"
                                            @start="drag=true"
                                            @end="drag=false"
                                            ghost-class="ghost"
                                            tag="tbody"
                                        >
                                            <!--                                    <transition-group type="transition" name="flip-list">-->
                                            <tr v-if="resource.media && resource.media.length === 0">
                                                <td class="text-center" colspan="6"
                                                    v-text="'No hay multimedias seleccionados'"/>
                                            </tr>
                                                <!-- style="cursor: pointer" -->
                                            <tr
                                                v-else
                                                v-for="(media, media_index) in resource.media" :key="media.media_index">
                                                <td >
                                                    <div class="multimedia-table-icon mt-2" title="Mover">
                                                        <i class="mdi mdi-drag"/>
                                                    </div>
                                                </td>
                                                <!-- :title="media.value || media.file.name " -->
                                                <td>
                                                    <div class="multimedia-table-icon mt-2">
                                                        <a title="Ver multimedia" class="" :href="getFullResourceLink(media)" target="_blank">
                                                            <i :class="mixin_multimedias.find(el => el.type === media.type_id).icon || 'mdi mdi-loading'"/>
                                                        </a>
                                                    </div>
                                                </td>
                                                <td>
                                                    <DefaultInput
                                                        v-model="media.title"
                                                        placeholder="Ingrese un título"
                                                        label="Título"
                                                        dense
                                                    />
                                                </td>
                                                <td class="">
                                                    <div class="multimedia-table-icon mt-2 " style="align-items: start;">
                                                        <a class="media-link" href="javascript:;"  title="Copiar código"
                                                            @click="copyToClipboard(media.value || media.file.name)">
                                                            <i :class="'mdi mdi-content-copy'"  style="font-size: 1rem !important; margin-right: 5px;" />

                                                            {{ media.value || media.file.name }}
                                                            <!-- <span :title="media.value || media.file.name"> -->
                                                            <!-- </span> -->
                                                        </a>
                                                    </div>
                                                </td>

                                                <td class="">
                                                    <div class="mt-1">
                                                        <span class="d-flex align-items-center">
                                                            <img width="26px"
                                                                v-if="media.ia_convert==1 && !media.path_convert"
                                                                class="mr-2 ia_convert_active img-rotate"
                                                                src="/img/loader-jarvis.svg"
                                                            >
                                                            <img width="32px"
                                                                v-else
                                                                class="mr-2"
                                                                :class="media.ia_convert ? 'ia_convert_active' : 'ia_convert_inactive' "
                                                                @click="openModalToConvert(media)"
                                                                src="/img/ia_convert.svg"
                                                                style="cursor: pointer;"
                                                            >
                                                            <p class="m-0" :style="media.ia_convert ? 'color:#5458EA' : 'color:gray'">Ai Convert</p>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="">
                                                    <div class="mt-2">
                                                        <DefaultToggle
                                                            dense
                                                            v-model="media.embed"
                                                            active-label="Embebido"
                                                            inactive-label="No embebido"
                                                            :disabled="media.disabled"
                                                            @onChange="verifyDisabledMediaEmbed"/>
                                                    </div>
                                                </td>
                                                <td class="">
                                                    <div class="mt-2">
                                                        <DefaultToggle
                                                            dense
                                                            v-model="media.downloadable"
                                                            active-label="Descargable"
                                                            inactive-label="No descargable"
                                                            :disabled="['youtube', 'vimeo', 'scorm', 'link','genially'].includes(media.type_id)"
                                                        />
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <DefaultDeleteBtnIcon
                                                        title="Eliminar"
                                                        @click="deleteMedia(media_index)"/>
                                                </td>
                                            </tr>
                                            <!--                                    </transition-group>-->
                                        </draggable>
                                    </table>
                                </v-col>

                            </v-row>

                            <TemaMultimediaTypes :limits="limits_ia_convert" @addMultimedia="addMultimedia($event)"/>
                        </template>
                    </DefaultModalSection>

                    <v-row>
                        <v-col cols="2">
                            <DefaultInput
                                dense
                                show-required
                                label="Orden"
                                v-model="resource.position"
                                :rules="rules.position"
                            />
                        </v-col>
                        <v-col cols="6">
                            <div class="mt-2">

                            <!--                            <DefaultToggle v-model="resource.active"/>-->
                                <DefaultToggle v-model="resource.active" :disabled="resource.disabled_estado_toggle"
                                    active-label="Tema activo"
                                    inactive-label="Tema inactivo"
                                />
                                <small v-if="resource.disabled_estado_toggle"
                                       v-text="'No se podrá activar el tema hasta que se le asigne o active una evaluación.'"/>
                            </div>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <DefaultModalActionButton
                    @cancel="leavePage"
                    @confirm="validate"
                    :loading="loadingActionBtn"
                />
            </v-card-actions>

        </v-card>
        <TemaValidacionesModal
            :width="topicsValidationModal.width"
            :ref="topicsValidationModal.ref"
            :options="topicsValidationModal"
            @onCancel="closeFormModal(topicsValidationModal)"
            @onConfirm="sendForm"
            :resource="resource"
        />
        <DialogConfirm
            :ref="mediaDeleteModal.ref"
            v-model="mediaDeleteModal.open"
            :options="mediaDeleteModal"
            width="408px"
            title="Eliminación de un archivo multimedia"
            subtitle="¡Estás por eliminar un archivo multimedia!"
            @onConfirm="confirmDeleteMedia"
            @onCancel="mediaDeleteModal.open = false"
        />
        <ConvertMediaToIaModal
            :limits="limits_ia_convert"
            width="40vw"
            :ref="convertMediaToIaOptions.ref"
            :options="convertMediaToIaOptions"
            @close="convertMediaToIaOptions.open = false "
            @onConfirm="addIaConvert"
        />
    </section>
</template>
<script>

import MultimediaBox from "./MultimediaBox";
import ConvertMediaToIaModal from "./ConvertMediaToIaModal";

// import DefaultRichText from "../../components/globals/DefaultRichText";
import TemaMultimediaTypes from "./TemaMultimediaTypes";
import draggable from 'vuedraggable'
import TemaValidacionesModal from "./TemaValidacionesModal";
import Editor from "@tinymce/tinymce-vue";
import DialogConfirm from "../../components/basicos/DialogConfirm";

const fields = ['name', 'description', 'content', 'imagen', 'position', 'assessable',
    'topic_requirement_id', 'type_evaluation_id', 'active', 'active_results', 'course_id', 'qualification_type',];

const file_fields = ['imagen'];

export default {
    components: {editor: Editor, TemaMultimediaTypes, MultimediaBox, draggable, TemaValidacionesModal,DialogConfirm,ConvertMediaToIaModal},
    props: ["modulo_id", 'school_id', 'course_id', 'topic_id'],
    data() {
        return {
            drag: false,
            base_endpoint: `/escuelas/${this.school_id}/cursos/${this.course_id}/temas`,
            media_url: null,
            resourceDefault: {
                name: null,
                course_id: this.course_id,
                topic_requirement_id: null,
                content: null,
                imagen: null,
                file_imagen: null,
                assessable: null,
                type_evaluation_id: null,
                position: null,
                media: [],
                active: false,
                active_results: false,
                hide_evaluable: null,
                hide_tipo_ev: null,
                disabled_estado_toggle: false,
                has_qualified_questions: 0,
                has_open_questions: 0,
                'update-validations': [],
                qualification_type: {position: 0},
            },
            selects: {
                assessable: [
                    {id: 1, nombre: 'Sí, el tema es evaluable'},
                    {id: 0, nombre: 'No, el tema no es evaluable'},
                ],
                evaluation_types: [],
                requisitos: [],
                qualification_types: [],
            },
            resource: {},
            rules: {
                name: this.getRules(['required', 'max:120']),
                // assessable: this.getRules(['required']),
                tipo_ev: this.getRules(['required']),
                position: this.getRules(['required', 'number']),
            },
            loadingActionBtn: false,
            topicsValidationModal: {
                ref: 'TemaValidacionesModal',
                open: false,
            },
            topicsValidationModalDefault: {
                ref: 'TemaValidacionesModal',
                action: null,
                width: '50vw',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'TemasValidaciones',
                persistent: false,
                showCloseIcon: true
            },
            mediaDeleteModal: {
                open: false,
                title_modal: 'Eliminación de un <b>archivo multimedia</b>',
                type_modal: 'delete',
                media_index: null,
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar un archivo multimedia!',
                        details: [
                            'Este archivo se eliminará para todos los temas asignados y lugares que haya sido asignado.'
                        ],
                    }
                },
            },
            limits_ia_convert:{},
            limits_descriptions_generate_ia:{},
            loading_description:false,
            convertMediaToIaOptions: {
                ref: 'ConvertMediaToIaOptions',
                title: null,
                open: false,
                confirmLabel: 'Guardar'
            },
        }
    },
    async mounted() {
        let vue = this
        vue.showLoader()
        await this.loadData()
        vue.hideLoader()
        vue.loadLimitsGenerateIaDescriptions();
    },
    computed: {
        showActiveResults() {
            let vue = this;

            if((vue.selects.evaluation_types).length && vue.resource.type_evaluation_id) {
                const evaluation_type = vue.selects.evaluation_types.find(el => el.id === vue.resource.type_evaluation_id);
                return (evaluation_type.name === "Calificada")
            }
            return false;
        }
    },
    methods: {
        leavePage() {
            let vue = this
            window.location.href = vue.base_endpoint;
        },
        async validate() {
            let vue = this
            const validForm = vue.validateForm('TemaForm')
            const hasMultimedia = vue.resource.media.length > 0

            if (!validForm || !hasMultimedia) {
                vue.hideLoader()
                vue.loadingActionBtn = false
                if (!hasMultimedia)
                    vue.showAlert("Debe seleccionar al menos un multimedia", 'warning')
                return
            }
            if (vue.topic_id !== '') {

                if (vue.resource.hide_evaluable !== vue.resource.assessable || vue.resource.hide_tipo_ev !== vue.resource.type_evaluation_id) {
                    const evaluation_types = vue.selects.evaluation_types;
                    const indexSelected = evaluation_types.findIndex(el => el.id === vue.resource.hide_tipo_ev);
                    let selectedType = {code: null};
                    if (indexSelected > -1) selectedType = evaluation_types[indexSelected];
                    let data = {
                        tema: vue.resource.id,
                        curso: vue.resource.course_id,
                        modulo: vue.modulo_id,
                        escuela: vue.resource.school_id, //falta
                        grupo: [],
                        cursos_libres: false,
                        UsuariosActivos: true,
                        UsuariosInactivos: false,
                        url: 'temas_noevaluables',
                        selectedType,
                    }

                    if (vue.resource.hide_evaluable === 0 || selectedType.code === 'qualified') {
                        data.carrera = []
                        data.ciclo = []
                        data.temasActivos = true
                        data.temasInactivos = true
                        if (selectedType.code === 'qualified') {
                            // Mostrar modal con check y opcion de descarga (endpoint notas por temas)
                            data.aprobados = true;
                            data.desaprobados = true;
                            data.validacion = false;
                            data.variantes = false;
                            data.url = 'notas_tema';
                        }
                    } else if (selectedType.code === 'open') {
                        // endpoint evaluaciones abiertas)
                        data.variantes = false;
                        data.url = 'evaluaciones_abiertas';
                    }
                    // await vue.cleanValidationsModal(vue.topicsValidationModal, vue.topicsValidationModalDefault);
                    vue.topicsValidationModal = Object.assign({}, vue.topicsValidationModal, vue.topicsValidationModalDefault);

                    vue.topicsValidationModal.hideConfirmBtn = false
                    vue.topicsValidationModal.cancelLabel = 'Cerrar'
                    await vue.openFormModal(vue.topicsValidationModal, data, 'validations-before-update', 'Atención')
                    return
                }
            }
            return vue.sendForm({checkbox: false})
        },
        sendForm(data, validateForm = true) {
            let vue = this

            // if (data.confirmMethod === 'messagesActions') {
            //     vue.leavePage()
            //     return
            // }

            vue.topicsValidationModal.open = false
            vue.loadingActionBtn = true
            vue.showLoader()
            const validForm = vue.validateForm('TemaForm')
            const hasMultimedia = vue.resource.media.length > 0

            if (!validForm || !hasMultimedia) {
                vue.hideLoader()
                vue.loadingActionBtn = false
                if (!hasMultimedia)
                    vue.showAlert("Debe seleccionar al menos un multimedia", 'warning')
                return
            }

            if (vue.topicsValidationModal.action === 'validations-after-update') {
                vue.hideLoader();
                vue.topicsValidationModal.open = false;
                setTimeout(() => vue.leavePage(), 2000);
                return;
            }

            const edit = vue.topic_id !== ''
            let url = `${vue.base_endpoint}/${edit ? `update/${vue.topic_id}` : 'store'}`
            let method = edit ? 'PUT' : 'POST';

            let formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
            formData.append('validate', validateForm ? "1" : "0");
            vue.addMedias(formData)
            if (data.checkbox)
                formData.append('check_tipo_ev', data.checkbox)

            vue.$http.post(url, formData)
                .then(async ({data}) => {
                    this.hideLoader()
                    const has_info_messages = data.data.messages.list.length > 0
                    console.log(has_info_messages)
                    if (has_info_messages){
                        const res = await vue.handleValidationsAfterUpdate(data.data, vue.topicsValidationModal, vue.topicsValidationModalDefault);
                        console.log('handleValidationsAfterUpdate:',res)
                        }
                    else {
                        vue.queryStatus("tema", "crear_tema");
                        vue.showAlert(data.data.msg)
                        setTimeout(() => vue.leavePage(), 2000)
                    }
                })
                .catch(async (error) => {
                    const res = await vue.handleValidationsBeforeUpdate(error, vue.topicsValidationModal, vue.topicsValidationModalDefault);
                    console.log('handleValidationsBeforeUpdate:',res)
                    vue.loadingActionBtn = false
                })
        },
        images_upload_handler(blobInfo, success, failure) {
            // console.log(blobInfo.blob());
            let formdata = new FormData();
            formdata.append("image", blobInfo.blob(), blobInfo.filename());
            formdata.append("model_id", null);

            axios
                .post("/upload-image/temas", formdata)
                .then((res) => {
                    success(res.data.location);
                })
                .catch((err) => {
                    console.log(err)
                    failure("upload failed!");
                });
        },
        addMedias(formData) {
            let vue = this
            vue.resource.media.forEach((el, index) => {
                if (el.file)
                    formData.append(`medias[${index}][file]`, el.file)
                else
                    formData.append(`medias[${index}][valor]`, el.value)

                formData.append(`medias[${index}][titulo]`, el.title)
                formData.append(`medias[${index}][tipo]`, el.type_id)
                formData.append(`medias[${index}][embed]`, Number(el.embed))
                formData.append(`medias[${index}][descarga]`, Number(el.downloadable))
                formData.append(`medias[${index}][ia_convert]`, Number(el.ia_convert))
            })
        },
        deleteMedia(media_index) {
            let vue = this
            // vue.resource.media.splice(media_index, 1)
            vue.mediaDeleteModal.media_index = media_index
            vue.mediaDeleteModal.open = true;
        },
        async loadData() {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.base_endpoint}/${vue.topic_id === '' ? 'form-selects' : `search/${vue.topic_id}`}`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.media_url = data.data.media_url
                    vue.selects.requisitos = data.data.requisitos
                    vue.selects.evaluation_types = data.data.evaluation_types
                    vue.selects.qualification_types = data.data.qualification_types
                    vue.limits_ia_convert = data.data.limits_ia_convert;
                    if (vue.topic_id !== '') {
                        vue.resource = Object.assign({}, data.data.tema)
                        vue.resource.assessable = (vue.resource.assessable == 1) ? 1 : 0;
                    } else {
                        vue.resource.qualification_type = data.data.qualification_type
                    }
                })
            return 0;
        },
        addMultimedia(multimedia) {
            let vue = this
            if(multimedia.ia_convert){
                vue.limits_ia_convert.media_ia_converted = vue.limits_ia_convert.media_ia_converted +1;
            }
            vue.resource.media.push({
                title: multimedia.titulo,
                ia_convert: multimedia.ia_convert || null,
                value: multimedia.valor || null,
                file: multimedia.file || null,
                type_id: multimedia.type,
                embed: ['office'].includes(multimedia.type) ? false : true,
                downloadable: ['youtube', 'vimeo', 'scorm', 'link','genially'].includes(multimedia.type) ? false : true,
                disabled: false,
            })
            vue.verifyDisabledMediaEmbed();
        },
        validateTipoEv() {
            let vue = this
            if (['0', null, 0, false].includes(vue.resource.assessable)) vue.resource.type_evaluation_id = null

            vue.resource.tipo_ev = null
            vue.resetFormValidation('TemaForm')
        },
        validateCountQuestions() {
            let vue = this;
            if (vue.resource) {
                if (vue.resource.assessable === 1) {
                    const evaluation_types = vue.selects.evaluation_types;
                    const indexSelected = evaluation_types.findIndex(el => el.id === vue.resource.type_evaluation_id);
                    if (indexSelected > -1) {
                        const selectedType = evaluation_types[indexSelected];
                        const canNotActive = selectedType.code === 'qualified' ?
                            !vue.resource.has_qualified_questions :
                            !vue.resource.has_open_questions;
                        if (canNotActive) vue.resource.active = false;
                        return canNotActive;
                    }
                }
            }
            return false;
        },
        verifyDisabledMediaEmbed() {
            let vue = this;
            const f = vue.resource.media.filter((e) => e.embed == true);
            if (f.length == 1) {
                const idx = vue.resource.media.findIndex((e) => e.embed == true);
                if (idx > -1) {
                    vue.resource.media[idx].disabled = true;
                }
            } else {
                vue.resource.media.map(e => e.disabled = false);
            }
        },
        async showAlertEvaluacion() {
            let vue = this
            vue.resource.disabled_estado_toggle = vue.validateCountQuestions();

            vue.topicsValidationModal.hideConfirmBtn = true
            const evaluation_type = vue.selects.evaluation_types.find(el => el.id === vue.resource.type_evaluation_id);
            const tipo_ev = evaluation_type.name === "Calificada" ? 'Calificada' : 'Abierta';
            const title = `Debe tener una evaluación ${tipo_ev}`;
            const data = {data: [title]}

            vue.resource.active_results = 0;

            // await vue.cleanValidationsModal(vue.topicsValidationModal, vue.topicsValidationModalDefault);
            vue.topicsValidationModal = Object.assign({}, vue.topicsValidationModal, vue.topicsValidationModalDefault);

            vue.topicsValidationModal.width = "30vw"
            vue.topicsValidationModal.hideConfirmBtn = true
            vue.topicsValidationModal.cancelLabel = 'Entendido'
            await vue.openFormModal(vue.topicsValidationModal, data, 'showAlertEvaluacion', 'Debes de tener en cuenta')
        },
        async confirmDeleteMedia(){
            let vue = this
            if(vue.resource.media.filter((media, index) => index != vue.mediaDeleteModal.media_index && media.embed).length == 0){
                vue.mediaDeleteModal.open = false
                vue.showAlert("Debe haber almenos un multimedia embebido.", 'warning')
                return;
            }
            if(vue.resource.media[vue.mediaDeleteModal.media_index].ia_convert){
                vue.limits_ia_convert.media_ia_converted = vue.limits_ia_convert.media_ia_converted - 1;
            }
            vue.resource.media.splice(vue.mediaDeleteModal.media_index, 1)
            vue.mediaDeleteModal.open = false
        },
        async copyToClipboard(text) {
            let vue = this;

            await navigator.clipboard.writeText(text);

            vue.showAlert('Código multimedia copiado', 'success', '', 3);
        },
        getFullResourceLink(media) {
            // let link = ""
            // let media_url = ""
            let vue = this;

            if (media.type_id == 'youtube')
                return 'https://www.youtube.com/embed/' + media.value + '?rel=0&modestbranding=1&showinfo=0';

            if (media.type_id == 'vimeo')
                return 'https://player.vimeo.com/video/' + media.value;

            if (media.type_id == 'video' || media.type_id == 'pdf' || media.type_id == 'audio')
                return vue.media_url + media.value;

            if (media.type_id == 'scorm' || media.type_id == 'genially' || media.type_id == 'link')
                return media.value;
        },
        async generateIaDescription(){
            const vue = this;
            let url = `/jarvis/generate-description-jarvis` ;
            if(vue.loading_description || !vue.resource.name){
                const message = vue.loading_description ? 'Se está generando la descripción, espere un momento' : 'Es necesario colocar un nombre al tema para poder generar la descripción';
                vue.showAlert(message, 'warning', '')
                return ''
            }
            if(vue.limits_descriptions_generate_ia.ia_descriptions_generated >= vue.limits_descriptions_generate_ia.limit_descriptions_jarvis){
                vue.showAlert('Ha sobrepasado el limite para poder generar descripciones con IA', 'warning', '')
                return ''
            }
            vue.loading_description = true;
            await axios.post(url,{
                name : vue.resource.name,
                type:'topic'
            }).then(({data})=>{
                let ia_descriptions_generated = document.getElementById("ia_descriptions_generated");
                ia_descriptions_generated.textContent = vue.limits_descriptions_generate_ia.ia_descriptions_generated+1;

                let characters = data.data.description.split('');
                vue.resource.content = ''; // Limpiar el contenido anterior
                function updateDescription(index) {
                    if (index < characters.length) {
                        vue.resource.content += characters[index];
                        setTimeout(() => {
                            updateDescription(index + 1);
                        }, 10);
                    }else{
                        vue.loading_description = false;
                    }
                }
                updateDescription(0);
            }).catch(()=>{
                vue.loading_description = false;
            })
        },
        getIconText(){
            return `
            <div>
                <image src="/img/ia_convert.svg" class="mt-2" style="width: 22px;cursor: pointer;"/ >
                <span class="badge_custom"><span id="ia_descriptions_generated">0</span>/<span id="limit_descriptions_jarvis">0</span></span>
            </div>
            `
            // return '<v-badge><image src="/img/ia_convert.svg" class="mt-2" style="width: 22px;cursor: pointer;"/ ></v-badge>';
        },
        openModalToConvert(media){
            let vue  = this;
            if(media.ia_convert){
                return '';
            }
            if(!['youtube','video','audio','pdf'].includes(media.type_id)){
                vue.showAlert('Este tipo de multimedia  para IA', 'warning', '')
                return '';
            }
            vue.openFormModal(vue.convertMediaToIaOptions, media, null , 'Generar evaluaciones automáticas')
            // convertMediaToIaOptions
        },
        addIaConvert(media){
            let vue  = this;
            const idx = vue.resource.media.findIndex(m => m.id = media.id)
            vue.resource.media[idx].ia_convert = 1;
            vue.limits_ia_convert.media_ia_converted = vue.limits_ia_convert.media_ia_converted + 1;
            vue.convertMediaToIaOptions.open = false;
        },
        async loadLimitsGenerateIaDescriptions(){
            await axios.get('/jarvis/limits?type=descriptions').then(({data})=>{
                this.limits_descriptions_generate_ia = data.data;
                let ia_descriptions_generated = document.getElementById("ia_descriptions_generated");
                let limit_descriptions_jarvis = document.getElementById("limit_descriptions_jarvis");
                ia_descriptions_generated.textContent = data.data.ia_descriptions_generated;
                limit_descriptions_jarvis.textContent =  data.data.limit_descriptions_jarvis;
            })
        }
    }
}
</script>
<style lang="scss">
@import "resources/sass/variables";

.date_reinicios_disabled {
    pointer-events: none;
    padding: 10px 0;
    border-radius: 9px;
    opacity: 0.3;
    background: #CCC;
}

.date_reinicios_error {
    padding: 10px 0;
    border: #FF5252 2px solid;
    border-radius: 5px;
}

.date_reinicios_error_message {
    line-height: 12px;
    word-break: break-word;
    overflow-wrap: break-word;
    word-wrap: break-word;
    -webkit-hyphens: auto;
    -ms-hyphens: auto;
    hyphens: auto;
    font-weight: 400;
    color: #FF5252;
    caret-color: #FF5252;
}

.box_date_reinicios {
    background: $primary-default-color;
    padding: 2px 9px 9px 9px;
    border-radius: 5px;

    label {
        color: white;
    }

    .input_date_reinicios {
        appearance: textfield;
        -moz-appearance: textfield;
        text-align: center;
        background: white;
        width: 50px;
        height: 30px;
    }
}
.ia_convert_inactive{
    filter: invert(42%) sepia(98%) saturate(0%) hue-rotate(349deg) brightness(111%) contrast(100%);
}
.ia_convert_active{
    filter: hue-rotate(360deg);
}
.img-rotate {
  animation: rotacion 4s linear infinite;
}

@keyframes rotacion {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
.badge_custom{
    position: absolute !important;
    color: white !important;
    background: rgb(87, 191, 227) !important;
    padding: 5px !important;
    border-radius: 16px !important;
    margin-right: 8px !important;
    margin-left: 2px !important;
    font-size:9px !important;
}
</style>
