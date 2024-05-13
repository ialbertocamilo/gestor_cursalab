<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="ModuloForm">
                <DefaultErrors :errors="errors" />
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
                    <v-col cols="12">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputLogo"
                            v-model="resource.logo"
                            label="Logo (400x142px) "
                            :file-types="['image']"
                            @onSelect="setFile($event, resource,'logo')"/>
                    </v-col>
                    <v-col cols="6" class="d-none">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputPlantillaDiploma"
                            v-model="resource.plantilla_diploma"
                            label="Plantilla de diploma (1743x1553px)"
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

                <v-row v-if="has_benefits_functionality"
                    justify="space-around" class="menuable">
                    <v-col cols="12">
                        <DefaultModalSectionExpand
                            v-if="resource.benefits_configuration"
                            title="Configuración de beneficios"
                            :expand="sections.showSectionBenefits"
                            class="mt-4"
                        >
                            <template slot="content">
                                <v-row justify="start">
                                    <v-col cols="6">
                                        <DefaultInput
                                            clearable
                                            v-model="resource.benefits_configuration.default_group_name"
                                            label="Nombre del grupo por defecto"
                                            dense
                                            show-required
                                            placeholder=""
                                        />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultModalSectionExpand>
                    </v-col>
                </v-row>

                <v-row justify="space-around" class="menuable">
                    <v-col cols="12">

                        <DefaultModalSectionExpand
                            title="Configuración de soporte"
                            :expand="sections.showSectionSoporte"
                            class="mt-4"
                        >
                            <template slot="content">

                        <!-- <DefaultModalSection -->
                            <!-- title="Soporte" -->
                        <!-- > -->
                            <!-- tooltip="Tooltip" -->
                            <!-- <template slot="content"> -->
                                <v-row justify="center">
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Correo de contacto"
                                            v-model="resource.contact_email"
                                            :rules="rules.contact_email"
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Teléfono de contacto"
                                            v-model="resource.contact_phone"
                                            :rules="rules.contact_phone"
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Horario de atención"
                                            v-model="resource.contact_schedule"
                                            :rules="rules.contact_schedule"
                                            dense
                                        />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultModalSectionExpand>
                    </v-col>
                </v-row>
                <v-row justify="space-around" class="menuable">
                    <v-col cols="12">
                        <DefaultModalSectionExpand
                            title="Configuración de diploma"
                            :expand="sections.showSectionCertificate"
                            class="my-4"
                        >
                            <!-- tooltip="Tooltip" -->
                            <template slot="content">
                                <DiplomaSelector
                                    v-model="resource.certificate_template_id" :old-preview="resource.plantilla_diploma" />
                            </template>
                        </DefaultModalSectionExpand>
                    </v-col>
                </v-row>

                <v-row v-if="has_registro_capacitacion_functionality && $root.isSuperUser"
                       justify="space-around" class="menuable">
                    <v-col cols="12">
                        <DefaultModalSectionExpand
                            title="Información para registro de capacitación"
                            :expand="sections.showSectionRegistroCapacitacion"
                            class="my-4"
                        >
                            >
                            <template slot="content">
                                <div v-if="resource.registro_capacitacion">
                                    <v-row v-if="resource.registro_capacitacion.company"
                                           justify="center">
                                        <v-col cols="6">
                                            <DefaultInput
                                                label="Razón Social"
                                                v-model="resource.registro_capacitacion.company.businessName"
                                                :rules="rules.businessName"
                                                dense
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                            <DefaultInput
                                                label="RUC"
                                                v-model="resource.registro_capacitacion.company.businessNumber"
                                                :rules="rules.businessNumber"
                                                dense
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                            <DefaultInput
                                                label="CIIU"
                                                v-model="resource.registro_capacitacion.company.CIIU"
                                                :rules="rules.CIIU"
                                                dense
                                            />
                                        </v-col>
                                    </v-row>

                                    <v-row v-if="resource.registro_capacitacion.company"
                                           justify="center">
                                        <v-col :cols="resource.registro_capacitacion.company.address ? 8 : 4">
                                            <DefaultInput
                                                label="Dirección"
                                                v-model="resource.registro_capacitacion.company.address"
                                                dense
                                            />
                                        </v-col>
                                        <v-col v-if="!resource.registro_capacitacion.company.address"
                                               cols="4">
                                            <DefaultSelect
                                                dense
                                                :items="selects.workspace_criteria"
                                                item-text="name"
                                                return-object
                                                show-required
                                                v-model="resource.registro_capacitacion.criteriaAddress"
                                                label="Criterio para dirección"
                                            />

                                        </v-col>
                                        <v-col cols="4">
                                            <DefaultInput
                                                label="Actividad económica"
                                                v-model="resource.registro_capacitacion.company.economicActivity"
                                                :rules="rules.economicActivity"
                                                dense
                                            />
                                        </v-col>
                                    </v-row>

                                    <v-row v-if="resource.registro_capacitacion.company"
                                           justify="left">
                                        <v-col cols="6">
                                            <DefaultSelect
                                                dense
                                                :items="selects.workspace_criteria"
                                                item-text="name"
                                                return-object
                                                show-required
                                                v-model="resource.registro_capacitacion.criteriaWorkersCount"
                                                label="Criterio para conteo de trabajadores"
                                                :rules="rules.criteriaWorkersCount"
                                            />
                                        </v-col>
                                        <v-col cols="6">
                                            <DefaultInput
                                                label="URL de la app"
                                                v-model="resource.registro_capacitacion.company.appUrl"
                                                :rules="rules.appUrl"
                                                dense
                                            />
                                        </v-col>
                                    </v-row>
                                    <v-row v-if="resource.registro_capacitacion.company"
                                           justify="left">
                                        <v-col cols="6">
                                            <DefaultSelect
                                                dense
                                                :items="selects.workspace_criteria"
                                                item-text="name"
                                                return-object
                                                show-required
                                                v-model="resource.registro_capacitacion.criteriaJobPosition"
                                                label="Criterio para cargo del trabajador"
                                                :rules="rules.criteriaJobPosition"
                                            />
                                        </v-col>
                                        <v-col cols="6">
                                            <DefaultSelect
                                                dense
                                                :items="selects.workspace_criteria"
                                                item-text="name"
                                                return-object
                                                show-required
                                                v-model="resource.registro_capacitacion.criteriaArea"
                                                label="Criterio para área del trabajador"
                                                :rules="rules.criteriaArea"
                                            />
                                        </v-col>
                                    </v-row>
                                </div>
                            </template>
                        </DefaultModalSectionExpand>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="3">
                        <DefaultToggle
                            v-model="resource.active"
                            active-label="Módulo activo"
                            inactive-label="Módulo inactivo"
                            dense />
                    </v-col>
                    <v-col cols="3">
                    </v-col>

                    <v-col cols="6">
                        <DefaultToggle
                            v-if="$root.isSuperUser"
                            class="--mt-5"
                            dense
                            v-model="resource.show_logo_in_app"
                            active-label="Mostrar logo de módulo en la aplicación"
                            inactive-label="No mostrar logo de módulo en la aplicación"
                        />
                    </v-col>
                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>
import DefaultRichText from "../../components/globals/DefaultRichText";
import draggable from 'vuedraggable'
import DiplomaSelector from "../../components/Diplomas/DiplomaSelector.vue";

const fields = ['name', 'codigo_matricula', 'active', 'reinicios_programado',
    'app_menu', 'mod_evaluaciones', 'plantilla_diploma', 'logo', 'certificate_template_id', 'show_logo_in_app', 'registro_capacitacion'];
const file_fields = ['logo', 'plantilla_diploma'];
export default {
    components: {DiplomaSelector, DefaultRichText, draggable},
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
            errors: [],
            drag: {
                main_menu: false,
                side_menu: false,
            },
            sections: {
                showSectionCertificate: {status: true},
                showSectionSoporte: {status: true},
                showSectionRegistroCapacitacion: {status: true},
                showSectionBenefits: {status: true}
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
                // preg_x_ev: null,
                nota_aprobatoria: null,
                nro_intentos: null,
                certificate_template_id: null,
                registro_capacitacion: {
                    company: { }
                },
                benefits_configuration : {}
            },
            rules: {
                name: this.getRules(['required']),
                plantilla_diploma: this.getRules(['required']),
                codigo_matricula: this.getRules(['required']),
                nota_aprobatoria: this.getRules(['required', 'number', 'min_value:1']),
                // preg_x_ev: this.getRules(['required', 'number', 'min_value:1']),
                nro_intentos: this.getRules(['required', 'number', 'min_value:1']),

                businessName: this.getRules(['required']),
                businessNumber: this.getRules(['required']),
                CIIU: this.getRules(['required']),
                economicActivity: this.getRules(['required']),
                appUrl: this.getRules(['required']),
                criteriaWorkersCount: this.getRules(['required']),
                criteriaJobPosition: this.getRules(['required']),
                criteriaArea: this.getRules(['required']),
            },
            resource: {},
            selects: {
                main_menu: [],
                side_menu: [],
                workspace_criteria: []
            },
            has_registro_capacitacion_functionality: false,
            has_benefits_functionality: false,
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
            vue.errors = [];

            if (validateForm && validateReinicio && vue.isValid()) {
                const edit = vue.options.action === 'edit'
                let url = `${vue.options.base_endpoint}/${edit ? `${vue.resource.id}/update` : 'store'}`
                let method = edit ? 'PUT' : 'POST';

                const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
                formData.set('registro_capacitacion', JSON.stringify(vue.resource.registro_capacitacion))
                formData.set('benefits_configuration', JSON.stringify(vue.resource.benefits_configuration))

                vue.getActiveOnly(formData)
                vue.getJSONReinicioProgramado(formData)
                vue.getJSONEvaluaciones(formData)
                vue.getJSONSoporte(formData)
                vue.$http.post(url, formData)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()
                        vue.queryStatus("modulo", "crear_modulo");
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
        getJSONSoporte(formData) {
            let vue = this

            const data = {
                contact_phone: vue.resource.contact_phone,
                contact_email: vue.resource.contact_email,
                contact_schedule: vue.resource.contact_schedule,
            }
            let json = JSON.stringify(data)
            formData.append('contact_support', json)
        },
        getJSONEvaluaciones(formData) {
            let vue = this

            const data = {
                // preg_x_ev: vue.resource.preg_x_ev,
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
                    vue.selects.workspace_criteria = data.data.workspace_criteria
                    vue.has_registro_capacitacion_functionality = data.data.has_registro_capacitacion_functionality
                    vue.has_benefits_functionality = data.data.has_benefits_functionality
                    if (resource) {
                        vue.resource = Object.assign({}, data.data.modulo)
                    }
                })
            return 0;
        },
        loadSelects() {
            let vue = this

        },
        isValid() {

            let valid = true;
            let errors = [];

            // Validation: address or address criteria should be selected

            if (this.has_registro_capacitacion_functionality) {
                if (!this.resource.registro_capacitacion.company.address &&
                    !this.resource.registro_capacitacion.criteriaAddress) {
                    errors.push({
                        message: 'Debe definir la dirección o criterio para obtener la direccióm'
                    })
                    valid = false;
                }
            }

            if (!valid) {
                this.errors = errors;
            }

            return valid;
        }
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
