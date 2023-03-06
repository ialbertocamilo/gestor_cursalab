<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Auditoría
                <v-spacer/>
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
<!--                    <v-col cols="12" md="4" lg="4">-->
<!--                        <DefaultSelect-->
<!--                            clearable dense-->
<!--                                        item-text="name"-->
<!--                                       :items="selects.search_in"-->
<!--                                       v-model="filters.search_in"-->
<!--                                       label="Buscar en"-->
<!--                                       @onChange="getSelects"-->
<!--                        />-->
<!--                    </v-col>-->
                    <v-col cols="12" md="4" lg="4">

                        <DefaultSelect
                            dense multiple clearable
                            itemText="name"
                            itemValue="path"
                            label="Sección"
                            :multiple="false"
                            v-model="filters.models"
                            :items="selects.models"
                        />
                    </v-col>
                    <v-col cols="12" md="4" lg="4">

                        <DefaultSelect
                            dense multiple
                                  label="Eventos"
                                  item-text="name"
                                  :show-select-all="false"
                                  v-model="filters.events"
                                  :items="selects.events"
                        />
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="12" md="4" lg="4">
          <!--               <v-text-field attach dense outlined
                                      hide-details="auto"
                                      label="Nombre de usuario"
                                      append-icon="mdi-magnify"
                                      v-model="filters.us_search"
                                      @keypress.enter="filter"
                                      :disabled="!filters.search_in"
                                      clearable
                        >
                        </v-text-field> -->

                        <DefaultInput
                            clearable dense
                            v-model="filters.us_search"
                            label="Buscar por nombre de usuario..."
                            append-icon="mdi-magnify"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="12" md="4" lg="4">
                        <DefaultInputDate
                                        referenceComponent="abc"
                                         :options="modalDateOptions"
                                          v-model="filters.date_range"
                                          dense
                                          range
                        />
                    </v-col>

                    <v-col cols="12" md="4" lg="4" class="d-flex justify-space-between">
                        <v-btn color="primary" class="white--text float-right" elevation="0" @click="refreshDefaultTable(dataTable, filters, 1)">
                            Buscar
                        </v-btn>
                    </v-col>

                </v-row>
            </v-card-text>
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                @showdetails="openFormModal(detailsModalOptions, $event, 'status', 'Detalles')"
                />

            <AuditoriaDetailsModal
                width="50vw"
                :ref="detailsModalOptions.ref"
                :options="detailsModalOptions"
                @onConfirm="closeFormModal(detailsModalOptions, dataTable, filters)"
                @onCancel="closeFormModal(detailsModalOptions)"
            />
        </v-card>

    <!--     <v-container>
            <v-card>
            </v-card>
        </v-container> -->

    </section>
</template>
<script>
import DatePicker from "vue2-datepicker";
import "vue2-datepicker/index.css";
import AuditoriaDetailsModal from "./AuditoriaDetailsModal";

const FileSaver = require("file-saver");
export default {
    components: {AuditoriaDetailsModal, DatePicker},
    data() {
        return {
            modalDateOptions: {

            },
            modalDateOptions2: {
                ref: 'DateRangeFilter2',
                open: false,
            },
            dataTable: {
                ref: 'AuditoriaTable',
                endpoint: '/auditoria/search',
                headers: [
                    {text: "Fecha", value: "created_at", align: 'center', sortable: true},
                    {text: "Usuario", value: "user", align: 'center', sortable: false},
                    {text: "Acción", value: "event", align: 'center', sortable: false},
                    // {text: "Sección", value: "model", align: 'center', sortable: false},
                    // {text: "Registro", value: "name", align: 'center', sortable: false},
                    {text: '# Modificados', align: 'center', value: 'modified_fields_count', sortable: false},
                    {text: "Detalle", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: 'Ver',
                        icon: 'mdi mdi-file-document-multiple',
                        type: 'action',
                        method_name: 'showdetails',

                        // route: 'show',
                        // route_type: 'external',
                        // show_condition: 'show'
                        // method_name: 'seeData',
                    },
                ],
            },
            audits: [],
            loading: true,
            date_range_modal: false,
            auditoria: [],
            activeAudit: null,
            page: 1,
            pageCount: 0,
            itemsPerPage: 20,
            total_paginas: 1,
            selects: {
                search_in: [
                    {id: 'User', name: "Gestor de contenidos"},
                    {id: 'Usuario_rest', name: "App"},
                ],
                events: [
                    {id: 'created', name: 'Se creó'},
                    {id: 'updated', name: 'Se actualizó'},
                    {id: 'deleted', name: 'Se eliminó'},
                ],
                models: [],
            },
            filters: {
                events: [],
                models: [],
                search_in: null,
                from: null,
                to: null,
                us_search: "",
                date_range: [],
            },
            detailsModalOptions: {
                ref: 'AuditoriaDetailsModal',
                open: false,
                resource: 'Audit',
            },
        };
    },
    mounted() {
        this.getSelects();
    },
    methods: {
        getSelects() {
            let vue = this
            let url = `/auditoria/selects`
            vue.filters.models = []

            axios.get(url)
                .then(({data}) => {

                    vue.selects.models = data.data.models
                })
        },
        filter() {
            let vue = this;
            vue.page = 1
            vue.getData()
        },
        getData() {
            let vue = this;
            vue.loading = true
            let url = `/auditoria/search?page=${vue.page}`
            let filters = vue.addParamsToURL(url, vue.filters)
            url = `${url}${filters}`
            axios.get(url)
                .then(({data}) => {
                    vue.auditoria = data.data.data;
                    vue.total_paginas = data.data.last_page;
                    setTimeout(() => {
                        vue.loading = false
                    }, 800)
                })
                .catch(err => {
                    vue.loading = false
                })

        },
        // seeData(row) {
        //     let vue = this
        //     vue.detailsModalOptions.activeAudit = row;
        //     vue.openFormModal(
        //         vue.detailsModalOptions, row, 'status', 'Detalles'
        //     )
        //     //console.log(row)
        //     //vue.consoleObjectTable(row, 'ROW DATA')
        // },
        getExcel() {
            let vue = this;
            let url = `/log/export?log=0`
            let filters = vue.addParamsToURL(url, vue.filters)
            url = `${url}${filters}`
            window.open(url);
        },
    },
};
</script>
