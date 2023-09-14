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
                     <v-btn icon :ripple="false" color="white" :href="`/api/download_file?ruta=${full_path_file}`" target="_blank">
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
        async loadData({type_media,full_path_file}) {
            let vue = this;
            vue.type_media = type_media;
            vue.full_path_file = full_path_file;
        },
        loadSelects() {
            let vue = this
        }
    }
}
</script>