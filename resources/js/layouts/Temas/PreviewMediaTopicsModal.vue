<template>
    <div>
        <DefaultDialog :customTitle="true" :showCardActions="false" :noPaddingCardText="true" :options="options"
            width="350px" @onCancel="closeModal" @onConfirm="confirmModal" vCardClass=" p-0 overflow-hidden ">
            <template v-slot:card-title>
                <v-card-title class="py-0">
                    <div class="d-flex w-100 justify-space-between">
                        <div>
                            <span>{{ currentTime }}</span>
                        </div>
                        <div>
                            <v-icon small>
                                mdi-wifi
                            </v-icon>
                            <v-icon small>
                                mdi-signal
                            </v-icon>
                            <v-icon small>
                                mdi-battery-90
                            </v-icon>
                        </div>
                    </div>
                </v-card-title>
            </template>
            <template v-slot:content>
                <div class="container-preview" >
                    <div v-if="currentMedia" style="height: 250px;">
                        <div v-if="['scorm','genially','office','link'].find(c => c==currentMedia.type_id)">
                            <div v-if="!isFullscreen" style="width:100%;height: 100%;" class="d-flex justify-content-center align-items-center">
                                <!-- <button @click="toggleFullscreen">Abrir</button> -->
                                <div>
                                    <div>{{ currentMedia.name }}</div>
                                    <DefaultButton
                                        label="Abrir"
                                        outlined
                                        @click="toggleFullscreen()"
                                    />
                                </div>
                            </div>
                            <div ref="iframe" >
                                <div v-if="isFullscreen" style="height: 100%;">
                                    <div style="background-color: black;" class="d-flex justify-content-end">
                                        <v-btn
                                            icon :ripple="false" color="white"
                                            @click="toggleFullscreen()">
                                            <v-icon> mdi-close </v-icon>
                                        </v-btn>
                                    </div>
                                    <DocPreview v-if="currentMedia.type_id == 'office'" :docValue="currentMedia.url" docType="office" style="height:100%" />
                                    <iframe v-else allowfullscreen="" frameborder="0" style="height: 100%;" width="100%" :src="currentMedia.url">
                                    </iframe>
                                </div>
                            </div>
                        </div>
                        <iframe v-else ref="iframe_media_emebebed" allowfullscreen="" frameborder="0" height="100%" width="100%" :src="currentMedia.url">
                        </iframe>
                    </div>
                    <div v-else style="height: 250px;"></div>
                    <div style="overflow-y: auto;scrollbar-width: thin;">
                        <v-list class="list-container" >
                            <v-list-group v-for="topic in topics" :key="topic.id" class="not-show-arrow v-list-custom-group">
                                <template v-slot:activator>
                                    <v-list-item-content class="v-list-custom-color">
                                        <v-list-item-title style="font-size: 0.8rem !important;">{{ topic.name }}</v-list-item-title>
                                    </v-list-item-content>
                                </template>
        
                                <v-list-item v-for="media in topic.medias" :key="media.id">
                                    <v-list-item-content @click="changeMedia(media)" class="v-list-item-custom">
                                        <v-list-item-title style="font-size: 0.8rem !important;">
                                            <i
                                                :class="mixin_multimedias.find(el => el.type === media.type_id).icon || 'mdi mdi-loading'" />
                                            {{ media.name }}</v-list-item-title>
                                    </v-list-item-content>
                                </v-list-item>
                            </v-list-group>
                        </v-list>
                    </div>
                </div>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>
import DocPreview from '../Project/DocPreview';
export default {
    components: { DocPreview },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            topics: [],
            currentMedia: '',
            currentTime: this.setCurrentTime(),
            isFullscreen: false,
        };
    },
    methods: {
        closeModal() {
            let vue = this
            vue.pauseMedia();
            console.log('onCancel');
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
        },
        async confirmModal() {

            vue.$emit('onConfirm')

        },
        resetSelects() {
            let vue = this
        },
        async loadData(course_id) {
            let vue = this
            console.log(course_id);
            let url = `/cursos/${course_id}/medias`
            vue.showLoader();
            await vue.$http.get(url).then(({ data }) => {
                vue.topics = data.data.topics;
                vue.currentMedia = vue.topics[0].medias[0];
                vue.hideLoader();
            })
        },
        async loadSelects() {
            let vue = this;
        },
        changeMedia(media) {
            let vue = this;
            vue.currentMedia = media;
            console.log('Cambiar media:', media);
        },
        setCurrentTime() {
            const currentTime = new Date();
            return this.formatDate(currentTime);
        },
        formatDate(currentTime) {
            const hour = currentTime.getHours().toString().padStart(2, '0');
            const minutes = currentTime.getMinutes().toString().padStart(2, '0');
            // const segundos = fecha.getSeconds().toString().padStart(2, '0');
            return `${hour}:${minutes}`;
        },
        toggleFullscreen() {
            const iframe = this.$refs.iframe;

            if (!this.isFullscreen) {
                if (iframe.requestFullscreen) {
                iframe.requestFullscreen();
                } else if (iframe.mozRequestFullScreen) {
                iframe.mozRequestFullScreen();
                } else if (iframe.webkitRequestFullscreen) {
                iframe.webkitRequestFullscreen();
                } else if (iframe.msRequestFullscreen) {
                iframe.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
                }
                this.pauseMedia();
            }

            this.isFullscreen = !this.isFullscreen;
        },
        pauseMedia() {
            this.$nextTick(() => {
                this.currentMedia = null;
            });
            // const iframe = this.$refs.iframe_media_emebebed;
            // // Acceder al contenido del iframe
            // var iframeContent = iframe.contentDocument || iframe.contentWindow.document;
            // // Verificar si el contenido es una página de YouTube
            // if (iframeContent && iframeContent.getElementById('movie_player')) {
            //     // Pausar el video de YouTube
            //     iframeContent.getElementById('movie_player').pauseVideo();
            // }
        }
    }
}
</script>
<style lang="scss">
@import "resources/sass/variables";

.v-list-custom-color {
    background: $primary-default-color !important;
    box-shadow: 0px 4px 15px 0px rgba(0, 0, 0, 0.15) !important;
    border-radius: 8px !important;
    color: white !important;
    padding-left: 12px !important;
}
.not-show-arrow{
    padding-top: 0px !important;
} 
.not-show-arrow .v-list-item__icon {
    display: none !important;
}
.not-show-arrow .v-list-group__header{
    padding: 0 !important;
}
.v-list-item-custom {
    border-radius: 8px !important;
    background: #FFF !important;
    box-shadow: 0px 4px 15px 0px rgba(0, 0, 0, 0.15) !important;
    margin-left: 6px;
    padding-left: 10px;
    cursor: pointer;
}

.container-preview {
    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: 250px auto;
    height: 80vh;
}
.overflow-hidden{
    overflow: hidden !important;
}
.v-list-custom-group{
    border-radius: 8px;
    background: #FFF;
    box-shadow: 0px 4px 15px 0px rgba(0, 0, 0, 0.15);
    margin: 0px 8px 8px 8px;
    padding: 4px 0px;
}
</style>