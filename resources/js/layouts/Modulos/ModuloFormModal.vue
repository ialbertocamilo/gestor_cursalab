<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="ModuloForm">
                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            :rules="rules.name"
                            label="Nombre"
                            dense
                            show-required
                            placeholder="Ingrese un nombre"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.codigo_matricula"
                            label="Código alfanumérico"
                            dense
                            show-required
                            placeholder="Ingrese un código"
                            :rules="rules.codigo_matricula"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputLogo"
                            v-model="resource.logo"
                            label="Logo"
                            :file-types="['image']"
                            @onSelect="setFile($event, resource,'logo')"/>
                    </v-col>
                    <v-col cols="6">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputPlantillaDiploma"
                            v-model="resource.plantilla_diploma"
                            label="Plantilla de diploma"
                            :file-types="['image']"
                            @onSelect="setFile($event, resource,'plantilla_diploma')"/>
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-start flex-column">
                        <DefaultFormLabel
                            label="Menú principal"
                        />
                        <draggable v-model="selects.main_menu"
                                   group="main_menu"
                                   @start="drag.main_menu=true"
                                   @end="drag.main_menu=false"
                                   class="w-100 box-scrollable"
                                   ghost-class="ghost"
                                   :disabled="true"
                        >
                            <transition-group type="transition" name="flip-list">
                                <div v-for="(element, value) in selects.main_menu" :key="element.id"
                                     class="element_draggable">
                                    <div>
                                        <input type="checkbox" class="mx-2"
                                               v-model="element.active">
                                        {{ element.name }}
                                    </div>
<!--                                    <v-icon color="grey" v-text="'mdi-drag'"/>-->
                                </div>
                            </transition-group>
                        </draggable>
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-start flex-column">
                        <DefaultFormLabel
                            label="Menú secundario"
                        />
                            <!-- tooltip="Tooltip" -->
                        <draggable v-model="selects.side_menu"
                                   group="main_menu"
                                   @start="drag.side_menu=true"
                                   @end="drag.side_menu=false"
                                   class="w-100 box-scrollable"
                                   ghost-class="ghost"
                                   :disabled="true"
                        >
                            <transition-group type="transition" name="flip-list">
                                <div v-for="(element,value) in selects.side_menu" :key="element.id"
                                     class="element_draggable">
                                    <div>
                                        <input type="checkbox" class="mx-2"
                                               v-model="element.active">
                                        {{ element.name }}
                                    </div>
<!--                                    <v-icon color="grey" v-text="'mdi-drag'"/>-->
                                </div>
                            </transition-group>
                        </draggable>
                    </v-col>
                </v-row>
                <v-row justify="space-around" class="menuable">
                    <v-col cols="12">
                        <DefaultModalSection
                            title="Evaluaciones"
                        >
                            <!-- tooltip="Tooltip" -->
                            <template slot="content">
                                <v-row justify="center">
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Preguntas por evaluación"
                                            v-model="resource.preg_x_ev"
                                            :rules="rules.preg_x_ev"
                                            show-required
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Nota aprobatoria"
                                            v-model="resource.nota_aprobatoria"
                                            :rules="rules.nota_aprobatoria"
                                            show-required
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Número de intentos"
                                            v-model="resource.nro_intentos"
                                            :rules="rules.nro_intentos"
                                            show-required
                                            dense
                                        />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultModalSection>
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <!--                    <v-col cols="3">-->
                    <!--                        <DefaultFormLabel-->
                    <!--                            label="Reinicios automáticos"-->
                    <!--                            tooltip="Tooltip"-->
                    <!--                        />-->
                    <!--                        <DefaultToggle-->
                    <!--                            v-model="resource.reinicio_automatico"-->
                    <!--                        />-->
                    <!--                    </v-col>-->
                    <!--                    <v-col cols="5">-->
                    <!--                        <DefaultFormLabel-->
                    <!--                            label="Programación de reinicios"-->
                    <!--                        />-->
                    <!--                        <div class="date_reinicios d-flex flex-row justify-content-around"-->
                    <!--                             :class="{'date_reinicios_disabled': !resource.reinicio_automatico,-->
                    <!--                             'date_reinicios_error': showErrorReinicios}"-->
                    <!--                        >-->
                    <!--                            <span class="box_date_reinicios d-flex flex-column align-items-center">-->
                    <!--                                <label for="days_date_reinicios">Días</label>-->
                    <!--                                <input type="number" class="input_date_reinicios" id="days_date_reinicios"-->
                    <!--                                       min="0"-->
                    <!--                                       v-model="resource.reinicio_automatico_dias">-->
                    <!--                            </span>-->
                    <!--                            <span class="box_date_reinicios d-flex flex-column align-items-center">-->
                    <!--                                <label for="horas_date_reinicios">Horas</label>-->
                    <!--                                <input type="number" class="input_date_reinicios" id="horas_date_reinicios"-->
                    <!--                                       min="0"-->
                    <!--                                       v-model="resource.reinicio_automatico_horas">-->
                    <!--                            </span>-->
                    <!--                            <span class="box_date_reinicios d-flex flex-column align-items-center">-->
                    <!--                                <label for="minutos_date_reinicios">Minutos</label>-->
                    <!--                                <input type="number" class="input_date_reinicios" id="minutos_date_reinicios"-->
                    <!--                                       min="0"-->
                    <!--                                       v-model="resource.reinicio_automatico_minutos">-->
                    <!--                            </span>-->
                    <!--                        </div>-->
                    <!--                        <small class="ml-2 date_reinicios_error_message" v-if="showErrorReinicios">-->
                    <!--                            texto de error-->
                    <!--                        </small>-->

                    <!--                    </v-col>-->
                    <v-col cols="12">
                        <DefaultModalSection
                            title="Programación de reinicios"
                        >
                            <!-- tooltip="Tooltip" -->
                            <template slot="content">
                                <v-row justify="center">
                                    <v-col cols="3" class="d-flex justify-content-center align-items-center">
                                        <DefaultToggle
                                            active-label="Automático"
                                            inactive-label="Manual"
                                            v-model="resource.reinicio_automatico"
                                        />
                                    </v-col>
                                    <v-col cols="3">
                                        <DefaultInput
                                            label="Días"
                                            v-model="resource.reinicio_automatico_dias"
                                            :disabled="!resource.reinicio_automatico"
                                            type="number"
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="3">
                                        <DefaultInput
                                            label="Horas"
                                            v-model="resource.reinicio_automatico_horas"
                                            :disabled="!resource.reinicio_automatico"
                                            type="number"
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="3">
                                        <DefaultInput
                                            label="Minutos"
                                            v-model="resource.reinicio_automatico_minutos"
                                            :disabled="!resource.reinicio_automatico"
                                            type="number"
                                            dense
                                        />
                                    </v-col>
                                </v-row>
                                <div class="d-flex justify-content-center mt-1"
                                     v-if="showErrorReinicios">
                                    <div style="color: #FF5252" class="v-messages__wrapper">
                                        <div class="v-messages__message">Validar hora de reinicio</div>
                                    </div>
                                </div>
                            </template>
                        </DefaultModalSection>
                    </v-col>
                </v-row>
                <v-row>

                    <v-col cols="2">
                        <!--                        <DefaultFormLabel-->
                        <!--                            label="Estado"-->
                        <!--                        />-->
                        <DefaultToggle v-model="resource.active"/>
                    </v-col>
                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>
import DefaultRichText from "../../components/globals/DefaultRichText";
import draggable from 'vuedraggable'

const fields = ['name', 'codigo_matricula', 'active', 'reinicios_programado',
    'app_menu', 'mod_evaluaciones', 'plantilla_diploma', 'logo'];
const file_fields = ['logo', 'plantilla_diploma'];
export default {
    components: {DefaultRichText, draggable},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            drag: {
                main_menu: false,
                side_menu: false,
            },
            resourceDefault: {
                id: null,
                name: null,
                logo: null,
                file_logo: null,
                plantilla_diploma: null,
                file_plantilla_diploma: null,
                codigo_matricula: null,
                active: false,
                reinicio_automatico: false,
                reinicio_automatico_dias: null,
                reinicio_automatico_horas: null,
                reinicio_automatico_minutos: 1,
                main_menu: null,
                preg_x_ev: null,
                nota_aprobatoria: null,
                nro_intentos: null,
            },
            rules: {
                name: this.getRules(['required']),
                plantilla_diploma: this.getRules(['required']),
                codigo_matricula: this.getRules(['required']),
                preg_x_ev: this.getRules(['required', 'number', 'min_value:1']),
                nota_aprobatoria: this.getRules(['required', 'number', 'min_value:1']),
                nro_intentos: this.getRules(['required', 'number', 'min_value:1']),
            },
            resource: {},
            selects: {
                main_menu: [],
                side_menu: [],
            },
            error_reinicios: false
        }
    },
    computed: {
        showErrorReinicios() {
            let vue = this
            const reinicio = vue.resource.reinicio_automatico
            const dias = vue.resource.reinicio_automatico_dias
            const horas = vue.resource.reinicio_automatico_horas
            const minutos = vue.resource.reinicio_automatico_minutos

            if (!reinicio) {
                return false
            }

            if (dias >= 0 && horas >= 0 && minutos > 0) {
                return false
            }

            return true
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.resetSelects()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.resetFormValidation('ModuloForm')
        },
        resetSelects() {
            let vue = this
            // Limpiar inputs file
            vue.removeFileFromDropzone(vue.resource.logo, 'inputLogo')
            vue.removeFileFromDropzone(vue.resource.plantilla_diploma, 'inputPlantillaDiploma')
            // Selects independientes
            // Selects dependientes
            vue.resource = Object.assign({}, {})
        },
        confirmModal() {
            let vue = this
            this.showLoader()
            const validateForm = vue.validateForm('ModuloForm')
            const validateReinicio = vue.validateReinicio();
            if (validateForm && validateReinicio) {
                const edit = vue.options.action === 'edit'
                let url = `${vue.options.base_endpoint}/${edit ? `${vue.resource.id}/update` : 'store'}`
                let method = edit ? 'PUT' : 'POST';

                const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
                vue.getActiveOnly(formData)
                vue.getJSONReinicioProgramado(formData)
                vue.getJSONEvaluaciones(formData)
                vue.$http.post(url, formData)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()
                    })
            } else {
                this.hideLoader()
            }
        },
        getActiveOnly(formData) {
            let vue = this
            vue.selects.main_menu.forEach(el => {
                if (el.active)
                    formData.append('app_menu[]', el.id)
            })
            vue.selects.side_menu.forEach(el => {
                if (el.active)
                    formData.append('app_menu[]', el.id)
            })
            return 1
        },
        getJSONEvaluaciones(formData) {
            let vue = this

            const data = {
                preg_x_ev: vue.resource.preg_x_ev,
                nota_aprobatoria: vue.resource.nota_aprobatoria,
                nro_intentos: vue.resource.nro_intentos,
            }
            let json = JSON.stringify(data)
            formData.append('mod_evaluaciones', json)
        },
        getJSONReinicioProgramado(formData) {
            let vue = this
            const minutes = parseInt(vue.resource.reinicio_automatico_minutos) +
                (parseInt(vue.resource.reinicio_automatico_horas) * 60) +
                (parseInt(vue.resource.reinicio_automatico_dias) * 1440)
            const data = {
                activado: vue.resource.reinicio_automatico,
                tiempo_en_minutos: minutes,
                reinicio_dias: vue.resource.reinicio_automatico_dias,
                reinicio_horas: vue.resource.reinicio_automatico_horas,
                reinicio_minutos: vue.resource.reinicio_automatico_minutos,
            }
            let json = JSON.stringify(data)
            formData.append('reinicios_programado', json)
        },
        validateReinicio() {
            let vue = this
            vue.error_reinicios = false
            const reinicio = vue.resource.reinicio_automatico
            const dias = vue.resource.reinicio_automatico_dias
            const horas = vue.resource.reinicio_automatico_horas
            const minutos = vue.resource.reinicio_automatico_minutos

            if (!reinicio) {
                return true
            }
            if (dias > 0 || horas > 0 || minutos > 0) {
                return true
            }
            vue.error_reinicios = true
            return false
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.options.base_endpoint}/${resource ? `${resource.id}/edit` : `form-selects`}`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.main_menu = data.data.main_menu
                    vue.selects.side_menu = data.data.side_menu
                    if (resource) {
                        vue.resource = Object.assign({}, data.data.modulo)
                    }
                })
            return 0;
        },
        loadSelects() {
            let vue = this

        },

    },
}
</script>
<style lang="scss">
@import "resources/sass/variables";

.date_reinicios_disabled {
    pointer-events: none;
    padding: 10px 0;
    border-radius: 9px;
    opacity: 0.3;
    background: #CCC;
}

.date_reinicios_error {
    padding: 10px 0;
    border: #FF5252 2px solid;
    border-radius: 5px;
}

.date_reinicios_error_message {
    line-height: 12px;
    word-break: break-word;
    overflow-wrap: break-word;
    word-wrap: break-word;
    -webkit-hyphens: auto;
    -ms-hyphens: auto;
    hyphens: auto;
    font-weight: 400;
    color: #FF5252;
    caret-color: #FF5252;
}

.box_date_reinicios {
    background: $primary-default-color;
    padding: 2px 9px 9px 9px;
    border-radius: 5px;

    label {
        color: white;
    }

    .input_date_reinicios {
        appearance: textfield;
        -moz-appearance: textfield;
        text-align: center;
        background: white;
        width: 50px;
        height: 30px;
    }
}


</style>
