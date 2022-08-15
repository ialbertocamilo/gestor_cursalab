<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>

            <DefaultErrors :errors="errors"/>

            <v-form ref="workspaceForm">
                <v-row class="justify-content-center pt-4">
                    <v-col cols="6">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            label="Título del workspace"
                            :rules="rules.name"
                        />
                    </v-col>
                    <v-col cols="6">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            label="Link de learning analytics (PowerBI)"
                            :rules="rules.name"
                        />
                    </v-col>
                </v-row>
                <v-row class="justify-content-center">
                    <v-col cols="6">
                        <v-img :src="resource.logo" contain aspect-ratio="2"
                               :lazy-src="`/img/loader.gif`"
                        />
                        <div class="d-flex justify-content-center">
                            <DefaultButton
                                label="Seleccionar logotipo"
                                @click="openFormModal(workspaceDropzoneModalOptions, rowData = null, action = null, title = 'Seleccionar logotipo')"
                                />
                        </div>
                    </v-col>
                    <v-col cols="6" >
                        <v-img :src="resource.logo_negativo"
                               contain aspect-ratio="2"
                               :lazy-src="`/img/loader.gif`"
                        />
                        <div class="d-flex justify-content-center">
                            <DefaultButton
                                label="Seleccionar logotipo negativo"

                                />
                        </div>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col>
                        <v-subheader class="mt-4 px-0">
                            <strong>Criterios</strong>
                        </v-subheader>

                        <v-alert
                            border="top"
                            colored-border
                            type="info"
                            elevation="2"
                        >
                           Aquí van las indicaciones sobre los criterios.
                        </v-alert>

                        <v-divider class="mt-0"/>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="6">
                        <v-subheader class="mb-3 px-0">
                            <strong>Obligatorios</strong>
                        </v-subheader>
                        <v-checkbox

                            :label="'ÁREA (requerido)'"
                        ></v-checkbox>
                        <v-checkbox

                            :label="'SEDE (requerido)'"
                        ></v-checkbox>
                        <v-checkbox

                            :label="'PUESTO (requerido)'"
                        ></v-checkbox>
                        <v-checkbox

                            :label="'GÉNERO (requerido)'"
                        ></v-checkbox>
                        <v-checkbox

                            :label="'GRUPO (requerido)'"
                        ></v-checkbox>
                        <v-checkbox

                            :label="'ORGANIZACIÓN (requerido)'"
                        ></v-checkbox>
                        <v-checkbox

                            :label="'UNIDAD DE NEGOCIO (requerido)'"
                        ></v-checkbox>
                        <v-checkbox

                            :label="'TIPO DE BONO (requerido)'"
                        ></v-checkbox>
                    </v-col>
                    <v-col cols="6">

                        <v-subheader class="mb-3 px-0">
                            <strong>Personalizados</strong>
                        </v-subheader>

                        <v-checkbox
                            :label="'CARRERA (opcional)'"
                        ></v-checkbox>
                        <v-checkbox
                            :label="'CICLO (opcional)'"
                        ></v-checkbox>
                        <v-checkbox
                            :label="'BOTICA (opcional)'"
                        ></v-checkbox>
                        <v-checkbox
                            :label="'GRUPO (opcional)'"
                        ></v-checkbox>
                        <v-checkbox
                            :label="'CLASIFICACIÓN EDV (opcional)'"
                        ></v-checkbox>
                        <v-checkbox
                            :label="'DEPARTAMENTO NIVEL 5 (opcional)'"
                        ></v-checkbox>
                        <v-checkbox
                            :label="'DEPARTAMENTO NIVEL 6 (opcional)'"
                        ></v-checkbox>
                    </v-col>
                </v-row>
            </v-form>


        <!--
            Modals
        ======================================== -->

        <WorkspaceDropzone
            :options="workspaceDropzoneModalOptions"
            width="50vw"
            :ref="workspaceDropzoneModalOptions.ref"
            @onCancel="closeSimpleModal(workspaceDropzoneModalOptions)"
            @onConfirm=""
        />
        </template>
    </DefaultDialog>
</template>

<script>

import WorkspaceDropzone from "./WorkspaceDropzone";

export default {
    components: {
        WorkspaceDropzone
    }
    ,
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data: () => ({
        errors: [],
        resourceDefault: {
            name: '',
            logo: '',
            logo_negativo: ''
        }
        ,
        resource: {}
        ,
        rules: {
            //name: this.getRules(['required', 'max:255']),
        }
        ,
        workspaceDropzoneModalOptions: {
            ref: 'WorkspaceDropzone',
            open: false,
            confirmLabel: 'Guardar'
        }
    })
    ,
    mounted() {

        this.loadData();
    }
    ,
    methods: {
        resetValidation() {
            let vue = this
            //vue.resetFormValidation('workspaceForm')
        }
        ,
        closeModal() {
            let vue = this;
            vue.$emit('onCancel')
        }
        ,
        confirmModal() {

        }
        ,
        /**
         * Load data from server
         */
        loadData () {

            let vue = this;

            /*

            let url = `/multimedia/search?` +
                `page=${page || vue.pagination.actual_page}` +
                `&paginate=${vue.pagination.rows_per_page}`

            if (vue.sortParams.sortBy) // Add param to sort result
                url += `&sortBy=${vue.sortParams.sortBy}`

            if (vue.sortParams.sortDesc) // Add param to sort orientation
                url += `&sortDesc=${vue.sortParams.sortDesc}`

            const filters = vue.addParamsToURL("", vue.filters)
            // console.log('FILTROS :: ', filters)

            url = url + filters
            this.$http.get(url)
                .then(({data}) => {
                    console.log(data.medias)
                    vue.data = data.medias.data
                    // console.log(vue.data)
                    if (vue.pagination.actual_page > data.medias.total_pages)
                        vue.pagination.actual_page = data.medias.total_pages

                    vue.pagination.total_pages = data.medias.last_page;
                    vue.pagination.fromRow = data.medias.from || 0;
                    vue.pagination.toRow = data.medias.to || 0;
                    vue.pagination.total_rows = data.medias.total;
                    vue.loading = false
                })
        */
        },
        loadSelects() {
        },
    }
}
</script>
