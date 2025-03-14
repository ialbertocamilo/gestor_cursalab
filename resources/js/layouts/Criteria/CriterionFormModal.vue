<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="tipoCriterioForm">

                <DefaultErrors :errors="errors"/>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            label="Nombre"
                            :rules="rules.name"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultAutocomplete
                            style="width: 100%;"
                            clearable
                            :items="selects.criteria"
                            v-model="resource.parent_id"
                            label="Criterio Padre"
                            itemText="name"
                            :disabled="options.action === 'edit' && resource.parent_id"
                        />
                    </v-col>
                </v-row>
                <v-row align="start">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.data_types"
                            v-model="resource.field_id"
                            label="Tipo de Criterio"
                            return-object
                            :rules="rules.data_types"
                            :disabled="options.action === 'edit'"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.position"
                            label="Orden"
                            type="number"
                            min="1"
                            :max="resource.default_position"
                        />
                    </v-col>

                </v-row>

                <v-row align="start" align-content="center">

                    <v-col cols="6">
                        <DefaultToggle
                            v-model="resource.multiple"
                            type="multiple"
                            label="Selección múltiple"
                            :disabled="options.action === 'edit'"
                        />
                    </v-col>
                    <v-col cols="6">
                        <DefaultToggle
                            v-model="resource.show_in_segmentation"
                            type="show_in_segmentation"
                            label="Mostrar en segmentación"
                            :disabled="options.action === 'edit'"
                        />
                    </v-col>
                    <v-col cols="6">
                        <DefaultToggle
                            v-model="resource.can_be_create"
                            type="can_be_create"
                            label="Crear desde subida masiva"
                        />
                    </v-col>
                    <v-col cols="6" v-if="false">
                        <DefaultToggle
                            v-model="resource.is_default"
                            type="is_default"
                            label="¿Es obligatorio?"
                            :disabled="options.action === 'edit'"
                        />
                    </v-col>

                </v-row>

                <v-row justify="space-around">
                    <img src="/svg/criterios.svg" width="350" class="my-7">
                </v-row>

            </v-form>
            <CriterionValidationsModal
                ref="CriterionValidationsModal"
                :width="validationsModal.width"
                :options="validationsModal"
                @onCancel="closeFormModal(validationsModal)"
                @onConfirm="confirmModal"
                :resource="resource"
            />
        </template>

    </DefaultDialog>
</template>

<script>

import CriterionValidationsModal from "./CriterionValidationsModal";

const fields = ['name', 'multiple', 'show_in_segmentation','can_be_create','parent_id', 'field_id', 'position'];
const file_fields = [];

export default {
    components: {CriterionValidationsModal},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            errors: [],
            resourceDefault: {
                id: null,
                name: '',
                tipo: null,
                field_id: null,
                parent_id:null,
                multiple: false,
                show_in_segmentation: false,
                can_be_create:true,
                is_default: false,
                position: 1,
                default_position: 1,
            },
            resource: {},
            selects: {
                data_types: [],
                criteria : []
            },

            rules: {
                data_types: this.getRules(['required']),
                name: this.getRules(['required', 'max:150']),
                // dni: this.getRules(['required', 'number'])
            },
            validationsModal: {},
            validationsModalDefault: {
                ref: 'CriterionValidationsModal',
                width: '50vw',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'CriterionValidations',
                persistent: false,
                showCloseIcon: true
            },
        }
    },
    methods: {
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.$refs['tipoCriterioForm'].resetValidation()
        },
        async confirmModal() {
            let vue = this

            vue.errors = []

            const validateForm = vue.validateForm('tipoCriterioForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            if (validateForm) {
                this.showLoader()

                let formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);

                await vue.$http.post(url, formData)
                    .then(async ({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()
                    })
                    .catch(async (error) => {
                        if (error && error.errors)
                            vue.errors = error.errors

                        if (error && error.data.validations) {
                            await vue.cleanValidationsModal()
                            let validations = error.data.validations;

                            if (validations.show_confirm) {
                                vue.validationsModal.hideConfirmBtn = false
                                vue.validationsModal.hideCancelBtn = false
                                vue.validationsModal.cancelLabel = 'Cancelar'
                                vue.validationsModal.confirmLabel = 'Confirmar'
                            } else {
                                vue.validationsModal.hideConfirmBtn = true
                                vue.validationsModal.cancelLabel = 'Entendido'
                            }

                            await vue.openFormModal(vue.validationsModal, validations, 'validateUpdateCriterion', validations.title)
                        }
                    })
            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this
            vue.selects.data_types = []
        },
        async loadData(resource) {
            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/form-selects`;

            await vue.$http.get(url).then(({data}) => {

                vue.selects.data_types = data.data.data_types;
                vue.selects.criteria = data.data.criteria;
                if (resource) {
                    vue.resource = data.data.criterion
                } else {
                    vue.resource.position = data.data.default_position
                    vue.resource.default_position = data.data.default_position
                }
            })

            return 0;
        },
        loadSelects() {
            let vue = this
        },
        async cleanValidationsModal() {
            let vue = this
            await vue.$nextTick(() => {
                vue.validationsModal = Object.assign({}, vue.validationsModal, vue.validationsModalDefault)
            })
        },
    }
}
</script>
