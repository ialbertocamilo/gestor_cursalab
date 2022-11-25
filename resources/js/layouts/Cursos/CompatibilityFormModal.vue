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

                <v-row>
                    <v-col cols="12">
                            <!-- :rules="rules.lista_escuelas" -->
                        <DefaultAutocomplete
                            dense
                            label="Cursos compatibles"
                            v-model="resource.compatibilities"
                            :items="courses"
                            item-text="name"
                            item-value="id"
                            multiple
                            :show-select-all="false"
                            :count-show-values="6"
                        />
                    </v-col>
                </v-row>
     
            </v-form>

            <div  class="d-flex justify-content-center py-10">
                <v-img max-width="350" class="text-center" src="/img/guides/visits.svg"></v-img>
            </div>
        </template>
    </DefaultDialog>
</template>

<script>

const fields = [
    "name",
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
    },
    data() {
        return {
            errors: [],
            resourceDefault: {
                compatibilities: []
            },

            resource: {compatibilities: []},
            // resource: {},
            courses: [],
            // compatibilities: [],
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

        confirmModal() {
            let vue = this;

            vue.errors = [];

            this.showLoader();

            const validateForm = vue.validateForm("compatibilityForm");
            const edit = vue.options.action === "edit";

            let base = `${vue.options.base_endpoint}`;
            let url = `${base}/${vue.resource.id}/compatibilities/update`;

            let method = 'PUT';

            // if (validateForm && validateSelectedModules) {
            if (validateForm) {
                // let formData = vue.getMultipartFormData(method, vue.segments, fields);
                let formData = JSON.stringify({
                    _method: method,
                    compatibilities: vue.resource.compatibilities,
                });

                vue.$http
                    .put(url, formData)
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
            let url =  `${base}/${resource.id}/compatibilities`

            await vue.$http.get(url).then(({data}) => {
                let _data = data.data;

                vue.resource.compatibilities = _data.compatibilities
                vue.courses = _data.courses
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
