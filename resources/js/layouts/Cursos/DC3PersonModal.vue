<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="instructorForm">
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
                            ref="inputImagen"
                            v-model="resource.signature"
                            label="signature (500x350px)"
                            :file-types="['image']"
                            @onSelect="setFile($event, resource, 'signature')"
                        />
                    </v-col>
                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>
import DefaultRichText from "../../components/globals/DefaultRichText";
import moment from "moment";

const fields = [
    "name",
];

const file_fields = ["signature", "archivo"];

export default {
    components: { DefaultRichText },
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
            },
            resource:{
                name: "",
                signature: null,
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
            // if (vue.options.action !== 'edit'){
            vue.$refs.inputImagen.removeAllFilesFromDropzone()
            // vue.resource.contenido = ""
            // }
            vue.$refs.instructorForm.resetValidation()
        }
        ,
        confirmModal() {

            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('instructorForm')
            let base = `${vue.options.base_endpoint}`
            let url = `${base}/store`;

            let method = 'POST';

            if (validateForm && vue.isValid()) {

                let formData = vue.getMultipartFormData(
                    method, vue.resource, fields, file_fields
                );
                vue.$http
                   .post(url, formData)
                   .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                   }).catch((error) => {
                       if (error && error.errors)
                            vue.errors = error.errors
                    })
            }

            this.hideLoader()
        }
        ,
        resetSelects() {
            let vue = this
        }
        ,
        async loadData({type}) {
            console.log(type);
            let vue = this
            vue.label= type == 'dc3_instructor' ? 'Nombre del instructor' : 'Nombre del representante' ;
            return 0;
        }
        ,
        isValid() {

            let valid = true;
            let errors = [];
            let formData = this.getMultipartFormData(
                '', this.resource, fields, file_fields
            );

            // Validation: signature has been set or not,
            // from file or from gallery

            if (!this.resource.signature) {
                errors.push({
                    message: 'No ha seleccionado ninguna imágen'
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
