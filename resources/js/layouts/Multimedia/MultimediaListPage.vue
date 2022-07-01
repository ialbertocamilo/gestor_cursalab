<template>
    <section class="section-list">
        <DefaultFilter
            v-model="open_advanced_filter"
            @filter="filtrar"
            @cleanFilters="clearObject(filters)"
            :disabled-confirm-btn="isValuesObjectEmpty(filters)"
        >
            <template v-slot:content>
                <v-row justify="center">
                    <v-col cols="12">
                        <DefaultSelect
                            multiple
                            clearable
                            dense
                            v-model="filters.tipo"
                            :items="selects.tipo"
                            label="Tipo"
                            :count-show-values="2"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultInputDate
                            clearable
                            dense
                            range
                            :referenceComponent="'modalDateFilter1'"
                            :options="modalDateFilter1"
                            v-model="filters.fecha"
                            label="Fecha"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultInput
                            clearable
                            dense
                            v-model="filters.q"
                            label="Buscar por título"
                            append-icon="mdi-magnify"
                        />
                    </v-col>
                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Multimedia
                <v-spacer/>
                <DefaultModalButton
                    :label="'Subir multimedia'"
                    @click="openFormModal(modalUpdateMultimedia, null, 'updateMultimedia', `Subir archivos`)"
                />
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3">
                        <DefaultSelect
                            @click.stop
                            multiple
                            clearable
                            dense
                            v-model="filters.tipo"
                            :items="selects.tipo"
                            label="Tipo"
                            @onChange="getData"
                            :count-show-values="2"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInputDate
                            clearable
                            dense
                            range
                            :referenceComponent="'modalDateFilter2'"
                            :options="modalDateFilter2"
                            v-model="filters.fecha"
                            label="Fecha"
                            @onChange="getData"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput
                            clearable
                            dense
                            v-model="filters.q"
                            label="Buscar por título"
                            @onEnter="getData"
                            @clickAppendIcon="getData"
                            append-icon="mdi-magnify"
                        />
                    </v-col>
                    <v-col cols="3" class="d-flex justify-content-end">
                        <DefaultButton
                            label="Ver Filtros"
                            icon="mdi-filter"
                            @click="open_advanced_filter = !open_advanced_filter"/>
                    </v-col>
                </v-row>
                <v-row justify="space-between" class="mx-1" style="background-color: #F9FAFB; border-radius: 6px">
                    <v-col cols="4">
                        <v-btn
                            elevation="0"
                            small
                            color="primary"
                            :fab="view === 'grid'"
                            :icon="view === 'list'"
                            @click="view = 'grid'">
                            <v-icon v-text="'mdi-grid' "/>
                        </v-btn>
                        <v-btn
                            elevation="0"
                            small
                            color="primary"
                            :fab="view === 'list'"
                            :icon="view === 'grid'"
                            @click="view = 'list'">
                            <v-icon v-text="'mdi-format-list-bulleted'"/>
                        </v-btn>
                    </v-col>
                    <v-col cols="2">
                        <DefaultSelect
                            label="Ordernar por"
                            dense
                            v-model="filters.order_by"
                            :items="selects.order_by"
                            @onChange="getData"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
            <transition name="fade" mode="out-in">
                <component
                    :is="view"
                    :data="data"
                    :loading="loading"
                    @detalles="openFormModal(modalOptions, $event, 'detalles', 'Detalle de multimedia')"
                    @download="download"
                />
            </transition>
            <section>
                <v-row class="justify-content-end" no-gutters>
                    <v-col cols="1" class="d-flex align-items-end justify-content-around">
                        <small
                            v-text="`${pagination.fromRow} - ${pagination.toRow} de ${pagination.total_rows}`"/>
                    </v-col>
                    <v-col cols="1" class="d-flex align-items-center justify-content-around">
                        <v-icon :disabled="pagination.actual_page === 1" v-text="'mdi-chevron-left'"
                                @click="changePage(false)"/>
                        <v-icon :disabled="pagination.actual_page === pagination.total_pages"
                                v-text="'mdi-chevron-right'"
                                @click="changePage(true)"/>
                    </v-col>
                </v-row>
            </section>
        </v-card>
        <MultimediaDetailModal
            width="60vw"
            :ref="modalOptions.ref"
            :options="modalOptions"
            @onCancel="closeFormModal(modalOptions); getData()"
        />
        <MultimediaUpdateModal
            width="60vw"
            :ref="modalUpdateMultimedia.ref"
            :options="modalUpdateMultimedia"
            @onCancel="closeFormModal(modalUpdateMultimedia)"
            @onConfirm="getData"
        />
    </section>
</template>


<script>
import MultimediaGrid from "./MultimediaGrid";
import MultimediaListView from "./MultimediaListView";
import MultimediaDetailModal from "./MultimediaDetailModal";
import MultimediaUpdateModal from "./MultimediaUpdateModal";

export default {
    components: {
        MultimediaUpdateModal,
        MultimediaDetailModal,
        'grid': {
            props: ['data', 'loading'],
            components: {MultimediaGrid},
            template: `
                <MultimediaGrid
                    ref="MultimediaGrid" :data='data' :loading='loading'
                    @detalles='detalles'
                    @download='download'
                />`,
            methods: {
                detalles(rowData) {
                    let vue = this
                    vue.$emit("detalles", rowData)
                },
                download(rowData) {
                    let vue = this
                    vue.$emit("download", rowData)
                }
            },
        },
        'list': {
            props: ['data', 'loading'],
            components: {MultimediaListView},
            template: `
                <MultimediaListView
                    :rows-per-page="12" ref="MultimediaTable" :data-table="dataTable"
                    :data='data' :loading='loading'
                    @detalles='detalles'
                    @download='download'
                />`,
            methods: {
                detalles(rowData) {
                    let vue = this
                    vue.$emit("detalles", rowData)
                },
                download(rowData) {
                    let vue = this
                    vue.$emit("download", rowData)
                }
            },
            data() {
                return {
                    dataTable: {
                        headers: [
                            {text: "Preview", value: "image", align: 'center', sortable: false},
                            {text: "Título", value: "title", sortable: false},
                            {text: "Tipo", value: "tipo", sortable: false},
                            {text: "Fecha de subida", value: "created_at", sortable: false},
                            {text: "Peso", value: "size", sortable: false},
                            {text: "Opciones", value: "actions", align: 'center', sortable: false},
                        ],
                        actions: [
                            {
                                text: "Descargar",
                                icon: 'mdi mdi-download',
                                type: 'action',
                                method_name: 'download'
                            },
                            {
                                text: "Detalles",
                                icon: 'mdi mdi-clipboard-text',
                                type: 'action',
                                method_name: 'detalles'
                            },
                        ],
                    },
                }
            }
        }
    },
    data() {
        return {
            view: 'grid',
            loading: true,
            data: [],
            pagination: {
                total_pages: 1,
                actual_page: 1,
                rows_per_page: 12,
                fromRow: 1,
                toRow: 1,
                total_rows: 0,
            },
            sortParams: {
                sortBy: null,
                sortDesc: false
            },
            modalOptions: {
                ref: 'MultimediaDetailModal',
                open: false,
                base_endpoint: '/multimedia',
                cancelLabel: 'Eliminar',
                confirmLabel: 'Cerrar',
                resource: 'Multimedia',
            },
            modalUpdateMultimedia: {
                ref: 'MultimediaUpdateModal',
                open: false,
                confirmLabel: 'Subir',
                base_endpoint: '/multimedia',
                cancelLabel: 'Cerrar',
            },
            filters: {
                q: null,
                tipo: [],
                fecha: [],
                order_by: null
            },
            selects: {
                tipo: [
                    {nombre: 'Imagen', id: 'image'},
                    {nombre: 'Video', id: 'video'},
                    {nombre: 'Audio', id: 'audio'},
                    {nombre: 'PDF', id: 'pdf'},
                    {nombre: 'Scorm', id: 'scorm'},
                ],
                order_by: [
                    {nombre: 'Tamaño', id: 'size'},
                    {nombre: 'Nombre', id: 'title'},
                    {nombre: 'Fecha de creación', id: 'created_at'},
                ]
            },
            modalDateFilter1: {
                open: false,
            },
            modalDateFilter2: {
                open: false,
            },
        }
    },
    mounted() {
        let vue = this
        // vue.getSelects();
        vue.getData();
    },
    methods: {
        getData(page = null) {
            let vue = this
            vue.loading = true
            if (page)
                vue.pagination.actual_page = page

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
        },
        detalles(rowData) {
            console.log('detalles first parent component :: ', rowData)
        },
        download(rowData) {
            console.log('download first parent component :: ', rowData)
        },
        changePage(sum) {
            let vue = this
            if (sum) {
                if (vue.pagination.actual_page < vue.pagination.total_pages)
                    vue.pagination.actual_page++
            } else {
                if (vue.pagination.actual_page > 1)
                    vue.pagination.actual_page--
                else
                    vue.pagination.actual_page = 1
            }
            vue.getData()
        },
        filtrar() {
            let vue = this
            vue.open_advanced_filter = false
            vue.getData()
        }
        // getSelects() {
        //     let vue = this
        //     const url = `/multimedia/get-list-selects`
        //     vue.$http.get(url)
        //         .then(({data}) => {
        //             vue.selects.tipo = data.data.tipo
        //         })
        // },
    }
}
</script>
