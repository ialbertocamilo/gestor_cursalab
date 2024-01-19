<template>
    <v-form :ref="formRef" class="---mb-6">
        <DefaultErrors :errors="errors"/>

        <!--
        Module-School breadcrumbs
        ======================================== -->
        <div class="row justify-center">

            <div v-if="isCourseSegmentation() && modulesSchools.length"
                    class="col-10">
                <!--
                                        <v-expansion-panels>
                                        <v-expansion-panel
                                        title="Item"
                                        text="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."
                                        ></v-expansion-panel>
                                    </v-expansion-panels> -->

                <v-expansion-panels class="school-breadcrumb">
                    <v-expansion-panel>
                        <v-expansion-panel-header>
                            Escuelas asignadas
                        </v-expansion-panel-header>
                        <v-expansion-panel-content>
                            <DefaultSimpleBreadcrumbs
                                v-for="moduleSchool of modulesSchools"
                                :key="moduleSchool.subworkspace_id"
                                :breadcrumbs="[
                            {title: moduleSchool.module_name, disabled: true},
                            {title: moduleSchool.school_name, disabled: true},
                        ]"/>
                        </v-expansion-panel-content>
                    </v-expansion-panel>
                </v-expansion-panels>

            </div>

        </div>

        <!--
        Tabs
        ======================================== -->

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
    </v-form>
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

import Segment from "./Segment";
import SegmentAlertModal from "./SegmentAlertModal";
import SegmentByDocument from "./SegmentByDocument";

export default {
    components: {
        Segment, SegmentByDocument, SegmentAlertModal
    },
    props: {
        formRef: {
            type: String
        },
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
            vue.errors = [];

            this.showLoader();

            // console.log(vue.options);

            const validateForm = vue.validateForm("segmentForm");
            const edit = vue.options.action === "edit";

            let base = `${vue.options.base_endpoint}`;
            // let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;
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
                    segment_by_document: vue.segment_by_document
                });

                vue.$http
                    .post(url, formData)
                    .then(({data}) => {
                        vue.$emit("onConfirm");
                        vue.closeModal();
                        vue.showAlert(data.data.msg);

                        vue.hideLoader();
                        if (vue.for_section == "aulas_virtuales"){
                            vue.queryStatus(
                                "aulas_virtuales",
                                "configura_anfitrion"
                            );
                        }
                        else if (vue.for_section == "supervisores"){
                            vue.queryStatus(
                                "supervisores",
                                "crear_supervisor"
                            );
                        }
                    })
                    .catch(error => {
                        if (error && error.errors) vue.errors = error.errors;
                        vue.hideLoader();
                    });
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

            // vue.$nextTick(() => {
            //     vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            // })

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
        },
        validateFormSegment() {
            let vue = this;
            return vue.validateForm("segmentForm");
        },
    }
};
</script>

<style scoped>
.add-button {
    margin-right: 35px;
}

.group-sheet {
    padding-bottom: 40px;
}
</style>
