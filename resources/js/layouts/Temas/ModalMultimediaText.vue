<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
        :customTitle="(type == 'youtube')"
    >
        <template v-slot:card-title>
            <v-card-title class="default-dialog-title mod_head">
                    <span v-html="options.title_modal ? options.title_modal : options.title"></span>
                    <v-spacer></v-spacer>
                    <div v-if="Object.keys(limits).length != 0">
                        <v-tooltip top>
                            <template  v-slot:activator="{ on, attrs }">
                                <v-btn  v-bind="attrs" v-on="on" class="py-1"  outlined text
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
                    <v-col cols="12" v-if="type == 'youtube' && Object.keys(limits).length != 0">
                        <AiSection :limits="limits" @onChange="changeIaConvertValue" />
                        <!-- <DefaultToggle  activeLabel="Habilitar contenido para AI" inactiveLabel="Habilitar contenido para AI" v-model="ia_convert"/> -->
                    </v-col>
                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>
<script>
import AiSection from './AiSection.vue'
export default {
    components:{AiSection},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String,
        type: String,
        limits:{
            type: Object,
            required: false
        },
    },
    data() {
        return {
            titulo: null,
            url: null,
            ia_convert:null,
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
        getYoutubeCode(link) {
            // Patron regular para coincidir con el código de YouTube
            const patron = /(?:youtube\.com\/(?:[^/]+\/.+\/|(?:v|e(?:mbed)?|watch)\?.*v=|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/;
            // Intentar hacer coincidir el patrón en el enlace
            const coincidencia = link.match(patron);
            // Si se encuentra una coincidencia, devolver el código del video
            if (coincidencia && coincidencia[1]) {
                return coincidencia[1];
            }
            // Si no se encuentra una coincidencia, devolver null o un mensaje de error
            return null;
        },
        confirmModal() {
            let vue = this
            const validateForm = vue.validateForm('TemaMultimediaTextForm')
            if(vue.type=='youtube' &&  vue.url.includes("https")){
                const code = vue.getYoutubeCode(vue.url);
                if(!code){
                    vue.showAlert('El link de youtube no tiene el formato correcto','warning') 
                    return '';
                }
                vue.url = code;
            }
            if (validateForm) {
                const data = {
                    titulo: vue.titulo,
                    valor: vue.url,
                    ia_convert: vue.ia_convert,
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
        changeIaConvertValue(value){
           let vue = this;
           vue.ia_convert = value;
        }
    }
}
</script>
