

<template>
    <v-dialog :max-width="width" v-model="value" scrollable @click:outside="closeModal">
        <v-card class="modal_qualifications_stages">
            <v-card-title class="default-dialog-title">
                Calificación de inducción
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="p-0">
                <v-card style="box-shadow:none !important;" class="bx_steps bx_step1">
                    <v-form ref="stageForm">
                        <v-card-text v-if="resource.stages.length > 0">
                                <v-row class="align-center">
                                    <v-col cols="12">
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
                                </v-row>
                                <v-row class="align-center" v-for="(stage, index) in resource.stages" :key="index">
                                    <v-col cols="12" class="py-1">
                                        <span class="text_default">Etapa {{ index + 1 }}:</span>
                                    </v-col>
                                    <v-col cols="6">
                                        <DefaultInput
                                                    v-model="stage.qualification_percentage"
                                                    label="Porcentaje del total"
                                                    placeholder="0"
                                                    @input="calculatedPercentage(stage.qualification_percentage, index)"
                                        />
                                    </v-col>
                                    <v-col cols="6">
                                        <DefaultInput
                                                    v-model="stage.qualification_equivalent"
                                                    label="Equivalente"
                                                    placeholder="0"
                                                    @input="calculatedEquivalent(stage.qualification_equivalent, index)"
                                        />
                                    </v-col>
                                </v-row>
                                <v-row v-if="message_error">
                                    <v-col cols="12">
                                        <span class="text_default" style="color: red;" v-html="message_error"></span>
                                    </v-col>
                                </v-row>
                        </v-card-text>
                        <v-card-text v-else>
                            <span class="text_default">Aún no tienes etapas creadas.</span>
                        </v-card-text>
                    </v-form>
                </v-card>

            </v-card-text>

            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)" class="actions_btn_modal">
                <DefaultButtonModalSteps
                    @cancel="closeModal"
                    @confirm="confirmModal"
                    :cancelLabel="cancelLabel"
                    confirmLabel="Guardar"
                    :disabled_next="disabled_btn_next"
                    />
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script>
const fields = [
    'title',
    'active',
    'process_id',
    'duration',
    'stages',
    'qualification_type'
];
const file_fields = [];

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
        process_id: Number,
        options: {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            message_error: '',
            disabled_btn_next: true,
            limit_absences: false,
            cancelLabel: "Cancelar",
            isLoading: false,
            resourceDefault: {
                id: null,
                process_id: null,
                title: '',
                duration: '',
                active: false,
                stages: [],
                qualification_type: null
            },
            resource: {
                id: null,
                process_id: null,
                title: '',
                duration: '',
                active: false,
                stages: [],
                qualification_type: null
            },
            selects: {
                qualification_types: [],
            },
            rules: {
                qualification_type_id: this.getRules(['required']),
            }
        };
    },
    watch: {
        'resource.qualification_type': {
            handler( n, o ) {
                let vue = this
                if(vue.resource.qualification_type)
                {
                    let max_value = vue.resource.qualification_type.position
                    let stages = vue.resource.stages
                    if(stages.length > 0) {
                        stages.forEach(element => {
                            element.qualification_percentage = (Math.round((100 / stages.length) * 100) / 100).toFixed(2);
                            element.qualification_equivalent = (Math.round((max_value / stages.length) * 100) / 100).toFixed(2);
                        });
                    }
                }
            },
            deep: true
        },
        resource: {
            handler( n, o ) {
                let vue = this

                if(vue.resource.qualification_type)
                {
                    let qualification_percentage_total = 0
                    let qualification_equivalent_total = 0

                    let max_value = vue.resource.qualification_type.position
                    let stages = vue.resource.stages
                    if(stages.length > 0) {
                        stages.forEach(element => {
                            let qualification_percentage = element.qualification_percentage ? element.qualification_percentage : 0
                            let qualification_equivalent = element.qualification_equivalent ? element.qualification_equivalent : 0
                            qualification_percentage_total += parseFloat(qualification_percentage)
                            qualification_equivalent_total += parseFloat(qualification_equivalent)
                        });
                    }

                    if((qualification_percentage_total < 100 || qualification_percentage_total > 100) &&
                        (qualification_equivalent_total < max_value || qualification_equivalent_total > max_value)
                    ) {
                        vue.disabled_btn_next = true
                        vue.message_error = `La suma de los valores ingresados no cumplen con el 100% del sistema de calificación.<br><b>Total: ${(Math.round(qualification_percentage_total * 100) / 100).toFixed(2)}%</b>`
                    }
                    else {
                        vue.disabled_btn_next = false
                        vue.message_error = ''
                    }
                }
            },
            deep: true
        }
    },
    methods: {
        calculatedPercentage(value, position) {
            let vue = this
            if(vue.resource.qualification_type && value)
            {
                let max_value = vue.resource.qualification_type.position
                let stages = vue.resource.stages
                if(stages.length > 0) {
                    stages[position].qualification_equivalent = (Math.round((value * max_value / 100) * 100) / 100).toFixed(2);
                }
            }
        },
        calculatedEquivalent(value, position) {
            let vue = this
            if(vue.resource.qualification_type && value)
            {
                let max_value = vue.resource.qualification_type.position
                let stages = vue.resource.stages
                if(stages.length > 0) {
                    stages[position].qualification_percentage = (Math.round((value * 100 / max_value) * 100) / 100).toFixed(2);
                }
            }
        },
        async loadData(resource)
        {
            let vue = this
            vue.resource.stages = resource
        },
        async loadSelects() {
            let vue = this;

            let base = `${vue.options.endpoint}`
            let url = `${base}/${vue.process_id}/etapas/form-selects`;

            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.qualification_types = data.data.qualification_types

                    if(data.data.qualification_type)
                        vue.resource.qualification_type = data.data.qualification_type
                })
        },
        resetValidation()
        {
            let vue = this
            vue.$refs.stageForm.resetValidation()
            vue.message_error = ''
        },
        closeModal()
        {
            let vue = this;
            vue.disabled_btn_next = true
            vue.resetValidation()
            vue.$refs.stageForm.reset()
            vue.$emit("onCancel");
        },
        async confirmModal() {

            let vue = this
            vue.disabled_btn_next = true

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('stageForm')

            let base = `${vue.options.endpoint}`
            console.log(base);
            console.log(vue.resource.id);
            let url = `${base}/${vue.process_id}/update_qualifications`;

            let method = 'PUT';

            if (validateForm) {
                const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);

                let stages_json = JSON.stringify(vue.resource.stages)
                formData.append('stages_json', stages_json)

                formData.append('validateForm', validateForm ? "1" : "0");

                vue.$http.post(url, formData)
                        .then(async ({data}) => {
                            vue.closeModal()
                            this.hideLoader()
                            vue.showAlert(data.data.msg)
                            vue.$emit("onConfirm", vue.resource);
                        })
                        .catch(error => {
                            if (error && error.errors) {
                                const errors = error.errors ? error.errors : error;
                                vue.show_http_errors(errors);
                            }
                        })
            }
            else {
                this.hideLoader()
            }
        }
    }
};
</script>
<style lang="scss">

.modal_qualifications_stages {

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
    .bx_steps.bx_step1 {
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
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
