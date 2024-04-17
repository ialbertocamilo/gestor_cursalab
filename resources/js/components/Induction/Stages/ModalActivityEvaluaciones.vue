<template>
    <div>
        <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        @onCancelSteps="prevStep"
        :steps="true"
        content-class="br-dialog"
        >
            <template v-slot:content>
                    <v-stepper non-linear class="stepper_box" v-model="stepper_box">
                        <v-stepper-items>
                            <v-stepper-content step="1" class="p-0">
                                <v-form ref="projectForm">
                                <v-row>
                                    <v-col cols="12" class="d-flex justify-content-center">
                                        <DefaultInput
                                            v-model="resource.titulo"
                                            label="Nombre"
                                            :dense="true"
                                            :rules="rules.titulo"
                                            placeholder="Ingrese un nombre"
                                            show-required
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
                                    <v-col cols="6">
                                        <DefaultAutocomplete
                                            dense
                                            label="Selecciona el tema a evaluar"
                                            v-model="resource.topic"
                                            :items="selects.topic_list"
                                            item-text="name"
                                            item-value="code"
                                            :rules="rules.topic_list"
                                            show-required
                                        />
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="12" class="pt-0 pb-0 check_active">
                                        <DefaultToggle
                                            v-model="resource.required"
                                            activeLabel="Aprobación obligatoria (El colaborador deberá aprobar esta actividad para continuar con su proceso de inducción)"
                                            inactiveLabel="Aprobación obligatoria (El colaborador deberá aprobar esta actividad para continuar con su proceso de inducción)"
                                            dense
                                        />
                                    </v-col>
                                </v-row>
                                <DefaultModalSectionExpand
                                    title="Configuración de calificación"
                                    :expand="sections.showSectionCalificacion"
                                    class="my-4"
                                >
                                    <template slot="content">
                                        <v-row justify="center">
                                            <v-col cols="6">
                                                <DefaultSelect
                                                    dense
                                                    :items="selects.qualification_types"
                                                    item-text="name"
                                                    return-object
                                                    show-required
                                                    v-model="resource.qualification_type"
                                                    label="Sistema de calificación"
                                                    :rules="rules.qualification_type_id"
                                                />
                                            </v-col>

                                            <v-col cols="3">
                                                <DefaultInput
                                                    label="Nota mínima aprobatoria"
                                                    v-model="resource.nota_aprobatoria"
                                                    :rules="rules.nota_aprobatoria"
                                                    type="number"
                                                    :min="0"
                                                    :max="resource.qualification_type ? resource.qualification_type.position : 0"
                                                    show-required
                                                    dense
                                                    @onFocus="curso_id && conf_focus ? alertNotaMinima() : null"
                                                />
                                            </v-col>

                                            <v-col cols="3">
                                                <DefaultInput
                                                    label="Número de intentos"
                                                    v-model="resource.nro_intentos"
                                                    :rules="rules.nro_intentos"
                                                    type="number"
                                                    show-required
                                                    dense
                                                />
                                            </v-col>
                                        </v-row>
                                    </template>
                                </DefaultModalSectionExpand>
                                <DefaultModalSectionExpand
                                    title="Programación de reinicios"
                                    :expand="sections.showSectionReinicios"
                                    class="my-4"
                                >
                                    <template slot="content">
                                        <v-row justify="center">
                                            <v-col cols="3" class="d-flex justify-content-center align-items-center">
                                                <DefaultToggle
                                                    active-label="Automático"
                                                    inactive-label="Manual"
                                                    v-model="resource.scheduled_restarts_activado"
                                                />
                                            </v-col>
                                            <v-col cols="3">
                                                <DefaultInput
                                                    label="Días"
                                                    v-model="resource.scheduled_restarts_dias"
                                                    :disabled="!resource.scheduled_restarts_activado"
                                                    type="number"
                                                    dense
                                                />
                                            </v-col>
                                            <v-col cols="3">
                                                <DefaultInput
                                                    label="Horas"
                                                    v-model="resource.scheduled_restarts_horas"
                                                    :disabled="!resource.scheduled_restarts_activado"
                                                    type="number"
                                                    dense
                                                />
                                            </v-col>
                                            <v-col cols="3">
                                                <DefaultInput
                                                    label="Minutos"
                                                    v-model="resource.scheduled_restarts_minutos"
                                                    :disabled="!resource.scheduled_restarts_activado"
                                                    type="number"
                                                    dense
                                                />
                                            </v-col>
                                        </v-row>
                                        <div class="d-flex justify-content-center mt-1" v-if="showErrorReinicios">
                                            <div style="color: #FF5252" class="v-messages__wrapper">
                                                <div class="v-messages__message">Validar hora de reinicio</div>
                                            </div>
                                        </div>
                                    </template>
                                </DefaultModalSectionExpand>
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
                                                />
                                            </v-col>
                                        </v-row>

                                    </template>
                                </DefaultModalSectionExpand>
                                </v-form>
                            </v-stepper-content>
                            <v-stepper-content step="2" class="p-0">
                                <v-row>
                                    <v-col cols="12" v-if="loadStep2">
                                        <tema-preguntas-layout
                                            :modulo_id="options.school_id"
                                            modulo_name="escruela_name"
                                            :categoria_id="options.school_id"
                                            categoria_name="escruela_name"
                                            :curso_id="course_id"
                                            curso_name="curso_name"
                                            :tema_id="resource.topic"
                                            tema_name=""
                                            :evaluable="'' + resource.topic_data.type_evaluation_id"
                                            :status="false"
                                            :missing_score="resource.topic_data.verify_evaluation.score_missing"
                                            :qualification_type="resource.qualification_type ? resource.qualification_type.type : ''"
                                            :qualification_type_value="resource.qualification_type ? resource.qualification_type.position : 0"
                                            :qualification_type_name="resource.qualification_type ? resource.qualification_type.name : ''"
                                            :evaluation_type="resource.topic_data.evaluation_type"
                                            :evaluation_data_sum="resource.topic_data.verify_evaluation.sum"
                                            :evaluation_data_sum_required="resource.topic_data.verify_evaluation.sum_required"
                                            :evaluation_data_sum_not_required="resource.topic_data.verify_evaluation.sum_not_required"
                                            :custom_breadcrumbs="breadcrumbs"
                                            :show_breadcrumbs="false"
                                            >
                                        </tema-preguntas-layout>
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
            </template>
        </DefaultDialog>
    </div>
</template>

<script>

const fields = [
    'titulo',
    'description',
    'required',
    'requirement',
    'model_id',
    'model_type',
    'qualification_type',
    'reinicios_programado',
    'topic',
    'course'
];

export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String,
        curso_id: String,
        proceso_text: {
            type: String,
            default: ''
        },
        etapa_text: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            course_id: null,
            breadcrumbs: [
                {
                    title: 'Proceso de inducción',
                    text: this.proceso_text,
                    disabled: true,
                    href: ''
                },
                {
                    title: '',
                    text: 'Evaluación',
                    disabled: true,
                    href: ''
                },
            ],
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
                model_type: null,
                name: '',
                description: '',
                required: false,
                requirement: null,
                nota_aprobatoria: null,
                nro_intentos: null,
                scheduled_restarts_activado: false,
                scheduled_restarts_dias: null,
                scheduled_restarts_horas: null,
                scheduled_restarts_minutos: 1,
                qualification_type: {
                    position: 0,
                    name: '',
                    type: ''
                },
                topic_data: {
                    type_evaluation_id: null,
                    evaluation_type: null,
                    verify_evaluation: {
                        score_missing: 0,
                        sum_not_required: 0,
                        sum: 0,
                        sum_required: 0
                    }
                }
            },
            resource: {
                qualification_type: {
                    position: 0,
                    name: '',
                    type: ''
                },
                requirement: {position: 0},
                topic_data: {
                    type_evaluation_id: null,
                    evaluation_type: null,
                    verify_evaluation: {
                        score_missing: 0,
                        sum_not_required: 0,
                        sum: 0,
                        sum_required: 0
                    }
                }
            },
            sections: {
                showSectionAdvanced: {status: false},
                showSectionReinicios: {status: false},
                showSectionCalificacion: {status: true},
            },
            selects: {
                requirement_list: [{'code':'none', 'name': 'Sin requisito'}],
                topic_list: [],
                qualification_types: [],
            },
            rules: {
                titulo: this.getRules(['required', 'max:255']),
                nota_aprobatoria: this.getRules(['required']),
                nro_intentos: this.getRules(['required', 'number', 'min_value:1']),
                qualification_type_id: this.getRules(['required']),
                topic_list: this.getRules(['required']),
            },
            resources: [],
            search: null,
            isLoading: false,
            debounceTimer: null,
            file: null,
            edit_resource: false,
            fileSelected: null,
            new_value: 0,
        };
    },
    computed: {
        showErrorReinicios() {
            let vue = this
            const reinicio = vue.resource.scheduled_restarts
            const dias = vue.resource.scheduled_restarts_dias
            const horas = vue.resource.scheduled_restarts_horas
            const minutos = vue.resource.scheduled_restarts_minutos
            if (!reinicio) {
                return false
            }
            if (dias > 0 || horas > 0 || minutos > 0) {
                return false
            }
            return true
        },
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
                const validateForm = vue.validateForm('projectForm')
                vue.options.confirmDisabled = !validateForm
            },
            deep: true
        },
        'resource.qualification_type': function (newValue, oldValue) {
            let vue = this
            let value = vue.resource.nota_aprobatoria

            if (newValue) {
                if (value && newValue.position && oldValue.position) {

                    vue.new_value = value * newValue.position / oldValue.position
                    vue.resource.nota_aprobatoria = parseFloat(vue.new_value.toFixed(2))
                }
            }
        },
        'resource.topic': function (newValue, oldValue) {
            let vue = this

            if (newValue) {
                let vue = this;
                let url = `${vue.options.base_endpoint}/topic/${newValue}`

                vue.$http.get(url)
                    .then(({data}) => {
                        let topic = data.data.topic
                        if(topic) {
                            vue.resource.topic_data.type_evaluation_id = topic.type_evaluation_id
                            vue.resource.topic_data.evaluation_type = topic.evaluation_type
                        }
                    })
            }
        },
    },
    methods: {
        closeModal()
        {
            let vue = this;

            vue.resetSelects()
            vue.resetValidation()
            vue.$refs.projectForm.reset()

            vue.stepper_box = 1
            vue.width = '868px';
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
                vue.width = '868px';
                vue.loadStep2 = false
            }
            else if(vue.stepper_box == 1){
                vue.closeModal()
            }
        },
        async confirmModal()
        {
            let vue = this;

            vue.options.cancelLabel = "Retroceder";
            vue.options.confirmLabel = "Guardar";

            if(vue.stepper_box == 1)
            {
                vue.errors = []

                this.showLoader()

                const validateForm = vue.validateForm('projectForm')
                const edit = vue.options.action === 'edit'

                let base = `${vue.options.base_endpoint}`
                let url = edit
                    ? `${base}/${vue.resource.activity_id}/update`
                    : `${base}/store`;

                let method = 'POST';

                if (validateForm) {
                    const formData = vue.createFormData(method, validateForm);
                    await vue.$http
                        .post(url, formData)
                        .then(({ data }) => {
                            vue.course_id = data.data.course
                            let verify_evaluation = data.data.verify_evaluation.data
                            let topic_info = data.data.topic
                            if(topic_info) {
                                vue.resource.topic_data.type_evaluation_id = topic_info.type_evaluation_id
                                vue.resource.topic_data.evaluation_type = topic_info.evaluation_type
                            }
                            if(verify_evaluation) {
                                vue.resource.topic_data.verify_evaluation = {
                                    score_missing: verify_evaluation.score_missing,
                                    sum_not_required: verify_evaluation.sum_not_required,
                                    sum: verify_evaluation.sum,
                                    sum_required: verify_evaluation.sum_required
                                }
                            }

                            this.hideLoader()
                            vue.stepper_box = 2;
                            vue.width = '1280px';
                            vue.loadStep2 = true

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
                await vue.$http.get(url).then(({ data }) => {
                    let _data = data.data

                    vue.resource = Object.assign({}, vue.resource, _data.curso)
                    vue.resource.titulo = _data.activity.title
                    vue.resource.description = _data.activity.description
                    vue.resource.activity_id = _data.activity.id
                    vue.resource.nota_aprobatoria = _data.curso.mod_evaluaciones.nota_aprobatoria;
                    vue.resource.nro_intentos = _data.curso.mod_evaluaciones.nro_intentos;
                    vue.resource.qualification_type = _data.topic.qualification_type
                    vue.resource.topic = _data.topic.id
                    vue.resource.requirement = _data.activity.activity_requirement_id
                })
                const validateForm = vue.validateForm('projectForm')
                vue.options.confirmDisabled = !validateForm
            } else {
                vue.resource.id = null;
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
                    vue.selects.topic_list = data.data.topics
                    vue.selects.qualification_types = data.data.qualification_types
                    // vue.resource.qualification_type = data.data.qualification_type
                })
        },
        createFormData(method, validateForm)
        {
            let vue = this;

            vue.resource.model_id = vue.options.model_id;
            vue.resource.model_type = 'App\\Models\\Stage';

            vue.resource.course = vue.options.course_id;
            vue.resource.school = vue.options.school_id;

            let formData = vue.getMultipartFormData(method, vue.resource, fields);
            formData.append('validateForm', validateForm ? "1" : "0");
            vue.setJSONReinicioProgramado(formData)
            vue.getJSONEvaluaciones(formData)

            return formData;
        },
        setJSONReinicioProgramado(formData)
        {
            let vue = this
            const minutes = parseInt(vue.resource.scheduled_restarts_minutos) +
                (parseInt(vue.resource.scheduled_restarts_horas) * 60) +
                (parseInt(vue.resource.scheduled_restarts_dias) * 1440)
            const data = {
                activado: vue.resource.scheduled_restarts_activado,
                tiempo_en_minutos: minutes,
                reinicio_dias: vue.resource.scheduled_restarts_dias,
                reinicio_horas: vue.resource.scheduled_restarts_horas,
                reinicio_minutos: vue.resource.scheduled_restarts_minutos,
            }

            let json = JSON.stringify(data)
            formData.append('reinicios_programado', json)
        },
        getJSONEvaluaciones(formData)
        {
            let vue = this

            const data = {
                // preg_x_ev: vue.resource.preg_x_ev,
                nota_aprobatoria: vue.resource.nota_aprobatoria,
                nro_intentos: vue.resource.nro_intentos,
            }
            let json = JSON.stringify(data)
            formData.append('mod_evaluaciones', json)
        },
    }
}
</script>
