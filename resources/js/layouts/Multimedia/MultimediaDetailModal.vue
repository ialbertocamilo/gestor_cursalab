<template>
    <div>
        <DefaultDialog
            :options="options"
            :width="width"
            @onCancel="closeModal"
            @onConfirm="confirmModal"
        >
            <!-- :show-card-actions="false" -->
            <template v-slot:content>
                <v-row class="mb-3">
                    <div class="col col-8 text-left py-0 text-overflow-ellipsis small" :title="resource.title">
                        {{ resource.title }}
                    </div>

                    <div class="col col-2 text-right py-0 text-overflow-ellipsis small" :title="resource.type">{{ resource.type }}</div>
                    <div class="col col-2 text-right py-0 text-overflow-ellipsis small" :title="resource.formattedSize">{{ resource.formattedSize }}</div>
                </v-row>
                <v-row>
                    <v-col cols="12" class="py-0">
                    
                        <v-img
                            :src="resource.preview"
                            :lazy-src="`https://picsum.photos/10/6?image=200`"
                            max-height="350"
                            contain
                            class="grey lighten-5"
                        />
                            <!-- aspect-ratio="4/3" -->

                        <p class="text-center mb-3">
                            <!-- <strong>[ {{ resource.created }} ]</strong> -->
                        </p>
                    </v-col>
                </v-row>

                <v-row justify="center" class="mx-0">
                    <v-col cols="12" class="d-flex justify-content-center py-0">


                        <v-btn
                            class=""
                            text
                            elevation="0"
                            :ripple="false"
                            color="primary"
                            @click="openMultimedia"
                            title="Abrir multimedia externamente"
                        >
                            <v-icon color="primary">mdi-open-in-new</v-icon>
                            <!-- Abrir multimedia -->
                        </v-btn>

                        <v-btn
                            class=""
                            text
                            elevation="0"
                            :ripple="false"
                            color="primary"
                            @click="downloadMultimedia"
                            v-if="resource.ext !== 'scorm'"
                            title="Descargar multimedia"
                        >
                            <v-icon color="primary">mdi-download</v-icon>
                            <!-- Descargar multimedia -->
                        </v-btn>

                        <v-btn
                            class=""
                            text
                            elevation="0"
                            :ripple="false"
                            color="grey"
                            @click="modalDeleteOptions.open = true"
                            v-if="resource.ext !== 'scorm'"
                            title="Eliminar multimedia"
                        >
                            <v-icon color="grey">mdi-trash-can</v-icon>
                            <!-- Eliminar multimedia -->
                        </v-btn>
                    </v-col>
                   
                </v-row>

                <v-row v-if="resource.sections">

                    <div class="col col-12 text-center">
                        <hr class="mt-0">

                        <strong>Secciones donde se utiliza:</strong>
                    </div>

                    <MultimediaSectionsInfo :resource="resource.sections.courses" label="Cursos" />
                    <MultimediaSectionsInfo :resource="resource.sections.topics" label="Temas" />
                    <MultimediaSectionsInfo :resource="resource.sections.schools" label="Escuelas" />
                    <MultimediaSectionsInfo :resource="resource.sections.announcements" label="Anuncios" />
                    <MultimediaSectionsInfo :resource="resource.sections.videotecas" label="Videotecas" />
                    <MultimediaSectionsInfo :resource="resource.sections.vademecums" label="Protocolos y documentos" />
                    <MultimediaSectionsInfo :resource="resource.sections.modules" label="Módulos" />

                    <!-- <v-col cols="6" v-if="false">
                        <DefaultModalSection
                            title="Ubicación asignada"
                        />
                    </v-col> -->
                </v-row>
               
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
            </template>

        </DefaultDialog>
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
