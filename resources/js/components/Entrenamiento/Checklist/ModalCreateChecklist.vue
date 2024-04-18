

<template>
    <div>
        <v-dialog :max-width="width" v-model="value" scrollable @click:outside="closeModal">
            <v-card>
                <v-card-title class="default-dialog-title">
                    {{ checklist.id == 0 ? "Crear Checklist" : "Editar Checklist" }}
                    <v-spacer/>
                    <v-btn icon :ripple="false" color="white"
                           @click="closeModal">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text class="pt-0">
                    <v-card style="height: 100%;overflow: auto;" class="bx_steps bx_step1">
                        <v-card-text>
                            <v-row>
                                <v-col cols="7">
                                    <DefaultInput 
                                        dense
                                        clearable
                                        v-model="resource.title"
                                        label="Título de checklist"
                                        placeholder="Escribe el titulo del checklist aquí"
                                    />
                                </v-col>
                                <v-col cols="5">
                                    <DefaultAutocomplete
                                        dense
                                        label="Seleccione el tipo de checklist"
                                        placeholder="Tipo de checklist"
                                        v-model="resource.type_checklist"
                                        :items="selects.type_checklist"
                                        item-text="name"
                                        item-value="id"
                                    />
                                </v-col>
                                <v-col cols="7">
                                    <DefaultRichText
                                        clearable
                                        :height="300"
                                        v-model="resource.description"
                                        label="Descripción u objetivo"
                                        :rules="rules.content"
                                        :ignoreHTMLinLengthCalculation="true"
                                        :showGenerateIaDescription="showButtonIaGenerate"
                                        :key="`${showButtonIaGenerate}-editor`"
                                        :limits_descriptions_generate_ia:="limits_descriptions_generate_ia"
                                        :loading="loading_description"
                                        ref="descriptionRichText"
                                        @generateIaDescription="generateIaDescription"
                                    />
                                </v-col>
                                <v-col cols="5">
                                    <DefaultSelectOrUploadMultimedia ref="inputLogo" v-model="resource.image"
                                        label="Imagen (500x350px)" :file-types="['image']"
                                        @onSelect="setFile($event, resource, 'imagen')" select-width="60vw" select-height="100%" />
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="7">
                                    <DefaultSimpleSection title="Escalas de evaluación" marginy="my-1 px-2 pb-4" marginx="mx-0">
                                        <template slot="content">
                                            <div class="d-flex justify-space-between" style="color:#5458EA">
                                                <p>Define las escalas de evaluación</p>
                                                <div>
                                                    <i class="mdi mdi-account-multiple-check"></i>
                                                    <span>Max: {{resource.evaluation_types.length}}/{{selects.max_limit_create_evaluation_types}}</span>
                                                </div>
                                            </div>
                                            <draggable v-model="resource.evaluation_types" @start="drag_evaluation_type=true"
                                                            @end="drag_evaluation_type=false" class="custom-draggable" ghost-class="ghost">
                                                <transition-group type="transition" name="flip-list" tag="div">
                                                    <div v-for="(evaluation_type,index) in resource.evaluation_types"
                                                        :key="`key-${index}`">
                                                        <div class="activities">
                                                            <v-row class="align-items-center px-2">
                                                                <v-col cols="1" class="d-flex align-center justify-content-center ">
                                                                    <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                                    </v-icon>
                                                                </v-col>
                                                                <!-- COLOR EDITABLE -->
                                                                <v-col cols="1">
                                                                    <v-menu v-model="evaluation_type.menu_picker"
                                                                            bottom
                                                                            :close-on-content-click="false"
                                                                            offset-y
                                                                            right
                                                                            nudge-bottom="10"
                                                                            min-width="auto">
                                                                            <template v-slot:activator="{ on, attrs }">
                                                                                <div class="container-evaluation-type"  v-bind="attrs" v-on="on" :style="`background:${evaluation_type.color};`">
                                                                                </div>
                                                                            </template>
                                                                        <v-card>
                                                                            <v-card-text class="pa-0">
                                                                                <v-color-picker v-model="evaluation_type.color" mode="hexa" flat />
                                                                            </v-card-text>
                                                                        </v-card>
                                                                    </v-menu>
                                                                </v-col>
                                                                <v-col cols="1" class="p-0 pb-2">
                                                                    <ButtonEmojiPicker
                                                                        v-model="evaluation_type.extra_attributes.emoji"
                                                                    ></ButtonEmojiPicker>
                                                                </v-col>
                                                                <v-col cols="5">
                                                                    <DefaultInput 
                                                                        dense
                                                                        v-model="evaluation_type.name"
                                                                        appendIcon="mdi mdi-pencil"
                                                                    />
                                                                </v-col>
                                                                <v-col cols="3">
                                                                    <DefaultInput 
                                                                        suffix="%"
                                                                        dense
                                                                        v-model="evaluation_type.extra_attributes.percent"
                                                                    />
                                                                </v-col>
                                                                <v-col cols="1" class="d-flex justify-content-center">
                                                                    <DefaultButton
                                                                        label=""
                                                                        icon="mdi-minus-circle"
                                                                        isIconButton
                                                                        @click="removeScaleEvaluation(index)"
                                                                    />
                                                                </v-col>
                                                            </v-row>
                                                        </div>
                                                    </div>
                                                </transition-group>
                                            </draggable>
    
                                            <div class="my-2">
                                                <DefaultButton
                                                    label="Agregar escala"
                                                    icon="mdi-plus"
                                                    :outlined="true"
                                                    :disabled="resource.evaluation_types.length >= selects.max_limit_create_evaluation_types"
                                                    @click="openFormModal(
                                                        modalScalesChecklist,
                                                        selects.max_limit_create_evaluation_types - resource.evaluation_types.length,
                                                        null,
                                                        'Agregar escala'
                                                    )"
                                                />
                                            </div>
                                        </template>
                                    </DefaultSimpleSection>
                                </v-col>
                                <v-col cols="5" class="pt-1">
                                    <v-col cols="12">
                                        <DefaultSimpleSection title="Escalas de evaluación" marginy="my-1 px-2 py-4" marginx="mx-0">
                                            <template slot="content">
                                                <DefaultSelect dense :items="selects.qualification_types" item-text="name"
                                                    show-required v-model="resource.extra_attributes.qualification_type"
                                                    label="Sistema de calificación" :rules="rules.qualification_type_id" />
                                            </template>
                                        </DefaultSimpleSection>
                                    </v-col>
                                    <v-col cols="12">
                                        <DefaultSimpleSection title="Comentarios" marginy="my-1" marginx="mx-0">
                                            <template slot="content">
                                                <div class="d-flex">
                                                    <DefaultToggle class="ml-4 mb-2"
                                                        v-model="resource.extra_attributes.required_comments" dense
                                                        :active-label="'Activar comentarios dentro de las actividades del checklist'"
                                                        :inactive-label="'Activar comentarios dentro de las actividades del checklist'" />
                                                    <DefaultInfoTooltip
                                                        text="Se agregarán comentarios a las actividades del checklist que puede brindar al supervisor"
                                                        top
                                                    />
                                                </div>
                                            </template>
                                        </DefaultSimpleSection>
                                    </v-col>
                                </v-col>
                            </v-row>
                            <DefaultModalSectionExpand title="Configuración avanzada"
                                :expand="sections.showSectionAdvancedconfiguration" :simple="true">
                                <template slot="content">
                                    <v-row>
                                        <v-col cols="6">
                                            <DefaultSimpleSection title="Visualización" marginy="my-1" marginx="mx-0">
                                                <template slot="content">
                                                    <div class="d-flex">
                                                        <DefaultToggle class="ml-4 mb-2"
                                                            v-model="resource.extra_attributes.visualiazation_results" dense
                                                            :active-label="'Permite que al finalizar el checklist la entidad evaluada visualice sus resultados.'"
                                                            :inactive-label="'Permite que al finalizar el checklist la entidad evaluada visualice sus resultados.'" />
                                                        <DefaultInfoTooltip
                                                            text="Solo se podrá realizar actividades si se encuentra ubicado en su centro laboral asignado"
                                                            top
                                                        />    
                                                    </div>
                                                </template>
                                            </DefaultSimpleSection>
                                        </v-col>
                                        <v-col cols="6">
                                            <DefaultSimpleSection title="Comentarios post finalizado el checklist" marginy="my-1" marginx="mx-0">
                                                <template slot="content">
                                                    <div class="d-flex">
                                                        <DefaultToggle class="ml-4 mb-2"
                                                            :disabled="!resource.extra_attributes.visualiazation_results"
                                                            v-model="resource.extra_attributes.comments_if_checklist_completed" dense
                                                            :active-label="'Se pueden agregar comentarios luego de haber terminado el checklist'"
                                                            :inactive-label="'Se pueden agregar comentarios luego de haber terminado el checklist'" />
                                                        <DefaultInfoTooltip
                                                            text="Al finalizar el checklist el supervisor podrá responder comentarios de sus colaboradores."
                                                            top
                                                        />    
                                                    </div>
                                                </template>
                                            </DefaultSimpleSection>
                                        </v-col>
                                        <v-col cols="6">
                                            <DefaultSimpleSection title="Modo 360°" marginy="my-1" marginx="mx-0">
                                                <template slot="content">
                                                    <div class="d-flex">
                                                        <DefaultToggle class="ml-4 mb-2"
                                                            v-model="resource.extra_attributes.view_360" dense
                                                            :active-label="'Este checklist podrá ser calificado por mas de un supervisor'"
                                                            :inactive-label="'Este checklist podrá ser calificado por mas de un supervisor'" />
                                                        <DefaultInfoTooltip
                                                            text="Solo se podrá realizar actividades si se encuentra ubicado en su centro laboral asignado"
                                                            top
                                                        />    
                                                    </div>
                                                </template>
                                            </DefaultSimpleSection>
                                        </v-col>
                                        <v-col cols="6">
                                            <DefaultSimpleSection title="Modo recurrente" marginy="my-1" marginx="mx-0">
                                                <template slot="content">
                                                    <div class="d-flex">
                                                        <DefaultToggle class="ml-4 mb-2"
                                                            v-model="resource.extra_attributes.replicate" dense
                                                            :active-label="'Este checklist se replicará'"
                                                            :inactive-label="'Este checklist se replicará'" />
                                                        <DefaultInfoTooltip
                                                            text="Este checklist se repetirá de manera indefinida."
                                                            top
                                                        />    
                                                    </div>
                                                </template>
                                            </DefaultSimpleSection>
                                        </v-col>
                                        <v-col cols="6">
                                            <DefaultSimpleSection title="Fecha límite de vigencia" marginy="my-1" marginx="mx-0">
                                                <template slot="content">
                                                    <div class="d-flex  pb-3 px-4">
                                                        <DefaultInputDate
                                                            clearable
                                                            dense
                                                            :referenceComponent="'modalDateFilter'"
                                                            :options="modalDateFilter"
                                                            v-model="resource.extra_attributes.end_date_checklist"
                                                            label="Selecciona la fecha límite que tendrá tu checklist"
                                                        />
                                                    </div>
                                                </template>
                                            </DefaultSimpleSection>
                                        </v-col>
                                        <v-col cols="12">
                                            <DefaultSimpleSection title="Sistema de firma del checklist" marginy="my-1" marginx="mx-0">
                                                <template slot="content">
                                                    <div class="row">
                                                        <v-col cols="6" class="d-flex">
                                                            <DefaultToggle class="ml-4 mb-2"
                                                                v-model="resource.extra_attributes.required_signature_supervisor" dense
                                                                :active-label="'Solicitar una firma al supervisor para finalizar checklist'"
                                                                :inactive-label="'Solicitar una firma al supervisor para finalizar checklist'" />
                                                            <DefaultInfoTooltip
                                                                text="Solo se podrá realizar actividades si se encuentra ubicado en su centro laboral asignado"
                                                                top
                                                            />    
                                                        </v-col>
                                                        <v-col cols="6">
                                                            <v-checkbox
                                                                :disabled="!resource.extra_attributes.required_signature_supervisor"
                                                                class="my-0 mr-2 checkbox-label"
                                                                label="Solicitar firma al supervisado"
                                                                color="primary"
                                                                v-model="resource.extra_attributes.supervised_signature"
                                                                hide-details="false"
                                                            />
                                                        </v-col>
                                                    </div>
                                                </template>
                                            </DefaultSimpleSection>
                                        </v-col>
                                        <v-col cols="6">
                                            <DefaultSimpleSection title="Geolocalización" marginy="my-1" marginx="mx-0">
                                                <template slot="content">
                                                    <div class="d-flex">
                                                        <DefaultToggle class="ml-4 mb-2"
                                                            v-model="resource.extra_attributes.required_geolocation" dense
                                                            :active-label="'Activar geolocalización'"
                                                            :inactive-label="'Activar geolocalización'" />
                                                        <DefaultInfoTooltip
                                                            text="Solo se podrá realizar actividades si se encuentra ubicado en su centro laboral asignado"
                                                            top
                                                        />    
                                                    </div>
                                                </template>
                                            </DefaultSimpleSection>
                                        </v-col>
                                        <v-col cols="12">
                                            <span>Calificación de entidad</span>
                                        </v-col>
                                        <v-col cols="6">
                                            <DefaultSimpleSection title="Autocalificación de entidad" marginy="my-1" marginx="mx-0">
                                                <template slot="content">
                                                    <div class="d-flex">
                                                        <DefaultToggle class="ml-4 mb-2"
                                                            v-model="resource.extra_attributes.autocalificate_entity" dense
                                                            :active-label="'El responsable de la entidad puede autocalificar su tienda, local, vehiculo, etc.'"
                                                            :inactive-label="'El responsable de la entidad puede autocalificar su tienda, local, vehiculo, etc.'" />
                                                        <DefaultInfoTooltip
                                                            text="Tanto la entidad física como los usuarios podrán ver el resultado de sus checklist al finalizar el proceso"
                                                            top
                                                        />    
                                                    </div>
                                                </template>
                                            </DefaultSimpleSection>
                                        </v-col>
                                        <v-col cols="6">
                                            <DefaultSimpleSection title="Selecciona el criterio del responsable" marginy="my-1" marginx="mx-0">
                                                <template slot="content">
                                                    <div class="d-flex pb-3 px-4">
                                                        <DefaultSelect 
                                                            v-model="resource.extra_attributes.autocalificate_entity_criteria"
                                                            :items="selects.criteria" 
                                                            dense 
                                                            item-text="name"
                                                            item-value="id"
                                                            show-required 
                                                            label="Selecciona el criterio del responsable"
                                                        />   
                                                    </div>
                                                </template>
                                            </DefaultSimpleSection>
                                        </v-col>
                                    </v-row>
                                </template>
                            </DefaultModalSectionExpand>
                        </v-card-text>
                    </v-card>
                </v-card-text>
    
                <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)" class="actions_btn_modal">
                    <ButtonsModal
                        @cancel="cancel()"
                        @confirm="confirm()"
                        cancelLabel="Cancelar"
                        confirmLabel="Guardar"
                    />
                </v-card-actions>
            </v-card>
            <DefaultAlertDialog
                :ref="modalAlert.ref"
                :options="modalAlert"
                :confirmLabel="modalAlert.confirmLabel"
                :hideCancelBtn="modalAlert.hideCancelBtn"
                @onConfirm ="closeSimpleModal(modalAlert)"
                @onCancel ="closeSimpleModal(modalAlert)"
            >
                <template v-slot:content> {{ modalAlert.contentText }}</template>
            </DefaultAlertDialog>
        </v-dialog>
        <ModalAddScaleEvaluation
            :ref="modalScalesChecklist.ref"
            :options="modalScalesChecklist"
            width="40vw"
            height="65vh"
            :confirmLabel="modalScalesChecklist.confirmLabel"
            @onConfirm ="closeSimpleModal(modalScalesChecklist)"
            @onCancel ="closeSimpleModal(modalScalesChecklist)"
        />
    </div>
</template>
<script>
import draggable from 'vuedraggable'
import ButtonsModal from './Blocks/ButtonsModal';
import DefaultRichText from "../../globals/DefaultRichText";
import DefaultCardAction from "../../globals/DefaultCardAction"
import ButtonEmojiPicker from '../../basicos/ButtonEmojiPicker';
import ModalAddScaleEvaluation from './ModalAddScaleEvaluation'
export default {
    components: {
    draggable,
    ButtonsModal,
    DefaultRichText,
    DefaultCardAction,
    ButtonEmojiPicker,
    ModalAddScaleEvaluation
},
    props: {
        value: Boolean,
        width: String,
        checklist: Object,
        limitOne: {
            type:Boolean,
            default:false
        },
        tabs_title:{
            type: String,
            default: 'Segmentación'
        },
    },
    data() {
        return {
            modalDateFilter: {
                open: false,
            },
            sections:{
                showSectionAdvancedconfiguration:{ status: true },
            },
            checklist_actions :[
                {id:1,icon:'mdi mdi-home-city',code:'calificate_entity',name:'Calificar entidad',description:'Con este tipo de checklist se revisará a la entidad (tienda, oficina,etc)',color:'#57BFE3'},
                {id:2,icon:'mdi mdi-clipboard-account',code:'calificate_user',name:'Calificar al usuario',description:'El supervisor podrá evaluar a personalmente a cada uno de los usuarios asignados',color:'#CE98FE'},
                {id:3,icon:'mdi mdi-account-multiple-check',code:'autocalificate',name:'Autoevaluación',description:'Sube actividades para guiar a tus usuarios en sus primeros pasos.',color:'#547AE3'},
            ],
            checklist_actions_to_create:[
                {id:1,icon:'mdi mdi-file-edit',code:'create_activities',name:'Crear actividades',description:'Crea las actividades de tu checklist desde cero tu mismo',color:'#F5539B'},
                {id:2,icon:'mdi mdi-file-upload',code:'import_activities',name:'Importar actividades',description:'Sube tus actividades con una plantilla de excel',color:'#5357E0'},
                {id:3,image:'/img/robot_jarvis.png',code:'ia_activities',name:'Ayuda con IA',description:'Sube actividades para guiar a tus usuarios en sus primeros pasos.',color:'#9B98FE'},
            ],
            checklist_type_response:[
                {id:1,name:'Por escala de ev.'},
                {id:2,name:'Selecciona'},
                {id:3,name:'Desplegable'},
            ],
            drag_evaluation_type: false,
            dialog: false,
            file: null,
            isLoading: false,
            formRules: {
                titulo_descripcion: [
                    v => !!v || 'Campo requerido',
                    v => (v && v.length <= 280) || 'Máximo 280 caracteres.',
                ],
                actividad: [
                    v => !!v || 'Campo requerido',
                ],
            },
            modalAlert: {
                ref: 'modalAlert',
                title: 'Alerta',
                contentText: 'Este checklist debe de tener por lo menos una (1) actividad "Se califica al alumno"',
                open: false,
                endpoint: '',
                confirmLabel:"Entendido",
                hideCancelBtn:true,
            },
            selects: {
                type_checklist: [
                    {"id": "libre", "name": "Libre"},
                    {"id": "curso", "name": "Por curso"},
                ],
                qualification_types:[
                ],
                max_limit_create_evaluation_types:5,
                criteria:[

                ]
            },
            type_checklist: "libre",
            tabs: null,
            steps: 0,
            // total: 0,
            errors: [],
            resourceDefault: {
                id: null,
                name: null,
                extra_attributes :{},
                type_checklist: null,
                evaluation_types:[],
            },
            resource: {
                id: null,
                name: null,
                extra_attributes:{},
                type_checklist: null,
                evaluation_types:[],
            },
            rules: {
                // name: this.getRules(['required', 'max:255']),
            },
            modalScalesChecklist:{
                open:false,
                ref: 'modalScalesChecklist',
                base_endpoint: '/checklist',
                confirmLabel: 'Agregar escala(s)',
                resource: 'checklist',
                title: 'Agregar Escala',
                action: null,
                persistent: true,
            },
           //Jarvis
            loading_description: false,
            limits_descriptions_generate_ia: {
                ia_descriptions_generated: 0,
                limit_descriptions_jarvis: 0
            },
            showButtonIaGenerate: true,
            // data segmenteacion
        };
    },
    async mounted() {
        let vue = this;
        await vue.loadLimitsGenerateIaDescriptions();
        await vue.loadFormSelects();
    },
    methods: {
        async loadFormSelects(){
            let vue = this;
            await axios.get('/entrenamiento/checklists/form-selects').then(({ data }) => {
                vue.resource.evaluation_types = data.data.checklist_default_configuration.evaluation_types;
                vue.resource.extra_attributes.qualification_type = data.data.checklist_default_configuration.qualification_type;
                vue.selects.qualification_types = data.data.qualification_types;
                vue.selects.max_limit_create_evaluation_types =  data.data.checklist_default_configuration.max_limit_create_evaluation_types;
                vue.selects.criteria = data.data.criteria;
            })
        },
        validateRequired(input) {
            return input != undefined && input != null && input != "";
        },
        closeModal() {
            let vue = this;
            vue.resetValidation()
            vue.$emit("onClose");
        },
        resetValidation() {
            let vue = this;
        },
        confirm() {
            let vue = this;
            vue.$emit("onConfirm");
            // vue.checklist.list_segments = {
            //     'segments' : vue.checklist.segments,
            //     'model_type': "App\\Models\\Checklist",
            //     'model_id': null,
            //     'code': "direct-segmentation"
            // };
            // vue.checklist.list_segments_document = {
            //     'segment_by_document' : vue.checklist.segmentation_by_document,
            //     'model_type': "App\\Models\\Checklist",
            //     'model_id': null,
            //     'code': "segmentation-by-document"
            // };
            // const allIsValid = vue.moreValidaciones()

            // if (allIsValid == 0)
            //     vue.$emit("onConfirm");
        },
        moreValidaciones() {
            let vue = this
            let errors = 0

            let hasActividadEntrenadorUsuario = false;
            vue.checklist.checklist_actividades.map(actividad=>{
               if( actividad.type_name=='trainer_user') hasActividadEntrenadorUsuario=true;
            });
            if(!hasActividadEntrenadorUsuario){
                this.modalAlert.open= true;
               errors++
            }
            return errors > 0
        },
        cancel() {
            let vue = this;
            vue.$emit("onClose");
        },
        async loadLimitsGenerateIaDescriptions() {
            await axios.get('/jarvis/limits?type=descriptions').then(({ data }) => {
                this.limits_descriptions_generate_ia = data.data;
            })
        },
        async generateIaDescription() {
            const vue = this;
            let url = `/jarvis/generate-description-jarvis`;
            if (vue.loading_description || !vue.resource.title) {
                const message = vue.loading_description ? 'Se está generando la descripción, espere un momento' : 'Es necesario colocar un nombre al checklist para poder generar la descripción';
                vue.showAlert(message, 'warning', '')
                return ''
            }
            if (vue.limits_descriptions_generate_ia.ia_descriptions_generated >= vue.limits_descriptions_generate_ia.limit_descriptions_jarvis) {
                vue.showAlert('Ha sobrepasado el limite para poder generar descripciones con IA', 'warning', '')
                return ''
            }
            vue.loading_description = true;
            await axios.post(url, {
                name: vue.resource.title,
                type: 'checklist'
            }).then(({ data }) => {
                vue.limits_descriptions_generate_ia.ia_descriptions_generated += 1;
                let characters = data.data.description.split('');
                vue.resource.description = ''; // Limpiar el contenido anterior
                function updateDescription(index) {
                    if (index < characters.length) {
                        vue.resource.description += characters[index];
                        setTimeout(() => {
                            updateDescription(index + 1);
                        }, 10);
                    } else {
                        vue.loading_description = false;
                    }
                }
                updateDescription(0);
            }).catch(() => {
                vue.loading_description = false;
            })
        },
    }
};
</script>
<style>
.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}


.txt-white-bold {
    color: white !important;
    font-weight: bold !important;
}

.v-input__icon {
    padding-bottom: 12px;
}

.v-icon.v-icon.v-icon--link {
    color: #1976d2;
}

.icon_size .v-icon.v-icon {
    font-size: 31px !important;
}

.lista-boticas {
    list-style-type: disc;
    -webkit-columns: 3;
    -moz-columns: 3;
    columns: 3;
    list-style-position: inside;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity .5s;
}

.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */
{
    opacity: 0;
}

.custom-draggable, .custom-draggable span {
    width: 100%;
}

.v-expansion-panel-header {
    padding: 0.7rem !important;
}
.bx_steps .v-text-field__slot label.v-label,
.bx_steps .v-select__slot label.v-label,
.text_default {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 13px;
}
.box_info_checklist_1 {
    text-align: justify;
}
.v-stepper__header.stepper_dots {
    justify-content: center;
    height: initial;
}
.v-stepper__header.stepper_dots .v-divider {
    margin: 0;
    flex: auto;
    border-color: #5458ea !important;
    border-width: 2px;
    width: 30px;
    min-width: 30px;
    max-width: 30px;
}
.v-stepper__header.stepper_dots .v-stepper__step {
    padding: 10px 0;
    margin: 0;
}
.v-stepper__header.stepper_dots .v-stepper__step span.v-stepper__step__step {
    margin: 0;
}
.v-stepper__header.stepper_dots .v-stepper__step:hover {
    background: none;
}
.v-stepper.stepper_box {
    box-shadow: none;
}
.txt_desc textarea {
    min-height: 280px;
}
.v-tab span.title_sub {
    font-size: 16px;
    color: #B9E0E9;
    display: flex;
    justify-content: center;
    margin: 12px 0;
    font-family: "Nunito", sans-serif;
    font-weight: 400;
    position: relative;
    text-transform: initial;
    letter-spacing: 0.1px;
}
.v-tab.v-tab--active span.title_sub,
span.title_sub {
    font-size: 16px;
    color: #5458EA;
    display: flex;
    justify-content: center;
    margin: 12px 0;
    font-family: "Nunito", sans-serif;
    font-weight: 700;
    position: relative;
    text-transform: initial;
    letter-spacing: 0.1px;
}
.v-tab.v-tab--active span.title_sub:after,
span.title_sub:after {
    content: '';
    border-bottom: 2px solid #5458EA;
    width: 112px;
    position: absolute;
    bottom: -2px;
}
.v-tab span.title_sub:after  {
    content: none;
}
button.btn_secondary {
    background: none !important;
    border: none;
    box-shadow: none;
}
button.btn_secondary span.v-btn__content {
    color: #5458EA;
    font-weight: 700;
    font-size: 12px;
    font-family: "Nunito", sans-serif;
}
button.btn_secondary span.v-btn__content i{
    font-size: 14px;
    line-height: 1;
}
.divider_light{
    border-color: #94DDDB !important;
}
.item-draggable.activities {
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
    margin: 10px 0;
}
.item-draggable.activities textarea,
.no-white-space .v-select__selection--comma,
.item-draggable.activities .toggle_text_default label.v-label {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 12px;
}
.no-white-space .v-select__selection--comma {
    white-space: initial;
    line-height: 13px;
    font-size: 13px;
}
.item-draggable.activities textarea {
    font-size: 13px;
}
.no-white-space .v-input__append-inner .v-input__icon {
    padding: 0;
}
.item-draggable.activities .default-toggle.default-toggle.v-input--selection-controls {
    margin-top: initial !important;
}
.box_resultados,
.box_seleccionados {
    height: 130px;
    overflow-y: auto;
    border-radius: 8px;
    border: 1px solid #D9D9D9;
    padding: 10px 0;
}
.box_resultados .v-btn:not(.v-btn--text):not(.v-btn--outlined):hover:before,
.box_seleccionados .v-btn:not(.v-btn--text):not(.v-btn--outlined):hover:before {
    opacity: 0;
}
.bx_message {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}
span.v-stepper__step__step {
    color: #5458ea !important;
    background-color: #5458ea !important;
    border-color: #5458ea !important;
    position: relative;
}
span.v-stepper__step__step:before {
    content: '';
    background: #fff;
    height: 17px;
    width: 17px;
    left: 50%;
    top: 50%;
    position: absolute;
    transform: translate(-50%, -50%);
    border-radius: 50%;
}
span.v-stepper__step__step:after {
    content: '';
    background-color: #5458ea;
    height: 6px;
    width: 6px;
    left: 50%;
    top: 50%;
    position: absolute;
    transform: translate(-50%, -50%);
    border-radius: 50%;
}
.v-stepper__step.v-stepper__step--inactive span.v-stepper__step__step,
.v-stepper__step.v-stepper__step--inactive span.v-stepper__step__step:after,
.v-stepper__step.v-stepper__step--inactive .v-divider {
    color: #E4E4E4 !important;
    background-color: #E4E4E4 !important;
    border-color: #E4E4E4 !important;
}
.bx_type_checklist .v-input__icon.v-input__icon--append,
.bx_steps .v-input__icon.v-input__icon--append {
    margin: 0;
    padding: 0;
}
.bx_steps .v-input__slot {
    min-height: 40px !important;
}
.bx_steps .v-text-field--outlined .v-label {
    top: 10px;
}
.bx_steps .v-btn--icon.v-size--default {
    height: 22px;
    width: 22px;
}
.bx_steps .v-select__slot,
.v-dialog.v-dialog--active .bx_steps .v-select--is-multi.v-autocomplete .v-select__slot {
    padding: 0 !important;
}
.bx_steps .v-text-field__details {
    display: none;
}
.bx_step1 .default-toggle {
    margin-top: 3px !important;
}
.bx_step1 .default-toggle .v-input__slot label.v-label {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 13px;
}
.v-stepper__step.v-stepper__step--complete span.v-stepper__step__step:before,
.v-stepper__step.v-stepper__step--complete span.v-stepper__step__step:after {
    content: initial;
}
.bx_steps .v-text-field--enclosed.v-input--dense:not(.v-text-field--solo).v-text-field--outlined .v-input__append-inner {
    margin-top: 10px !important;
}
.v-card__actions.actions_btn_modal button.default-modal-action-button.btn_back.v-btn.v-btn--flat span.v-btn__content {
    color: #5458ea;
}
.v-menu.bx_calendar_top .v-menu__content {
    bottom: 0px;
    top: initial !important;
    right: calc(100% - 10px);
    left: initial !important;
    transform-origin: right bottom !important;
}
.bx_seg .v-select__slot .v-input__append-inner,
.bx_steps .bx_seg .v-text-field--enclosed.v-input--dense:not(.v-text-field--solo).v-text-field--outlined .v-input__append-inner {
    margin-top: 6px !important;
}
.border-error .v-input__slot fieldset {
    border-color: #FF5252 !important;
}
.container-evaluation-type{
    width: 20px;
    height: 20px;
    border-radius: 50%;
}
.v-carousel__controls__item{
    font-size: 6px !important;
    background: #D9D9D9 !important;
    border-radius: 50% !important;
    padding: 0 !important;
    height: 18px !important;
    width: 18px !important;
}
.custom-control-buttom{
    font-size: 6px !important;
    background: #D9D9D9 !important;
    border-radius: 50% !important;
    height: 18px !important;
    width: 18px !important;
}
.carousel__controls__item .v-item--active{
    background:#5458EA !important; 
    background-color: #5458EA !important; /* Color de fondo del botón activo */
}
.checkbox-label .v-label{
    margin:auto !important;
}
</style>
