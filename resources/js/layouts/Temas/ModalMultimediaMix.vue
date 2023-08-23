<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
    >
        <template v-slot:content>
            <v-form ref="TemaMultimediaTextForm" @submit.prevent="null">
                <v-row>
                    <v-col cols="12">
                        <DefaultInput
                            label="Título"
                            placeholder="Ingresar título"
                            v-model="titulo"
                            :rules="rules.titulo"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputLogo"
                            v-model="multimedia"
                            :label="label"
                            :file-types="[filterType]"
                            @onSelect="setMultimedia"/>
                    </v-col>
                    <v-col cols="12" v-if="['video','audio','pdf'].includes(filterType)">
                        <DefaultToggle  activeLabel="Habilitar contenido para AI" inactiveLabel="Habilitar contenido para AI" v-model="ia_convert"/>
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
        filterType: {
            type: String,
            required: true
        },
        width: String,
        type: String,
        label: String,

    },
    data() {
        return {
            titulo: null,
            multimedia: null,
            file_multimedia: null,
            ia_convert:null,
            rules: {
                titulo: this.getRules(['required']),
            }
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.titulo = null
            vue.multimedia = null
            vue.$refs.TemaMultimediaTextForm.reset()

            if (vue.$refs.inputLogo && vue.$refs.inputLogo.$refs.dropzoneDefault)
                vue.$refs.inputLogo.$refs.dropzoneDefault.removeAll()

            vue.$emit('close')
        },
        confirmModal() {
            let vue = this
            event.preventDefault();
            const validateForm = vue.validateForm('TemaMultimediaTextForm')
            if (validateForm) {
                const data = {
                    titulo: vue.titulo,
                    ia_convert:vue.ia_convert,
                    file: vue.TypeOf(vue.multimedia) === 'object' ? vue.multimedia : null,
                    valor: vue.TypeOf(vue.multimedia) === 'string' ? vue.multimedia : null,
                    type: vue.type
                }
                vue.$emit('onConfirm', data)
                vue.closeModal()
            }
        },
        setMultimedia(multimedia) {
            let vue = this
            vue.multimedia = multimedia
        }
    }
}
</script>
