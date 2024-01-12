<template>
    <v-dialog
        class="default-dialog"
        v-model="value"
        :width="width"
        scrollable
        persistent
        @click:outside="closeModalSelectLogoPromotor"
        :class="{}"
        content-class="br-dialog"
    >
        <v-card>
            <div class="bx_close_modal_activity">
                <v-btn icon :ripple="false" @click="closeModalSelectLogoPromotor">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </div>
            <v-card-text class="py-8 text-center">
                <p class="title_act">Selecciona cual archivo utilizarás</p>
                <div class="bx_items_activitys">
                    <div class="bx_item_activity">
                        <OrdenadorLogoPromotor
                            :ref="dropzoneDefault"
                            :types-allowed="['image']"
                            @onUpload="setMediaPreviewUpload"
                        />
                    </div>
                    <div class="bx_item_activity" @click="selectLogoPromotorModal('multimedia')">
                        <div class="img"><img src="/img/benefits/promotor_multimedia.svg"></div>
                        <h5>Multimedia</h5>
                        <p>Selecciona una imagen de tus archivos multimedia</p>
                    </div>
                </div>
            </v-card-text>
            <SelectMultimedia
                :ref="modalPreviewMultimedia.ref"
                :options="modalPreviewMultimedia"
            :custom-filter="fileTypes"
                width="85vw"
                @onClose="closeSelectPreviewMultimediaModal"
                @onConfirm="onSelectMediaPreview"
            />
        </v-card>
    </v-dialog>
</template>


<script>
import SelectMultimedia from "../forms/SelectMultimedia";
import OrdenadorLogoPromotor from "./OrdenadorLogoPromotor.vue";
export default {
    components: { SelectMultimedia, OrdenadorLogoPromotor },
    props: ["value", "width", "title", "subtitle", "txt_btn_confirm", "txt_btn_cancel", "options", "content_modal", "title_modal"],
    data() {
        return {
            archivos: new FormData(),
            modalPreviewMultimedia: {
                ref: 'modalSelectPreviewMultimedia',
                open: false,
                title: 'Buscar multimedia',
                confirmLabel: 'Seleccionar',
                cancelLabel: 'Cerrar'
            },
            fileTypes: {
                type: Object | Array,
                default: function () {
                    return []
                }
            },
            dialog: false,
            isLoading: false,
            uploadReady: true,
            file: {
                name: "",
                size: 0,
                type: "",
                fileExtention: "",
                url: "",
                isImage: false,
                isUploaded: false,
            },
            fileSelected: null,
            dropzoneDefault: 'dropzoneDefault'
        };
    },
    methods: {
        setMediaPreviewUpload(file) {
            let vue = this
            vue.fileSelected = file
            vue.$emit('confirmSelectLogoPromotorOrdenador', vue.fileSelected)
        },
        openSelectPreviewMultimediaModal() {
            let vue = this
            vue.modalPreviewMultimedia.open = true
            vue.$refs[vue.modalPreviewMultimedia.ref].getData()
        },
        closeSelectPreviewMultimediaModal() {
            let vue = this
            vue.modalPreviewMultimedia.open = false
        },
        onSelectMediaPreview(media) {
            let vue = this
            vue.closeSelectPreviewMultimediaModal()
            console.log(media);
            vue.closeModalSelectLogoPromotor()
            // vue.fileSelected = media.file
            // vue.$emit('onSelect', vue.fileSelected)
            vue.confirmSelectLogoPromotor(media.file)
        },
        handleFileChange(e) {
            let vue = this;
        // Check if file is selected
        if (e.target.files && e.target.files[0]) {
            // Get uploaded file
            const file = e.target.files[0],
            // Get file size
            fileSize = Math.round((file.size / 1024 / 1024) * 100) / 100,
            // Get file extension
            fileExtention = file.name.split(".").pop(),
            // Get file name
            fileName = file.name.split(".").shift(),
            // Check if file is an image
            isImage = ["jpg", "jpeg", "png", "gif"].includes(fileExtention);
            // Print to console
            console.log(fileSize, fileExtention, isImage);

            vue.archivos.append('file[]', file)
console.log(vue.archivos, file, vue.archivos.values());
vue.closeModalSelectLogoPromotor()
            // let reader = new FileReader();
            // reader.addEventListener(
            //     "load",
            //     () => {
            //     // Set file data
            //     this.file = {
            //         name: fileName,
            //         size: fileSize,
            //         type: file.type,
            //         fileExtention: fileExtention,
            //         isImage: isImage,
            //         url: reader.result,
            //         isUploaded: true,
            //     };
            //     },
            //     false
            // );
            // this.file = {
            //     name: fileName,
            //     size: fileSize,
            //     type: file.type,
            //     fileExtention: fileExtention,
            //     isImage: isImage,
            //     url: reader.result,
            //     isUploaded: true,
            // };
            // console.log(this.file, reader);
        }
        },
        confirmSelectLogoPromotor(value) {
            let vue = this;
            vue.$emit("confirmSelectLogoPromotor",value);
        },
        doSomething(e) {
        const file = e.target.files[0]
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },

        closeModalSelectLogoPromotor() {
            let vue = this
            vue.$emit('closeModalSelectLogoPromotor')
        },
        selectLogoPromotorModal(value) {
            let vue = this
            console.log(value);
            if(value == 'multimedia') {
                vue.openSelectPreviewMultimediaModal()
            }
            else {
                let fileInputElement = this.$refs.input_logo_promotor;
                fileInputElement.click();
            }
            // vue.$emit('selectLogoPromotorModal', value)
        }
    },
};
</script>

<style lang="scss">
.br-dialog, .br-dialog .v-sheet.v-card{
    border-radius: 16px !important;
}
.bx_close_modal_activity {
    position: absolute;
    right: 10px;
    top: 10px;
}
.bx_items_activitys {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}
.bx_item_activity {
    padding: 26px;
    border-radius: 10px;
    width: 215px;
    margin: 5px;
    cursor: pointer;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
}
.bx_item_activity:hover {
    box-shadow: 0px 4px 15px rgba(194,194,194,1);
}
.bx_item_activity h5 {
    color: #2A3649;
    font-size: 17px;
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
</style>
