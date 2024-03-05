<template>
    <v-dialog
        class="default-dialog"
        v-model="value"
        :width="width"
        scrollable
        persistent
        @click:outside="closeModal"
        :class="{}"
        content-class="br-dialog"
    >
        <v-card class="card_modal_upload_resize">
            <!-- <div class="bx_close_modal_activity">
                <v-btn icon :ripple="false" @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </div> -->
            <v-card-text class="py-8">
                <!-- <p class="title_act">Edita la imagen que estas por subir</p>
                <p class="title_act">Agregar icono al final del proceso de inducción</p>
                <div class="bx_items_activitys"> -->
                <v-form ref="projectForm">
                    <v-row class="mt-3">
                        <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                            <h4 class="text_default lbl_tit" style="font-size: 16px;">Edita la imagen que estás por subir</h4>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                            <span class="text_default">{{ subtitle ? subtitle : 'Agregar ícono al final del proceso de inducción' }}</span>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="12" class="bx_upload_resize">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputLogo"
                                v-model="resource.logotipo"
                                :label="label ? label : 'Recomendado (500 x 500px)'"
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'logotipo')"
                                @onPreview="logo_selected = $event"
                                @croppedImage="logo_cropped = $event"
                                @removeImage="logo_cropped = $event"
                                :sizeCropp="{width:500, height:500}"
                                :showButton="false"
                                :cropImage="true"
                                />
                        </v-col>
                    </v-row>
                </v-form>
                <!-- </div> -->
            </v-card-text>

            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)" class="actions_btn_modal">
                <DefaultButtonModalSteps
                    @cancel="closeModal"
                    @confirm="confirm"
                    :disabled_next="disabled_btn_next"
                    />
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script>
export default {
    props: ["value", "width", "label", "subtitle"],
    data() {
        return {
            dialog: false,
            resource: {},
            logo_selected: null,
            logo_cropped: null,
            disabled_btn_next: true
        };
    },
    watch: {
        logo_cropped: {
            handler(n, o) {
                let vue = this;
                vue.disabled_btn_next = n ? false : true
            },
            deep: true
        },
    },
    methods: {
        async confirm() {
            let vue = this;
            let data = {
                'logo_cropped' : vue.logo_cropped,
                'logo_selected' : vue.logo_selected
            }
            if(vue.logo_cropped) {
                data.logotipo_blob = await fetch(vue.logo_cropped).then(res => res.blob());
            }
            vue.$emit("onConfirm", data);
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },

        closeModal() {
            let vue = this
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        selectTemplateOrNewProcessModal(value) {
            let vue = this
            vue.$emit('selectTemplateOrNewProcessModal', value)
        },

        async loadData(resource) {
            let vue = this
        },
        loadSelects() {
            let vue = this
        },
        resetValidation() {
            let vue = this;
            vue.removeFileFromDropzone(vue.resource.logotipo, 'inputLogo')
            vue.$refs.projectForm.resetValidation()
            vue.$refs.projectForm.reset()
            console.log('resetValidation')
            // vue.resource = {}
            // vue.logo_selected = null
            // vue.logo_cropped = null
        },
    },
};
</script>

<style lang="scss">
.card_modal_upload_resize {
    .bx_close_modal_activity {
        position: absolute;
        right: 10px;
        top: 10px;
    }
    .bx_items_activitys {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        margin: 40px 0 20px;
    }
    .bx_item_activity {
        padding: 26px;
        border-radius: 10px;
        width: 218px;
        margin: 5px;
        cursor: pointer;
        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
    }
    .bx_item_activity:hover {
        box-shadow: 0px 4px 15px rgba(194,194,194,1);
    }
    .bx_item_activity h5 {
        color: #2A3649;
        font-size: 16px;
        line-height: 20px;
        font-family: "Nunito", sans-serif;
        font-weight: bold;
        margin: 14px 0;
        min-height: 40px;
        display: inline-flex;
        align-items: center;
    }
    .bx_item_activity p {
        color: #2A3649;
        font-size: 13px;
        line-height: 17px;
        font-family: "Nunito", sans-serif;
    }
    .title_act {
        color: #2A3649;
        font-size: 19px;
        line-height: 21px;
        font-family: "Nunito", sans-serif;
        font-weight: bold;
        margin: 14px 0 25px;
    }
}
</style>
