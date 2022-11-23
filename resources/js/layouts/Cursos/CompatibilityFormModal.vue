<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        :persistent="true"
    >
        <template v-slot:content>
            <v-form ref="compatibilityForm" class="---mb-6">
                <DefaultErrors :errors="errors"/>
         
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

// import Segment from "./Segment";
// import SegmentByDocument from "./SegmentByDocument";

export default {
    // components: {
    //     Segment, SegmentByDocument
    // },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String,
        // model_type: String,
        // model_id: Number,
        code: {
            type: String,
            default: null
        },
        // tabs_title:{
        //     type: String,
        //     default: 'Segmentación'
        // },
        // limitOne: {
        //     type:Boolean,
        //     default:false
        // }
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

            // vue.$refs["SegmentByDocument"].resetFields();
        },
        resetValidation() {
            let vue = this;
            vue.$refs.compatibilityForm.resetValidation();
        },
        // getNewSegment(type_code) {
        //     return {
        //         id: `new-segment-${Date.now()}`,
        //         type_code,
        //         criteria_selected: []
        //     };
        // },
        // async addSegmentation(type_code) {
        //     let vue = this;
        //     vue.segments.push(this.getNewSegment(type_code));

        //     vue.steps = vue.segments.length - 1;
        // },
        // borrarBloque(segment) {
        //     let vue = this;
        //     // const isNewSegment = segment.id.search("new") !== -1;
        //     // if (vue.segments.length === 1 && !isNewSegment) return;

        //     vue.segments = vue.segments.filter((obj, idx) => {
        //         return obj.id != segment.id;
        //     });
        // },
        confirmModal() {
            let vue = this;

            vue.errors = [];

            this.showLoader();

            const validateForm = vue.validateForm("compatibilityForm");
            const edit = vue.options.action === "edit";

            let base = `${vue.options.base_endpoint}`;
            let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;
            // let url = `${base}/store`;

            let method = edit ? 'PUT' : 'POST';
            // let method = "POST";

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

            await vue.$http.get(url).then(({data}) => {
                let _data = data.data;

            });

            return 0;
        },
        loadSelects() {
            let vue = this;
        },
        // addUser(user) {
        //     let vue = this;

        //     const already_added = vue.segment_by_document.criteria_selected.filter(el => el.document == user.document).length > 0;

        //     if (!already_added) {

        //         vue.segment_by_document.criteria_selected.push(user)

        //         vue.$refs["SegmentByDocument"].addOrRemoveFromFilterResult(user, 'remove');
        //     }
        // },
        // deleteUser(user) {
        //     let vue = this;

        //     const index = vue.segment_by_document.criteria_selected.findIndex(el => el.document == user.document);

        //     if (index !== -1) {

        //         vue.segment_by_document.criteria_selected.splice(index, 1);

        //         // vue.$refs["SegmentByDocument"].addOrRemoveFromFilterResult(user);
        //     }
        // }
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
