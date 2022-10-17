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
                    <DefaultSectionLabel label="Contenido General"/>
                    <v-row justify="center">
                        <v-col cols="6">
                            <DefaultInput
                                dense
                                label="Nombre"
                                show-required
                                placeholder="Ingrese un nombre"
                                v-model="resource.name"
                                :rules="rules.name"
                                counter="120"
                            />
                        </v-col>
                        <v-col cols="6">
                            <DefaultAutocomplete
                                dense
                                label="Requisito"
                                placeholder="Seleccione un requisito"
                                v-model="resource.topic_requirement_id"
                                :items="selects.requisitos"
                                clearable
                                item-text="name"
                            />
                        </v-col>
                    </v-row>
                    <v-row justify="center">
                        <v-col cols="8">
                            <editor
                                api-key="6i5h0y3ol5ztpk0hvjegnzrbq0hytc360b405888q1tu0r85"
                                v-model="resource.content"
                                :init="{
                                content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                height: 175,
                                menubar: false,
                                language: 'es',
                                force_br_newlines : true,
                                force_p_newlines : false,
                                forced_root_block : '',
                                plugins: ['lists image preview anchor', 'code', 'paste','link'],
                                toolbar:
                                    'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code | link',
                                images_upload_handler: images_upload_handler,
                            }"/>

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
                    <DefaultSectionLabel label="Método de Evaluación"/>
                    <v-row justify="center">
                        <v-col cols="4">
                            <DefaultSelect
                                dense
                                show-required
                                label="Evaluable"
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
                                label="Tipo Evaluación"
                                v-model="resource.type_evaluation_id"
                                :items="selects.evaluation_types"
                                :rules="resource.assessable === 1 ? rules.tipo_ev : []"
                                :disabled="resource.assessable === '0' || !resource.assessable"
                                @onChange="showAlertEvaluacion"
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultInput
                                dense
                                show-required
                                label="Orden"
                                v-model="resource.position"
                                :rules="rules.position"
                            />
                        </v-col>
                    </v-row>
                    <br>
                    <DefaultSectionLabel label="Multimedia"/>
                    <TemaMultimediaTypes @addMultimedia="addMultimedia($event)"/>
                    <br>
                    <v-row justify="center">
                        <v-col cols="12">

                            <table class="table table-hover">
                                <thead class="bg-default-primary">
                                <tr>
                                    <th class="text-left white--text" v-text="'Tipo'"/>
                                    <th class="text-left white--text"
                                        style="max-width: 25% !important;     justify-content: right !important;"
                                        v-text="'Título'"/>
                                    <th class="text-center white--text" v-text="'Archivo'"/>
                                    <!--                                <th class="text-center white&#45;&#45;text" v-text="'Valor'"/>-->
                                    <th class="text-center white--text" v-text="'¿Embebido?'"/>
                                    <th class="text-center white--text" v-text="'¿Descargable?'"/>
                                    <th class="text-center white--text" v-text="'Eliminar'"/>
                                </tr>
                                </thead>
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
                                    <tr
                                        v-else
                                        style="cursor: pointer"
                                        v-for="(media, media_index) in resource.media" :key="media.media_index">
                                        <td>
                                            <div class="multimedia-box"
                                                 style="height: 40px !important; width: 40px !important;">
                                                <i :class="mixin_multimedias.find(el => el.type === media.type_id).icon || 'mdi mdi-loading'"/>
                                            </div>
                                        </td>
                                        <td>
                                            <DefaultInput
                                                v-model="media.title"
                                                placeholder="Ingrese un título"
                                                dense
                                            />
                                        </td>
                                        <td>{{ media.value || media.file.name }}</td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <DefaultToggle v-model="media.embed"
                                                               no-label
                                                               :disabled="media.disabled"
                                                               @onChange="verifyDisabledMediaEmbed"/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <DefaultToggle
                                                    v-model="media.downloadable"
                                                    no-label
                                                    :disabled="['youtube', 'vimeo', 'scorm', 'link','genially','rise'].includes(media.type_id)"
                                                />
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <DefaultDeleteBtnIcon
                                                @click="deleteMedia(media_index)"/>
                                        </td>
                                    </tr>
                                    <!--                                    </transition-group>-->
                                </draggable>
                            </table>
                        </v-col>

                    </v-row>
                    <v-row>
                        <v-col cols="5">
                            <!--                            <DefaultToggle v-model="resource.active"/>-->
                            <DefaultToggle v-model="resource.active" :disabled="resource.disabled_estado_toggle"/>
                            <small v-if="resource.disabled_estado_toggle"
                                   v-text="'No se podrá activar el tema hasta que se le asigne o active una evaluación.'"/>
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
    </section>
</template>
<script>

import MultimediaBox from "./MultimediaBox";
// import DefaultRichText from "../../components/globals/DefaultRichText";
import TemaMultimediaTypes from "./TemaMultimediaTypes";
import draggable from 'vuedraggable'
import TemaValidacionesModal from "./TemaValidacionesModal";
import Editor from "@tinymce/tinymce-vue";

const fields = ['name', 'description', 'content', 'imagen', 'position', 'assessable',
    'topic_requirement_id', 'type_evaluation_id', 'active', 'course_id'];

const file_fields = ['imagen'];

export default {
    components: {editor: Editor, TemaMultimediaTypes, MultimediaBox, draggable, TemaValidacionesModal},
    props: ["modulo_id", 'school_id', 'course_id', 'topic_id'],
    data() {
        return {
            drag: false,
            base_endpoint: `/escuelas/${this.school_id}/cursos/${this.course_id}/temas`,
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
                hide_evaluable: null,
                hide_tipo_ev: null,
                disabled_estado_toggle: false,
                has_qualified_questions: 0,
                has_open_questions: 0,
                'update-validations': [],
            },
            selects: {
                assessable: [
                    {id: 1, nombre: 'Si'},
                    {id: 0, nombre: 'No'},
                ],
                evaluation_types: [],
                requisitos: []
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
        }
    },
    async mounted() {
        let vue = this
        vue.showLoader()
        await this.loadData()
        vue.hideLoader()
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
                    if (has_info_messages)
                        await vue.handleValidationsAfterUpdate(data.data, vue.topicsValidationModal, vue.topicsValidationModalDefault);
                    else {
                        vue.showAlert(data.data.msg)
                        setTimeout(() => vue.leavePage(), 2000)
                    }
                })
                .catch(async (error) => {
                    await vue.handleValidationsBeforeUpdate(error, vue.topicsValidationModal, vue.topicsValidationModalDefault);
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
            })
        },
        deleteMedia(media_index) {
            let vue = this
            vue.resource.media.splice(media_index, 1)
        },
        async loadData() {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.base_endpoint}/${vue.topic_id === '' ? 'form-selects' : `search/${vue.topic_id}`}`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.requisitos = data.data.requisitos
                    vue.selects.evaluation_types = data.data.evaluation_types
                    if (vue.topic_id !== '') {
                        vue.resource = Object.assign({}, data.data.tema)
                        vue.resource.assessable = (vue.resource.assessable == 1) ? 1 : 0;
                    }
                })
            return 0;
        },
        addMultimedia(multimedia) {
            let vue = this
            vue.resource.media.push({
                title: multimedia.titulo,
                value: multimedia.valor || null,
                file: multimedia.file || null,
                type_id: multimedia.type,
                embed: true,
                downloadable: false,
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

            // await vue.cleanValidationsModal(vue.topicsValidationModal, vue.topicsValidationModalDefault);
            vue.topicsValidationModal = Object.assign({}, vue.topicsValidationModal, vue.topicsValidationModalDefault);

            vue.topicsValidationModal.width = "30vw"
            vue.topicsValidationModal.hideConfirmBtn = true
            vue.topicsValidationModal.cancelLabel = 'Entendido'
            await vue.openFormModal(vue.topicsValidationModal, data, 'showAlertEvaluacion', 'Debes de tener en cuenta')
        },
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
</style>
