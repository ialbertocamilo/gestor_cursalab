<template>
    <DefaultDialog
        width="50vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>
            <v-form ref="DiplomaForm">
                <v-row>
                    <!-- <v-col cols="12">
                        <p>
                            Vista previa del diploma. Haz click en "Guardar" para generarlo.
                        </p>
                    </v-col> -->
                    <v-col cols="12">
                        <img
                            :src="options.resource.preview"
                            class="img-preview w-100"
                            alt="Previsualización de diploma">
                    </v-col>
                    <v-col cols="12">
                        <DefaultInput
                            v-model="resource.diploma"
                            clearable
                            label="Nombre de la plantilla"
                            :rules="rules.diploma"
                        />
                    </v-col>
                </v-row>
            </v-form>
        </template>
        
    </DefaultDialog>
</template>

<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            resource: {},
            rules: {
                diploma: this.getRules([
                    'required', 'min:5', 'max:50'
                ]),
            },
        }
    },
    methods: {
        resetValidation() {},
        loadData(resource) {},
        loadSelects() {},
        onCancel() {
            let vue = this;
            vue.resource = {};
            vue.resetFormValidation('DiplomaForm');
            vue.$emit('onCancel');
        },
        onConfirm() {
            let vue = this;
            vue.validateForm('DiplomaForm');
            
            vue.$emit('onConfirm', true);
        }
    }
}
</script>
<style>
    .bx_header .cont {
        color: #2A3649;
        font-size: 20px;
        font-family: "Nunito", sans-serif;
        font-weight: 700;
        margin-left: 29px;
        text-align: left;
        line-height: 25px;
    }
</style>
