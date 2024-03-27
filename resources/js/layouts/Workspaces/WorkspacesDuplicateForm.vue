<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>

            <DefaultErrors :errors="errors"/>

            <v-form ref="workspaceDuplicateForm">
                <v-row class="justify-content-center pt-4">
                    <v-col cols="6">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            label="Nombre del workspace"
                            :rules="rules.name"
                        />
                    </v-col>
                    <v-col cols="6">
                        <DefaultInput
                            clearable
                            v-model="resource.url_powerbi"
                            label="Link de learning analytics (PowerBI)"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="12">
                        <DefaultSection
                            title="Duplicar funcionalidades"
                        >
                            <template v-slot:content>

                                <v-row justify="start" class="custom-row-checkbox">

                                    <v-col cols="4">
                                        <v-checkbox
                                            hide-details
                                            label="Checklist"
                                        >
                                        </v-checkbox>
                                    </v-col>
                                    <v-col cols="4">
                                        <v-checkbox
                                            hide-details
                                            label="Beneficios"
                                        >
                                        </v-checkbox>
                                    </v-col>
                                    <v-col cols="4">
                                        <v-checkbox
                                            hide-details
                                            label="Protocolos y Documentos"
                                        >
                                        </v-checkbox>
                                    </v-col>
                                    <v-col cols="4">
                                        <v-checkbox
                                            hide-details
                                            label="Diplomas"
                                        >
                                        </v-checkbox>
                                    </v-col>
                                    <v-col cols="4">
                                        <v-checkbox
                                            hide-details
                                            label="Reconocimiento"
                                        >
                                        </v-checkbox>
                                    </v-col>
                                </v-row>

                            </template>
                        </DefaultSection>
                    </v-col>
                </v-row>
                <v-row class="justify-content-center">
                    <v-col cols="6">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputLogo"
                            v-model="resource.logo"
                            label="Logotipo (400x142px)"
                            :file-types="['image']"
                            :rules="rules.logo"
                            @onSelect="setFile($event, resource,'logo')"/>
                    </v-col>
                    <v-col cols="6">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputLogoNegativo"
                            v-model="resource.logo_negativo"
                            label="Logotipo negativo (400x142px)"
                            :file-types="['image']"
                            @onSelect="setFile($event, resource,'logo_negativo')"/>
                    </v-col>
                </v-row>

            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>

const fields = [
    'name', 'url_powerbi', 
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
    data() {
        return {
            is_superuser: false,
            errors: [],
            resourceDefault: {
                name: '',
                url_powerbi: '',
                logo: null,
                logo_negativo: null,
                // selected_criteria: {},
                // selected_functionality: {}
            },
            limit_allowed_users: null,
            resource: {
            },
            functionalities: [],
            rules: {
                name: this.getRules(['required', 'max:255']),
                // logo: this.getRules(['required']),
            }
        }
    }
    // })
    ,
    mounted() {
        // this.loadData();
    },
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

            const isValid = vue.validateForm('workspaceDuplicateForm');
            // const edit = vue.options.action === 'edit';

            let base = `${vue.options.base_endpoint}`;
            let url = `/${base}/${vue.resource.id}/duplicate`

            let method = 'POST';

            if (isValid) {

                // Prepare data

                let formData = vue.getMultipartFormData(
                    method, vue.resource, fields, file_fields
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
            } else {
                this.hideLoader();
            }
        },

        /**
         * Load data from server
         */
        loadData(workspace) {

            if (!workspace) return;

            this.showLoader()

            let vue = this;
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let url = `/workspaces/${workspace.id}/copy`;

            this.$http
                .get(url)
                .then(({data}) => {
                    // vue.hideLoader();

                    // vue.is_superuser = data.data.is_superuser || false;

                    vue.resource.id = workspace.id;
                   
                    this.hideLoader();
                })
                .catch((error) => {
                    this.hideLoader();
                })
        }
        ,
        loadSelects() {
        },
    }
}
</script>
