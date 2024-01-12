<template>
    <div class="multimedia-box" @click="openModal">
        <i :class="data.icon || 'mdi mdi-loading'"/>
        <span class="multimedia-box-span" v-text="data.label"/>
        <ModalMultimediaText
            :limits ="limits"
            width="60vw"
            :options="modalMultimediaTextOptions"
            :type="data.type"
            @close="modalMultimediaTextOptions.open = false "
            @onConfirm="emitConfirm"
        />
            <!-- height="70vh" -->
        <ModalMultimediaMix
            :limits="limits"
            width="60vw"
            :type="data.type"
            :label="data.label"
            :options="modalMultimediaMixOptions"
            @close="modalMultimediaMixOptions.open = false "
            :filter-type="data.type"
            @onConfirm="emitConfirm"
        />
    </div>
</template>
<script>
import ModalMultimediaText from "./ModalMultimediaText";
import ModalMultimediaMix from "./ModalMultimediaMix";
export default {
    components: {ModalMultimediaText, ModalMultimediaMix},
    props: {
        data: {
            type: Object,
            required: true
        },
        limits:{
            type: Object,
            required: false
        }
    },
    data() {
        return {
            modalMultimediaTextOptions: {
                ref: 'TemaMultimediaStringModal',
                title: null,
                open: false,
                confirmLabel: 'Guardar'
            },
            modalMultimediaMixOptions: {
                ref: 'TemaMultimediaMixModal',
                title: null,
                open: false,
                confirmLabel: 'Guardar'
            },
        }
    },
    methods: {
        openModal() {
            let vue = this
            const type = vue.data.type

            if (['youtube', 'vimeo','genially'].includes(type)) {
                vue.modalMultimediaTextOptions.title = vue.data.label
                vue.modalMultimediaTextOptions.open = true
            } else if (type === 'link') {
                vue.modalMultimediaTextOptions.title = 'Link (Genially, SCORM, etc.)'//vue.data.label
                vue.modalMultimediaTextOptions.open = true
            } else {
                vue.modalMultimediaMixOptions.title = vue.data.label
                vue.modalMultimediaMixOptions.open = true
            }
        },
        emitConfirm(multimedia){
            let vue = this

            vue.modalMultimediaTextOptions.open = false
            vue.modalMultimediaMixOptions.open = false
            vue.$emit('addMultimedia', multimedia)
        }
    }
}
</script>
