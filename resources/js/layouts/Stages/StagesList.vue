<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs" class="breadcrumbs_line"/>
                <v-spacer/>
                <DefaultModalButton :label="'Agregar etapa'" @click="openFormModal(modalEditStage)" :disabled="!(stages.length < max_stages)"/>
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start" class="align-items-center">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por título o descripción..."
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="8">
                        <div class="d-flex bx_actions_stages">
                            <div class="bx_item_stage cursor-pointer" @click="openFormModal(modalQualificationStage, stages)">
                                <div id="tooltip-target-assigned_value" class="btn_tooltip d-inline-flex me-2">
                                    <v-icon class="icon_size" small color="#5458EA" style="font-size: 1.25rem !important;">
                                        mdi-percent-box
                                    </v-icon>
                                </div>
                                <b-tooltip target="tooltip-target-assigned_value" triggers="hover" placement="top">
                                    Porcentaje de evaluación correspondiente a la etapa
                                </b-tooltip>

                                <span class="text_default">Pesos asignados</span>
                            </div>
                            <div class="bx_item_stage">
                                <span class="text_default">Etapas: {{ count_stages }}</span>

                                <span v-if="stages.length < max_stages">
                                    <div id="tooltip-target-count_stages" class="btn_tooltip d-inline-flex ms-2">
                                        <v-icon class="icon_size" small color="#5458EA" style="font-size: 1.25rem !important;">
                                            mdi-information
                                        </v-icon>
                                    </div>
                                    <b-tooltip target="tooltip-target-count_stages" triggers="hover" placement="top">
                                        Cantidad de etapas máximas que se permiten en el sistema
                                    </b-tooltip>
                                </span>
                                <span v-else>
                                    <div id="tooltip-target-count_stages_limit" class="btn_tooltip d-inline-flex ms-2" style="padding-bottom: 7px;">
                                        <v-icon class="icon_size" small color="#FF4242" style="font-size: 0.9rem !important;">
                                            fas fa-exclamation-triangle
                                        </v-icon>
                                    </div>
                                    <b-tooltip target="tooltip-target-count_stages_limit" triggers="hover" placement="top">
                                        Haz llegado al límite de etapas permitidas
                                    </b-tooltip>
                                </span>
                            </div>
                            <div class="bx_item_stage">
                                <span class="text_default">Duración (días): {{ count_days }} días</span>
                            </div>
                        </div>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <v-row>
                            <v-col cols="1" class="d-flex align-center">
                            </v-col>
                            <v-col cols="5" class="d-flex align-center">
                                <span class="text_default fw-bold">Nombre</span>
                            </v-col>
                            <v-col cols="1" class="d-flex align-center justify-content-center">
                                <span class="text_default fw-bold">Duración</span>
                            </v-col>
                            <v-col cols="2" class="d-flex align-center justify-content-center">
                                <span class="text_default fw-bold">Porcentaje ev.</span>
                            </v-col>
                            <v-col cols="3" class="d-flex align-center justify-content-center">
                                <span class="text_default fw-bold">Opciones</span>
                            </v-col>
                        </v-row>
                        <draggable v-model="stages" @start="drag=true"
                                @end="drag=false" class="custom-draggable" ghost-class="ghost">
                            <transition-group type="transition" name="flip-list" tag="div">
                                <div v-for="(stage, i) in stages"
                                    :key="'stage_'+stage.id">
                                    <div class="item-draggable stages">
                                        <v-row>
                                            <v-col cols="6" class="d-flex align-center justify-content-center">
                                                <div style="margin-right: 15px;">
                                                    <v-icon class="ml-0 mr-2 icon_size icon_size_drag">fas fa-grip-vertical
                                                    </v-icon>
                                                </div>
                                                <div class="number_order" style="margin-right: 10px;">{{ i + 1 }}</div>
                                                <div class="d-flex justify-content-between" style="column-gap: 20px; flex: 1;">
                                                    <div style="flex: 1;">
                                                        <div>
                                                            <span class="text_default fw-bold">{{ stage.title }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </v-col>
                                            <v-col cols="1" class="d-flex align-center justify-content-center">
                                                <div class="d-flex">
                                                    <div class="txt_duration">
                                                        <div>
                                                            <span class="text_default">{{ stage.duration }} días</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </v-col>
                                            <v-col cols="2" class="d-flex align-center justify-content-center">
                                            </v-col>
                                            <v-col cols="3" class="bx_actions_stage">
                                                <div>
                                                    <div class="btn_action" @click="openFormModal(modalEditStage, stage)" :class="{'disabled': !stage.active}">
                                                        <v-icon class="ml-0 icon_size">
                                                            mdi mdi-pencil
                                                        </v-icon>
                                                        <span class="text_default">Editar</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="btn_action" @click="createEditCertificate(stage, i)" :class="{'disabled': !stage.active}">
                                                        <v-icon class="ml-0 icon_size">
                                                            mdi mdi-certificate
                                                        </v-icon>
                                                        <span class="text_default">Certificado</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="btn_action" @click="statusStage(stage, i)" :class="{'disabled': !stage.active}">
                                                        <v-icon class="ml-0 icon_size">
                                                            {{stage.active ? 'fas fa-circle' : 'far fa-circle'}}
                                                        </v-icon>
                                                        <span class="text_default">{{stage.active ? 'Activo' : 'Inactivo'}}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="btn_action" @click="deleteStage(stage, i)" :class="{'disabled': !stage.active}">
                                                        <v-icon class="ml-0 icon_size">
                                                            mdi mdi-trash-can
                                                        </v-icon>
                                                        <span class="text_default">Eliminar</span>
                                                    </div>
                                                </div>
                                            </v-col>
                                        </v-row>
                                        <v-row>
                                            <v-col cols="12">
                                                <draggable v-model="stage.activities" @start="drag=true"
                                                        @end="drag=false" class="custom-draggable" ghost-class="ghost">
                                                    <transition-group type="transition" name="flip-list" tag="div">
                                                        <div v-for="(activity, i) in stage.activities"
                                                            :key="'act_'+activity.id">
                                                            <div class="item-draggable stages stages_activities">
                                                                <v-row>
                                                                    <v-col cols="6" class="d-flex align-center">
                                                                        <div style="margin-left: 35px; margin-right: 10px;">
                                                                            <v-icon class="ml-0 mr-2 icon_size icon_size_drag">fas fa-grip-vertical
                                                                            </v-icon>
                                                                        </div>
                                                                        <div v-if="activity.new">
                                                                            <div class="btn_add_activity" @click="addNewActivity(stage.id, stage.school_id)">
                                                                                <span class="text_default c-default">+ Añadir actividad</span>
                                                                            </div>
                                                                        </div>
                                                                        <div v-else>
                                                                            <div class="d-flex align-items-center">
                                                                                <div>
                                                                                    <v-icon class="ml-0 mr-2 icon_size" color="#2A3649" style="font-size: 16px;">{{ typeActivity(activity, true) }}</v-icon>
                                                                                </div>
                                                                                <span class="text_default">{{ activity.title }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </v-col>
                                                                    <v-col cols="1" class="d-flex align-center justify-content-center">
                                                                    </v-col>
                                                                    <v-col cols="2" class="d-flex align-center justify-content-center">
                                                                        <div class="input_edit_percentage">
                                                                            <div :id="'tooltip-target-percentage_'+i " class="btn_tooltip d-inline-flex ms-2">
                                                                                <v-icon class="icon_size" small color="#5458EA" style="font-size: 1.25rem !important;">
                                                                                    mdi-percent-box
                                                                                </v-icon>
                                                                            </div>
                                                                            <b-tooltip :target="'tooltip-target-percentage_'+i " triggers="hover" placement="top">
                                                                                Porcentaje de evaluación correspondiente a la actividad
                                                                            </b-tooltip>
                                                                            <input type="number"
                                                                            placeholder="0"
                                                                            max="100"
                                                                            min="0"
                                                                            v-model="activity.percentage_ev" :ref="'inputPercentage_'+stage.id+'_'+activity.id"
                                                                            @focusout="updatePercentageActivity(stage, activity, 'inputPercentage_'+stage.id+'_'+activity.id, $event)"
                                                                            v-on:keyup.enter="updatePercentageActivity(stage, activity, 'inputPercentage_'+stage.id+'_'+activity.id, $event)"
                                                                            >
                                                                            <span class="text_default">%</span>
                                                                        </div>
                                                                    </v-col>
                                                                    <v-col cols="3" class="d-flex align-items-center justify-content-around bx_actions_activities">
                                                                        <div style="width: 56px;"></div>
                                                                        <!-- <div>
                                                                            <div class="btn_action" @click="deleteInstruction(stage, i)" :class="{'disabled': activity.new || !activity.active}">
                                                                                <v-icon class="ml-0 icon_size">
                                                                                    fas fa-exchange-alt
                                                                                </v-icon>
                                                                                <span class="text_default">Actividad</span>
                                                                            </div>
                                                                        </div> -->
                                                                        <div>
                                                                            <div class="btn_action" @click="()=>{}" :class="{'disabled': activity.new || !activity.active}">
                                                                                <v-icon class="ml-0 icon_size">
                                                                                    mdi mdi-pencil
                                                                                </v-icon>
                                                                                <span class="text_default">Editar</span>
                                                                            </div>
                                                                        </div>
                                                                        <div style="min-width: 40px;">
                                                                            <div class="btn_action" @click="statusActivity(stage, activity, i)" :class="{'disabled': activity.new || !activity.active}">
                                                                                <v-icon class="ml-0 icon_size">
                                                                                    {{activity.active ? 'fas fa-circle' : 'far fa-circle'}}
                                                                                </v-icon>
                                                                                <span class="text_default">{{activity.active ? 'Activo' : 'Inactivo'}}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            <div class="btn_action" @click="deleteActivity(stage, activity, i)" :class="{'disabled': activity.new || !activity.active}">
                                                                                <v-icon class="ml-0 icon_size">
                                                                                    mdi mdi-trash-can
                                                                                </v-icon>
                                                                                <span class="text_default">Eliminar</span>
                                                                            </div>
                                                                        </div>
                                                                    </v-col>
                                                                    <!-- <v-col cols="1" class="d-flex align-center" :class="{'disabled': activity.new}">
                                                                        <v-icon class="ml-0 mr-2 icon_size" color="black"
                                                                                @click="deleteActivity(activity, i)"
                                                                                :disabled="activity.new">
                                                                            mdi trash-can
                                                                        </v-icon>
                                                                    </v-col> -->
                                                                </v-row>
                                                            </div>
                                                        </div>
                                                        <div key="act_new_0">
                                                            <div class="item-draggable stages">
                                                                <v-row>
                                                                    <v-col cols="9" class="d-flex align-center">
                                                                        <div style="margin-left: 35px; margin-right: 10px;">
                                                                            <v-icon class="ml-0 mr-2 icon_size icon_size_drag">fas fa-grip-vertical
                                                                            </v-icon>
                                                                        </div>
                                                                        <div class="btn_add_activity" @click="addNewActivity(stage.id, stage.school_id)">
                                                                            <span class="text_default c-default">+ Añadir actividad</span>
                                                                        </div>
                                                                    </v-col>
                                                                    <v-col cols="3" class="d-flex align-items-center justify-content-around bx_actions_activities">
                                                                        <div style="width: 56px;"></div>
                                                                        <!-- <div>
                                                                            <div class="btn_action disabled">
                                                                                <v-icon class="ml-0 icon_size">
                                                                                    fas fa-exchange-alt
                                                                                </v-icon>
                                                                                <span class="text_default">{{ typeActivity('') }}</span>
                                                                            </div>
                                                                        </div> -->
                                                                        <div>
                                                                            <div class="btn_action disabled">
                                                                                <v-icon class="ml-0 icon_size">
                                                                                    mdi mdi-pencil
                                                                                </v-icon>
                                                                                <span class="text_default">Editar</span>
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            <div class="btn_action disabled">
                                                                                <v-icon class="ml-0 icon_size">
                                                                                    far fa-circle
                                                                                </v-icon>
                                                                                <span class="text_default">Inactivo</span>
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            <div class="btn_action disabled">
                                                                                <v-icon class="ml-0 icon_size">
                                                                                    mdi mdi-trash-can
                                                                                </v-icon>
                                                                                <span class="text_default">Eliminar</span>
                                                                            </div>
                                                                        </div>
                                                                    </v-col>
                                                                    <!-- <v-col cols="1" class="d-flex align-center disabled">
                                                                        <v-icon class="ml-0 mr-2 icon_size" color="black"
                                                                                disabled>
                                                                            mdi-delete
                                                                        </v-icon>
                                                                    </v-col> -->
                                                                </v-row>
                                                            </div>
                                                        </div>
                                                    </transition-group>
                                                </draggable>
                                            </v-col>
                                        </v-row>
                                    </div>
                                </div>
                            </transition-group>
                        </draggable>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <div class="d-flex align-items-center justify-content-center">
                            <div id="tooltip-target-1" class="btn_tooltip d-inline-flex" @click="!(stages.length < max_stages) ? null : openFormModal(modalEditStage)">
                                <v-icon class="icon_size" small :color="!(stages.length < max_stages) ? '#BDBEC0' : '#5458EA'" style="font-size: 1.25rem !important;">
                                    mdi-plus-circle
                                </v-icon>
                            </div>
                            <b-tooltip target="tooltip-target-1" triggers="hover" placement="bottom">
                                Agregar una nueva etapa.
                            </b-tooltip>
                        </div>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!--
        <DialogConfirm
            v-model="confirmationUpdateTitleModal.open"
            :options="confirmationUpdateTitleModal"
            width="408px"
            title="Cambio de nombre de etapa"
            subtitle="¡Estás por cambiar el nombre de la etapa!"
            @onConfirm="confirmationUpdateTitleModal.open = false"
            @onCancel="confirmationUpdateTitleModal.open = false"
        />

        <DialogConfirm
            v-model="confirmationUpdateDurationModal.open"
            :options="confirmationUpdateDurationModal"
            width="408px"
            title="Cambio de fecha de etapa"
            subtitle="¡Estás por cambiar la fecha de la etapa!"
            @onConfirm="confirmationUpdateDurationModal.open = false"
            @onCancel="confirmationUpdateDurationModal.open = false"
        />
        -->
        <DialogConfirm
            v-model="deleteActivityModal.open"
            :options="deleteActivityModal"
            width="408px"
            title="Eliminar actividad"
            subtitle="¡Estás por eliminar esta actividad!"
            @onConfirm="confirmDeleteActivityModal"
            @onCancel="deleteActivityModal.open = false"
        />

        <DialogConfirm
            v-model="statusActivityModal.open"
            :options="statusActivityModal"
            width="408px"
            title="Editar actividad"
            subtitle="¡Estás por cambiar de estado esta actividad!"
            @onConfirm="confirmStatusActivityModal"
            @onCancel="statusActivityModal.open = false"
        />

        <DialogConfirm
            v-model="statusStageModal.open"
            :options="statusStageModal"
            width="408px"
            title="Editar etapa"
            subtitle="¡Estás por cambiar el estado de esta etapa!"
            @onConfirm="confirmStatusStageModal"
            @onCancel="statusStageModal.open = false"
        />

        <DialogConfirm
            v-model="deleteStageModal.open"
            :options="deleteStageModal"
            width="408px"
            title="Eliminar etapa"
            subtitle="¡Estás por eliminar esta etapa!"
            @onConfirm="confirmDeleteStageModal"
            @onCancel="deleteStageModal.open = false"
        />

        <DialogConfirm
            v-model="updatePercentageActivityModal.open"
            :options="updatePercentageActivityModal"
            width="408px"
            @onConfirm="updatePercentageActivityModal.open = false"
            @onCancel="updatePercentageActivityModal.open = false"
        />

        <DialogConfirm
            v-model="changeTypeActivityModal.open"
            :options="changeTypeActivityModal"
            width="408px"
            title="Cambio de tipo de actividad"
            subtitle="¡Estás por cambiar el tipo de actividad seleccionada!"
            @onConfirm="changeTypeActivityModal.open = false"
            @onCancel="changeTypeActivityModal.open = false"
        />

        <DefaultDeleteModal
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalDeleteOptions)"
        />

        <DefaultStatusModal
            :options="modalStatusOptions"
            :ref="modalStatusOptions.ref"
            @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalStatusOptions)"
        />

        <ModalEditStage
            :options="modalEditStage"
            :ref="modalEditStage.ref"
            v-model="modalEditStage.open"
            width="500px"
            :process_id="process_id"
            @onCancel="modalEditStage.open = false"
            @onConfirm="saveEditStage(true)"
        />

        <ModalQualificationStage
            :options="modalQualificationStage"
            :ref="modalQualificationStage.ref"
            v-model="modalQualificationStage.open"
            width="400px"
            :process_id="process_id"
            @onCancel="modalQualificationStage.open = false"
            @onConfirm="saveQualificationStages"
        />

        <ModalSelectActivity
            :ref="modalSelectActivity.ref"
            v-model="modalSelectActivity.open"
            width="868px"
            :process_id="modalSelectActivity.process_id"
            :stage_id="modalSelectActivity.stage_id"
            :school_id="modalSelectActivity.school_id"
            @onCancel="modalSelectActivity.open = false"
            @selectActivityModal="selectActivityModal"
        />

        <ModalActivityTareas
            width="50vw"
            :ref="modalActivityTareas.ref"
            :options="modalActivityTareas"
            @onConfirm="saveEditStage(false)"
            @onCancel="closeFormModal(modalActivityTareas)"
        />

        <ModalActivitySesiones
            width="50vw"
            :ref="modalActivitySesiones.ref"
            :options="modalActivitySesiones"
            @onConfirm="saveEditStage(false)"
            @onCancel="closeFormModal(modalActivitySesiones)"
        />

        <ModalActivityTemas
            width="70vw"
            :ref="modalActivityTemas.ref"
            :options="modalActivityTemas"
            @onConfirm="saveEditStage(false)"
            @onCancel="closeFormModal(modalActivityTemas)"
        />
        <ModalActivityChecklist
            :ref="modalActivityChecklist.ref"
            :width="'868px'"
            @onCancel="closeFormModal(modalActivityChecklist)"
            @onConfirm="saveEditStage(false)"
            :options="modalActivityChecklist"
        />
        <ModalActivityEncuestas
            :ref="modalActivityEncuestas.ref"
            :width="'868px'"
            @onCancel="closeFormModal(modalActivityEncuestas)"
            @onConfirm="saveEditStage(false)"
            :options="modalActivityEncuestas"
        />
        <ModalActivityEvaluaciones
            :ref="modalActivityEvaluaciones.ref"
            :width="'868px'"
            @onCancel="closeFormModal(modalActivityEvaluaciones)"
            @onConfirm="saveEditStage(false)"
            :options="modalActivityEvaluaciones"
        />
    </section>
</template>

<script>
import DialogConfirm from "../../components/basicos/DialogConfirm";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import ModalSelectActivity from "../../components/Induction/Stages/ModalSelectActivity";
import ModalCreateProcess from "../../components/Induction/Process/ModalCreateProcess";

import ModalActivityTareas from "../../components/Induction/Stages/ModalActivityTareas";
import ModalActivitySesiones from "../../components/Induction/Stages/ModalActivitySesiones";
import ModalActivityTemas from "../../components/Induction/Stages/ModalActivityTemas";
import ModalEditStage from "../../components/Induction/Stages/ModalEditStage";
import ModalActivityChecklist from "../../components/Induction/Stages/ModalActivityChecklist";
import ModalActivityEncuestas from "../../components/Induction/Stages/ModalActivityEncuestas";
import ModalActivityEvaluaciones from "../../components/Induction/Stages/ModalActivityEvaluaciones";
import ModalQualificationStage from "../../components/Induction/Stages/ModalQualificationStage";

export default {
    components: {
    DialogConfirm,
    DefaultStatusModal,
    DefaultDeleteModal,
    ModalSelectActivity,
    ModalCreateProcess,
    ModalActivityTareas,
    ModalActivitySesiones,
    ModalActivityTemas,
    ModalEditStage,
    ModalActivityChecklist,
    ModalActivityEncuestas,
    ModalActivityEvaluaciones,
    ModalQualificationStage
},
    mounted() {
        let vue = this
        vue.loadInfo();
    },
    props: {
        process_id: {
            type: Number,
            required: true
        },
        process_name: {
            type: String,
            required: true
        }
    },
    computed: {
        count_days() {
            let vue = this
            let days = 0
            vue.stages.forEach(element => {
                days += parseInt(element.duration)
            });
            return days
        },
        count_stages() {
            let vue = this
            return vue.stages.length + ' / ' + vue.max_stages
        }
    },
    data() {
        return {
            max_stages: 4,
            breadcrumbs: [
                {title: 'Proceso de inducción', text: `${this.process_name}`, disabled: false, href: `/procesos`,tooltip:true},
                {title: '', text: 'Etapas y Actividades', disabled: true, href: '',tooltip:false},
            ],
            stages:[],
            // positioned_stage: null,
            base_endpoint: '/etapas',
            modalSegment: {
                open: false,
                ver_items: false,
                asignar: false,
                subida_masiva: false
            },

            modalActivityTareas: {
                ref: 'ModalActivityTareas',
                open: false,
                base_endpoint: '/procesos',
                title_modal: 'Crear una subida de datos',
                confirmLabel: 'Guardar',
                create_from_course_list:false,
                model_id: 0
            },

            modalActivitySesiones: {
                ref: 'ModalActivitySesiones',
                open: false,
                base_endpoint: '/procesos',
                title_modal: 'Crear una sesión',
                confirmLabel: 'Guardar',
                create_from_course_list:false,
            },

            modalActivityTemas: {
                ref: 'ModalActivityTemas',
                open: false,
                base_endpoint: '/procesos',
                confirmLabel: 'Guardar',
                resource: 'tema',
                title: '',
                action: null,
                persistent: true,
            },

            modalActivityChecklist: {
                ref: 'ModalActivityChecklist',
                open: false,
                base_endpoint: '/procesos',
                title_modal: 'Crear checklist empresa',
                confirmLabel: 'Continuar',
                checklist: null,
                model_id: 0
            },

            modalActivityEncuestas: {
                ref: 'ModalActivityEncuestas',
                open: false,
                base_endpoint: '/procesos',
                title_modal: 'Crear encuestas',
                confirmLabel: 'Continuar',
                model_id: 0
            },

            modalActivityEvaluaciones: {
                ref: 'ModalActivityEvaluaciones',
                open: false,
                base_endpoint: '/procesos',
                title_modal: 'Crear evaluación',
                confirmLabel: 'Continuar',
                model_id: 0
            },

            // confirmationUpdateTitleModal: {
            //     open: false,
            //     title_modal: 'Cambio de nombre de etapa',
            //     type_modal: 'confirm',
            //     content_modal: {
            //         confirm: {
            //             title: '¡Estás por cambiar el nombre de la etapa!',
            //             details: [
            //                 'Esto afectará en la visualización de esta etapa.',
            //                 'Esto afectará al nombre indicado en el diploma.'
            //             ],
            //         }
            //     },
            // },

            // confirmationUpdateDurationModal: {
            //     open: false,
            //     title_modal: 'Cambio de fecha de etapa',
            //     type_modal: 'confirm',
            //     content_modal: {
            //         confirm: {
            //             title: '¡Estás por cambiar la fecha de la etapa!',
            //             details: [
            //                 'Esto afectará los tiempos de visualización de esta etapa y las siguientes.'
            //             ],
            //         }
            //     },
            // },

            deleteActivityModal: {
                open: false,
                title_modal: 'Eliminar actividad',
                type_modal: 'confirm',
                content_modal: {
                    confirm: {
                        title: '¡Estás por eliminar esta actividad!',
                        details: [
                            'Los datos de la actividad no se podrán recuperar.'
                        ],
                    }
                },
            },

            statusActivityModal: {
                open: false,
                title_modal: 'Editar actividad',
                type_modal: 'confirm',
                content_modal: {
                    confirm: {
                        title: '¡Estás por actualizar el estado a esta actividad!',
                        details: [
                            'Los datos de la actividad no se podrán recuperar.'
                        ],
                    }
                },
            },

            statusStageModal: {
                open: false,
                title_modal: 'Desactivar etapa',
                type_modal: 'confirm',
                content_modal: {
                    confirm: {
                        title: '¡Estas por desactivar una etapa!',
                        details: [
                            'Recuerda que al desactivar una etapa todas las actividades que estén enlazadas tambien serán desactivadas.'
                        ],
                    }
                },
            },

            deleteStageModal: {
                open: false,
                title_modal: 'Eliminar etapa',
                type_modal: 'confirm',
                content_modal: {
                    confirm: {
                        title: '¡Estás por eliminar esta estapa!',
                        details: [
                            'Los datos de la actividad no se podrán recuperar.',
                            'Se eliminarán todas las actividades relacionadas a esta etapa.'
                        ],
                    }
                },
            },

            updatePercentageActivityModal: {
                open: false,
                title_modal: 'Porcentaje erróneo',
                type_modal: 'confirm',
                content_modal: {
                    confirm: {
                        title: '¡El porcentaje indicado supera el 100% de la etapa!',
                        details: [
                            'Recuerda que por cada etapa la suma de los porcentajes debe ser de 100% para que este activa.'
                        ],
                    }
                },
            },

            changeTypeActivityModal: {
                open: false,
                title_modal: 'Cambio de tipo de actividad',
                type_modal: 'confirm',
                content_modal: {
                    confirm: {
                        title: '¡Estás por cambiar el tipo de actividad seleccionada!',
                        details: [
                            'Los datos de la actividad no se podrán recuperar',
                            'Podrás seleccionar una actividad nueva de la lista que reemplazara la seleccionada.'
                        ],
                    }
                },
            },
            dataModalSegment: {},

            modalEditStage: {
                ref: 'ModalEditStage',
                open: false,
                endpoint: '/procesos',
            },
            modalQualificationStage: {
                ref: 'ModalQualificationStage',
                open: false,
                endpoint: '/procesos',
            },
            modalSelectActivity: {
                ref: 'ModalSelectActivity',
                open: false,
                endpoint: '',
                process_id: 0,
                stage_id: 0,
                school_id: 0
            },

            selects: {
                sub_workspaces: [],
                statuses: [
                    {id: null, name: 'Todos'},
                    {id: 1, name: 'Activos'},
                    {id: 2, name: 'Inactivos'},
                ],
                progress: [
                    {id: 1, name: 'Pendiente de asignación'},
                    {id: 2, name: 'En curso'},
                    {id: 3, name: 'Terminado'},
                ],
                // progress: [
                //     null => 'Todos',
                //     1 => 'Activos',
                //     0 => 'Inactivos',
                // ],
            },
            filters: {
                q: '',
                subworkspace_id: null,
                active: 1,
            },
            btn_reload_data: false,
            mostrarFiltros: true,
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            modalDeleteOptions: {
                ref: 'BenefitDeleteModal',
                open: false,
                base_endpoint: '/procesos',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar un Proceso!',
                        details: [
                            'Este proceso no podrá ser visto por los usuarios.',
                            'La información eliminada no podra recuperarse'
                        ],
                    }
                },
                width: '408px'
            },
            modalStatusOptions: {
                ref: 'BenefitStatusModal',
                open: false,
                base_endpoint: '/procesos',
                contentText: '¿Desea cambiar de estado a este registro?',
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un Proceso!',
                        details: [
                            'Este proceso ahora no podrá ser visto por los usuarios.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un Proceso!',
                        details: [
                            'Este proceso ahora podrá ser visto por los usuarios.'
                        ]
                    }
                },
                endpoint: '',
                width: '408px'
            },
            file: null,
            max_benefits_x_users: 0,
        }
    },
    methods: {
        updatePercentageActivity( stage, activity, ref, event) {
            console.log(activity);
            console.log(ref);
            console.log(event);
            let vue = this
            if(activity.percentage_ev && activity.percentage_ev > 0)
            {
                if(activity.percentage_ev > 100){
                    this.$nextTick(() => {
                        activity.percentage_ev = 0;
                    });
                }
                else {
                    if(stage.activities && stage.activities.length > 0)
                    {
                        let sum_act = 0;
                        stage.activities.forEach(element => {
                            sum_act += parseFloat(element.percentage_ev)
                        });
                        console.log(sum_act);
                        if(sum_act > 100) {
                            console.log('mucho');
                            vue.updatePercentageActivityModal.open = true
                            this.$nextTick(() => {
                                activity.percentage_ev = 0
                            });
                        }
                        else {
                            console.log('save');
                            this.showLoader()

                            const url = `/procesos/${stage.process_id}/etapas/${stage.id}/activity/${activity.id}/update`
                            const formData = new FormData();
                            formData.append('percentage_ev', activity.percentage_ev);

                            vue.$http.post(url, formData)
                                .then(async ({data}) => {

                                    let result = data.data.msg;
                                    vue.showAlert(result)

                                    this.hideLoader()

                                    vue.$nextTick(() => {
                                        vue.loadInfo()
                                    });
                                })
                                .catch((err) => {
                                    console.log(err);
                                    this.hideLoader()
                                });
                        }
                    }
                    console.log('save2');
                }
            }
        },
        createEditCertificate( stage, index ) {
            if(stage.certificate_route)
                window.location.href = stage.certificate_route;
        },
        async saveEditStage( scroll = false )
        {
            let vue = this
			vue.$nextTick(() => {
                vue.loadInfo()
                if(scroll)
                    window.scrollTo(0, document.body.scrollHeight || document.documentElement.scrollHeight);
			});
        },
        async saveQualificationStages() {

        },
        typeActivity( activity, icon = false )
        {
            let name = 'Pendiente';
            let name_icon = '';

            if(activity != null && activity.type != null) {
                switch (activity.type.code) {
                    case 'checklist':
                        name = 'Checklist';
                        name_icon = 'mdi mdi-list-box';
                        break;
                    case 'tareas':
                        name = 'Tareas';
                        name_icon = 'mdi mdi-cloud-upload';
                        break;
                    case 'evaluacion':
                        name = 'Pruebas';
                        name_icon = 'mdi mdi-note-text';
                        break;
                    case 'temas':
                        name = 'Temas';
                        name_icon = 'mdi mdi-bookmark-box';
                        break;
                    case 'encuesta':
                        name = 'Encuesta';
                        name_icon = 'mdi mdi-medal';
                        break;
                    case 'sesion_online':
                        name = 'Sesión en vivo';
                        name_icon = 'mdi mdi-video';
                        break;
                    default:
                        name = 'Pendiente';
                        name_icon = '';
                        break;
                }
            }
            return icon ? name_icon : name;
        },
        addNewActivity( stage_id = null, school_id = null )
        {
            let vue = this
            console.log(school_id);
            vue.modalSelectActivity.process_id = vue.process_id
            vue.modalSelectActivity.stage_id = stage_id
            vue.modalSelectActivity.school_id = school_id

            vue.openFormModal(this.modalSelectActivity)
        },
        // updateValueStage( input, item )
        // {
        //     let vue = this;
        //     // console.log(item.new != undefined);
        //     if(vue.positioned_stage[input] != item[input]) {
        //         if(input == 'duration')
        //             vue.confirmationUpdateDurationModal.open = true
        //         else if(input == 'title')
        //             vue.confirmationUpdateTitleModal.open = true
        //     }
        // },
        async deleteStage( stage, position )
        {
            console.log(stage);
            console.log(position);
            let vue = this;
            vue.deleteStageModal.open = true
            vue.deleteStageModal.stage_id = stage.id
            // // vue.openFormModal(deleteStageModal,stage,'delete','Cambio de estado de un beneficio')
            // vue.deleteStageModal.open = true;

            // await vue.$refs[vue.deleteStageModal.ref].loadData(rowData);
            // // await vue.$refs[vue.deleteStageModal.ref].resetValidation();

            // vue.$nextTick(() => {
            //     vue.$refs[vue.deleteStageModal.ref].loadSelects();
            // });
        },
        confirmDeleteStageModal()
        {
            let vue = this;

            if(vue.validateRequired(vue.deleteStageModal.stage_id))
            {
                this.showLoader()

                const url = `/procesos/${vue.process_id}/etapas/${vue.deleteStageModal.stage_id}/delete`

                vue.$http.delete(url)
                    .then(({data}) => {

                        let result = data.data.msg;
                        vue.deleteStageModal.open = false
                        vue.deleteStageModal.stage_id = null
                        vue.showAlert(result)

                        this.hideLoader()

                        vue.$nextTick(() => {
                            vue.loadInfo()
                        });
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                    });
            }
        },
        deleteActivity( stage, activity, position )
        {
            let vue = this;
            vue.deleteActivityModal.open = true
            vue.deleteActivityModal.stage_id = stage.id
            vue.deleteActivityModal.activity_id = activity.id
        },
        confirmDeleteActivityModal()
        {
            let vue = this;

            if(vue.validateRequired(vue.deleteActivityModal.stage_id))
            {
                this.showLoader()

                const url = `/procesos/${vue.process_id}/etapas/${vue.deleteActivityModal.stage_id}/activity/${vue.deleteActivityModal.activity_id}/delete`

                vue.$http.delete(url)
                    .then(async ({data}) => {

                        let result = data.data.msg;
                        vue.deleteActivityModal.open = false
                        vue.deleteActivityModal.stage_id = null
                        vue.showAlert(result)

                        this.hideLoader()

                        vue.$nextTick(() => {
                            vue.loadInfo()
                        });
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                    });
            }
        },
        statusActivity( stage, activity, position )
        {
            let vue = this;
            vue.statusActivityModal.open = true
            vue.statusActivityModal.stage_id = stage.id
            vue.statusActivityModal.activity_id = activity.id
        },
        confirmStatusActivityModal()
        {
            let vue = this;

            if(vue.validateRequired(vue.statusActivityModal.stage_id))
            {
                this.showLoader()

                const url = `/procesos/${vue.process_id}/etapas/${vue.statusActivityModal.stage_id}/activity/${vue.statusActivityModal.activity_id}/status`

                vue.$http.put(url)
                    .then(async ({data}) => {

                        let result = data.data.msg;
                        vue.statusActivityModal.open = false
                        vue.statusActivityModal.stage_id = null
                        vue.showAlert(result)

                        this.hideLoader()

                        vue.$nextTick(() => {
                            vue.loadInfo()
                        });
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                    });
            }
        },
        statusStage( stage, position )
        {
            let vue = this;
            vue.statusStageModal.open = true
            vue.statusStageModal.stage_id = stage.id
        },
        confirmStatusStageModal()
        {
            let vue = this;

            if(vue.validateRequired(vue.statusStageModal.stage_id))
            {
                this.showLoader()

                const url = `/procesos/${vue.process_id}/etapas/${vue.statusStageModal.stage_id}/status`

                vue.$http.put(url)
                    .then(async ({data}) => {

                        let result = data.data.msg;
                        vue.statusStageModal.open = false
                        vue.statusStageModal.stage_id = null
                        vue.showAlert(result)

                        this.hideLoader()

                        vue.$nextTick(() => {
                            vue.loadInfo()
                        });
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                    });
            }
        },
        changeTypeActivity( activity, position ) {
            console.log(activity);
            console.log(position);
            let vue = this;
            vue.changeTypeActivityModal.open = true
        },
        addStage() {
            let vue = this;

            const newID = `n-${Date.now()}`;
            const newStage = {
                id: newID,
                title: "",
                active: 1,
                hasErrors: false,
                new: true,
                activities: [
                    // {
                    //     id: `act-${Date.now()}`,
                    //     title: "",
                    //     active: 0,
                    //     new: true
                    // }
                ]
            };
            vue.stages.push(newStage);
            console.log(vue.stages);
        },
        confirmModalSegment() {
            let vue = this;
            this.showLoader()
            vue.$http.post(`/beneficios/segments/save`, vue.dataModalSegment)
                .then((res) => {
                    if (res.data.type == "success") {
  						vue.$toast.success(`${res.data.data.msg}`, {position: 'bottom-center'});
                        vue.closeModalSegment();
                        vue.refreshDefaultTable(vue.dataTable, vue.filters);
  					}
                    this.hideLoader()
                })
                .catch((err) => {
                    console.log(err);
                    this.hideLoader()
                });
        },
        async loadInfo() {
            let vue = this
            this.showLoader()

            const url = `/procesos/${vue.process_id}/etapas/search`

            vue.$http.get(url)
                .then(({data}) => {
                    let result = data.data.data;
                    console.log(result);
                    if(result && result.length)
                        vue.stages = result;
                    this.hideLoader()
                })
                .catch((err) => {
                    console.log(err);
                    this.hideLoader()
                });
            console.log(vue.stages);
        },
        confirmModalMaxColaborador(value = null) {
            let vue = this;
            if(value != null)
            {
                this.showLoader()
                vue.$http.post(`/beneficios/max_benefits_x_users/update`, {'value': value})
                    .then((res) => {
                        vue.max_benefits_x_users = res.data.data.max_benefits;
                        if (res.data.type == "success") {
                            vue.$notification.success(`${res.data.data.msg}`, {
                                timer: 6,
                                showLeftIcn: false,
                                showCloseIcn: true
                            });
                        }
                        this.hideLoader()
                        vue.modalMaxColaborador.open = false
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                        vue.modalMaxColaborador.open = false
                    });
            }
        },
        closeModalSegment() {
            let vue = this

            vue.dataModalSegment.segments = [];
            vue.dataModalSegment.segmentation_by_document = {
                    segmentation_by_document:[]
                }
            vue.modalSegment.open = false;
        },
        selectActivityModal( value ) {
            let vue = this
            // window.location.href = '/beneficios/create?type=' + value;
            console.log(value);
            let stage_id = vue.modalSelectActivity.stage_id
            let school_id = vue.modalSelectActivity.school_id

            if(value == 'tareas')
            {
                vue.modalActivityTareas.base_endpoint = `/procesos/${vue.process_id}/etapas/${stage_id}/activity/tareas`
                vue.modalActivityTareas.model_id = stage_id
                vue.modalActivityTareas.school_id = school_id

                vue.openFormModal(vue.modalActivityTareas)
                // vue.modalCreateProcess.open = true
                vue.modalSelectActivity.open = false
            }
            else if(value == 'sesiones')
            {
                vue.modalActivitySesiones.base_endpoint = `/procesos/${vue.process_id}/etapas/${stage_id}/activity/sesiones`
                vue.modalActivitySesiones.model_id = stage_id
                vue.modalActivitySesiones.school_id = school_id

                vue.openFormModal(this.modalActivitySesiones)
                // vue.modalCreateProcess.open = true
                vue.modalSelectActivity.open = false
            }
            else if(value == 'temas')
            {
                vue.modalActivityTemas.base_endpoint = `/procesos/${vue.process_id}/etapas/${stage_id}/activity/temas`
                vue.modalActivityTemas.model_id = stage_id
                vue.modalActivityTemas.school_id = school_id

                vue.openFormModal(this.modalActivityTemas)
                // vue.modalCreateProcess.open = true
                vue.modalSelectActivity.open = false
            }
            else if(value == 'checklist')
            {
                vue.modalActivityChecklist.base_endpoint = `/procesos/${vue.process_id}/etapas/${stage_id}/activity/checklist`
                vue.modalActivityChecklist.model_id = stage_id
                vue.modalActivityChecklist.school_id = school_id

                vue.openFormModal(this.modalActivityChecklist)
                // vue.modalCreateProcess.open = true
                vue.modalSelectActivity.open = false
            }
            else if(value == 'encuestas')
            {
                vue.modalActivityEncuestas.base_endpoint = `/procesos/${vue.process_id}/etapas/${stage_id}/activity/encuestas`
                vue.modalActivityEncuestas.model_id = stage_id
                vue.modalActivityEncuestas.school_id = school_id

                vue.openFormModal(this.modalActivityEncuestas)
                // vue.modalCreateProcess.open = true
                vue.modalSelectActivity.open = false
            }
            else if(value == 'evaluaciones')
            {
                vue.modalActivityEvaluaciones.base_endpoint = `/procesos/${vue.process_id}/etapas/${stage_id}/activity/evaluaciones`
                vue.modalActivityEvaluaciones.model_id = stage_id
                vue.modalActivityEvaluaciones.school_id = school_id

                vue.openFormModal(this.modalActivityEvaluaciones)
                // vue.modalCreateProcess.open = true
                vue.modalSelectActivity.open = false
            }
        },
        saveNewProcessInline( item ) {

            console.log(item);
            console.log(item.title);
            let vue = this

            vue.showLoader()

            const edit = vue.benefit_id !== ''
            let url = `${vue.base_endpoint}/store_inline`
            let method = 'POST';

            if(item.title != '')
            {
                const resource = { 'title' : item.title };
                const fields = ['title'];
                const formData = vue.getMultipartFormData(method, resource, fields);
                // formData.append('validateForm', validateForm ? "1" : "0");

                vue.$http.post(url, formData)
                        .then(async ({data}) => {
                            this.hideLoader()
                            vue.showAlert(data.data.msg)
                            // setTimeout(() => vue.closeModal(), 2000)
                            vue.refreshDefaultTable(vue.dataTable, vue.filters);
                        })
                        .catch(error => {
                            if (error && error.errors){
                                vue.errors = error.errors
                            }
                            // vue.loadingActionBtn = false
                        })
            }
        },
    }
};
</script>
<style lang="scss">
.number_order {
    font-size: 20px;
    font-weight: 700;
    color: #434D56;
    font-family: "Nunito", sans-serif;
    position: relative;
    &:after {
        content: '°';
    }
}
.btn_icon_action {
    display: flex;
    align-items: center;
    .text_default {
        font-size: 13px;
        color: #434D56;
        line-height: 1;
    }
    button.v-icon.v-icon {
        color: #5458EA !important;
    }
    .v-input.v-input--switch label.v-label {
        font-size: 13px;
        margin: 0;
        line-height: 1;
        min-width: 70px;
    }
}
.tooltip.b-tooltip {
    .tooltip-inner {
        padding: 10px 20px;
        color: #5458ea;
        background-color: #fff;
        border-radius: 8px;
        border: 2px solid #5458ea;
    }
    .arrow::before,
    .arrow::before {
        border-bottom-color: #5458ea !important;
        border-top-color: #5458ea !important;
    }
}
.add_cert {
    line-height: 1;
    text-align: center;
    display: flex;
    flex-direction: column;
    .v-icon {
        font-size: 16px;
        color: #5458ea !important;
        margin-right: 0 !important;
    }
    span.text_default {
        font-size: 12px !important;
        margin-top: 3px;
    }
}
.btn_add_activity {
    cursor: pointer;
    span.text_default {
        color: #5458EA;
    }
}
.txt_duration {
    position: relative;
    .v-input input {
        padding-right: 32px;
        width: 70px;
    }
    span.txt_after {
        padding: 0 5px;
        color: #434D56;
        font-weight: 400;
        font-family: "Nunito", sans-serif;
        font-size: 13px;
        position: absolute;
        top: 50%;
        right: 4px;
        transform: translateY(-50%);
        align-items: center;
        display: flex;
    }
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] {
  -moz-appearance: textfield;
}
.bx_actions_stage, .bx_actions_activities {
    display: flex;
    align-items: center;
    justify-content: space-around;
    .btn_action {
        line-height: 1;
        text-align: center;
        display: flex;
        flex-direction: column;
        cursor: pointer;
        i {
            font-size: 16px !important;
            color: #5458ea !important;
            margin-right: 0 !important;
        }
        .text_default {
            font-size: 11px;
        }
        &.disabled {
            i, span.text_default {
                color: #BDBEC0 !important;
            }
        }
    }
}
.btn_tooltip {
    cursor: pointer;
}
i.icon_size_drag {
    color: #D9D9D9 !important;
    font-size: 17px !important;
}
.bx_item_stage {
    padding: 0 15px;
    border-right: 1px solid #D9D9D9;
    display: flex;
    align-items: center;
    span.text_default {
        color: #5458ea !important;
        font-size: 14px;
    }
    &:last-child {
        border: none;
    }
}
.item-draggable.stages.stages_activities:hover {
    background: #5458ea0a;
}
.input_edit_percentage {
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px solid #D9D9D9;
    border-radius: 4px;
    input[type="number"] {
        width: 28px;
        text-align: right;
        height: 30px;
    }
    span.text_default {
        line-height: 1;
        padding-top: 3px;
        padding-right: 10px;
    }
}
</style>
