<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
        :customTitle="true"
    >
        <template v-slot:card-title>
            <v-card-title class="default-dialog-title mod_head">
                    <span v-html="options.title_modal ? options.title_modal : options.title"></span>
                    <v-spacer></v-spacer>
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
                </v-card-title>
        </template>
        <template v-slot:content>
            <v-row>
                <v-col cols="12">
                    <h4>¿Deseas generar evaluaciones automáticas con este contenido?</h4>
                    <div>

                    </div>
                </v-col>
            </v-row>
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
        limits:{
            type: Object,
            required: false
        },
        width: String,
        type: String,
        label: String,

    },
    data() {
        return {
            ia_convert:null,
            defaultMedia:{}
        }
    },
    methods: {
        closeModal() {
            this.$emit('close')
        },
        loadData (media){
            this.defaultMedia=media;
        },
        confirmModal() {
            this.$emit('onConfirm',this.defaultMedia)
        },
        resetValidation (){

        },
        loadSelects (){

        }
    }
}
</script>
