

<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        @onCancelSteps="prevStep"
        :steps="true"
        content-class="br-dialog"
    >
        <template v-slot:content>
            <v-form ref="projectForm">

                <v-stepper non-linear class="stepper_box" v-model="stepper_box">
                    <v-stepper-items>
                        <v-stepper-content step="1" class="p-0">
                            <v-row>
                                <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                    <span class="text_default lbl_tit">Indica la información que tendrá el checklist</span>
                                </v-col>
                                <v-col cols="12" md="12" lg="12" class="pb-0">
                                    <DefaultInput
                                        clearable
                                        v-model="resource.title"
                                        label="Título"
                                        :dense="true"
                                        :rules="rules.title"
                                        show-required
                                    />
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="8" md="8" lg="8" class="pb-0">
                                    <v-textarea
                                        rows="2"
                                        outlined
                                        dense
                                        hide-details="auto"
                                        label="Descripción"
                                        v-model="resource.description"
                                        class="txt_desc"
                                    ></v-textarea>
                                </v-col>
                                <v-col cols="4" md="4" lg="4">
                                    <v-row>
                                        <v-col cols="12" class="pb-0 pt-0">
                                            <span class="text_default">Configuraciones</span>
                                        </v-col>
                                        <v-col cols="12" class="py-0 separated">
                                            <DefaultDivider class="divider_light"/>
                                        </v-col>
                                        <v-col cols="12" class="check_active">
                                            <DefaultToggle v-model="resource.active" dense/>
                                        </v-col>
                                        <v-col cols="12" class="pb-0 pt-0">
                                            <span class="text_default">Avanzado</span>
                                        </v-col>
                                        <v-col cols="12" class="py-0 separated">
                                            <DefaultDivider class="divider_light"/>
                                        </v-col>
                                        <v-col cols="12" class="bx_type_checklist">
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
                                        <v-col cols="12" class="check_active">
                                            <DefaultToggle v-model="resource.qualified" activeLabel="Calificable" inactiveLabel="Calificable" dense/>
                                        </v-col>
                                        <v-col cols="12" class="check_active">
                                            <DefaultToggle v-model="resource.required" activeLabel="Obligatorio" inactiveLabel="Obligatorio" dense/>
                                        </v-col>
                                    </v-row>
                                </v-col>

                            </v-row>
                        </v-stepper-content>
                        <v-stepper-content step="2" class="p-0">
                            <v-row>
                                <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                    <span class="text_default lbl_tit">Indica la información que tendrá el checklist</span>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" md="12" lg="12" class="pb-0">
                                    <span class="text_default">Actividades</span>
                                </v-col>
                                <v-col cols="12" class="py-0 separated">
                                    <DefaultDivider class="divider_light"/>
                                </v-col>
                                <v-col cols="12" md="12" lg="12" class="text-center pt-0">
                                    <div class="text_default box_info_checklist_1 mt-3">
                                        Tener en cuenta que al agregar o quitar actividades a un checklist completado por un usuario no tendrá efectos en su avance. Si un usuario ya completó un checklist se mantiene su estado y porcentaje, pero sí se actualiza para usuarios que aún no completan el checklist.
                                    </div>
                                </v-col>
                                <v-col cols="12" md="12" lg="12" class="d-flex justify-content-center">
                                    <v-btn color="primary" outlined @click="addActividad">
                                        <v-icon class="icon_size">mdi-plus</v-icon>
                                        Agregar Actividad
                                    </v-btn>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" md="12" lg="12">
                                        <draggable v-model="resource.checklist_actividades" @start="drag=true"
                                                @end="drag=false" class="custom-draggable" ghost-class="ghost">
                                            <transition-group type="transition" name="flip-list" tag="div">
                                                <div v-for="(actividad, i) in resource.checklist_actividades"
                                                    :key="actividad.id">
                                                    <div class="item-draggable activities">
                                                        <v-row>
                                                            <v-col cols="1" class="d-flex align-center justify-content-center ">
                                                                <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                                </v-icon>
                                                            </v-col>
                                                            <v-col cols="12" md="5" lg="5">
                                                                <v-textarea
                                                                    rows="1"
                                                                    outlined
                                                                    dense
                                                                    auto-grow
                                                                    hide-details="auto"
                                                                    v-model="actividad.activity"
                                                                    :class="{'border-error': actividad.hasErrors}"
                                                                ></v-textarea>
                                                            </v-col>
                                                            <v-col cols="12" md="3" lg="3" class="d-flex align-center no-white-space">
                                                                <v-select
                                                                    outlined
                                                                    dense
                                                                    hide-details="auto"
                                                                    attach
                                                                    :items="tipo_actividades"
                                                                    v-model="actividad.type_name"
                                                                    item-text="text"
                                                                    item-value="value"
                                                                >
                                                                </v-select>
                                                            </v-col>
                                                            <v-col cols="12" md="2" lg="2" class="d-flex align-center toggle_text_default">
                                                                <DefaultToggle v-model="actividad.active"/>
                                                            </v-col>
                                                            <v-col cols="1" class="d-flex align-center">
                                                                <v-icon class="ml-0 mr-2 icon_size" color="black"
                                                                        @click="eliminarActividad(actividad, i)">
                                                                    mdi-delete
                                                                </v-icon>
                                                            </v-col>
                                                        </v-row>
                                                    </div>
                                                </div>
                                            </transition-group>
                                        </draggable>
                                </v-col>
                            </v-row>
                        </v-stepper-content>
                    </v-stepper-items>
                    <v-stepper-header class="stepper_dots">
                        <v-stepper-step step="1" :complete="stepper_box > 1">
                            <v-divider></v-divider>
                        </v-stepper-step>
                        <v-stepper-step step="2">
                        </v-stepper-step>
                    </v-stepper-header>
                </v-stepper>

                <DefaultAlertDialog
                    :ref="modalAlert.ref"
                    :options="modalAlert"
                    :confirmLabel="modalAlert.confirmLabel"
                    :hideCancelBtn="modalAlert.hideCancelBtn"
                    @onConfirm="modalAlert.open=false"
                    @onCancel="modalAlert.open=false"
                >
                    <template v-slot:content> {{ modalAlert.contentText }}</template>
                </DefaultAlertDialog>
            </v-form>
        </template>
    </DefaultDialog>
</template>


<script>
const fields = ['title', 'description', 'requirement', 'model_id', 'model_type', 'qualified', 'active', 'required', 'checklist_actividades' ];

import draggable from 'vuedraggable'

export default {
    components: {
    draggable,
},
    props: {
        value: Boolean,
        width: String,
        checklist: Object,
        limitOne: {
            type:Boolean,
            default:false
        },
        tabs_title:{
            type: String,
            default: 'Segmentación'
        },
        options: {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            disabled_btn_next: true,
            stepper_box_btn1: true,
            stepper_box_btn2: true,
            stepper_box_btn3: false,
            stepper_box: 1,
            cancelLabel: "Cancelar",
            list_segments:[],
            sections: {
                showAdvancedOptions: false
            },
            modalDateStart: {
                open: false,
            },
            modalDateEnd:{
                open: false,
            },
            drag: false,
            expand_cursos: true,
            actividades_expanded: [],
            tipo_actividades: [
                {
                    text: "Se califica al alumno",
                    value: "trainer_user"
                },
                {
                    text: "Se califica al entrenador",
                    value: "user_trainer"
                }
            ],
            dialog: false,
            file: null,
            search_text: null,
            results_search: [],
            disabled_add_courses: false,
            isLoading: false,
            messageActividadesEmpty: false,
            formRules: {
                titulo_descripcion: [
                    v => !!v || 'Campo requerido',
                    v => (v && v.length <= 280) || 'Máximo 280 caracteres.',
                ],
                actividad: [
                    v => !!v || 'Campo requerido',
                ],
            },
            modalAlert: {
                ref: 'modalAlert',
                title: 'Alerta',
                contentText: 'Este checklist debe de tener por lo menos una (1) actividad "Se califica al alumno"',
                open: false,
                endpoint: '',
                confirmLabel:"Entendido",
                hideCancelBtn:true,
            },
            selects: {
                type_checklist: [
                    {"id": "none", "name": "Sin requisito"},
                ],
                requirement_list: [{'code':'none', 'name': 'Sin requisito'}],
            },
            type_checklist: "none",
            tabs: null,
            steps: 0,
            loadStep2: false,
            // total: 0,
            total: [],

            errors: [],
            showConfigTokens: false,
            resourceDefault: {
                id: null,
                title: null,
                active: false,
                qualified: false,
                required: false,
                description: null,
                requirement: null,
                checklist_actividades: []
            },
            resource: {
            },
            segments: [{
                id: `new-segment-${Date.now()}`,
                type_code: '',
                criteria_selected: []
            }],
            segment_by_document: {
                id: `new-segment-${Date.now()}`,
                type_code: '',
                criteria_selected: []
            },
            criteria: [],
            courseModules: [],
            modulesSchools: [],

            modalInfoOptions: {
                ref: 'SegmentAlertModal',
                open: false,
                title: null,
                resource:'data',
                hideConfirmBtn: true,
                persistent: true,
                cancelLabel: 'Entendido'
            },
            stackModals: { continues: [],
                backers: [] },
            criteriaIndexModal: 0,

            segment_by_document_clean: false,
            rules: {
                title: this.getRules(['required', 'max:255']),
            },
            // data segmenteacion
        };
    },
    computed: {
        actividadesEmpty() {
            return this.resource.checklist_actividades && this.resource.checklist_actividades.length === 0
        }
    },
    async mounted() {
    },
    watch: {
        resource: {
            handler(n, o) {
                let vue = this;
                const validateInputs = vue.validateRequired(vue.resource.title)
                vue.options.confirmDisabled = !validateInputs
            },
            deep: true
        }
    },
    methods: {

        async loadSelects()
        {
        },
        async loadData(resource) {
            let vue = this

            vue.options.confirmDisabled = true

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
                vue.resource.media = []
            })

            let url = `${vue.options.base_endpoint}/${ resource ? `edit/${resource.id}` : 'form-selects'}`
            
            await vue.$http.get(url)
                .then(({data}) => {

                    let _data = data.data
                    vue.selects.requirement_list = _data.requirements

                    if (resource) {
                        vue.resource = Object.assign({}, vue.resource, _data.checklist)
                        vue.resource.activity_id = resource.id
                        vue.resource.requirement = _data.activity.activity_requirement_id
                        vue.resource.qualified = _data.activity.qualified
                        vue.resource.required = _data.activity.required
                    }
                })
                const validateInputs = vue.validateRequired(vue.resource.title)
                vue.options.confirmDisabled = !validateInputs
            return 0;
        },
        validateRequired(input) {
            return input != undefined && input != null && input != "";
        },
        prevStep()
        {
            let vue = this;
            vue.options.cancelLabel = "Cancelar";
            vue.options.confirmLabel = "Continuar";

            if(vue.stepper_box == 2){
                vue.stepper_box = 1;
                vue.loadStep2 = false
            }
            else if(vue.stepper_box == 1){
                vue.closeModal()
            }
        },
        setActividadesHasErrorProp() {
            let vue = this
            if (vue.resource.checklist_actividades) {
                vue.resource.checklist_actividades.forEach(el => {
                    el = Object.assign({}, el, {hasErrors: false})
                })
            }
        },
        closeModal() {
            let vue = this

            vue.stepper_box = 1
            vue.options.cancelLabel = "Cancelar";
            vue.options.confirmLabel = "Continuar";
            vue.loadStep2 = false

            vue.resetSelects()
            vue.resetValidation()
            vue.$refs.projectForm.reset()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.$refs.projectForm.resetValidation()
        },
        resetSelects() {
            let vue = this
        },
        async confirmModal()
        {
            let vue = this

            vue.options.cancelLabel = "Retroceder";
            vue.options.confirmLabel = "Guardar";

            if(vue.stepper_box == 1)
            {
                vue.stepper_box = 2;
                vue.loadStep2 = true
            }
            else if(vue.stepper_box == 2)
            {
                vue.errors = []

                this.showLoader()

                const validateForm = vue.validateForm('projectForm')
                const edit = vue.options.action === 'edit'

                let base = `${vue.options.base_endpoint}`
                let url = edit
                    ? `${base}/${vue.resource.activity_id}/update`
                    : `${base}/store`;

                let method = 'POST';

                if (validateForm) {
                    const formData = vue.createFormData(method, validateForm);
                    await vue.$http
                        .post(url, formData)
                        .then(({ data }) => {
console.log(data);

                            this.hideLoader()
                            vue.closeModal()
                            vue.$emit("onConfirm");

                        }).catch((error) => {
                            if (error && error.errors) {
                                const errors = error.errors ? error.errors : error;
                                vue.show_http_errors(errors);
                                this.hideLoader()
                            }
                        })
                }
                else
                {
                    this.hideLoader()
                }
            }
        },
        createFormData(method, validateForm) {
            let vue = this;

            vue.resource.model_id = vue.options.model_id;
            vue.resource.model_type = 'App\\Models\\Stage';


            let formData = vue.getMultipartFormData(method, vue.resource, fields);
            formData.append('validate', validateForm ? "1" : "0");


            let checklist_actividades = JSON.stringify(vue.resource.checklist_actividades)
            formData.append('checklist_actividades', checklist_actividades)

            return formData;
        },
        showValidateActividades() {
            let vue = this
            let errors = 0;

            if( vue.resource.checklist_actividades.length > 0 ) {
                vue.resource.checklist_actividades.forEach((el, index) => {
                    el.hasErrors = !el.activity || el.activity === ''
                    if(el.hasErrors) errors++;
                })
            }
            return errors;
        },
        moreValidaciones() {
            let vue = this
            let errors = 0

            let hasActividadEntrenadorUsuario = false;
            vue.resource.checklist_actividades.map(actividad=>{
               if( actividad.type_name=='trainer_user') hasActividadEntrenadorUsuario=true;
            });
            if(!hasActividadEntrenadorUsuario){
                this.modalAlert.open= true;
               errors++
            }
            return errors > 0
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        eliminarActividad(actividad, index) {
            let vue = this;
            if (String(actividad.id).charAt(0).includes('n')) {
                vue.actividades_expanded = []
                vue.resource.checklist_actividades.splice(index, 1);
                return
            }
            axios
                .post(`/entrenamiento/checklists/delete_actividad_by_id`, actividad)
                .then((res) => {
                    if (res.data.error) {
                        vue.$notification.warning(`${res.data.msg}`, {
                            timer: 6,
                            showLeftIcn: false,
                            showCloseIcn: true
                        });
                    } else {
                        vue.actividades_expanded = []
                        vue.resource.checklist_actividades.splice(index, 1);
                        vue.$notification.success(`${res.data.msg}`, {
                            timer: 6,
                            showLeftIcn: false,
                            showCloseIcn: true
                        });
                    }
                })
                .catch((err) => {
                    console.err(err);
                });
        },
        addActividad() {
            let vue = this;
            const newID = `n-${vue.resource.checklist_actividades.length + 1}`;
            const newActividad = {
                id: newID,
                activity: "",
                active: 1,
                type_name: "trainer_user",
                checklist_id: vue.resource.id,
                hasErrors: false,
                is_default:false
            };
            vue.resource.checklist_actividades.unshift(newActividad);
        },
        search() {
            clearTimeout(this.timeout);
            let vue = this;
            if (vue.search_text == null || vue.search_text === "") return;
            if (vue.isLoading) return;
            this.timeout = setTimeout(function () {
                vue.isLoading = true;
                const data = {
                    filtro: vue.search_text,
                };
                axios
                    .post(`/entrenamiento/checklists/buscar_curso`, data)
                    .then((res) => {
                        console.log(vue.results_search);
                        vue.results_search = res.data.data;
                        // vue.$nextTick(() => {
                        //     vue.$refs.resultSearch.focus()
                        //     vue.$refs.resultSearch.isMenuActive = true
                        //     vue.$refs.resultSearch.isMenuActive = true;
                        // })
                        setTimeout(() => {
                            vue.isLoading = false;
                        }, 1500);
                    })
                    .catch((err) => {
                        console.log(err);
                        vue.isLoading = false;
                    });
            }, 1000);
        },
        removeCurso(curso, index) {
            let vue = this;
            vue.results_search.push(curso)
            vue.resource.courses.splice(index, 1)
            if(vue.resource.courses.length >= 2) {
                vue.disabled_add_courses = true;
            } else {
                vue.disabled_add_courses = false;
            }

        },
        seleccionarCurso(curso, index) {
            let vue = this;
            vue.resource.courses.push(curso)
            vue.results_search.splice(index, 1)
            if(vue.resource.courses.length >= 2) {
                vue.disabled_add_courses = true;
            } else {
                vue.disabled_add_courses = false;
            }
        },
        getNewSegment(type_code) {
            return {
                id: `new-segment-${Date.now()}`,
                type_code,
                criteria_selected: []
            };
        },
        async addSegmentation(type_code) {
            let vue = this;
            vue.segments.push(this.getNewSegment(type_code));

            vue.steps = vue.segments.length - 1;
        },
        borrarBloque(segment) {
            let vue = this;
            // const isNewSegment = segment.id.search("new") !== -1;
            // if (vue.segments.length === 1 && !isNewSegment) return;

            vue.segments = vue.segments.filter((obj, idx) => {
                return obj.id != segment.id;
            });
        },
        isCourseSegmentation() {
            return this.model_type === 'App\\Models\\Course'
        },
        async changeTypeChecklist() {

            let vue = this;

            console.log(vue.resource.type_checklist);
            console.log(vue.type_checklist);
            vue.type_checklist = vue.resource.type_checklist;
        },
    }
};
</script>

<style>
.list-cursos-carreras {
    width: 500px;
    white-space: unset;
}

.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}

.flip-list-move {
    transition: transform 0.5s;
}

.no-move {
    transition: transform 0s;
}

.txt-white-bold {
    color: white !important;
    font-weight: bold !important;
}

.v-icon.v-icon.v-icon--link {
    color: #1976d2;
}

.icon_size .v-icon.v-icon {
    font-size: 31px !important;
}

.lista-boticas {
    list-style-type: disc;
    -webkit-columns: 3;
    -moz-columns: 3;
    columns: 3;
    list-style-position: inside;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity .5s;
}

.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */
{
    opacity: 0;
}

.custom-draggable, .custom-draggable span {
    width: 100%;
}

.v-expansion-panel-header {
    padding: 0.7rem !important;
}
.bx_steps .v-text-field__slot label.v-label,
.bx_steps .v-select__slot label.v-label,
.text_default {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 13px;
}
.box_info_checklist_1 {
    text-align: justify;
}
.v-stepper__header.stepper_dots {
    justify-content: center;
    height: initial;
}
.v-stepper__header.stepper_dots .v-divider {
    margin: 0;
    flex: auto;
    border-color: #5458ea !important;
    border-width: 2px;
    width: 30px;
    min-width: 30px;
    max-width: 30px;
}
.v-stepper__header.stepper_dots .v-stepper__step {
    padding: 10px 0;
    margin: 0;
}
.v-stepper__header.stepper_dots .v-stepper__step span.v-stepper__step__step {
    margin: 0;
}
.v-stepper__header.stepper_dots .v-stepper__step:hover {
    background: none;
}
.v-stepper.stepper_box {
    box-shadow: none;
}
.txt_desc textarea {
    min-height: 280px;
}
.v-tab span.title_sub {
    font-size: 16px;
    color: #B9E0E9;
    display: flex;
    justify-content: center;
    margin: 12px 0;
    font-family: "Nunito", sans-serif;
    font-weight: 400;
    position: relative;
    text-transform: initial;
    letter-spacing: 0.1px;
}
.v-tab.v-tab--active span.title_sub,
span.title_sub {
    font-size: 16px;
    color: #5458EA;
    display: flex;
    justify-content: center;
    margin: 12px 0;
    font-family: "Nunito", sans-serif;
    font-weight: 700;
    position: relative;
    text-transform: initial;
    letter-spacing: 0.1px;
}
.v-tab.v-tab--active span.title_sub:after,
span.title_sub:after {
    content: '';
    border-bottom: 2px solid #5458EA;
    width: 112px;
    position: absolute;
    bottom: -2px;
}
.v-tab span.title_sub:after  {
    content: none;
}
button.btn_secondary {
    background: none !important;
    border: none;
    box-shadow: none;
}
button.btn_secondary span.v-btn__content {
    color: #5458EA;
    font-weight: 700;
    font-size: 12px;
    font-family: "Nunito", sans-serif;
}
button.btn_secondary span.v-btn__content i{
    font-size: 14px;
    line-height: 1;
}
.divider_light{
    border-color: #94DDDB !important;
}
.item-draggable.activities {
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
    margin: 10px 0;
}
.item-draggable.activities textarea,
.no-white-space .v-select__selection--comma,
.item-draggable.activities .toggle_text_default label.v-label {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 12px;
}
.no-white-space .v-select__selection--comma {
    white-space: initial;
    line-height: 13px;
    font-size: 13px;
}
.item-draggable.activities textarea {
    font-size: 13px;
}
.no-white-space .v-input__append-inner .v-input__icon {
    padding: 0;
}
.item-draggable.activities .default-toggle.default-toggle.v-input--selection-controls {
    margin-top: initial !important;
}
.box_resultados,
.box_seleccionados {
    height: 130px;
    overflow-y: auto;
    border-radius: 8px;
    border: 1px solid #D9D9D9;
    padding: 10px 0;
}
.box_resultados .v-btn:not(.v-btn--text):not(.v-btn--outlined):hover:before,
.box_seleccionados .v-btn:not(.v-btn--text):not(.v-btn--outlined):hover:before {
    opacity: 0;
}
.bx_message {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}
span.v-stepper__step__step {
    color: #5458ea !important;
    background-color: #5458ea !important;
    border-color: #5458ea !important;
    position: relative;
}
span.v-stepper__step__step:before {
    content: '';
    background: #fff;
    height: 17px;
    width: 17px;
    left: 50%;
    top: 50%;
    position: absolute;
    transform: translate(-50%, -50%);
    border-radius: 50%;
}
span.v-stepper__step__step:after {
    content: '';
    background-color: #5458ea;
    height: 6px;
    width: 6px;
    left: 50%;
    top: 50%;
    position: absolute;
    transform: translate(-50%, -50%);
    border-radius: 50%;
}
.v-stepper__step.v-stepper__step--inactive span.v-stepper__step__step,
.v-stepper__step.v-stepper__step--inactive span.v-stepper__step__step:after,
.v-stepper__step.v-stepper__step--inactive .v-divider {
    color: #E4E4E4 !important;
    background-color: #E4E4E4 !important;
    border-color: #E4E4E4 !important;
}
.bx_type_checklist .v-input__icon.v-input__icon--append,
.bx_steps .v-input__icon.v-input__icon--append {
    margin: 0;
    padding: 0;
}
.bx_steps .v-input__slot {
    min-height: 40px !important;
}
.bx_steps .v-text-field--outlined .v-label {
    top: 10px;
}
.bx_steps .v-btn--icon.v-size--default {
    height: 22px;
    width: 22px;
}
.bx_steps .v-select__slot,
.v-dialog.v-dialog--active .bx_steps .v-select--is-multi.v-autocomplete .v-select__slot {
    padding: 0 !important;
}
.bx_steps .v-text-field__details {
    display: none;
}
.bx_step1 .default-toggle {
    margin-top: 3px !important;
}
.bx_step1 .default-toggle .v-input__slot label.v-label {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 13px;
}
.v-stepper__step.v-stepper__step--complete span.v-stepper__step__step:before,
.v-stepper__step.v-stepper__step--complete span.v-stepper__step__step:after {
    content: initial;
}
.bx_steps .v-text-field--enclosed.v-input--dense:not(.v-text-field--solo).v-text-field--outlined .v-input__append-inner {
    margin-top: 10px !important;
}
.v-card__actions.actions_btn_modal button.default-modal-action-button.btn_back.v-btn.v-btn--flat span.v-btn__content {
    color: #5458ea;
}
.v-menu.bx_calendar_top .v-menu__content {
    bottom: 0px;
    top: initial !important;
    right: calc(100% - 10px);
    left: initial !important;
    transform-origin: right bottom !important;
}
.bx_seg .v-select__slot .v-input__append-inner,
.bx_steps .bx_seg .v-text-field--enclosed.v-input--dense:not(.v-text-field--solo).v-text-field--outlined .v-input__append-inner {
    margin-top: 6px !important;
}
.border-error .v-input__slot fieldset {
    border-color: #FF5252 !important;
}
</style>
