<template>
    <div>
        <DefaultDialog
            :options="options"
            :width="width"
            @onCancel="closeModal"
            @onConfirm="confirmModal"
        >
            <template v-slot:content>
                <v-row>
                    <v-col cols="8">
                        <v-img
                            :src="resource.preview"
                            :lazy-src="`https://picsum.photos/10/6?image=200`"
                            aspect-ratio="1.7"
                            contain/>
                    </v-col>
                    <v-col cols="4" class="d-flex flex-column align-center" style="justify-content: space-evenly">
                        <div class="d-flex justify-content-center flex-column align-center">
                            <span class="multimedia-label mb-1">
                                Abrir en otra pestaña
                            </span>
                            <DefaultButton
                                append-icon="mdi-open-in-new"
                                label="Abrir"
                                @click="openMultimedia"
                            />
                        </div>
                        <DefaultButton
                            v-if="resource.ext !== 'scorm'"
                            append-icon="mdi-download"
                            label="Descargar"
                            @click="downloadMultimedia"
                        />
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <v-row>
                            <v-col cols="4" class="multimedia-label" v-text="'Nombre de archivo:'"/>
                            <v-col cols="8" v-text="resource.title"/>
                            <v-col cols="4" class="multimedia-label" v-text="'Tipo:'"/>
                            <v-col cols="8" v-text="resource.type"/>
                            <v-col cols="4" class="multimedia-label" v-text="'Peso:'"/>
                            <v-col cols="8" v-text="resource.formattedSize"/>
                            <v-col cols="4" class="multimedia-label" v-text="'Fecha de creación:'"/>
                            <v-col cols="8" v-text="resource.created"/>
                        </v-row>
                    </v-col>

                    <div v-if="resource.sections">

                        <MultimediaSectionsInfo :resource="resource.sections.courses" label="Cursos" />
                        <MultimediaSectionsInfo :resource="resource.sections.topics" label="Temas" />
                        <MultimediaSectionsInfo :resource="resource.sections.schools" label="Escuelas" />
                        <MultimediaSectionsInfo :resource="resource.sections.announcements" label="Anuncios" />
                        <MultimediaSectionsInfo :resource="resource.sections.videotecas" label="Videotecas" />
                        <MultimediaSectionsInfo :resource="resource.sections.vademecums" label="Protocolos y documentos" />
                        <MultimediaSectionsInfo :resource="resource.sections.modules" label="Módulos" />

                    </div>  

                    <v-col cols="6" v-if="false">
                        <DefaultModalSection
                            title="Ubicación asignada"
                        />
                    </v-col>
                </v-row>
            </template>
        </DefaultDialog>
        <DialogConfirm
            :ref="modalDeleteOptions.ref"
            v-model="modalDeleteOptions.open"
            :options="modalDeleteOptions"
            width="408px"
            title="Eliminar Multimedia"
            subtitle="¿Está seguro de eliminar el archivo multimedia?"
            @onConfirm="confirmDelete"
            @onCancel="modalDeleteOptions.open = false"
        />
    </div>
</template>
<script>
import DialogConfirm from "../../components/basicos/DialogConfirm";
import MultimediaSectionsInfo from "./MultimediaSectionsInfo";

export default {
    components: { DialogConfirm, MultimediaSectionsInfo },
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
            modalDeleteOptions: {
                ref: 'MediaDeleteModal',
                title: 'Eliminar Media',
                contentText: '¿Desea eliminar este registro?',
                open: false,
                title_modal: 'Eliminación de un <b>archivo multimedia</b>',
                type_modal: 'delete',
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar un archivo multimedia!',
                        details: [
                            'Este archivo se eliminará para todos los temas asignados y lugares que haya sido asignado.'
                        ],
                    }
                },
                width: '408px'
            },
            resourceDefault: {
                created_at: null,
                ext: null,
                file_url: null,
                id: null,
                title: null,
                type: null,
            },
            resource: {
                sections: {
                    course: [],
                    topics: [],
                    schools: [],
                    announcements: [],
                    videotecas: [],
                    vademecums: []
                }
            },
        }
    },
    methods: {
        closeModal() {
            let vue = this;
            vue.$emit('onCancel');
        },
        confirmModal() {
            let vue = this

            vue.modalDeleteOptions.open = true
        },
        confirmDelete() {
            let vue = this
            vue.showLoader()

            let url = `${vue.options.base_endpoint}/eliminar/${vue.resource.id}`

            vue.$http.get(url)
                .then(({data}) => {
                    vue.modalDeleteOptions.open = false
                    vue.closeModal()
                    vue.showAlert(data.data.msg)
                    vue.$emit('onConfirm')
                    vue.hideLoader()
                })
                .catch(err => {
                    vue.modalDeleteOptions.open = false
                    vue.hideLoader()
                })
        },
        resetValidation() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.options.base_endpoint}/show/${resource.id}`
            await vue.$http.get(url)
                .then(({data}) => {
                    if (resource) {
                        vue.resource = Object.assign({}, data.data.multimedia)
                    }
                })
            return 0;
        },
        loadSelects() {
            let vue = this

        },
        openMultimedia() {
            let vue = this
            this.openInNewTab(vue.resource.file_url)
        },
        downloadMultimedia() {
            let vue = this
            const url = `/multimedia/${vue.resource.id}/download`
            this.openInNewTab(url)
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
