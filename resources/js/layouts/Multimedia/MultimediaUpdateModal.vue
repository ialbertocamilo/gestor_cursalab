<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-row justify="center">
                <v-col cols="12">
                    <fieldset class="editor">
                        <legend v-text="'Archivo'"/>
                        <DropzoneDefault
                            :ref="dropzoneDefault"
                            @onUpload="setFileSelected"
                            :types-allowed="types"
                        />
                    </fieldset>
                </v-col>
            </v-row>
        </template>
    </DefaultDialog>
</template>
<script>
import DropzoneDefault from "./DefaultMultimediaDropzone";

export default {
    components: {DropzoneDefault},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            archivos: new FormData(),
            // archivo: null,
            dropzoneDefault: 'dropzoneDefault',
            types: ['image', 'video', 'audio', 'pdf', 'excel', 'scorm']
        }
    },
    methods: {
        thereAreFileSelected() {
            let vue = this
            let len = 0

            for (var value of vue.archivos.values()) {
                len++
            }

            return len > 0
        },
        closeModal() {
            let vue = this

            vue.archivos = new FormData()
            vue.$refs['dropzoneDefault'].removeAll()

            vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            vue.showLoader()
            // if (vue.archivo) {
            // console.log(vue.thereAreFileSelected())
            if (vue.thereAreFileSelected()) {
                vue.logFormDataValues(vue.archivos)
                let url = `${vue.options.base_endpoint}/upload`
                // // let formData = new FormData()
                // // formData.append('file', vue.archivo)
                vue.$http.post(url, vue.archivos)
                    .then(({data}) => {
                        // console.log('DATA RESPONSE :: ', data)
                        vue.queryStatus("multimedia", "crear_contenido");
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        setTimeout(() => {
                            vue.hideLoader()
                        }, 800)
                    })
                    .catch(e => {

                        if (e.response.data.msg) {
                            vue.showAlert(e.response.data.msg, 'warning')
                        }

                        vue.hideLoader()
                    })
            }
        },
        resetValidation() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this

            vue.archivos = new FormData()
            if (vue.$refs['dropzoneDefault'])
                vue.$refs['dropzoneDefault'].removeAll()
            // vue.$nextTick(() => {
            //     vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            // })
            return 0;
        },
        loadSelects() {
            let vue = this

        },
        // setFileSelected(file) {
        //     let vue = this
        //     vue.archivo = file
        // }
        setFileSelected(file) {
            let vue = this
            // console.log(file)
            // files.forEach(file => {
            //     console.log('se agregar el file ::'.file)
            vue.archivos.append('file[]', file)
            // })
        }
    }
}
</script>
<style lang="scss">
@import "resources/sass/variables";

.multimedia-label {
    font-weight: bold;
    line-height: 21px;
    letter-spacing: 0.01em;
    color: $primary-default-color;
}
</style>
