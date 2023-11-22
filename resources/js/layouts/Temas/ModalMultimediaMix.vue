<template>
    <DefaultDialog
        :options="options"
        :width="width"
        :height="height"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
        :customTitle="['video','audio','pdf'].includes(filterType)"
    >
        <template v-slot:card-title>
            <v-card-title class="default-dialog-title mod_head">
                    <span v-html="options.title_modal ? options.title_modal : options.title"></span>
                    <v-spacer></v-spacer>
                    <div v-if="Object.keys(limits).length != 0">
                        <v-tooltip top>
                            <template  v-slot:activator="{ on, attrs }">
                                <v-btn v-bind="attrs" v-on="on" class="py-1"  outlined text
                                    style="border-radius: 15px;border-color: white;height: auto;">
                                    <span style="color:white">{{limits.media_ia_converted}}/{{limits.limit_allowed_media_convert }}</span>
                                    <img 
                                        width="22px" 
                                        style="filter: grayscale(100%) brightness(0) invert(100%);"
                                        class="ml-2" 
                                        src="/img/ia_convert.svg"
                                    >
                                </v-btn>
                            </template>
                            <span v-html="`Te quedan ${limits.limit_allowed_media_convert - limits.media_ia_converted} contenidos para <br>  aprovechar nuestra Inteligencia <br> artificial en tus evaluaciones`"></span>
                        </v-tooltip>
                    </div>
                </v-card-title>
        </template>
        <template v-slot:content>
            <v-form ref="TemaMultimediaTextForm" @submit.prevent="null">
                <v-row>
                    <v-col cols="12">
                        <DefaultInput
                            label="Título"
                            placeholder="Ingresar título"
                            v-model="titulo"
                            :rules="rules.titulo"
                            dense
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputLogo"
                            v-model="multimedia"
                            :label="label"
                            :file-types="[filterType]"
                            @onSelect="setMultimedia"
                            select-width="55vw"
                            select-height="55vh"
                        />
                    </v-col>
                    <v-col cols="12" v-if="['video','audio','pdf'].includes(filterType) && Object.keys(limits).length != 0">
                        <AiSection :limits="limits" @onChange="changeIaConvertValue" />
                    </v-col>
                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>
<script>
import AiSection from './AiSection.vue'
export default {
    components : {AiSection},
    props: {
        options: {
            type: Object,
            required: true
        },
        filterType: {
            type: String,
            required: true
        },
        limits:{
            type: Object,
            required: false
        },
        width: String,
        height: String,
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
        },
        changeIaConvertValue(value){
           let vue = this;
           vue.ia_convert = value;
        }
    }
}
</script>
