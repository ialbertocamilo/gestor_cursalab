

<template>
    <v-dialog :max-width="width" v-model="value" scrollable @click:outside="closeModal">
        <v-card class="modal_create_process">
            <v-card-title class="default-dialog-title">
                Crear inducción
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-0">
                <v-card style="box-shadow:none !important;" class="bx_steps bx_step1">
                    <v-card-text>
                        <v-row>
                            <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                <span class="text_default lbl_tit">Indica la información que tendrá la inducción</span>
                            </v-col>
                            <v-col cols="12" md="12" lg="12" class="pb-0">
                                <DefaultInput clearable
                                            v-model="process.title"
                                            label="Título"
                                />
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" md="12" lg="12" class="pb-0">
                                <v-textarea
                                    rows="5"
                                    outlined
                                    dense
                                    hide-details="auto"
                                    label="Ingresa aquí el texto de bienvenida al momento de ingresar en la inducción"
                                    v-model="process.description"
                                    class="txt_desc"
                                ></v-textarea>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12">
                                <div class="row_dates d-flex">
                                    <div class="d-flex align-center">
                                        <span class="text_default">Fecha de inicio del proceso:</span>
                                        <div>
                                            <DefaultInputDate
                                                placeholder="Ingresar fecha"
                                                reference-component="StartProcessDate"
                                                :options="modalDateOptions"
                                                label=""
                                                v-model="process.starts_at"
                                                :offset-y="false"
                                                :offset-x="true"
                                                :top="true"
                                            />
                                        </div>
                                    </div>
                                    <div class="d-flex align-center">
                                        <span class="text_default ms-3">Fecha de fin del proceso: (opcional)</span>
                                        <DefaultInputDate
                                            placeholder="Ingresar fecha"
                                            reference-component="StartProcessDate"
                                            :options="modalDateOptions2"
                                            label=""
                                            v-model="process.finishes_at"
                                            :offset-y="false"
                                            :offset-x="true"
                                            :top="true"
                                            :left="true"
                                        />
                                    </div>
                                </div>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12">
                                <div class="bx_switch_attendance">
                                    <v-switch
                                        class="default-toggle"
                                        inset
                                        label="Deseas contabilizar las inasistencias de los colaboradores"
                                        hide-details="auto"
                                        v-model="process.count_absences"
                                        dense
                                    ></v-switch>
                                </div>
                            </v-col>
                        </v-row>
                        <transition name="fade">
                            <v-row v-if="process.count_absences">
                                <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                    <span class="text_default lbl_tit">¿Deseas agregar límite de inasistencia a este proceso de inducción?</span>
                                </v-col>
                                <v-col cols="12" md="12" lg="12">
                                    <div class="d-flex">
                                        <div>
                                            <v-radio-group v-model="process.limit_absences" row>
                                                <v-radio label="No" :value="false"></v-radio>
                                                <div class="divider_inline"></div>
                                                <v-radio label="Sí, indica máximas inasistencias permitidas:" :value="true"></v-radio>
                                            </v-radio-group>
                                        </div>
                                        <div class="bx_input_inasistencias">
                                            <input type="number" v-model="process.absences">
                                        </div>
                                    </div>
                                </v-col>
                            </v-row>
                        </transition>
                    </v-card-text>
                </v-card>

            </v-card-text>

            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)" class="actions_btn_modal">
                <DefaultButtonModalSteps
                    @cancel="prevStep"
                    @confirm="nextStep"
                    :cancelLabel="cancelLabel"
                    confirmLabel="Continuar"
                    :disabled_next="disabled_btn_next"
                    />
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script>
import draggable from 'vuedraggable'
import DefaultButtonModalSteps from '../../globals/DefaultButtonModalSteps.vue';

export default {
    components: {
        draggable,
        DefaultButtonModalSteps
    },
    props: {
        value: Boolean,
        width: String,
    },
    data() {
        return {
            modalDateOptions: {
                ref: 'DateEvent',
                open: false,
            },
            modalDateOptions2: {
                ref: 'DateEvent',
                open: false,
            },
            disabled_btn_next: true,
            process: {
                limit_absences: false,
                count_absences: false
            },
            limit_absences: false,
            cancelLabel: "Cancelar",
            isLoading: false,
        };
    },
    watch: {
        process: {
            handler(n, o) {
                let vue = this;
                vue.disabled_btn_next = !(vue.validateRequired(vue.process.title) && vue.validateRequired(vue.process.description));
            },
            deep: true
        }
    },
    methods: {
        // updateValue(value) {
        //     let vue = this
        //     console.log(value);
        // },
        validateRequired(input) {
            return input != undefined && input != null && input != "";
        },
        nextStep(){
            let vue = this;
            vue.cancelLabel = "Cancelar";

            vue.confirm();
        },
        prevStep(){
            let vue = this;
            vue.closeModal();
        },
        closeModal() {
            let vue = this;
            vue.process = {}
            vue.$emit("onCancel");
        },
        confirm() {
            let vue = this;
            vue.$emit("onConfirm", vue.process);
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
    }
};
</script>
<style lang="scss">

.modal_create_process {

    .txt_desc textarea {
        min-height: auto;
    }

    .bx_input_inasistencias input {
        border: 1px solid #D9D9D9;
        border-radius: 8px;
        max-width: 100px;
        height: 40px;
        text-align: center;
    }

    .bx_steps.bx_step1 .v-input--selection-controls .v-input__slot>.v-label,
    .bx_steps.bx_step1 .v-input--selection-controls .v-radio>.v-label {
        margin-bottom: 0;
        font-family: "Nunito", sans-serif;
        font-size: 13px;
        font-weight: 400;
        color: #2C2D2F;
    }
}
</style>
