<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        :persistent="true"
    >
        <template v-slot:content>
            <v-form ref="segmentForm" class="---mb-6">
                <DefaultErrors :errors="errors"/>

                <v-tabs
                    v-model="tabs"
                    fixed-tabs
                    slider-color="primary"
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
                            <v-col cols="12" class="d-flex justify-content-end pr-3">
                                <v-btn
                                    class="- add-button"
                                    color="primary"
                                    @click="addSegmentation('direct-segmentation')"
                                >
                                    <v-icon class="" v-text="'mdi-plus'"/>
                                    Segmento
                                </v-btn>
                            </v-col>
                        </v-row>

                        <v-row justify="space-around">
                            <v-col cols="10" class="d-flex justify-content-center">
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
                                                :segments="segments"
                                                :segment="row"
                                                :criteria="criteria"
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
                            @addUser="addUser"
                            @deleteUser="deleteUser"
                        />

                    </v-tab-item>
                </v-tabs-items>

            </v-form>
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

import Segment from "./Segment";
import SegmentByDocument from "./SegmentByDocument";

export default {
    components: {
        Segment, SegmentByDocument
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
            vue.$emit("onCancel");

            vue.$refs["SegmentByDocument"].resetFields();
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
        confirmModal() {
            let vue = this;

            vue.errors = [];

            this.showLoader();

            const validateForm = vue.validateForm("segmentForm");
            const edit = vue.options.action === "edit";

            let base = `${vue.options.base_endpoint}`;
            // let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;
            let url = `${base}/store`;

            // let method = edit ? 'PUT' : 'POST';
            let method = "POST";

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
                    })
                    .catch(error => {
                        if (error && error.errors) vue.errors = error.errors;
                    });
            }

            this.hideLoader();
        },
        resetSelects() {
            let vue = this;
            vue.tabs = null;
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
                vue.total = _data.users_count;
            });

            return 0;
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
        }
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
