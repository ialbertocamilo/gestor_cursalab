<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        @onCancelSteps="prevStep"
        :persistent="true"
        :steps="true"
        content-class="br-dialog dialog_segment_process"
    >
        <template v-slot:content>
            <div>
                <v-form ref="segmentForm" class="---mb-6">
                    <DefaultErrors :errors="errors"/>
                    <v-stepper non-linear class="stepper_box" v-model="stepper_box">
                        <v-stepper-items>
                            <v-stepper-content step="1" class="p-0">
                                <v-tabs
                                    v-model="tabs"
                                    fixed-tabs
                                    slider-color="primary"

                                    class="col-10 offset-1"
                                >
                                    <v-tab>
                                        {{ tabs_title }} Directa
                                    </v-tab>
                                    <v-tab>
                                        {{ tabs_title }} por Documento
                                    </v-tab>
                                </v-tabs>

                                <v-tabs-items v-model="tabs">

                                    <v-tab-item>

                                        <v-row justify="space-around" v-if="!limitOne">
                                            <v-col cols="10" class="d-flex justify-content-end">
                                                <v-btn
                                                    class="--add-button"
                                                    color="primary"
                                                    @click="addSegmentation('direct-segmentation')"
                                                >
                                                    <v-icon class="" v-text="'mdi-plus'"/>
                                                    Segmento
                                                </v-btn>
                                            </v-col>
                                        </v-row>

                                        <v-row justify="space-around">
                                            <v-col cols="11" class="d-flex justify-content-center">
                                                <!-- hide-delimiter-background -->
                                                <v-carousel
                                                    height="100%"
                                                    show-arrows-on-hover
                                                    light
                                                    v-model="steps"
                                                    hide-delimiters
                                                    class="---mb-6"
                                                >
                                                    <v-carousel-item
                                                        v-for="(row, i) in segments"
                                                        :key="i"
                                                    >
                                                        <v-sheet class="group-sheet" height="100%">
                                                            <div class="text-h6 text-center"  v-if="!limitOne">
                                                                {{ tabs_title }} {{ i + 1 }} /
                                                                {{ segments.length }}
                                                            </div>

                                                            <v-divider class="mx-12"/>

                                                            <segment
                                                                :is-course-segmentation="isCourseSegmentation()"
                                                                :segments="segments"
                                                                :segment="row"
                                                                :criteria="criteria"
                                                                :course-modules="courseModules"
                                                                class="mx-5"
                                                                :options="options"
                                                                @borrar_segment="borrarBloque"
                                                            />

                                                            <!-- <v-divider class="mx-12"/>

                                                            <p class="text-center">Usuarios alcanzados: <strong>{{ total[i] || 0 }}</strong></p> -->

                                                        </v-sheet>
                                                    </v-carousel-item>
                                                </v-carousel>
                                            </v-col>
                                        </v-row>

                                    </v-tab-item>

                                    <v-tab-item>

                                        <SegmentByDocument
                                            ref="SegmentByDocument"
                                            :segment="segment_by_document"
                                            :modules-ids="modulesIds"
                                            :current-clean="segment_by_document_clean"
                                            @addUser="addUser"
                                            @deleteUser="deleteUser"
                                        />

                                    </v-tab-item>
                                </v-tabs-items>

                                <SegmentAlertModal
                                    :options="modalInfoOptions"
                                    :ref="modalInfoOptions.ref"
                                    @onConfirm="closeFormModal(modalInfoOptions)"
                                    @onCancel="closeFormModal(modalInfoOptions)"
                                />
                            </v-stepper-content>
                            <v-stepper-content step="2" class="p-0">
                                <v-row justify="space-around">
                                    <v-col cols="11">
                                        <span class="title_sub">Criterios de vinculación</span>
                                        <span class="text_default lbl_tit">Define los criterios que se aplicaran para hacer la relación entre los supervisores y los colaboradores.</span>
                                    </v-col>
                                    <v-col cols="11" v-if="stepper_box == 2">
                                        <DefaultAutocompleteOrder
                                            dense
                                            label="Criterios"
                                            v-model="list_criteria_selected"
                                            :items="list_criteria"
                                            multiple
                                            item-text="name"
                                            item-id="id"
                                            return-object
                                            :count-show-values="4"
                                            :showSelectAll="true"
                                            :loading-state="true"
                                            placeholder="Indicar criterios aquí"
                                        />
                                        <div class="bx_criteria_selected">
                                            <div class="d-flex align-items-center justify-content-center" style="height: 100%;" v-if="list_criteria_selected == 0">
                                                <span class="text_default text-center">Aquí se listaran tus criterios<br>de vinculación a tus<br>administadores</span>
                                            </div>
                                            <div v-else>
                                                <span v-for="(itemc, indexc) in list_criteria_selected " :key="indexc">
                                                    <v-chip
                                                    class="ma-2"
                                                    close
                                                    close-icon="mdi-minus-circle"
                                                    @click:close="list_criteria_selected.splice(indexc, 1)"
                                                    >
                                                    {{ itemc.name }}
                                                    </v-chip>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-center mt-3">
                                            <span class="text_default c-default fw-bold cursor-pointer" @click="openModalUserSupervisors()">Ver listado de los supervisores</span>
                                        </div>
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
                </v-form>

                <ModalUsersSupervisors
                    :ref="modalUserSupervisors.ref"
                    v-model="modalUserSupervisors.open"
                    width="650px"
                    :list_supervisors="modalUserSupervisors.supervisors"
                    @onCancel="modalUserSupervisors.open = false"
                />
            </div>
        </template>
    </DefaultDialog>

</template>

<script>
const fields = [
    "name",
    "email",
    "username",
    "password",
    "key",
    "secret",
    "token",
    "active"
];

const CustomMessages = {
    module: {
        title: '¡Ups! Olvidaste seleccionar un módulo',
        noexist: 'Recuerda que cuando segmentas por criterios, es necesario que selecciones mínimo un módulo.',
        nodata: 'Selecciona uno o varios modulos'
    }
}

import Segment from "../Blocks/Segment";
import SegmentAlertModal from "../Blocks/SegmentAlertModal";
import SegmentByDocument from "../Blocks/SegmentByDocument";
import ModalUsersSupervisors from "./ModalUsersSupervisors";

export default {
    components: {
    Segment,
    SegmentAlertModal,
    SegmentByDocument,
    ModalUsersSupervisors
},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String,
        model_type: String,
        model_id: Number,
        code: {
            type: String,
            default: null
        },
        tabs_title:{
            type: String,
            default: 'Segmentación'
        },
        limitOne: {
            type:Boolean,
            default:false
        },
        for_section: {
            type: String,
            default: null
        }
    },
    data() {
        return {

            modalUserSupervisors: {
                ref: 'ModalUsersSupervisors',
                open: false,
                endpoint: '',
            },
            list_criteria: [],
            list_criteria_selected: [],
            // steps
                step_title: '',
                disabled_btn_next: true,
                stepper_box_btn1: true,
                stepper_box_btn2: true,
                stepper_box_btn3: false,
                stepper_box_btn4: false,
                stepper_box: 1,
                cancelLabel: "Cancelar",

            // steps
            tabs: null,
            steps: 0,
            // total: 0,
            total: [],

            errors: [],
            showConfigTokens: false,
            resourceDefault: {
                id: null,
                name: null
            },
            // resource: {},
            segments: [],
            segment_by_document: null,
            criteria: [],
            courseModules: [],
            modulesSchools: [],
            modulesIds: [],

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
                // name: this.getRules(['required', 'max:255']),
            }
        };
    },
    methods: {

        async openModalUserSupervisors() {
            let vue = this

            let url = 'procesos/supervisors_users'
            let formData = JSON.stringify({
                        model_type: vue.model_type,
                        model_id: null,
                        code: vue.code,
                        segments: vue.segments,
                        segment_by_document: vue.segment_by_document,
                        segments_supervisors: vue.list_criteria_selected
                    });

            vue.$http
                .post(url, formData)
                .then(({data}) => {
                    console.log(data);

                })
                .catch(error => {
                    if (error && error.errors) vue.errors = error.errors;
                    vue.hideLoader();
                });

            vue.modalUserSupervisors.supervisors = [
                {
                    id: 1,
                    name: "asdas",
                    users: [
                        {
                            id:1,
                            name: "aaaa"
                        },
                        {
                            id:2,
                            name:"bbbbb"
                        }
                    ]
                }
            ]
            vue.modalUserSupervisors.open = true
        },
        nextStep(){
            let vue = this;
            vue.options.cancelLabel = "Cancelar";
            vue.options.confirmLabel = "Continuar";


            if(vue.stepper_box == 1){
                vue.options.cancelLabel = "Retroceder";
                vue.stepper_box = 2;
                vue.step_title = '> Instructivo'
            }
            else if(vue.stepper_box == 2){
                vue.options.cancelLabel = "Retroceder";
                vue.confirm();
            }
        },
        prevStep(){
            let vue = this;
            if(vue.stepper_box == 1){
                vue.closeModal();
                // vue.stepper_box = 2;
            }
            else if(vue.stepper_box == 2){
                vue.options.cancelLabel = "Cancelar";
                vue.options.confirmLabel = "Continuar";
                vue.options.title = "Segmentación de usuarios";
                vue.stepper_box = 1;
            }
        },
        closeModal() {
            let vue = this;
            // vue.options.open = false
            vue.resetSelects();
            vue.resetValidation();

            // alert modal info
            vue.modalInfoOptions.hideConfirmBtn = false;
            vue.criteriaIndexModal = 0;
            vue.stackModals.continues = [];

            // clean segment_by_document_clean
            vue.segment_by_document_clean = !vue.segment_by_document_clean;

            vue.$emit("onCancel");
            // vue.$refs["SegmentByDocument"].resetFields();
        },
        resetValidation() {
            let vue = this;
            vue.list_criteria = [];
            vue.stepper_box = 1;
            vue.$refs.segmentForm.resetValidation();
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
        checkIfExistCriteria(stackSegments, current) {
            const vue = this;
            let stackMessage = [];

            //local scope function
            const VerifyCodeAndValues = (criterians, current) => {
                let cri_state = false,
                    cri_data = false;

                for (let i = 0; i < criterians.length; i++) {
                    const { code, values_selected } = criterians[i];

                    if(code === current) {
                        cri_state = true;
                        cri_data = values_selected ? values_selected.length > 0 : false;
                        break;
                    }
                }
                return { cri_state, cri_data };
            };

            const SetMessageByCurrent = (customMessage, stateVerify, segIndex) => {

                const { noexist, nodata, title } = customMessage;
                const { cri_state, cri_data } = stateVerify;

                const state = (!cri_state || !cri_data);
                let message;

                if(!cri_state) message = `${noexist}`;
                else if(!cri_data) message = `${nodata} en la segmentación ${segIndex}, para continuar.`;
                else message = null;

                return { state, message, title, detail: { cri_data, cri_state } };
            };

            for (let i = 0; i < stackSegments.length; i++) {
                const { criteria_selected } = stackSegments[i];

                const stateVerify = VerifyCodeAndValues(criteria_selected, current);
                const stateMessage = SetMessageByCurrent(CustomMessages[current], stateVerify, i + 1);

                if(stateMessage.state) stackMessage.push(stateMessage);
            }

            return stackMessage;
        },
        setAndOpenAlertModal(responseCheck) {
            const vue = this;
            const count = responseCheck.length;

            for (let i = 0; i < count; i++) {
                const responseData = responseCheck[i];
                const { message, title } = responseData;
                vue.modalInfoOptions.resource = message;
                vue.modalInfoOptions.title = title;

                const { cri_state, cri_data } = responseData.detail;

                if(cri_state && !cri_data) vue.modalInfoOptions.hideConfirmBtn = true;
                else vue.modalInfoOptions.hideConfirmBtn = true;

                if(vue.criteriaIndexModal === i) {
                    vue.criteriaIndexModal = i + 1;
                    vue.openFormModal(vue.modalInfoOptions, null, null, title);
                    break;
                }
            }

            let continues = [];
            let backers = [];

            for (let i = 0; i < count; i++) {
                const responseData = responseCheck[i];
                const { cri_state, cri_data } = responseData.detail;

                //continues count
                if(cri_state && !cri_data) backers.push(i);
                else continues.push(i);
            }

            vue.stackModals.continues = continues;
            vue.stackModals.backers = backers;
        },
        continueRegister(flag) {
            const vue = this;
            vue.confirmModal();
        },
        backRegister() {
            const vue = this;
            vue.criteriaIndexModal = 0;
        },
        showModalCondition(){
            const vue = this;
            const responseCheck = vue.checkIfExistCriteria(vue.segments, 'module');

            let state = true;

            if(responseCheck.length) {
                if(vue.criteriaIndexModal) {
                    const continuesCount = vue.stackModals.continues.length;

                    if(vue.criteriaIndexModal === continuesCount){
                        state = true;
                    } else {
                        vue.setAndOpenAlertModal(responseCheck);
                        state = false;
                    }
                } else {
                    vue.setAndOpenAlertModal(responseCheck);
                    state = false;
                }
            }

            return state;
        },
        confirmModal() {
            let vue = this;
            vue.criteriaIndexModal = 0;
            vue.options.cancelLabel = "Cancelar";
            vue.options.confirmLabel = "Continuar";
console.log(vue.options);
console.log(vue.segments);
            if(vue.stepper_box == 1)
            {
                vue.list_criteria = [];
                if(vue.segments.length > 0) {
                    vue.segments.forEach(element => {
                        if(element.criteria_selected.length > 0) {
                            element.criteria_selected.forEach(item => {
                                vue.$nextTick(() => {
                                    vue.list_criteria.push(item)
                                })
                            });
                        }
                    });
                }

                vue.stepper_box = 2;
                vue.options.title = "Segmentación de usuarios > <b>Vinculación por criterios</b>";
                vue.options.cancelLabel = "Retroceder";
                vue.options.confirmLabel = "Guardar";
            }
            else if(vue.stepper_box == 2) {
                vue.errors = [];

                this.showLoader();

                const validateForm = vue.validateForm("segmentForm");
                const edit = vue.options.action === "edit";

                // let base = `${vue.options.base_endpoint}`;
                let base = 'procesos/segments';
                let url = `${base}/store`;

                // let method = edit ? 'PUT' : 'POST';
                let method = "POST";

                // === check criteria and open alert === SEGMENTACION DIRECTA ===
                if(vue.segments.length && vue.tabs === 0) {
                    const state = vue.showModalCondition();
                    if(!state) return;
                }
                // === check criteria and open alert === SEGMENTACION DIRECTA ===

                // if (validateForm && validateSelectedModules) {
                if (validateForm) {
                    // let formData = vue.getMultipartFormData(method, vue.segments, fields);
                    let formData = JSON.stringify({
                        model_type: vue.model_type,
                        model_id: vue.resource.id,
                        code: vue.code,
                        segments: vue.segments,
                        segment_by_document: vue.segment_by_document,
                        segments_supervisors: vue.list_criteria_selected
                    });

                    vue.$http
                        .post(url, formData)
                        .then(({data}) => {

                            vue.$emit("onConfirm");
                            vue.closeModal();
                            vue.showAlert(data.data.msg);

                            vue.hideLoader();
                        })
                        .catch(error => {
                            if (error && error.errors) vue.errors = error.errors;
                            vue.hideLoader();
                        });
                }
            }
        },
        resetSelects() {
            let vue = this;
            vue.tabs = null;

            //reset selects at blocks
            for(const segment of vue.segments) {
                vue.borrarBloque(segment);
            }
        },
        async loadData(resource) {
            let vue = this;
            vue.errors = [];

            vue.resource = resource;

            let base = `${vue.options.base_endpoint}`;
            let url = resource
                ? `${base}/${resource.id}/edit`
                : `${base}/create`;

            url = url + "?model_type=" + vue.model_type +
                "&model_id=" + resource.id;

            await vue.$http.get(url).then(({data}) => {
                let _data = data.data;

                vue.segments = _data.segments.filter(segment => segment.type.code === 'direct-segmentation');
                vue.segment_by_document = _data.segments.find(segment => segment.type.code === 'segmentation-by-document');

                if (vue.segments.length === 0) this.addSegmentation();
                if (vue.segment_by_document === undefined) {
                    vue.segment_by_document = {
                        criteria_selected: []
                    };
                }
                vue.criteria = _data.criteria;
                vue.courseModules = _data.courseModules;
                vue.total = _data.users_count;

                // Replace modules with course's school modules

                if (vue.isCourseSegmentation()) {

                    let courseModulesIds = [];
                    vue.courseModules.forEach(cm => courseModulesIds.push(cm.module_id))

                    // Replace modules in criteria collection
                    let modules = [];
                    vue.criteria.forEach((c, index, collection) => {
                        if (c.code === 'module') {
                            collection[index].values = c.values.filter(v => {
                                return courseModulesIds.includes(v.id)
                            })
                            modules = collection[index].values
                        }
                    })

                    // Replace modules in existing segments

                    vue.segments.forEach(s => {
                        s.criteria_selected.forEach((cs, index, collection) => {
                            if (cs.code === 'module') {
                                collection[index].values = modules;
                            }
                        })
                    })

                }
            });

            // When model type is course, load module ids

            if (resource.id && vue.isCourseSegmentation()) {
                await vue.loadModulesFromCourseSchools(resource.id)
            }

            return 0;
        },
        /**
         * Load modules ids from schools the course belongs to
         * @param courseId
         * @returns {Promise<void>}
         */
        async loadModulesFromCourseSchools(courseId) {
            if (!this.resource) return
            let url = `${this.options.base_endpoint}/modules/course/${courseId}`;
            try {
                const response = await this.$http.get(url);

                if (response.data.data) {
                    this.modulesIds = response.data.data.modulesIds
                    this.modulesSchools = response.data.data.modulesSchools

                    // When there is no criteria selected, adds
                    // module criteria and select modules from
                    // course schools

                    // let moduleCriteria = this.criteria.find(c => c.code === 'module')
                    // if (moduleCriteria) {
                    //     if (this.segments[0].criteria_selected.length === 0) {
                    //         moduleCriteria.values_selected = moduleCriteria.values.filter(v => modulesIds.includes(v.id))
                    //         this.segments[0].criteria_selected.push(moduleCriteria)
                    //     }
                    // }
                }
            } catch (ex) {
                console.log(ex)
            }
        },
        loadSelects() {
            let vue = this;
        },
        addUser(user) {
            let vue = this;

            const already_added = vue.segment_by_document.criteria_selected.filter(el => el.document == user.document).length > 0;

            if (!already_added) {

                vue.segment_by_document.criteria_selected.push(user)

                vue.$refs["SegmentByDocument"].addOrRemoveFromFilterResult(user, 'remove');
            }
        },
        deleteUser(user) {
            let vue = this;

            const index = vue.segment_by_document.criteria_selected.findIndex(el => el.document == user.document);

            if (index !== -1) {

                vue.segment_by_document.criteria_selected.splice(index, 1);

                // vue.$refs["SegmentByDocument"].addOrRemoveFromFilterResult(user);
            }
        },
        isCourseSegmentation() {
            return this.model_type === 'App\\Models\\Course'
        }
    }
};
</script>
<style lang="scss">
.dialog_segment_process {
    .v-stepper, .v-stepper__header {
        box-shadow: none !important;
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
    .bx_criteria_selected {
        height: 350px;
        border: 1px solid #D9D9D9;
        width: 100%;
        margin-top: 12px;
        border-radius: 8px;
        span.v-chip .v-icon.v-icon.v-icon--link {
            color: #fff !important;
            margin-top: -5px;
        }
    }
}
</style>
