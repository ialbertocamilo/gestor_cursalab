<template>
    <section class="section-list">
        <v-card flat elevation="0">
            <v-card-title>
                Cursos: {{ curso_id ? 'Editar' : 'Crear' }}
            </v-card-title>
        </v-card>
        <br>
        <v-card flat elevation="0">
            <v-card-text>
                <v-form ref="CursoForm">
                    <DefaultErrors :errors="errors"/>

                    <v-row>
                        <v-col cols="6">
                            <DefaultInput
                                dense
                                label="Nombre"
                                placeholder="Ingrese un nombre"
                                v-model="resource.name"
                                :rules="rules.name"
                                show-required
                                counter="120"
                                emojiable
                            />
                        </v-col>
                        <v-col cols="6">
                            <DefaultAutocomplete
                                show-required
                                :rules="rules.lista_escuelas"
                                dense
                                label="Escuelas"
                                v-model="resource.lista_escuelas"
                                :items="selects.lista_escuelas"
                                item-text="name"
                                item-value="id"
                                multiple
                            />
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="6">
                            <v-row>
                                <v-col cols="12">
                                    <!-- <v-btn @click="generateIaDescription()">Generar con IA</v-btn> -->
                                    <DefaultTextArea
                                        :ref="'textAreaDescription'"
                                        dense
                                        label="Descripción y/u objetivos"
                                        placeholder="Ingrese una descripción y/o objetivo del curso"
                                        v-model="resource.description"
                                        :rows="4"
                                        @eventGenerateIA="generateIaDescription"
                                        :limits="limits_descriptions_generate_ia"
                                        :loading="loading_description"
                                        :disabled="loading_description"
                                        :showButtonIaGenerate="true"
                                    />
                                </v-col>
                                <v-col cols="12">
                                    <DefaultAutocomplete
                                        show-required
                                        :rules="rules.types"
                                        dense
                                        label="Tipo de curso"
                                        v-model="resource.type_id"
                                        :items="selects.types"
                                        item-text="name"
                                        item-value="id"
                                    />
                                </v-col>
                                <v-col cols="12">
                                    <DefaultAutocomplete
                                        dense
                                        label="Requisito"
                                        v-model="resource.requisito_id"
                                        :items="selects.requisito_id"
                                        custom-items
                                        item-text="name"
                                        item-value="id"
                                        clearable
                                    >
                                        <template v-slot:customItems="{item}">
                                            <v-list-item-content>
                                                <v-list-item-title v-html="item.name"/>
                                                <v-list-item-subtitle class="list-cursos-carreras" v-html="item.escuelas"/>
                                            </v-list-item-content>
                                        </template>
                                    </DefaultAutocomplete>
                                </v-col>

                            </v-row>

                            <v-row>

                                <v-col cols="6">
                                    <DefaultAutocomplete
                                        dense
                                        label="Duración (hrs.)"
                                        v-model="resource.duration"
                                        :items="selects.duration"
                                        item-text="name"
                                        item-value="id"
                                        placeholder="Ej. 2:00"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <DefaultInput
                                        numbersOnly
                                        dense
                                        label="Inversión"
                                        placeholder="Ej. 2000"
                                        v-model="resource.investment"
                                    />
                                </v-col>
                            </v-row>
                        </v-col>

                        <v-col cols="6">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputLogo"
                                v-model="resource.imagen"
                                label="Imagen (500x350px)"
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'imagen')"/>
                        </v-col>

                    </v-row>
                    <!-- <v-row justify="center"> -->

                       <!--  <v-col cols="6">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputDiploma"
                                v-model="resource.plantilla_diploma"
                                label="Plantilla de Diploma (Medida: 1743x1553 píxeles)  "
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'plantilla_diploma')"/>
                        </v-col> -->
                    <!-- </v-row> -->

                    <v-row justify="space-around" class="menuable">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Configuración de calificación"
                            >
                                <template slot="content">
                                    <v-row justify="center">

                                      <!--   <v-col cols="1">
                                            <DefaultInfoTooltip
                                                class=""
                                                top
                                                text="Utilizado para mostrar el resultado del curso y que se tendrá por defecto en la creación de temas."
                                            />

                                        </v-col> -->

                                        <v-col cols="4">

                                            <DefaultSelect
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

                                        <!-- <v-col cols="3">
                                            <small>*Utilizado para mostrar el resultado del curso y que se tendrá por defecto en la creación de temas.</small>
                                        </v-col> -->

                                        <v-col cols="4">
                                            <DefaultInput
                                                label="Nota mínima aprobatoria"
                                                v-model="resource.nota_aprobatoria"
                                                :rules="rules.nota_aprobatoria"
                                                type="number"
                                                :min="0"
                                                :max="resource.qualification_type ? resource.qualification_type.position : 0"
                                                show-required
                                                dense
                                                @onFocus="curso_id && conf_focus ? alertNotaMinima() : null"
                                            />
                                            <!-- -- {{ new_value }} -->
                                        </v-col>

                                        <v-col cols="4">
                                            <DefaultInput
                                                label="Número de intentos"
                                                v-model="resource.nro_intentos"
                                                :rules="rules.nro_intentos"
                                                type="number"
                                                show-required
                                                dense
                                            />
                                        </v-col>

                                        <v-col cols="12" class="py-1">
                                            <p class="mb-0">** Utilizado para mostrar el resultado del curso y que se tendrá por defecto en la creación de temas.</p>
                                        </v-col>
                                    </v-row>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>

                    <v-row justify="space-around">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Programación de reinicios"
                            >
                                <template slot="content">
                                    <v-row justify="center">
                                        <v-col cols="3" class="d-flex justify-content-center align-items-center">
                                            <DefaultToggle
                                                active-label="Automático"
                                                inactive-label="Manual"
                                                v-model="resource.scheduled_restarts_activado"
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                            <DefaultInput
                                                label="Días"
                                                v-model="resource.scheduled_restarts_dias"
                                                :disabled="!resource.scheduled_restarts_activado"
                                                type="number"
                                                dense
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                            <DefaultInput
                                                label="Horas"
                                                v-model="resource.scheduled_restarts_horas"
                                                :disabled="!resource.scheduled_restarts_activado"
                                                type="number"
                                                dense
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                            <DefaultInput
                                                label="Minutos"
                                                v-model="resource.scheduled_restarts_minutos"
                                                :disabled="!resource.scheduled_restarts_activado"
                                                type="number"
                                                dense
                                            />
                                        </v-col>
                                    </v-row>
                                    <div class="d-flex justify-content-center mt-1" v-if="showErrorReinicios">
                                        <div style="color: #FF5252" class="v-messages__wrapper">
                                            <div class="v-messages__message">Validar hora de reinicio</div>
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>

                    <v-row justify="space-around">
                        <v-col cols="12">
                          <DefaultModalSection
                              title="Programación de curso">
                            <template slot="content">
                              <v-row justify="center">

                                        <v-col cols="3" class="d-flex justify-content-center align-items-center">
                                            <DefaultInputDate
                                                clearable
                                                :referenceComponent="'modalDateFilter1'"
                                                :options="modalDateFilter1"
                                                v-model="resource.publish_date_1"
                                                label="Fecha de inicio"
                                                dense
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                          <DefaultInput
                                              class="time-input"
                                              type="time"
                                              label="Hora"
                                              v-model="resource.publish_time_1"
                                              :disabled="!resource.publish_date_1"
                                              :rules="rules.time"
                                              step="60"
                                          />
                                        </v-col>

                                         <v-col cols="3" class="d-flex justify-content-center align-items-center">
                                           <DefaultInputDate
                                               clearable
                                               :referenceComponent="'modalDateFilter1'"
                                               :options="modalDateFilter2"
                                               v-model="resource.publish_date_2"
                                               label="Fecha de fin"
                                               dense
                                           />
                                        </v-col>
                                        <v-col cols="3">
                                          <DefaultInput
                                              class="time-input"
                                              type="time"
                                              label="Hora"
                                              v-model="resource.publish_time_2"
                                              :disabled="!resource.publish_date_2"
                                              :rules="rules.time"
                                              step="60"
                                          />
                                        </v-col>
                                        <!-- :disabled="!resource.scheduled_restarts_activado" -->

                                        <v-col cols="12" class="py-1">
                                            <p class="mb-0">** El curso pasará a estar activo de acuerdo a la fecha configurada.</p>
                                            <p class="mb-0">** Recuerda que el curso debe estar segmentado, pertenecer a una escuela activa y contener al menos un tema activo para que este sea visible por tus usuarios cuando este se active.</p>
                                        </v-col>
                                    </v-row>
                                    <!-- <div class="d-flex justify-content-center mt-1" v-if="showErrorReinicios">
                                        <div style="color: #FF5252" class="v-messages__wrapper">
                                            <div class="v-messages__message">Validar hora de reinicio</div>
                                        </div>
                                    </div> -->
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>

                    <v-row justify="space-around">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Configuración de diploma"
                            >
                              <template slot="content">

                                <DiplomaSelector v-model="resource.certificate_template_id" :old-preview="resource.plantilla_diploma"/>

                                <div class="p-2 mt-3">
                                  <DefaultToggle dense
                                      :active-label="'Mostrar diploma al usuario'"
                                      :inactive-label="'Mostrar diploma al usuario'"
                                      v-model="resource.show_certification_to_user"/>
                                </div>

                                <div class="p-2 --mt-3">
                                  <DefaultToggle dense
                                      :active-label="'Confirmación para habilitarles el diploma a los usuarios donde acepten haber culminado satisfactoriamente el curso.'"
                                      :inactive-label="'Confirmación para habilitarles el diploma a los usuarios donde acepten haber culminado satisfactoriamente el curso.'"
                                      v-model="resource.user_confirms_certificate"/>
                                </div>

                                <!--                                    <DiplomaSelector-->
                                <!--                                        v-if="resource.show_certification_to_user"-->
                                <!--                                        v-model="resource.certificate_template_id"/>-->

                              </template>

                            </DefaultModalSection>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col cols="2">
                            <DefaultToggle v-model="resource.active" @onChange="modalStatusEdit"/>
                        </v-col>
                    </v-row>

                </v-form>
            </v-card-text>
            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)">
                <DefaultModalActionButton
                    @cancel="closeModal"
                    @confirm="confirmModal"
                    :loading="loadingActionBtn"
                />
            </v-card-actions>
            <CursoValidacionesModal
                width="408px"
                :ref="courseValidationModal.ref"
                :options="courseValidationModal"
                :resource="resource"
                @onCancel="closeFormModal(courseValidationModal)"
                @onConfirm="confirmValidationModal(
                    courseValidationModal,
                    `${base_endpoint}?${addParamsToURL(base_endpoint, getAllUrlParams(url))}`,
                    confirmModal(false))"
            />
            <DialogConfirm
                v-model="deleteConfirmationDialog.open"
                :options="deleteConfirmationDialog"
                width="408px"
                title="Cambiar de estado del curso"
                subtitle="¡Estás a punto cambiar la configuración de un curso!"
                @onConfirm="confirmDelete"
                @onCancel="deleteConfirmationDialog.open = false"
            />
            <DialogConfirm
                :ref="courseUpdateStatusModal.ref"
                v-model="courseUpdateStatusModal.open"
                :options="courseUpdateStatusModal"
                width="408px"
                title="Cambiar de estado al curso"
                subtitle="¿Está seguro de cambiar de estado al curso?"
                @onConfirm="courseUpdateStatusModal.open = false"
                @onCancel="closeModalStatusEdit"
            />
        </v-card>
    </section>
</template>
<script>
const fields = [
    'name', 'reinicios_programado', 'active', 'position', 'imagen',
    'plantilla_diploma', 'config_id', 'categoria_id', 'type_id', 'qualification_type',
    'description', 'requisito_id', 'lista_escuelas',
    'duration', 'investment', 'show_certification_date', 'certificate_template_id',
    'activate_at', 'deactivate_at', 'show_certification_to_user', 'user_confirms_certificate'
];
const file_fields = ['imagen', 'plantilla_diploma'];
import CursoValidacionesModal from "./CursoValidacionesModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import DiplomaSelector from "../../components/Diplomas/DiplomaSelector";

export default {
    components: { CursoValidacionesModal, DialogConfirm, DiplomaSelector },
    props: ["modulo_id", 'categoria_id', 'curso_id'],
    data() {
        const route_school = (this.categoria_id !== '')
            ? `/escuelas/${this.categoria_id}`
            : ``;

        let base_endpoint_temp = `${route_school}/cursos`;



        return {
            url: window.location.search,
            errors: [],
            conf_focus: true,
            // base_endpoint: base_endpoint_temp,
            base_endpoint: base_endpoint_temp,
            resourceDefault: {
                name: null,
                description: null,
                position: null,
                imagen: null,
                plantilla_diploma: null,
                file_imagen: null,
                file_plantilla_diploma: null,
                config_id: this.modulo_id,
                categoria_id: this.categoria_id,
                active: true,
                requisito_id: null,
                type_id: null,
                duration: null,
                investment: null,
                nota_aprobatoria: null,
                nro_intentos: null,
                scheduled_restarts_activado: false,
                scheduled_restarts_dias: null,
                scheduled_restarts_horas: null,
                certificate_template_id: null,
                scheduled_restarts_minutos: 1,
                lista_escuelas: [],
                show_certification_date: false,
                qualification_type: {position: 0},
                show_certification_to_user: null,
                user_confirms_certificate: 1,

                activate_at: null,
                deactivate_at: null,
                publish_date_1: null,
                publish_time_1: null,
                publish_date_2: null,
                publish_time_2: null
            },
            resource: {
                qualification_type: {position: 0},
            },
            rules: {
                name: this.getRules(['required', 'max:120']),
                lista_escuelas: this.getRules(['required']),
                types: this.getRules(['required']),
                position: this.getRules(['required', 'number']),
                nota_aprobatoria: this.getRules(['required']),
                nro_intentos: this.getRules(['required', 'number', 'min_value:1']),
                qualification_type_id: this.getRules(['required']),
            },
            selects: {
                requisito_id: [],
                lista_escuelas: [],
                types: [],
                qualification_types: [],
                duration: [
                    { 'id':'0.50', 'name':'0:30' },
                    { 'id':'1.00', 'name':'1:00' },
                    { 'id':'1.50', 'name':'1:30' },
                    { 'id':'2.00', 'name':'2:00' },
                    { 'id':'3.00', 'name':'3:00' },
                    { 'id':'4.00', 'name':'4:00' },
                    { 'id':'5.00', 'name':'5:00' },
                    { 'id':'6.00', 'name':'6:00' },
                ],
            },
            loadingActionBtn: false,
            courseValidationModal: {
                ref: 'CursoValidacionesModal',
                open: false,
                title_modal: 'El curso es pre-requisito',
                type_modal:'requirement',
                content_modal: {
                    requirement: {
                        title: '¡El curso que deseas desactivar es un pre-requisito!'
                    },
                }
            },
            courseValidationModalDefault: {
                ref: 'CursoValidacionesModal',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'CursosValidaciones',
                persistent: false,
                showCloseIcon: true,
                type: null
            },
            deleteConfirmationDialog: {
                open: false,
                title_modal: 'Cambiar de estado del <b>curso</b>',
                type_modal: 'confirm',
                content_modal: {
                    confirm: {
                        title: '¡Estás a punto cambiar la configuración de un curso!',
                        details: [
                            'Los usuarios con histórico se mantendrán con la información y no se recalculará su estado.'
                        ],
                    }
                },
            },
            courseUpdateStatusModal: {
                ref: 'CourseUpdateStatusModal',
                title: 'Actualizar Curso',
                contentText: '¿Desea actualizar este registro?',
                open: false,
                endpoint: '',
                title_modal: 'Cambio de estado de un <b>curso</b>',
                type_modal: 'status',
                status_item_modal: null,
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un curso!',
                        details: [
                            'Los usuarios verán los cambios en su progreso en unos minutos.',
                            'Los usuarios no podrán acceder al curso.',
                            'El diploma del curso no aparecerá para descargar desde el app.',
                            'No podrás ver el curso como opción para la descarga de reportes.',
                            'El detalle del curso activos/inactivos aparecerá en “Notas de usuario”.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un curso!',
                        details: [
                            'Los usuarios verán los cambios en su progreso en unos minutos.',
                            'Los usuarios ahora podrán acceder al curso.',
                            'El diploma del curso ahora aparecerá para descargar desde el app.',
                            'Podrás ver el curso como opción para descargar reportes.'
                        ]
                    }
                },
            },

            modalDateFilter1: {
                open: false
            },

            modalDateFilter2: {
                open: false
            },
            new_value: 0,
            loading_description:false,
            limits_descriptions_generate_ia:{
                ia_descriptions_generated:0,
                limit_descriptions_jarvis:0
            }
        }
    },
    computed: {
        showErrorReinicios() {
            let vue = this
            const reinicio = vue.resource.scheduled_restarts
            const dias = vue.resource.scheduled_restarts_dias
            const horas = vue.resource.scheduled_restarts_horas
            const minutos = vue.resource.scheduled_restarts_minutos
            if (!reinicio) {
                return false
            }
            if (dias > 0 || horas > 0 || minutos > 0) {
                return false
            }
            return true
        },
    },
    watch: {
        'resource.qualification_type': function (newValue, oldValue) {
            let vue = this
            let value = vue.resource.nota_aprobatoria

            if (newValue) {
                if (value && newValue.position && oldValue.position) {

                    vue.new_value = value * newValue.position / oldValue.position
                    vue.resource.nota_aprobatoria = parseFloat(vue.new_value.toFixed(2))
                }
            }

        },
    },
    async mounted() {
        this.showLoader()
        await this.loadData()
        this.hideLoader()

        // if (+this.$props.categoria_id) {
        //     let exists = this.resource
        //         .lista_escuelas
        //         .includes(+this.$props.categoria_id);
        //     if (!exists) {
        //         this.resource.lista_escuelas.push(+this.$props.categoria_id);
        //     }
        // }
        this.loadLimitsGenerateIaDescriptions();
        // if (+this.$props.categoria_id) {
        //     let exists = this.resource
        //         .lista_escuelas
        //         .includes(+this.$props.categoria_id);
        //     if (!exists) {
        //         this.resource.lista_escuelas.push(+this.$props.categoria_id);
        //     }
        // }
    },
    methods: {
        calculateBySystem(val) {
            // console.log(val)
            // let vue = this

            // let value = vue.resource.nota_aprobatoria

            // if (vue.resource.qualification_type.code == 'vigesimal') {
            //     vue.new_value = value * vue.resource.qualification_type.position / 2
            // }

            // if (vue.resource.qualification_type.code == 'centesimal') {
            //     vue.new_value = value * vue.resource.qualification_type.position / 2

            // }

            // return
        },
        alertNotaMinima(){
            let vue = this
            vue.deleteConfirmationDialog.open = true
        },
        confirmDelete(validateForm = true) {
            let vue = this
            vue.deleteConfirmationDialog.open = false
            vue.conf_focus = false
        },
        closeModal() {
            let vue = this

            let params = this.getAllUrlParams(window.location.search);
            let temp = `${this.addParamsToURL(vue.base_endpoint, params)}`;
            temp = `${vue.base_endpoint}?${temp}`;

            // console.log(temp);
            // return;

            window.location.href = temp;
        },
        closeModalStatusEdit(){
            let vue = this
            vue.courseUpdateStatusModal.open = false
            vue.resource.active = !vue.resource.active
        },
        modalStatusEdit(){
            let vue = this
            const edit = vue.curso_id !== ''
            if(edit){
                vue.courseUpdateStatusModal.open = true
                vue.courseUpdateStatusModal.status_item_modal = !vue.resource.active
            }
        },
        confirmModal(validateForm = true) {


          // Get datetimes values
          if (this.resource.publish_date_1) {
            let time1 = this.resource.publish_time_1 || '00:01';
            this.resource.activate_at = `${this.resource.publish_date_1} ${time1}`
          } else {
            this.resource.activate_at = null
          }

          if (this.resource.publish_date_2) {
            let time2 = this.resource.publish_time_2 || '00:01';
            this.resource.deactivate_at = `${this.resource.publish_date_2} ${time2}`
          } else {
            this.resource.deactivate_at = null
          }

          let vue = this
            vue.errors = []
            vue.loadingActionBtn = true
            vue.showLoader()
            const validForm = vue.validateForm('CursoForm')

            if (!validForm || !vue.isValid()) {
                this.hideLoader()
                vue.loadingActionBtn = false
                return
            }

            if (vue.courseValidationModal.action === 'validations-after-update') {
                vue.hideLoader();
                vue.courseValidationModal.open = false;
                setTimeout(() => vue.closeModal(), 10000);
                return;
            }

            const edit = vue.curso_id !== ''
            let url = `${vue.base_endpoint}/${edit ? `update/${vue.curso_id}` : 'store'}`
            let method = edit ? 'PUT' : 'POST';

            const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
            formData.append('validateForm', validateForm ? "1" : "0");
            vue.setJSONReinicioProgramado(formData)
            vue.getJSONEvaluaciones(formData)

            vue.$http.post(url, formData)
                .then(async ({data}) => {
                    this.hideLoader()
                    const has_info_messages = data.data.messages.list.length > 0
                    if (has_info_messages)
                        await vue.handleValidationsAfterUpdate(data.data, vue.courseValidationModal, vue.courseValidationModalDefault);
                    else {
                        vue.queryStatus("curso", "crear_curso");
                        vue.showAlert(data.data.msg)
                        setTimeout(() => vue.closeModal(), 2000)
                    }
                })
                .catch(error => {
                    if (error && error.errors){
                        vue.errors = error.errors

                        if(error.data.validations.list){
                            error.data.validations.list.forEach(element => {
                                if(element.type == "has_active_topics" && error.data.validations.list.length == 1){
                                    vue.courseValidationModal.title_modal = 'Cambio de estado de un <b>curso</b>';
                                    vue.courseValidationModal.content_modal.requirement.title = '¡Estás por desactivar un curso!';
                                }
                            });
                        }
                    }
                    vue.handleValidationsBeforeUpdate(error, vue.courseValidationModal, vue.courseValidationModalDefault);
                    vue.loadingActionBtn = false
                })
        },
        setJSONReinicioProgramado(formData) {
            let vue = this
            const minutes = parseInt(vue.resource.scheduled_restarts_minutos) +
                (parseInt(vue.resource.scheduled_restarts_horas) * 60) +
                (parseInt(vue.resource.scheduled_restarts_dias) * 1440)
            const data = {
                activado: vue.resource.scheduled_restarts_activado,
                tiempo_en_minutos: minutes,
                reinicio_dias: vue.resource.scheduled_restarts_dias,
                reinicio_horas: vue.resource.scheduled_restarts_horas,
                reinicio_minutos: vue.resource.scheduled_restarts_minutos,
            }
            let json = JSON.stringify(data)
            formData.append('reinicios_programado', json)
        },
        getJSONEvaluaciones(formData) {
            let vue = this

            const data = {
                // preg_x_ev: vue.resource.preg_x_ev,
                nota_aprobatoria: vue.resource.nota_aprobatoria,
                nro_intentos: vue.resource.nro_intentos,
            }
            let json = JSON.stringify(data)
            formData.append('mod_evaluaciones', json)
        },
        async loadData() {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.base_endpoint}/${vue.curso_id === '' ? 'form-selects' : `search/${vue.curso_id}`}`
            await vue.$http.get(url)
                .then(({data}) => {
                    let response = data.data ? data.data : data;

                    vue.selects.requisito_id = response.requisitos
                    vue.selects.qualification_types = response.qualification_types
                    vue.selects.lista_escuelas = response.escuelas
                    vue.selects.types = response.types
                    if (vue.curso_id !== '') {
                        response.curso.nota_aprobatoria = response.curso.mod_evaluaciones.nota_aprobatoria;
                        response.curso.nro_intentos = response.curso.mod_evaluaciones.nro_intentos;

                        vue.resource = Object.assign({}, response.curso)


                      // Set schedule datetime

                      if (response.curso.activate_at) {
                        vue.resource.publish_date_1 = response.curso.activate_at.substring(0, 10);
                        vue.resource.publish_time_1 = response.curso.activate_at.substring(11, 16);
                      }

                      if (response.curso.deactivate_at) {
                        vue.resource.publish_date_2 = response.curso.deactivate_at.substring(0, 10);
                        vue.resource.publish_time_2 = response.curso.deactivate_at.substring(11, 16);
                      }

                    } else {
                        vue.resource.qualification_type = response.qualification_type
                    }
                })
            return 0;
        },
        isValid() {

            let valid = true;
            let errors = [];

            // Validation: At least one school should be selected

            if (this.resource.lista_escuelas.length === 0) {
                errors.push({
                    message: 'Debe seleccionar una escuela'
                })
                valid = false;
            }

            if (!valid) {
                this.errors = errors;
            }

            return valid;
        },
        async generateIaDescription(){
            const vue = this;
            let url = `/jarvis/generate-description-jarvis` ;
            if(vue.loading_description || !vue.resource.name){
                const message = vue.loading_description ? 'Se está generando la descripción, espere un momento' : 'Es necesario colocar un nombre al curso para poder generar la descripción';
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
                type:'course'
            }).then(({data})=>{
                vue.limits_descriptions_generate_ia.ia_descriptions_generated +=1;
                let characters = data.data.description.split('');
                vue.resource.description = ''; // Limpiar el contenido anterior
                function updateDescription(index) {
                    if (index < characters.length) {
                        vue.resource.description += characters[index];
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
        async loadLimitsGenerateIaDescriptions(){
            await axios.get('/jarvis/limits?type=descriptions').then(({data})=>{
                this.limits_descriptions_generate_ia = data.data;
            })
        }
    }
}
</script>
<style lang="scss">
@import "resources/sass/variables";

.time-input .v-input__slot {
  min-height: 40px !important;
}

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
.sep-left {
    position: relative;
}
.sep-left:before {
    border-left: 1px solid #D9D9D9;
    position: absolute;
    left: 0;
    content: '';
    top: 25px;
    bottom: 25px;
}
</style>
