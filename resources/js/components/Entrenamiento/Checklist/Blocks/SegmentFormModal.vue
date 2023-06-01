<template>
    <div>

        <v-tabs
            v-model="tabs"
            fixed-tabs
            slider-color="primary"

            class="col-10 offset-1 tabs_title pb-0 pt-2"
        >
            <v-tab>
                <span class="title_sub">Criterios</span>
            </v-tab>
            <v-tab>
                <span class="title_sub">Doc. de Identidad</span>
            </v-tab>
        </v-tabs>

        <v-tabs-items v-model="tabs">

            <v-tab-item>

                <v-row justify="space-around" v-for="(row, i) in list_segments" :key="i">
                    <v-col cols="12" class="d-flex justify-content-center" style="height: 364px;" v-if="row.type_code == 'direct-segmentation'">
                                    <Segment
                                        :is-course-segmentation="isCourseSegmentation()"
                                        :segments="list_segments"
                                        :segment="row"
                                        :criteria="criteria"
                                        :course-modules="courseModules"
                                        class="mx-5"
                                        :options="options"
                                        @borrar_segment="borrarBloque"
                                        @disabledBtnModal="disabledBtnModal"
                                    />

                    </v-col>
                </v-row>
                <div v-if="list_segments!= null
                && list_segments[0] != null
                && list_segments[0].direct_segmentation.length > 0
                && list_segments[0].direct_segmentation[0] != null
                && list_segments[0].direct_segmentation.length < 3" style="text-align: center;">
                    <div class="msg_label">Debes seleccionar al menos 3 criterios para continuar.</div>
                </div>
            </v-tab-item>

            <v-tab-item>

                <SegmentByDocument
                    ref="SegmentByDocument"
                    :segment="list_segments_document"

                    :current-clean="segment_by_document_clean"
                    @addUser="addUser"
                    @deleteUser="deleteUser"
                    @addUserAll="addUserAll"
                    @deleteUserAll="deleteUserAll"
                />

            </v-tab-item>
        </v-tabs-items>

        <SegmentAlertModal
            :options="modalInfoOptions"
            :ref="modalInfoOptions.ref"
            @onConfirm="closeFormModal(modalInfoOptions)"
            @onCancel="closeFormModal(modalInfoOptions)"
        />
    </div>
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
        },
        list_segments: Array,
        list_segments_document: Object
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
            segment_by_document: {
                id: `new-segment-${Date.now()}`,
                type: {code: 'segmentation-by-document'},
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
                // name: this.getRules(['required', 'max:255']),
            }
        };
    },
    mounted() {
        let vue = this;
    },
    watch: {
        segments(){
                const vue = this;
                vue.$props.options.model_type= vue.model_type;
        },
        tabs(){
                const vue = this;
        }
    },
    methods: {
        openModaltwo() {
            let vue = this;
        },
        closeModal() {
            let vue = this;
                vue.segments = [];

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
                criteria_selected: [],
                direct_segmentation: []
            };
        },
        async addSegmentation(type_code) {
            let vue = this;
            vue.list_segments.push(this.getNewSegment(type_code));

            vue.steps = vue.list_segments.length - 1;
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
console.log(vue.segments.length);
                vue.segments = _data.segments.filter(segment => segment.type.code === 'direct-segmentation');
                vue.segment_by_document = _data.segments.find(segment => segment.type.code === 'segmentation-by-document');
console.log(vue.segments.length);
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
                let modulesIds = []
                if (response.data.data) {
                    modulesIds = response.data.data.modulesIds
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
        addUserAll(user) {
            let vue = this;
            if(vue.list_segments_document === undefined || vue.list_segments_document === null) {
                vue.list_segments_document = {'segmentation_by_document': []};
            }
            const already_added = vue.list_segments_document.segmentation_by_document.filter(el => el.document == user.document).length > 0;

            if (!already_added) {
                vue.list_segments_document.segmentation_by_document.push(user)
            }
        },
        deleteUserAll() {
            let vue = this;
            if(vue.list_segments_document === undefined || vue.list_segments_document === null) {
                vue.list_segments_document = {'segmentation_by_document': []};
            }
            vue.list_segments_document.segmentation_by_document = [];
        },
        addUser(user) {
            let vue = this;
            if(vue.list_segments_document === undefined || vue.list_segments_document === null) {
                vue.list_segments_document = {'segmentation_by_document': []};
            }
            const already_added = vue.list_segments_document.segmentation_by_document.filter(el => el.document == user.document).length > 0;

            if (!already_added) {

                vue.list_segments_document.segmentation_by_document.push(user)

                vue.$refs["SegmentByDocument"].addOrRemoveFromFilterResult(user, 'remove');
            }
        },
        deleteUser(user) {
            let vue = this;

            const index = vue.list_segments_document.segmentation_by_document.findIndex(el => el.document == user.document);

            if (index !== -1) {

                vue.list_segments_document.segmentation_by_document.splice(index, 1);

                // vue.$refs["SegmentByDocument"].addOrRemoveFromFilterResult(user);
            }
        },
        isCourseSegmentation() {
            return this.model_type === 'App\\Models\\Course'
        },
        disabledBtnModal() {
            let vue = this;
            vue.$emit("disabledBtnModal");
        },
    }
};
</script>

<style lang="scss">
.add-button {
    margin-right: 35px;
}

.group-sheet {
    padding-bottom: 40px;
}
.tabs_title .v-tabs-slider-wrapper {
    display: none !important;
}
.tabs_title .v-tab--active:before,
.tabs_title .v-tab--active:hover:before,
.tabs_title .v-tab:focus:before {
    opacity: .0 !important;
}
.bx_segment .v-window__container .v-window-item {
    margin: 0 30px;
}
.bx_segment .v-window__container .v-window__prev button.v-btn:before,
.bx_segment .v-window__container .v-window__next button.v-btn:before {
    background: none !important;
}
.bx_segment .v-window__container .v-window__prev,
.bx_segment .v-window__container .v-window__next {
    transform: initial !important;
    background: none !important;
}
.bx_segment .v-window__container .v-window__prev i.v-icon,
.bx_segment .v-window__container .v-window__next i.v-icon {
    color: #B9E0E9;
}
.bx_segment .v-window__prev button.v-btn span.v-ripple__container,
.bx_segment .v-window__next button.v-btn span.v-ripple__container {
    opacity: 0;
}
.msg_label {
    font-family: "Nunito", sans-serif;
    font-size: 12px;
    text-align: center;
    border: 1px solid #5458ea;
    display: inline-block;
    padding: 0 10px;
    border-radius: 5px;
    color: #5458ea;
    margin: 0 auto;
}
</style>
