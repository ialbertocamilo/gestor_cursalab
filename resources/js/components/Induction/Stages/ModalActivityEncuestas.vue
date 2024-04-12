<template>
    <div>
        <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        @onCancelSteps="prevStep"
        :steps="true"
        content-class="br-dialog modal_activity_polls"
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
                                    <v-col cols="12">
                                        <DefaultTextArea
                                            label="Descripción"
                                            v-model="resource.description"
                                            placeholder="Descripción"
                                        />
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="5">
                                        <DefaultAutocomplete
                                            dense
                                            label="Encuestas guardadas"
                                            v-model="resource.poll"
                                            :items="selects.polls"
                                            item-text="name"
                                            item-value="code"
                                            :openUp="true"
                                            clearable
                                        />
                                    </v-col>
                                </v-row>

                                <DefaultModalSectionExpand
                                    title="Avanzado"
                                    :expand="sections.showSectionAdvanced"
                                    class="my-4 bg_card_none"
                                >
                                    <template slot="content">

                                        <v-row>
                                            <v-col cols="6" class="p-0">
                                                <DefaultAutocomplete
                                                    dense
                                                    label="Indicar actividad requisito"
                                                    v-model="resource.requirement"
                                                    :items="selects.requirement_list"
                                                    item-text="name"
                                                    item-value="code"
                                                    :openUp="true"
                                                    clearable
                                                />
                                            </v-col>
                                        </v-row>

                                    </template>
                                </DefaultModalSectionExpand>
                            </v-stepper-content>
                            <v-stepper-content step="2" class="p-0">
                                <v-row>
                                    <v-col cols="12" v-if="loadStep2 && encuesta_id != 0" class="step_questions">
                                        <encuesta-pregunta-layout :encuesta_id="encuesta_id" :is_modal="true"/>
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

const fields = ['titulo', 'description','requirement', 'model_id', 'model_type', 'stage_id' ];

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
            encuesta_id: 0,
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
                titulo: '',
                description: '',
                requirement: null,
                poll: null
            },
            resource: {},
            sections: {
                showSectionAdvanced: {status: false},
            },
            selects: {
                requirement_list: [{'code':'none', 'name': 'Sin requisito'}],
                polls: []
            },
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
        resource: {
            handler(n, o) {
                let vue = this;
                setTimeout(() => {
                    vue.encuesta_id = vue.resource.poll ? vue.resource.poll : vue.resource.poll_id
                    const validateForm = vue.validateForm('projectForm')
                    vue.options.confirmDisabled = !validateForm
                }, 100);
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

            vue.encuesta_id = 0
            vue.resource = {}
            vue.stepper_box = 1
            vue.options.cancelLabel = "Cancelar";
            vue.options.confirmLabel = "Continuar";
            vue.loadStep2 = false
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
                console.log(vue.resource.id);
                // if(!(vue.encuesta_id && vue.encuesta_id != 0))
                // {
                    vue.errors = []

                    this.showLoader()

                    const validateForm = vue.validateForm('projectForm')
                    const edit = vue.options.action === 'edit'

                    let base = `${vue.options.base_endpoint}`
                    let url = vue.resource.id
                        ? `${base}/${vue.resource.id}/update`
                        : `${base}/store`;

                    let method = 'POST';

                    if (validateForm) {
                        // vue.resource.model_id = vue.resource.poll;
                        const formData = vue.createFormData(method, validateForm);
                        await vue.$http
                            .post(url, formData)
                            .then(({ data }) => {
                                if(!vue.resource.id) {
                                    vue.resource.id = data.data.activity.id;
                                }
                                this.hideLoader()
                                vue.stepper_box = 2;
                                vue.loadStep2 = true
                                vue.encuesta_id = data.data.encuesta_id

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
                // }
                // else {

                //     vue.stepper_box = 2;
                //     vue.loadStep2 = true
                // }
            }
            else if(vue.stepper_box == 2)
            {
                console.log(vue.resource.id);
                if(vue.encuesta_id && vue.encuesta_id != 0)
                {
                    vue.errors = []

                    const validateForm = vue.validateForm('projectForm')

                    let base = `${vue.options.base_endpoint}`
                    let url = `${base}/${vue.resource.id}/update`;

                    let method = 'POST';

                    if (validateForm) {
                        vue.resource.model_id = vue.resource.poll;
                        const formData = vue.createFormData(method, validateForm);

                        this.showLoader()
                        await vue.$http
                            .post(url, formData)
                            .then(({ data }) => {
                                this.hideLoader()
                            }).catch((error) => {
                                if (error && error.errors) {
                                    const errors = error.errors ? error.errors : error;
                                    vue.show_http_errors(errors);
                                    this.hideLoader()
                                }
                            })
                    }
                }
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
                await vue.$http.get(url).then(({ data }) => {

                    let _data = data.data
                    vue.resource = Object.assign({}, vue.resource, _data.activity)
                    vue.resource.titulo = _data.activity.title;
                    // vue.resource.description = _data.activity.description;
                    vue.resource.poll_id = _data.poll.id
                    vue.encuesta_id = _data.poll.id
                    vue.resource.poll = _data.poll.id
                    vue.resource.requirement = _data.activity.activity_requirement_id
                    vue.selects.polls = _data.polls
                })
                const validateForm = vue.validateForm('projectForm')
                vue.options.confirmDisabled = !validateForm
            } else {
                vue.resource.id = null;
                vue.resource.titulo = '';
            }

            return 0;
        },
        async loadSelects()
        {
            let vue = this;
            let url = `${vue.options.base_endpoint}/form-selects`

            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.requirement_list = data.data.requirements
                    vue.selects.polls = data.data.polls
                })
        },
        createFormData(method, validateForm) {
            let vue = this;

            vue.resource.stage_id = vue.options.model_id;
            // vue.resource.model_type = 'App\\Models\\Stage';

            let formData = vue.getMultipartFormData(method, vue.resource, fields);
            formData.append('validate', validateForm ? "1" : "0");

            return formData;
        }
    }
}
</script>
<style lang="scss">
.modal_activity_polls {
    .step_questions section.section-list {
        padding-top: 0;
        .v-card .v-card__title {
            padding-top: 0;
            padding-bottom: 0;
        }
    }
}
</style>
