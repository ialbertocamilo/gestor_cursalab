

<template>
    <v-dialog :max-width="width" v-model="value" scrollable @click:outside="closeModal">
        <v-card>
            <v-card-title class="default-dialog-title">
                Segmentar Checklist
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-0 bx_card_modal_segment">
                <v-card style="height: 100%;overflow: auto;" class="bx_steps bx_step2">
                    <v-card-text>
                        <!-- (segmentacion) -->
                        <div>
                            <v-row>
                                <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                    <span class="text_default lbl_tit">Relaciona el checklist a los usuarios según criterio o doc. de identidad.</span>
                                </v-col>
                                <v-col cols="12" class="pb-0 pt-0">
                                    <SegmentFormModal
                                        :options="modalFormSegmentationOptions"
                                        :list_segments="segmentdata.segments"
                                        :list_segments_document="segmentdata.segmentation_by_document"
                                        width="55vw"
                                        model_type="App\Models\Checklist"
                                        :model_id="segmentdata.id"
                                        ref="modalFormSegmentationOptions"
                                        @onCancel="closeSimpleModal(modalFormSegmentationOptions)"
                                        @onConfirm="closeFormModal(modalFormSegmentationOptions, dataTable, filters)"
                                        @disabledBtnModal="disabledBtnModal"
                                    />
                                </v-col>
                            </v-row>
                        </div>
                        <!-- (segmentacion) -->
                    </v-card-text>
                </v-card>

            </v-card-text>

            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)" class="actions_btn_modal">
                <ButtonsModal
                    @cancel="prevStep"
                    @confirm="nextStep"
                    :cancelLabel="cancelLabel"
                    confirmLabel="Continuar"
                    :disabled_next="disabled_btn_next"
                    />
            </v-card-actions>
        </v-card>
        <DefaultAlertDialog
            :ref="modalAlert.ref"
            :options="modalAlert"
            :confirmLabel="modalAlert.confirmLabel"
            :hideCancelBtn="modalAlert.hideCancelBtn"
            @onConfirm ="modalAlert.open=false"
            @onCancel ="modalAlert.open=false"
        >
            <template v-slot:content> {{ modalAlert.contentText }}</template>
        </DefaultAlertDialog>
    </v-dialog>
</template>


<script>
import draggable from 'vuedraggable'
import SegmentFormModal from "../../components/Segment/SegmentFormModal.vue";
import ButtonsModal from '../../components/Segment/ButtonsModal.vue';

export default {
    components: {
    draggable,
    SegmentFormModal,
    ButtonsModal
},
    props: {
        value: Boolean,
        width: String,
        segmentdata: Object,
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
            disabled_btn_next: true,
            stepper_box_btn1: true,
            stepper_box_btn2: true,
            stepper_box_btn3: false,
            stepper_box: 1,
            cancelLabel: "Cancelar",
            list_segments:[],
            sections: {
                showAdvancedOptions: false
            },
            modalDateStart: {
                open: false,
            },
            modalDateEnd:{
                open: false,
            },
            drag: false,
            expand_cursos: true,
            actividades_expanded: [],
            tipo_actividades: [
                {
                    text: "Se califica al alumno",
                    value: "trainer_user"
                },
                {
                    text: "Se califica al entrenador",
                    value: "user_trainer"
                }
            ],
            dialog: false,
            file: null,
            search_text: null,
            results_search: [],
            disabled_add_courses: false,
            isLoading: false,
            messageActividadesEmpty: false,
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
                ]
            },
            type_checklist: "libre",
            // data segmentacion
            modalFormSegmentationOptions: {
                ref: 'SegmentFormModal',
                open: false,
                persistent: true,
                base_endpoint: '/segments',
                confirmLabel: 'Guardar',
                resource: 'segmentación',
            },
            // data segmentacion
            tabs: null,
            steps: 0,
            // total: 0,
            total: [],

            errors: [],
            showConfigTokens: false,
            resourceDefault: {
                id: null,
                name: null,
                type_checklist: null
            },
            resource: {},
            segments: [{
                id: `new-segment-${Date.now()}`,
                type_code: '',
                criteria_selected: []
            }],
            segment_by_document: {
                id: `new-segment-${Date.now()}`,
                type_code: '',
                criteria_selected: []
            },
            criteria: [],
            courseModules: [],
            modulesSchools: [],

            modalInfoOptions: {
                ref: 'SegmentAlertModal',
                open: false,
                title: null,
                resource:'data',
                hideConfirmBtn: true,
                persistent: true,
                cancelLabel: 'Entendido'
            },
            stackModals: { continues: [],
                backers: [] },
            criteriaIndexModal: 0,

            segment_by_document_clean: false,
            rules: {
                // name: this.getRules(['required', 'max:255']),
            }
            // data segmenteacion
        };
    },
    watch: {
        segmentdata: {
            handler(n, o) {
                let vue = this;

                if(vue.stepper_box == 1){
                    vue.disabledBtnModal()
                    vue.disabled_btn_next = vue.stepper_box_btn1;
                }
            },
            deep: true
        },
        stepper_box: {
            handler(n, o) {
                let vue = this;

                if(vue.stepper_box == 1){
                    vue.disabledBtnModal()
                    vue.disabled_btn_next = vue.stepper_box_btn1;
                }
            },
            deep: true
        }
    },
    methods: {
        rep(){
            let vue = this
        },
        validateRequired(input) {
            return input != undefined && input != null && input != "";
        },
        nextStep(){
            let vue = this;
            vue.cancelLabel = "Cancelar";

            // if(vue.stepper_box == 1){
            //     vue.cancelLabel = "Retroceder";
            //     vue.stepper_box = 2;
            // }
            // else if(vue.stepper_box == 2){
            //     vue.cancelLabel = "Retroceder";
            //     vue.confirm();
            // }
                vue.confirm();
        },
        prevStep(){
            let vue = this;
            if(vue.stepper_box == 1){
                vue.closeModal();
            }
            else if(vue.stepper_box == 2){
                vue.cancelLabel = "Cancelar";
                vue.stepper_box = 1;
            }
        },
        disabledBtnModal() {
            let vue = this;
            vue.stepper_box_btn1 = false;

            let direct_segmentation = (vue.segmentdata.segments != null && vue.segmentdata.segments.length > 0) ? vue.segmentdata.segments[0].direct_segmentation : [];
            let segmentation_by_document = vue.segmentdata.segmentation_by_document != null && vue.segmentdata.segmentation_by_document.segmentation_by_document.length > 0;

            if((direct_segmentation.length > 0 && direct_segmentation[0] == null) && !segmentation_by_document) {
                vue.stepper_box_btn1 = true;
            } else {

                if (direct_segmentation.length > 0)  {
                    if( direct_segmentation[0] == null ) {}
                    else {
                        direct_segmentation.forEach(element => {
                            if (direct_segmentation.length < 1 || element == null || element.values_selected == undefined || element.values_selected == null){
                                vue.stepper_box_btn1 = true;
                            }
                        });
                    }
                }
            }
        },
        closeModal() {
            let vue = this;
            vue.expand_cursos = true;
            vue.actividades_expanded = [];
            vue.search_text = null;
            vue.resetValidation()
            vue.$emit("onClose");
        },
        resetValidation() {
            let vue = this;
            console.log('resetValidation')
            vue.search_text = null
            vue.results_search = []
            vue.stepper_box = 1

            // if (vue.$refs.modalFormSegmentationOptions)
            //     vue.$refs.modalFormSegmentationOptions.closeModal()
        },
        confirm() {
            let vue = this;
            vue.segmentdata.list_segments = {
                'segments' : vue.segmentdata.segments,
                'model_type': "App\\Models\\Checklist",
                'model_id': null,
                'code': "direct-segmentation"
            };
            vue.segmentdata.list_segments_document = {
                'segment_by_document' : vue.segmentdata.segmentation_by_document,
                'model_type': "App\\Models\\Checklist",
                'model_id': null,
                'code': "segmentation-by-document"
            };
            // const allIsValid = vue.moreValidaciones()

            // if (allIsValid == 0)
                vue.$emit("onConfirm");
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        getNewSegment(type_code) {
            return {
                id: `new-segment-${Date.now()}`,
                type_code,
                criteria_selected: []
            };
        },
        async addSegmentation(type_code) {
            let vue = this;
            vue.segments.push(this.getNewSegment(type_code));

            vue.steps = vue.segments.length - 1;
        },
        borrarBloque(segment) {
            let vue = this;
            // const isNewSegment = segment.id.search("new") !== -1;
            // if (vue.segments.length === 1 && !isNewSegment) return;

            vue.segments = vue.segments.filter((obj, idx) => {
                return obj.id != segment.id;
            });
        },
        async changeTypeChecklist() {

            let vue = this;
            vue.type_checklist = vue.resource.type_checklist;
        },
    }
};
</script>
<style>
.list-cursos-carreras {
    width: 500px;
    white-space: unset;
}

.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}

.flip-list-move {
    transition: transform 0.5s;
}

.no-move {
    transition: transform 0s;
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
.bx_card_modal_segment .bx_steps.bx_step2 {
    box-shadow: none;
}
</style>
