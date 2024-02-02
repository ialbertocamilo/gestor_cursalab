<template>
    <v-row justify="center">
        <v-col cols="4" lg="2" class="--text-center">
            <img
                v-if="activeTemplate"
                :src="activeTemplate.image" height="90" class="--my-7 clickable"
                @click="modalDiplomaPreviewOptions.open = true"
                title="Ver preview"
                style="max-width: 110px;"
            />
            <img
                v-else
                :src="oldPreview || '/img/noimage.png'" height="90" class="--primary"  style="max-width: 110px;"
            />
        </v-col>

        <v-col cols="8" lg="6" class="">
            <v-row >
                <v-col cols="12" class="d-flex justify-content-start px-4 align-items-start">
                    <DefaultAutocomplete
                        clearable
                        dense
                        :items="certificateTemplates"
                        item-text="title"
                        return-object
                        show-required
                        v-model="activeTemplate"
                        label="Plantilla de diploma"
                        class="col col-12 pa-0"
                    />
                </v-col>

                    <!-- v-if="activeTemplate" -->
                <v-col
                    cols="12"
                    class="d-flex  justify-space-around px-0 py-0">

                    <span>
                        <v-icon :color="itHasFeature('courses', activeTemplate) ? '#5751e6' : '#e0e0e0'">
                            mdi-check-circle
                        </v-icon>
                        Curso
                    </span>

                    <span class="">
                        <v-icon :color="itHasFeature('users', activeTemplate) ? '#5751e6' : '#e0e0e0'">
                            mdi-check-circle
                        </v-icon>
                        Usuario
                    </span>

                    <span class="">
                        <v-icon
                            :color="itHasFeature('fecha', activeTemplate) ? '#5751e6' : '#e0e0e0'">
                            mdi-check-circle
                        </v-icon>
                        Fecha
                    </span>

                    <span class="">
                        <v-icon
                            :color="itHasFeature('course-average-grade', activeTemplate) ? '#5751e6' : '#e0e0e0'">
                            mdi-check-circle
                        </v-icon>
                        Nota
                    </span>
                </v-col>

            </v-row>
        </v-col>

        <v-col cols="4"  class="d-lg-flex d-none justify-content-center align-items-center text-right">
            <div class="row">
                <div class="col col-12 py-1">
                    <a href="/diplomas" target="_blank">Ir al listado de diplomas</a>
                </div>
                <div class="col col-12 py-1">
                    <a href="/diploma/create" target="_blank">Crear nuevo diploma</a>
                </div>
            </div>
        </v-col>

        <DiplomaPreviewModal
            width="50vh"
            :ref="modalDiplomaPreviewOptions.ref"
            :options="modalDiplomaPreviewOptions"
            @onConfirm="closeFormModal(modalDiplomaPreviewOptions)"
            @onCancel="closeFormModal(modalDiplomaPreviewOptions)"
        />

    </v-row>
</template>

<script>

import DiplomaPreviewModal from '../../layouts/Diplomas/DiplomaPreviewModal.vue';

export default {
    components:{
        DiplomaPreviewModal,
        // DiplomaFormSave,
    },
    props: {
        value: null,
        oldPreview: null,
    }
    ,
    data() {
        return {
            certificateTemplates: [],
            activeTemplate: null,
            modalDiplomaPreviewOptions:{
                ref: 'DiplomaPreviewModal',
                open: false,
                title: "Previsualización",
                confirmLabel: 'Cerrar',
                subTitle:'',
                showCloseIcon: true,
                hideCancelBtn: true,
                resource: {
                    preview: null
                }
            },
        }
    }
    ,
    watch : {
        value (newId, oldId) {
            this.selectTemplate(newId)
        },
        activeTemplate (newId, oldId) {
            if (newId) {
                this.$emit('input', newId.id)
            } else {
                this.$emit('input', null)
            }
        }
    }
    ,
    mounted () {
        this.loadData()
    },
    methods : {

        /**
         * Fetch data from server
         */
        async loadData() {

            try {

                const response = await axios({
                    url: '/diplomas/search?paginate=100',
                    method: 'get'
                })

                const certificates = response.data.data.data
                certificates.forEach(c => {
                    this.certificateTemplates.push({
                        id: c.id,
                        // title: this.generateTitle(c.title, c),
                        title: c.title,
                        image: c.image,
                        d_objects: c.d_objects
                    })
                })

                // When template id has been set,
                // select that template

                if (this.value)
                    this.selectTemplate(this.value)

            } catch (ex) {
                console.log(ex)
            }
        },
        /**
         * Generate diploma title, using its title and data it includes
         */
        generateTitle (title, template) {

            let features = []

            if (this.itHasFeature('course-average-grade', template)) {
                if (!features.includes('Nota')) {
                    features.push('Nota')
                }
            }
            if (this.itHasFeature('fecha', template)) {
                if (!features.includes('Fecha')) {
                    features.push('Fecha')
                }
            }

            if (features.length) {
                return `${title} [${features.join((', '))}]`
            } else {
                return title;
            }
        },
        itHasFeature(objectId, template) {

            if (!template) {
                return false
            }

            if (!template.d_objects) {
                return false
            }

            let itHasFeature = false
            template.d_objects.forEach(object => {

                if (object.id === objectId) {
                    itHasFeature = true;
                }
            })

            return itHasFeature;
        },
        selectTemplate(templateId) {
            this.activeTemplate = this.certificateTemplates.find(i => i.id === templateId)

            this.modalDiplomaPreviewOptions.resource.preview = this.activeTemplate ? this.activeTemplate.image : null;
        }

    }
}

</script>

<style lang="scss" scoped>

</style>
