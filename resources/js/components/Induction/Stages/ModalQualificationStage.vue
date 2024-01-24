

<template>
    <v-dialog :max-width="width" v-model="value" scrollable @click:outside="closeModal">
        <v-card class="modal_create_process">
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
                    <v-card-text>
                        <v-form ref="stageForm">
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
                                                v-model="stage.percentage"
                                                label="Porcentaje del total"
                                                placeholder="0"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <DefaultInput
                                                v-model="stage.equivalent"
                                                label="Equivalente"
                                                placeholder="0"
                                    />
                                </v-col>
                            </v-row>
                        </v-form>
                    </v-card-text>
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
    'duration'
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
                stages: []
            },
            resource: {
                id: null,
                process_id: null,
                title: '',
                duration: '',
                active: false,
                stages: []
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
        resource: {
            handler( n, o ) {
                let vue = this
                if(vue.validateRequired(vue.resource.title) && vue.validateRequired(vue.resource.duration))
                    vue.disabled_btn_next = false
                else
                    vue.disabled_btn_next = true
            },
            deep: true
        }
    },
    methods: {
        async loadData(resource) {

            let vue = this
            console.log('cargando');
            console.log(resource);
            vue.resource.stages = resource
            // console.log(vue.process_id);
            // if(resource){
            //     vue.resource = resource
            //     vue.$nextTick(() => {
            //         vue.resource = Object.assign({}, vue.resource, vue.resourceDefault, resource)
            //     })
            // }
            // else{
            //     vue.resource.process_id = vue.process_id
            // }
        },
        async loadSelects() {
            let vue = this;

            let base = `${vue.options.endpoint}`
            let url = `${base}/${vue.process_id}/etapas/form-selects`;

            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.qualification_types = data.qualification_types
                })
        },
        resetValidation() {
            let vue = this
            vue.$refs.stageForm.resetValidation()
        },
        validateRequired(input) {
            return input != undefined && input != null && input != "";
        },
        // nextStep(){
        //     let vue = this;
        //     vue.cancelLabel = "Cancelar";

        //     vue.confirm();
        // },
        // prevStep(){
        //     let vue = this;
        //     vue.closeModal();
        // },
        closeModal() {
            let vue = this;
            vue.resource = {}
            vue.disabled_btn_next = true
            vue.resetValidation()
            vue.$emit("onCancel");
        },
        // confirm() {
        //     let vue = this;
        // },
        async confirmModal() {

            let vue = this
            vue.disabled_btn_next = true

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('stageForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.endpoint}`
            console.log(base);
            console.log(vue.resource.id);
            let url = edit
                ? `${base}/${vue.process_id}/etapas/${vue.resource.id}/update`
                : `${base}/${vue.process_id}/etapas/store`;

            let method = edit ? 'PUT' : 'POST';
            if (validateForm) {
                const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
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
        // cancel() {
        //     let vue = this;
        //     vue.$emit("onCancel");
        // },
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
