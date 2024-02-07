<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="trainerForm">
                <DefaultErrors :errors="errors" />
                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            :label="label"
                            :rules="rules.name"
                            maxlength="120"
                            :max="120"
                            hint="Máximo 120 caracteres"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex-- justify-content-center">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputSignature"
                            v-model="resource.signature"
                            label="Firma (500x350px)"
                            :show-button="false"
                            :file-types="['image']"
                            @onSelect="setFile($event, resource, 'signature')"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex-- justify-content-center">
                        Esta firma se utilizará únicamente para el de registro de este curso.<br>
                        No se guardará para ningún otro propósito.
                    </v-col>
                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>
const fields = [
    "name",'signature','type'
];

const file_fields = ["signature"];

export default {
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
                name: "",
                signature: null,
                file_signature:null,
                type:''
            },
            resource:{
                name: "",
                signature: null,
                file_signature:null,
                type:''
            },
            rules: {
                name: this.getRules(["required", "max:100"]),
                signature: this.getRules(["required"]),
            },
            label:''
        };
    },
    methods: {
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        }
        ,
        resetValidation() {
            let vue = this
            vue.$refs.inputSignature.removeAllFilesFromDropzone()
            vue.$refs.trainerForm.resetValidation()
        }
        ,
        confirmModal() {

            let vue = this

            vue.errors = []

            const validateForm = vue.validateForm('trainerForm')
            let url = `/registrotrainer/store`;
            let method = 'POST';
            if (validateForm && vue.isValid()) {

                this.showLoader()

                let formData = vue.getMultipartFormData(
                    method, vue.resource, fields, file_fields
                );
                vue.$http
                   .post(url, formData)
                   .then(({data}) => {
                        vue.showAlert('Se creó correctamente')
                        this.hideLoader()
                        vue.$emit('onConfirm', data.data)
                   }).catch((error) => {
                       if (error && error.errors)
                            vue.errors = error.errors
                    })
            }

        }
        ,
        resetSelects() {
            let vue = this
        }
        ,
        async loadData({type}) {
            let vue = this
            vue.resource.name = null;
            vue.resource.signature = null;
            vue.resource.file_signature = null;
            vue.label =  'Nombre del instructor';
            vue.resource.type = type;
        },
        isValid() {

            let valid = true;
            let errors = [];
            let formData = this.getMultipartFormData(
                '', this.resource, fields, file_fields
            );

            // Validation: signature has been set or not,
            // from file or from gallery
            if (!formData.get('file_signature') && !this.resource.signature) {
                errors.push({
                    message: 'No ha seleccionado ninguna firma.'
                })
                valid = false;
            }

            if (!valid) {
                this.errors = errors;
            }

            return valid;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
