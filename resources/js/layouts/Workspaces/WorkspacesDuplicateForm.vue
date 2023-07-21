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

               <!--  <v-row justify="space-around" v-if="is_superuser">
                    <v-col cols="12">
                        <DefaultModalSection
                            title="Límite de Usuarios"
                        >
                            <template v-slot:content>

                                <v-col cols="12">
                                    <DefaultInput
                                        label="Límite"
                                        v-model="limit_allowed_users"
                                        type="number"
                                        min="0"
                                        clearable
                                    />
                                </v-col>
                            </template>
                        </DefaultModalSection>
                    </v-col>
                </v-row> -->
<!-- 
                <v-row justify="space-around" v-if="is_superuser">
                    <v-col cols="12">
                        <DefaultModalSection
                            title="Funcionalidades"
                        >
                            <template v-slot:content>

                                <v-col cols="12">
                                    <v-checkbox
                                        v-for="functionality in functionalities"
                                        :key="functionality.id"
                                        v-model="resource.selected_functionality[functionality.id]"
                                        :label="functionality.name"
                                    >
                                    </v-checkbox>
                                </v-col>

                            </template>
                        </DefaultModalSection>
                    </v-col>
                </v-row> -->
  
<!-- 
                <v-row>
                    <v-col cols="12">
                        <DefaultModalSection title="Notificaciones Push">
                                <template v-slot:content>
                                    <v-row>
                                        <v-col cols="6">
                                            <DefaultInput
                                                class="mb-4"
                                                label="Empezar envio luego de: (en minutos)"
                                                type="number"
                                                v-model="resource.notificaciones_push_envio_inicio" />
                                            <DefaultInput
                                                label="Número de usuarios por envio"
                                                type="number"
                                                v-model="resource.notificaciones_push_envio_intervalo"
                                            />
                                        </v-col>
                                        <v-col cols="6">
                                            <DefaultInput
                                                label="Frecuencia de envio por bloques (en minutos)"
                                                type="number"
                                                v-model="resource.notificaciones_push_chunk"
                                                />
                                        </v-col>
                                    </v-row>
                                </template>
                        </DefaultModalSection>
                    </v-col>
                </v-row> -->
              
            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>


const fields = [
    'name', 'url_powerbi', 'logo', 'logo_negativo'
    // 'logo_marca_agua', 'marca_agua_estado',
    // 'notificaciones_push_envio_inicio', 'notificaciones_push_envio_intervalo', 'notificaciones_push_chunk', 'selected_functionality'
];


export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    // data: () => ({
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

            const isValid = vue.validateForm('workspaceDuplicateForm');
            // const edit = vue.options.action === 'edit';

            let base = `${vue.options.base_endpoint}`;
            let url = `/${base}/${vue.resource.id}/duplicate`

            let method = 'POST';

            if (isValid) {

                // Prepare data

                let formData = vue.getMultipartFormData(
                    method, vue.resource, fields
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
        // criterionExistsInCriteriaValue(criterionId, criteria_workspace) {

        //     let exists = false;

        //     if (criteria_workspace) {

        //         criteria_workspace.forEach(v => {
        //             if (v.id === criterionId)
        //                 exists = true;
        //         });
        //     }


        //     return exists;
        // }
    }
}
</script>
