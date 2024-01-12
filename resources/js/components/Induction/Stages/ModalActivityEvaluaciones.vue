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
                                            <v-col cols="4">
                                                <DefaultSelect
                                                    clearable
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

                                            <v-col cols="4">
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

                                            <v-col cols="4">
                                                <DefaultInput
                                                    label="Número de intentos"
                                                    v-model="resource.nro_intentos"
                                                    :rules="rules.nro_intentos"
                                                    type="number"
                                                    show-required
                                                    dense
                                                />
                                            </v-col>

                                            <v-col cols="12" class="py-1">
                                                <p class="mb-0">** Utilizado para mostrar el resultado del curso y que se tendrá por defecto en la creación de temas.</p>
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
                                    class="my-4"
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
                            </v-stepper-content>
                            <v-stepper-content step="2" class="p-0">
                                <v-row>
                                    <v-col cols="12" v-if="loadStep2">
                                        <tema-preguntas-layout
                                            :modulo_id="options.school_id" modulo_name="escruela_name"
                                            :categoria_id="options.school_id" categoria_name="escruela_name"
                                            :curso_id="course_id" curso_name="curso_name"
                                            :tema_id="resource.topic" tema_name=""
                                            evaluable="4578"
                                            :status="false"
                                            missing_score="20"
                                            qualification_type="vigesimal"
                                            qualification_type_value="20"
                                            qualification_type_name="Sistema vigesimal (0-20)"
                                            evaluation_type="qualified"
                                            evaluation_data_sum="0"
                                            evaluation_data_sum_required="0"
                                            evaluation_data_sum_not_required="0"
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
                </v-form>
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
        curso_id: String
    },
    data() {
        return {
            course_id: null,
            breadcrumbs: [
                {
                    title: 'Proceso de inducción',
                    text: '',
                    disabled: false,
                    href: ''
                },
                {
                    title: 'Etapa',
                    text: '',
                    disabled: false,
                    href: ''
                },
                {
                    title: 'Evaluación',
                    text: null,
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
            },
            resource: {
                qualification_type: {position: 0},
                requirement: {position: 0},
            },
            sections: {
                showSectionAdvanced: {status: false},
                showSectionReinicios: {status: false},
                showSectionCalificacion: {status: false},
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
                vue.options.confirmDisabled = !vue.validateForm('projectForm')
            },
            deep: true
        }
    },
    methods: {
        closeModal()
        {
            let vue = this;
            vue.resetSelects()
            vue.resetValidation()
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
                    ? `${base}/${vue.resource.id}/update`
                    : `${base}/store`;

                let method = edit ? 'PUT' : 'POST';

                if (validateForm) {
                    const formData = vue.createFormData(method, validateForm);
                    await vue.$http
                        .post(url, formData)
                        .then(({ data }) => {
                            console.log(data.data.course);
                            vue.course_id = data.data.course
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
                let url = `${base}/${resource.id}/edit`
                await vue.$http.get(url).then(({ data }) => {
                    vue.resource.id = data.data.id;
                    vue.resource.model_id = data.data.model_id;
                    vue.resource.model_type = data.data.model_type;
                    vue.resource.indications = data.data.indications;
                    vue.resource.course_name = data.data.course_name;
                    vue.resource.count_file = data.data.count_file;
                    vue.resources = data.data.resources;
                    vue.selects.courses.push(data.data.course);
                })
                console.log(2);
            } else {
                console.log(3);
                vue.resource.id = null;
                vue.resource.model_id = vue.options.model_id;
                vue.resource.model_type = 'App\\Models\\Stage';
                vue.resource.indications = null;
                vue.resource.course_name = null;
                vue.resource.count_file = null;
                vue.resources = [];
                vue.selects.courses = [];
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
