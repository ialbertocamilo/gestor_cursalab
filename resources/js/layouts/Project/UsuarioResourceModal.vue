<template>
    <div>
        <DefaultDialog
            :options="options"
            width="60vw"
            @onCancel="closeModal"
            @onConfirm="confirmModal"
            :showCardActions="options.showCardActions"
            :showTitle="options.showTitle"
            :noPaddingCardText="options.noPaddingCardText"
            headerClass="m-0"
            customTitle
        >   
            <template v-slot:card-title>
                <v-card-title class="default-dialog-title sticky-card-text">
                    {{ options.title }}
                    <v-spacer/>
                     <v-btn icon :ripple="false" color="white" :href="`/projects/resource/${project_resource_id}/download`" target="_blank">
                        <v-icon>mdi-download</v-icon>
                    </v-btn>
                    <v-btn
                        v-show="options.showCloseIcon"
                        icon :ripple="false" color="white"
                        @click="closeModal">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
            </template>
            <template v-slot:content>
                <div v-if="type_media=='image'" style="width: auto;">
                    <v-img
                        lazy-src="https://picsum.photos/id/11/10/6"
                        :src="full_path_file"
                        width="auto"
                    ></v-img>
                </div>
                <div v-else-if="type_media=='office'">
                    <DocPreview :docValue="full_path_file" docType="office" :height="80" />
                </div>
                <div v-else-if="type_media=='pdf'">
                    <embed :src="full_path_file" style="height: 500px;width: 100%;">
                </div>
                <div v-else-if="type_media=='video'" >
                    <video :src="full_path_file" autoplay style="height: 500px;width: 100%;" controls ></video>
                </div>
                <div v-else>
                    <span>Este archivo no se puede visualizar, descargalo desde el botón de arriba 👆</span>
                </div>
            </template>
        </DefaultDialog>
    </div>
</template>
<script>
import DocPreview from './DocPreview.vue';
export default {
    components:{DocPreview},
    props: {
        options: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            selects:{},
            type_media:null,
            full_path_file:'',
            project_resource_id:0,
        }
    },
    methods:{
        closeModal() {
            let vue = this;
            vue.$emit('onCancel');
        },
        async confirmModal() {
            let vue = this;
            vue.$emit('onConfirm');
        },
        resetSelects() {
            let vue = this;
        },
         resetValidation() {
            let vue = this
        },
        async loadData({type_media,full_path_file,id}) {
            let vue = this;
            vue.type_media = type_media;
            vue.full_path_file = full_path_file;
            vue.project_resource_id = id;
        },
        loadSelects() {
            let vue = this
        },
        downloadFile(){
            let vue = this;
            const url = ``;
            const element = document.createElement('a');
            element.href = url;
            element.download = true; // Indicar que es una descarga
            element.style.display = 'none'; // Ocultar el elemento
            // Agregar el elemento al documento
            document.body.appendChild(element);
            // Simular un clic en el enlace de descarga
            element.click();
            // Eliminar el elemento después de la descarga
            document.body.removeChild(element);
        }
    }
}
</script>