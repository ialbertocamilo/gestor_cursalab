

<template>
    <v-dialog :max-width="width" v-model="value" scrollable @click:outside="closeModal">
        <v-card class="modal_avatars_repository">
            <v-card-text class="pt-0">
                <v-card style="box-shadow:none !important;" class="bx_steps bx_step1">
                    <v-card-text>
                        <v-row class="mt-3">
                            <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                <h4 class="text_default lbl_tit">Repositorio de avatars</h4>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                <span class="text_default">Cambiar foto de perfil de tu empresa.</span>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" md="12" lg="12" class="pb-0">
                                <div class="box_avatars_img">
                                    <div class="item_avatar_img">
                                        <img src="/img/induccion/personalizacion/perfil-hombre.png">
                                    </div>
                                    <div class="item_avatar_img">
                                        <img src="/img/induccion/personalizacion/perfil-mujer.png">
                                    </div>
                                    <div class="item_avatar_img" v-for="(img, index) in list_avatars" :key="999+index" @click="img_selected = img">
                                        <img :src="img.logo_cropped">
                                    </div>
                                    <div class="item_avatar_img" v-for="(img, index) in list_avatars_empty" :key="111+index"></div>
                                </div>
                            </v-col>
                        </v-row>
                        <v-row class="mt-4 text-center">
                            <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                <DefaultButton :label="'Agregar otra foto de perfil personalizada'"
                                    @click="openFormModal(modalUploadImageResize)"
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
            img_selected: null,
        };
    },
    watch: {
        // process: {
        //     handler(n, o) {
        //         let vue = this;
        //         vue.disabled_btn_next = !(vue.validateRequired(vue.process.title) && vue.validateRequired(vue.process.description));
        //     },
        //     deep: true
        // }
        img_selected: {
            handler(n, o) {
                let vue = this;
                vue.disabled_btn_next = n ? false : true
                console.log(n);
                console.log(o);
            },
            deep: true
        },
    },
    methods: {
        addIconFinishedOnboarding(data) {
            console.log(data);
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
            vue.$emit("onConfirm", vue.img_selected);
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        loadSelects(resource) {
        },
        async loadData(resource) {
            let vue = this
            console.log(resource);

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
        .item_avatar_img {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            background-color: #D9D9D9;
            img {
                max-width: 100%;
            }
            &:hover {
                outline: 2px solid #5458EA;
            }
        }
    }
}
</style>
