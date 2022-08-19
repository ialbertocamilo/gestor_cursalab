<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>

            <DefaultErrors :errors="errors"/>

            <v-form ref="workspaceForm">
                <v-row class="justify-content-center pt-4">
                    <v-col cols="6">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            label="Título del workspace"
                            :rules="rules.name"
                        />
                    </v-col>
                    <v-col cols="6">
                        <DefaultInput
                            clearable
                            v-model="resource.url_powerbi"
                            label="Link de learning analytics (PowerBI)"
                            :rules="rules.url_powerbi"
                        />
                    </v-col>
                </v-row>
                <v-row class="justify-content-center">
                    <v-col cols="6">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputLogo"
                            v-model="resource.logo"
                            label="Logotipo"
                            :file-types="['image']"
                            @onSelect="setFile($event, resource,'logo')"/>
                    </v-col>
                    <v-col cols="6" >
                        <DefaultSelectOrUploadMultimedia
                            ref="inputLogoNegativo"
                            v-model="resource.logo_negativo"
                            label="Logotipo negativo"
                            :file-types="['image']"
                            @onSelect="setFile($event, resource,'logo_negativo')"/>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col>
                        <v-subheader class="mt-4 px-0">
                            <strong>Criterios</strong>
                        </v-subheader>

                        <v-divider class="mt-0"/>

                        <v-alert
                            border="top"
                            colored-border
                            type="info"
                            elevation="2"
                        >
                            Aquí van las indicaciones sobre los criterios.
                        </v-alert>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="6">
                        <v-subheader class="mb-3 px-0">
                            <strong>Obligatorios</strong>
                        </v-subheader>
                        <v-checkbox
                            v-for="criterion in defaultCriteria"
                            :key="criterion.id"
                            v-model="resource.selected_criteria[criterion.id]"
                            :label="`${criterion.name} ` + criterion.required ? '(requerido)' : '(opcional)' ">
                        </v-checkbox>
                    </v-col>
                    <v-col cols="6">

                        <v-subheader class="mb-3 px-0">
                            <strong>Personalizados</strong>
                        </v-subheader>

                        <v-checkbox
                            v-for="criterion in customCriteria"
                            :key="criterion.id"
                            v-model="resource.selected_criteria[criterion.id]"
                            :label="`${criterion.name} ` + criterion.required ? '(requerido)' : '(opcional)' ">
                        </v-checkbox>
                    </v-col>
                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>


const fields = [
    'name', 'url_powerbi', 'logo', 'logo_negativo', 'selected_criteria'
];
const file_fields = ['logo', 'logo_negativo'];

export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data: () => ({

        errors: []
        ,
        resourceDefault: {
            name: '',
            url_powerbi: '',
            logo: '',
            logo_negativo: '',
            selected_criteria: {}
        }
        ,
        resource: {}
        ,
        defaultCriteria: []
        ,
        customCriteria: []
        ,
        rules: {
            //name: this.getRules(['required', 'max:255']),
        }
    })
    ,
    mounted() {

        this.loadData();
    }
    ,
    methods: {
        resetValidation() {
            let vue = this
            //vue.resetFormValidation('workspaceForm')
        }
        ,
        resetForm() {
            let vue = this
            vue.removeFileFromDropzone(vue.resource.logo, 'inputLogo')
            vue.removeFileFromDropzone(vue.resource.logo_negativo, 'inputLogoNegativo')
        }
        ,
        closeModal() {
            let vue = this;
            vue.resetForm();
            vue.$emit('onCancel');
        }
        ,
        confirmModal() {

            let vue = this
            vue.errors = []
            this.showLoader()

            const isValid = vue.validateForm('workspaceForm');
            const edit = vue.options.action === 'edit';

            let base = `${vue.options.base_endpoint}`;
            let url = vue.resource.id
                        ? `/${base}/${vue.resource.id}/update`
                        : `/${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            if (isValid) {

                // Prepare data

                let formData = vue.getMultipartFormData(
                    method, vue.resource, fields, file_fields
                );
                formData.set(
                    'selected_criteria', JSON.stringify(vue.resource.selected_criteria)
                );

                // Submit data to be saved

                vue.$http
                    .post(url, formData)
                    .then(({data}) => {

                        vue.resetForm();
                        vue.closeModal();
                        vue.showAlert(data.data.msg);
                        this.hideLoader();
                        vue.$emit('onConfirm');

                    }).catch((error) => {
                        this.hideLoader();
                        if (error && error.errors)
                            vue.errors = error.errors
                    })
            }
        }
        ,
        /**
         * Load data from server
         */
        loadData (workspace) {

            if (!workspace) return;

            let vue = this;
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let url = `/workspaces/${workspace.workspaceId}/edit`;

            this.$http
                .get(url)
                .then(({data}) => {

                    vue.resource = Object.assign({}, data.data);

                    // Filter criteria in two collections,
                    // according its "required" properties

                    vue.requiredCriteria = data.data.criteria.filter(c => c.required === 1);
                    vue.optionalCriteria = data.data.criteria.filter(c => c.required === 0);

                    // Update content of selected criteria

                    vue.resource.selected_criteria = {};
                    data.data.criteria.forEach(c => {
                        vue.resource.selected_criteria[c.id] = vue.criterionExistsInCriteriaValue(
                            c.id, data.data.criteria_value
                        );
                    });
                })
        }
        ,
        loadSelects() {
        }
        ,
        criterionExistsInCriteriaValue(criterionId, criteriaValue) {

            let exists = false;

            criteriaValue.forEach(v => {
                if (v.criterion_id === criterionId)
                    exists = true;
            });

            return exists;
        }
    }
}
</script>
