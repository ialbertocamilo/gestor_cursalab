<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
    >
        <template v-slot:content>
            <v-form ref="TemaMultimediaTextForm">
                <v-row>
                    <v-col cols="12">
                        <DefaultInput
                            label="Título"
                            placeholder="Ingresar título"
                            v-model="titulo"
                            :rules="rules.titulo"/>
                    </v-col>
                    <v-col cols="12">
                        <DefaultInput
                            v-if="type == 'youtube' || type == 'vimeo'"
                            label="URL / Código"
                            placeholder="Ingresar URL / Código"
                            v-model="url"
                            :rules="rules.url"/>
                        <DefaultInput
                            v-else
                            label="URL"
                            placeholder="Ingresar URL"
                            v-model="url"
                            :rules="rules.url"/>
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
        },
        width: String,
        type: String,
    },
    data() {
        return {
            titulo: null,
            url: null,
            rules: {
                titulo: this.getRules(['required']),
                url: this.getRules(['required']),
            }
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.titulo = null
            vue.url = null
            vue.$emit('close')
        },
        confirmModal() {
            let vue = this
            const validateForm = vue.validateForm('TemaMultimediaTextForm')
            if (validateForm) {
                const data = {
                    titulo: vue.titulo,
                    valor: vue.url,
                    type:vue.type
                }
                vue.cleanValues()
                vue.$emit('onConfirm', data)
            }
        },
        cleanValues(){
            let vue = this
            vue.titulo = null
            vue.url = null
            vue.$refs['TemaMultimediaTextForm'].reset()
        },
    }
}
</script>
