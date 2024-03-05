

<template>
    <v-dialog :max-width="width" v-model="value" scrollable @click:outside="closeModal">
        <v-card class="modal_avatars_repository">
            <v-card-text class="pt-0">
                <v-card style="box-shadow:none !important;" class="bx_steps bx_step1">
                    <v-card-text>
                        <v-row class="mt-3">
                            <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                <h4 class="text_default lbl_tit">Repositorio de guías</h4>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                <span class="text_default">Cambiar foto de guías de tu empresa.</span>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" md="12" lg="12" class="pb-0">
                                <div class="box_avatars_img">
                                    <div class="item_avatar_img">
                                        <div class="bg_icon_item" :class="[classSelectImageAvatar('img_guide_1')]" @click="selectImageAvatar('img_guide_1')">
                                            <img src="/img/induccion/personalizacion/perfil-hombre.png" ref="img_guide_1">
                                        </div>
                                    </div>
                                    <div class="item_avatar_img">
                                        <div class="bg_icon_item" :class="[classSelectImageAvatar('img_guide_2')]" @click="selectImageAvatar('img_guide_2')">
                                            <img src="/img/induccion/personalizacion/perfil-mujer.png" ref="img_guide_2">
                                        </div>
                                    </div>
                                    <div class="item_avatar_img" v-for="(icon, index) in list_avatars" :key="index">
                                        <div class="bg_icon_item" :class="[classSelectImageAvatar(icon.title ? icon.title : 'name_icon_'+index)]"  @click="selectImageAvatarUpload(icon, icon.title ? icon.title : 'name_icon_'+index)">
                                            <img :src="icon.url ? icon.url : icon.logo_cropped">
                                        </div>
                                    </div>
                                    <div class="item_avatar_img">
                                        <div class="bg_icon_item" style="outline: none !important;" @click="openModalUploadAvatarOnboarding(modalUploadImageResize)">
                                            <v-icon style="color: #5458EA;">
                                                mdi-plus-circle
                                            </v-icon>
                                        </div>
                                    </div>
                                </div>
                            </v-col>
                        </v-row>
                        <v-row class="mt-4 text-center">
                            <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                <DefaultButton :label="'Agregar otra foto de guía personalizada'"
                                    @click="openModalUploadAvatarOnboarding(modalUploadImageResize)"
                                    :outlined="true"
                                    style="border-radius: 20px;"
                                    />
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>

            </v-card-text>

            <v-card-actions class="actions_btn_modal">
                <DefaultButtonModalSteps
                    @cancel="prevStep"
                    @confirm="nextStep"
                    :cancelLabel="cancelLabel"
                    confirmLabel="Aplicar"
                    :disabled_next="disabled_btn_next"
                    />
            </v-card-actions>
        </v-card>

        <ModalUploadImageResize
            :ref="modalUploadImageResize.ref"
            v-model="modalUploadImageResize.open"
            :width="'500px'"
            @onCancel="closeFormModal(modalUploadImageResize)"
            @onConfirm="addIconFinishedOnboarding"
            :label="modalUploadImageResize.label"
            :subtitle="modalUploadImageResize.subtitle"
        />
    </v-dialog>
</template>


<script>
import draggable from 'vuedraggable'
import DefaultButtonModalSteps from '../../globals/DefaultButtonModalSteps.vue';
import ModalUploadImageResize from './ModalUploadImageResize';

export default {
    components: {
        draggable,
        DefaultButtonModalSteps,
        ModalUploadImageResize
    },
    props: {
        value: Boolean,
        width: String,
    },
    data() {
        return {
            disabled_btn_next: true,
            process: {
                limit_absences: false,
            },
            limit_absences: false,
            cancelLabel: "Cancelar",
            isLoading: false,
            modalUploadImageResize: {
                ref: 'ModalUploadImageResize',
                open: false,
                endpoint: '',
            },
            list_avatars: [],
            list_avatars_empty: [null,null,null,null],
            logo_selected: null,
            logo_cropped: null,
            img_avatar_selected: null,
            img_avatar_selected_name: '',
        };
    },
    watch: {
        img_avatar_selected: {
            handler(n, o) {
                let vue = this;
                vue.disabled_btn_next = n ? false : true
            },
            deep: true
        },
    },
    methods: {

        openModalUploadAvatarOnboarding(modalUploadImageResize) {
            let vue = this
            modalUploadImageResize.label = 'Recomendado (500 x 500px)'
            modalUploadImageResize.subtitle = 'Agrega la imagen del guía.'
            vue.openFormModal(modalUploadImageResize)
        },
        classSelectImageAvatar(ref_image) {
            let vue = this
            return vue.img_avatar_selected_name == ref_image ? 'selected' : ''
        },
        selectImageAvatar(ref_image) {
            let vue = this
            vue.$nextTick(() => {
                vue.img_avatar_selected = vue.$refs[ref_image].src
                vue.img_avatar_selected_name = ref_image
            })
        },
        selectImageAvatarUpload(url, name) {
            let vue = this
            vue.$nextTick(() => {
                vue.img_avatar_selected = url
                vue.img_avatar_selected_name = name
            })
        },
        addIconFinishedOnboarding(data) {
            let vue = this
            if(data.logo_cropped)
                vue.list_avatars.push(data)

            vue.closeFormModal(vue.modalUploadImageResize)
        },
        validateRequired(input) {
            return input != undefined && input != null && input != "";
        },
        nextStep(){
            let vue = this;
            vue.cancelLabel = "Cancelar";

            vue.confirm();
        },
        prevStep(){
            let vue = this;
            vue.closeModal();
        },
        closeModal() {
            let vue = this;
            vue.process = {}
            vue.$emit("onCancel");
        },
        async confirm() {
            let vue = this;
            vue.$emit("onConfirm", vue.img_avatar_selected, vue.img_avatar_selected_name);
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        loadSelects(resource) {
        },
        async loadData(resource) {
            let vue = this

            if(resource.repository && resource.repository.list_guide.length > 0)
                vue.list_avatars = resource.repository.list_guide
        },
        resetValidation() {
            let vue = this;
            console.log('resetValidation')
            // vue.search_text = null
            // vue.results_search = []
        },
    }
};
</script>
<style lang="scss">

.modal_avatars_repository {
    h4.text_default.lbl_tit {
        font-size: 16px;
    }
    .box_avatars_img {
        display: flex;
        column-gap: 20px;
        row-gap: 20px;
        background-color: #F3F3F3;
        padding: 30px 20px;
        border-radius: 8px;
        flex-wrap: wrap;
        height: 260px;
        overflow: auto;
        padding-right: 0;
        .item_avatar_img {
            .bg_icon_item{
                width: 88px;
                height: 88px;
                border-radius: 50%;
                overflow: hidden;
                cursor: pointer;
                background-color: #D9D9D9;
                display: flex;
                justify-content: center;
                align-items: center;
                img {
                    max-width: 100%;
                }
                &:hover, &.selected {
                    outline: 2px solid #5458EA;
                }
            }
        }
    }
}
</style>
