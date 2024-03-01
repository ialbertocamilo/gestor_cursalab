<template>
    <div>
        <DefaultDialog :customTitle="true" :showCardActions="false" :noPaddingCardText="true" :options="options"
            width="350px" @onCancel="closeModal" @onConfirm="confirmModal" vCardClass=" p-0 overflow-hidden ">
            <template v-slot:card-title>
                <v-card-title class="py-0">
                    <div class="d-flex w-100 justify-space-between">
                        <div >
                            <span style="font-size::0.8rem !important;">{{ currentTime }}</span>
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
                <div style="position: absolute; right: -18px; top: -19px;">
                    <v-btn
                        color="#7D80EF"
                        fab
                        small
                        dark
                        @click="closeModal"
                    >
                        <v-icon> mdi-close </v-icon>
                    </v-btn>
                </div>
            </template>
            <template v-slot:content>
                <div class="container-preview" >
                    <div v-if="currentMedia" style="height: 250px;">
                        <div v-if="['scorm','genially','office','link','h5p'].find(c => c==currentMedia.type_id)" style="height: 100%;">
                            <div v-if="!isFullscreen" style="width:100%;height: 100%;" class="d-flex justify-content-center align-items-center">
                                <div style="text-align: center;">
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
                                    <div v-else-if="currentMedia.type_id == 'h5p'" class="d-flex justify-item-center align-items-center">
                                        <div id='h5p-container' style="width: 100%;height: 100%;"></div>
                                    </div>
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
                                        <v-list-item-title style="font-size: 0.8rem !important;" >{{ topic.name }}</v-list-item-title>
                                    </v-list-item-content>
                                </template>

                                <v-list-item v-for="(media, index) in topic.medias" :key="index">
                                    <v-list-item-content
                                        @click="changeMedia(media)"
                                        class="v-list-item-custom">

                                        <v-list-item-title
                                            style="font-size: 0.8rem !important;"
                                            :style="currentMedia
                                            ? currentMedia.id == media.id ? 'color:#2E36CE' : null
                                            : null">

                                            <i :class="mixin_multimedias.find(el => el.type === media.type_id).icon || 'mdi mdi-loading'" />
                                            {{ media.name }}
                                        </v-list-item-title>
                                    </v-list-item-content>
                                </v-list-item>
                            </v-list-group>
                        </v-list>
                    </div>
                </div>
            </template>
            <template v-slot:card-actions>
                <div style="width: 100%">
                    <div style="background: #2E36CE;width: 100%; height: 45px; display: flex; justify-content: space-around;align-items: center;">
                        <div class="circle-content mdi mdi-bullhorn mdi-24px"></div>
                        <div class="circle-content mdi mdi-book-open-page-variant mdi-24px"></div>
                        <div class="circle-content mdi mdi-video mdi-24px"></div>
                        <div class="circle-content mdi mdi-chart-line mdi-24px"></div>
                    </div>
                    <div style="width: 100%;display: flex;justify-content: center;align-items: center;padding: 5px;">
                        <div style="width: 150px;height: 4px;background: #434D56;"></div>
                    </div>
                </div>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>
import DocPreview from '../Project/DocPreview';
import { H5P } from 'h5p-standalone';
// import H5PStandalone from 'h5p-standalone';
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
            currentMedia: {
                id:0
            },
            currentTime: this.setCurrentTime(),
            isFullscreen: false,
        };
    },
    methods: {
        closeModal() {
            let vue = this
            console.log('onCancel');
            vue.$emit('onCancel')
            vue.pauseMedia();
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
        async loadData({resource_id,type,route}) {
            let vue = this
            let url = route;
            if(!route){
                url =  (type == 'course') && `/cursos/${resource_id}/medias`;
            }
            console.log(url);
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
        setH5P(media){
            setTimeout(() => {
                const el = document.getElementById('h5p-container');
                const options = {
                    h5pJsonPath: media.url,
                    frameJs: '/dist-h5p/frame.bundle.js',
                    frameCss: '/dist-h5p/styles/h5p.css',
                };
                // new H5PStandalone(el, options);
                new H5P(el, options);
                // const h5pStandalone = new H5PStandalone(options, el);
                // h5pStandalone.run();
            }, 2500);
        },
        removeH5P(){
            var h5pContainer = document.getElementById("h5p-container");
            if (h5pContainer) {
                while (h5pContainer.firstChild) {
                    h5pContainer.removeChild(h5pContainer.firstChild);
                }
            }
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
            let vue = this;
            const iframe = vue.$refs.iframe;
            if (!vue.isFullscreen) {
                if (iframe.requestFullscreen) {
                iframe.requestFullscreen();
                } else if (iframe.mozRequestFullScreen) {
                iframe.mozRequestFullScreen();
                } else if (iframe.webkitRequestFullscreen) {
                iframe.webkitRequestFullscreen();
                } else if (iframe.msRequestFullscreen) {
                iframe.msRequestFullscreen();
                }
                if(vue.currentMedia.type_id == 'h5p'){
                    vue.setH5P(vue.currentMedia);
                }
            } else {
                vue.removeH5P();
                if (document.exitFullscreen) {
                document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
                }
            }

            this.isFullscreen = !this.isFullscreen;
        },
        pauseMedia() {
            this.$nextTick(() => {
                this.currentMedia = {
                    id:0
                };
            });
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
    margin: -8px;
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
.circle-content{
    height: 28px;
    width: 28px;
    background-color: #2e36ce;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
}
</style>
