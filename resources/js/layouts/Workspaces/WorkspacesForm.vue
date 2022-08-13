<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Editar workspace
                <v-spacer />
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-4">
            <v-form ref="workspaceForm">
                <v-row class="justify-content-center pt-4">
                    <v-col cols="4">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            label="Título del workspace"
                            :rules="rules.name"
                        />
                    </v-col>
                    <v-col cols="4">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            label="Link de learning analytics (PowerBI)"
                            :rules="rules.name"
                        />
                    </v-col>
                </v-row>
                <v-row class="justify-content-center">
                    <v-col cols="4">
                        <v-img :src="resource.logo" contain aspect-ratio="2"
                               :lazy-src="`/img/loader.gif`"
                        />
                        <div class="d-flex justify-content-center">
                            <DefaultModalButton
                                label="Seleccionar logotipo"
                                @click="openFormModal(modalDropzoneWorkspace, null, showMessage, `Subir archivo`)"
                                />
                        </div>
                    </v-col>
                    <v-col cols="4" >
                        <v-img :src="resource.logo_negativo"
                               contain aspect-ratio="2"
                               :lazy-src="`/img/loader.gif`"
                        />
                        <div class="d-flex justify-content-center">
                            <DefaultButton label="Seleccionar logotipo negativo"
                                           @click="showMessage"/>
                        </div>
                    </v-col>
                </v-row>
                <v-row class="justify-content-center pb-4 mt-4">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultButton
                            label="Guardar"
                            icon="mdi-content-save"
                            @click="showMessage" />
                    </v-col>
                </v-row>
            </v-form>
        </v-card>
    </section>
</template>

<script>

import DropzoneWorkspace from "./WorkspaceDropzone";

export default {
    components: {
        DropzoneWorkspace
    }
    ,
    data() {

        return {
            resourceDefault: {
                name: '',
                logo: '',
                logo_negativo: ''
            }
            ,
            resource: { }
            ,
            rules: {
                name: this.getRules(['required', 'max:255']),
            }
        }
    }
    ,
    mounted() {

        this.loadData();
    }
    ,
    methods: {
        showMessage () {
            window.alert('it workssss');
        }
        ,
        modalDropzoneWorkspace: {
            ref: 'DropzoneWorkspace',
            open: false,
            confirmLabel: 'Subir',
            base_endpoint: '/multimedia',
            cancelLabel: 'Cerrar',
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
        }
    }
}
</script>
