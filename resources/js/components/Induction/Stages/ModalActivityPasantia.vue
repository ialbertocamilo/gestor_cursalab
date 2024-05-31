<template>
    <div>
        <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        @onCancelSteps="prevStep"
        :steps="true"
        content-class="br-dialog modal_activity_pasantia"
        >
            <template v-slot:content>
                <v-form ref="projectForm">
                    <v-stepper non-linear class="stepper_box" v-model="stepper_box">
                        <v-stepper-items>
                            <v-stepper-content step="1" class="p-0">
                                <v-row>
                                    <v-col cols="12" class="d-flex justify-content-center">
                                        <DefaultInput
                                            v-model="resource.titulo"
                                            label="Nombre"
                                            :dense="true"
                                            :rules="rules.titulo"
                                            placeholder="Ingrese un nombre"
                                        />
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12" class="step_modalAsignacionXDni">
                                        <AsignacionXDni
                                            description='Define los colaboradores que participarán en el proceso de pasantía dentro de la inducción'
                                            apiSearchUser="/supervisores/search-leaders"
                                            apiUploadPlantilla="/supervisores/subir-excel-usuarios"
                                            :showSubidaMasiva="false"
                                            ref="AsignacionSupervisores"
                                            :load_data_default="true"
                                            :list_users_selected="list_users_selected_load"
                                            @changeListUsers="changeListUsers"
                                            v-if="show_list_users"
                                        >
                                        </AsignacionXDni>
                                    </v-col>
                                </v-row>
                            </v-stepper-content>
                            <v-stepper-content step="2" class="p-0">
                                <v-row>
                                    <v-col cols="12" class="step_questions">
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
            </template>
        </DefaultDialog>
    </div>
</template>

<script>

import AsignacionXDni from "../../../layouts/Supervisores/AsignacionXDni";
const fields = [ 'model_id', 'model_type', 'stage_id', 'users', 'titulo' ];

export default {
    components: { AsignacionXDni },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            list_users_selected: [],
            list_users_selected_load: [],
            show_list_users: true,
            loadStep2: false,
            disabled_btn_next: true,
            stepper_box_btn1: true,
            stepper_box_btn2: true,
            stepper_box: 1,
            advanced_config: false,
            errors: [],
            resourceDefault: {
                id: null,
                model_id: null,
                stage_id: null,
                model_type: null,
                name: '',
                users: [],
                titulo: ''
            },
            resource: {
                titulo: ''
            },
            sections: {
                showSectionAdvanced: {status: false},
            },
            selects: {},
            rules: {
                titulo: this.getRules(['required', 'max:255']),
            },
            // resources: [],
            search: null,
            isLoading: false,
            debounceTimer: null,
            file: null,
            edit_resource: false,
            fileSelected: null,
        };
    },
    watch: {
        search(val) {
            // Items have already been loaded
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }
            this.debounceTimer = setTimeout(() => {
                this.searchCourses(val);
            }, 800);
        },
        'resource.titulo': {
            handler(n, o) {
                let vue = this;

                const validateForm = vue.validateForm('projectForm')
                vue.options.confirmDisabled = true
                if(this.list_users_selected.length > 0 && validateForm) {
                    vue.options.confirmDisabled = false
                }
            },
            deep: true
        },
        list_users_selected: {
            handler(n, o) {
                let vue = this;

                const validateForm = vue.validateForm('projectForm')
                vue.options.confirmDisabled = true
                if(this.list_users_selected.length > 0 && validateForm) {
                    vue.options.confirmDisabled = false
                }
            },
            deep: true
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.resetSelects()
            vue.resetValidation()
            vue.$refs.projectForm.reset()
            vue.show_list_users = false
            setTimeout(() => {
                vue.show_list_users = true
            }, 100);

            vue.resource = {}
            vue.stepper_box = 1
            vue.options.cancelLabel = "Cancelar";
            vue.options.confirmLabel = "Continuar";
            vue.loadStep2 = false
            vue.list_users_selected = []
            vue.list_users_selected_load = []
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.$refs.projectForm.resetValidation()
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
        async confirmModal()
        {
            let vue = this

            vue.options.cancelLabel = "Retroceder";
            vue.options.confirmLabel = "Guardar";

            if(vue.stepper_box == 1)
            {
                console.log(this.list_users_selected);
                console.log(vue.resource.id);
                vue.errors = []

                this.showLoader()

                let base = `${vue.options.base_endpoint}`
                let url = vue.resource.id
                    ? `${base}/${vue.resource.id}/update`
                    : `${base}/store`;

                let method = 'POST';

                const validateForm = this.list_users_selected.length > 0
                if (validateForm) {
                    const formData = vue.createFormData(method, validateForm);
                    await vue.$http
                        .post(url, formData)
                        .then(({ data }) => {
                            console.log(data);
                            // if(!vue.resource.id) {
                            //     vue.resource.id = data.data.activity.id;
                            // }
                            this.hideLoader()
                            // vue.stepper_box = 2;
                            // vue.loadStep2 = true

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
            else if(vue.stepper_box == 2)
            {
                vue.closeModal()
                vue.$emit("onConfirm");
            }
        },
        resetSelects() {
            let vue = this
        },
        async loadData(resource)
        {
            let vue = this

            vue.options.confirmDisabled = true
            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`

            if (resource) {
                let url = `${base}/edit/${resource.id}`
                this.show_list_users = false
                await vue.$http.get(url).then(({ data }) => {

                    let _data = data.data
                    vue.resource = Object.assign({}, vue.resource, _data.activity)
                    vue.$nextTick(() => {
                        vue.resource.titulo = _data.activity.title;
                        vue.list_users_selected_load = _data.leaders
                        vue.list_users_selected = _data.leaders
                        setTimeout(() => {
                            this.show_list_users = true

                            const validateForm = vue.validateForm('projectForm')
                            if(this.list_users_selected.length > 0 && validateForm) {
                                vue.options.confirmDisabled = false
                            }
                        }, 10);
                    })
                })
            } else {
                vue.resource.id = null;
            }

            return 0;
        },
        async loadSelects()
        {
            let vue = this;
        },
        createFormData(method, validateForm) {
            let vue = this;

            vue.resource.stage_id = vue.options.model_id;
            // vue.resource.model_type = 'App\\Models\\Stage';


            let formData = vue.getMultipartFormData(method, vue.resource, fields);
            formData.append('validate', validateForm ? "1" : "0");

            let users = JSON.stringify(this.list_users_selected)
            formData.append('users', users)

            return formData;
        },
        changeListUsers() {
            let vue = this
            this.list_users_selected = vue.$refs.AsignacionSupervisores ? vue.$refs.AsignacionSupervisores.usuarios_ok : [];
            const validateForm = vue.validateForm('projectForm')
            vue.options.confirmDisabled = true
            if(this.list_users_selected.length > 0 && validateForm) {
                vue.options.confirmDisabled = false
            }
        }
    }
}
</script>
<style lang="scss">
.modal_activity_pasantia {
    .step_questions section.section-list {
        padding-top: 0;
        .v-card .v-card__title {
            padding-top: 0;
            padding-bottom: 0;
        }
    }
}
</style>
