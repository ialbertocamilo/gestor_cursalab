<template>
    <section class="section-list">
        <DefaultFilter
            v-model="open_advanced_filter"
            @filter="advanced_filter(dataTable, filters, 1)"
            @cleanFilters="clearObject(filters,dataTable)"
            :disabled-confirm-btn="isValuesObjectEmpty(filters)"
        >
            <template v-slot:content>
                <v-row justify="center">
                    <v-col cols="12">
                        <DefaultSelect
                            dense
                            multiple
                            clearable
                            itemText="name"
                            itemValue="path"
                            label="Sección"
                            :multiple="false"
                            v-model="filters.model_type"
                            :items="selects.model_type"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultSelect
                            dense
                            multiple
                            label="Eventos"
                            item-text="name"
                            :show-select-all="false"
                            v-model="filters.events"
                            :items="selects.events"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultInputDate
                            referenceComponent="abc"
                            :options="modalDateOptions"
                            v-model="filters.date_range"
                            dense
                            range
                        />
                    </v-col>
                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Auditoría
                <v-spacer />
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="d-flex justify-content-between">
                    <v-col cols="3" md="4" lg="4">
                        <DefaultInput
                            clearable
                            dense
                            v-model="filters.us_search"
                            label="Buscar por nombre de usuario..."
                            append-icon="mdi-magnify"
                            @onEnter="
                                refreshDefaultTable(dataTable, filters, 1)
                            "
                            @clickAppendIcon="
                                refreshDefaultTable(dataTable, filters, 1)
                            "
                        />
                    </v-col>

                    <v-col
                        cols="2"
                        md="2"
                        lg="2"
                        class="d-flex justify-content-end"
                    >
                        <DefaultButton
                            label="Ver Filtros"
                            icon="mdi-filter"
                            @click="
                                open_advanced_filter = !open_advanced_filter
                            "
                        />
                    </v-col>
                </v-row>
            </v-card-text>
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @showdetails="
                    openFormModal(
                        detailsModalOptions,
                        $event,
                        'status',
                        'Detalles'
                    )
                "
            />

            <AuditoriaDetailsModal
                width="50vw"
                :ref="detailsModalOptions.ref"
                :options="detailsModalOptions"
                @onConfirm="
                    closeFormModal(detailsModalOptions, dataTable, filters)
                "
                @onCancel="closeFormModal(detailsModalOptions)"
            />
        </v-card>
    </section>
</template>
<script>
import DatePicker from "vue2-datepicker";
import "vue2-datepicker/index.css";
import AuditoriaDetailsModal from "./AuditoriaDetailsModal";

const FileSaver = require("file-saver");
export default {
    components: { AuditoriaDetailsModal, DatePicker },
    data() {
        return {
            modalDateOptions: {},
            modalDateOptions2: {
                ref: "DateRangeFilter2",
                open: false
            },
            dataTable: {
                ref: 'AuditoriaTable',
                endpoint: '/auditoria/search',
                headers: [
                    {
                        text: "Usuario",
                        value: "user",
                        align: "start",
                        sortable: false
                    },
                    {
                        text: "Acción",
                        value: "action",
                        align: "center",
                        sortable: false
                    },
                    {
                        text: "Sección",
                        value: "models",
                        align: "center",
                        sortable: false
                    },
                    {
                        text: "Registro",
                        value: "name",
                        align: "center",
                        sortable: false
                    },
                    {
                        text: "# Modificados",
                        align: "center",
                        value: "modified_fields_count",
                        sortable: false
                    },
                    {
                        text: "Fecha",
                        value: "created_at",
                        align: "center",
                        sortable: true
                    },
                    {
                        text: "Acciones",
                        value: "actions",
                        align: "center",
                        sortable: false
                    }
                ],
                actions: [
                    {
                        text: "Detalles",
                        icon: "mdi mdi-file-document-multiple",
                        type: "action",
                        method_name: "showdetails"

                        // route: 'show',
                        // route_type: 'external',
                        // show_condition: 'show'
                        // method_name: 'seeData',
                    }
                ]
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
                    { id: "User", name: "Gestor de contenidos" },
                    { id: "Usuario_rest", name: "App" }
                ],
                events: [
                    { id: "created", name: "Creación" },
                    { id: "updated", name: "Actualización" },
                    { id: "deleted", name: "Eliminación" },
                    { id: "downloaded", name: "Descarga" },
                    { id: "impersonated", name: "Personificación" },
                    { id: "upload", name: "Subida" },
                ],
                model_type: []
            },
            filters: {
                events: [],
                model_type: [],
                search_in: null,
                from: null,
                to: null,
                us_search: "",
                date_range: []
            },
            detailsModalOptions: {
                ref: "AuditoriaDetailsModal",
                open: false,
                resource: "Audit",
                showCloseIcon: true
            }
        };
    },
    mounted() {
        let vue = this;
        vue.getSelects();
    },
    methods: {

        getSelects() {
            let vue = this;
            let url = `/auditoria/selects`;
            vue.filters.model_type = [];

            axios.get(url).then(({ data }) => {
                vue.selects.model_type = data.data.models;
            });
        },

        filter() {
            let vue = this;
            vue.page = 1;
            vue.getData();
        },

        getData() {
            let vue = this;
            vue.loading = true;
            let url = `/auditoria/search?page=${vue.page}`;
            let filters = vue.addParamsToURL(url, vue.filters);
            url = `${url}${filters}`;
            axios
                .get(url)
                .then(({ data }) => {
                    vue.auditoria = data.data.data;
                    vue.total_paginas = data.data.last_page;
                    setTimeout(() => {
                        vue.loading = false;
                    }, 800);
                })
                .catch(err => {
                    vue.loading = false;
                });
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
            let url = `/log/export?log=0`;
            let filters = vue.addParamsToURL(url, vue.filters);
            url = `${url}${filters}`;
            window.open(url);
        },
        filtrar() {
            let vue = this;
            vue.open_advanced_filter = false;
            vue.getData();
        }
    }
};
</script>
