<template>
    <section class="section-list">
        <DefaultFilter
            v-model="open_advanced_filter"
            @filter="advanced_filter(dataTable, filters, 1)"
            @cleanFilters="clearObject(filters)"
            :disabled-confirm-btn="isValuesObjectEmpty(filters)"
        >
            <template v-slot:content>
                <v-row justify="center">
                    <v-col cols="12">
                        <DefaultSelect
                            clearable dense
                            label="Tipo"
                            v-model="filters.types"
                            :items="selects.types"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultSelect
                            clearable dense multiple
                            label="Estado"
                            :count-show-values="2"
                            v-model="filters.status"
                            :items="selects.status"
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
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por título o descripción"
                            append-icon="mdi-magnify"
                        />
                    </v-col>
                </v-row>
            </template>
            <template v-slot:consideraciones>
                <v-col cols="12">
                    <DefaultFormLabel
                        label="Consideraciones:"
                        style="font-size: 1rem"
                    />
                    <ul>
                        <li v-for="text in filter_text"
                            v-text="text"
                            class="filter-text-consideraciones"
                        />
                    </ul>
                </v-col>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Aulas Virtuales
                <v-spacer/>
                <!-- <DefaultButton
                    append-icon="mdi-download"
                    outlined
                    :label="'Exportar'"
                    @click="activity()"
                /> -->
                <DefaultModalButton
                    :label="'Evento en vivo'"
                    @click="openCRUDPage('/aulas_virtuales/create')"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            label="Tipo"
                            v-model="filters.types"
                            :items="selects.types"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense multiple
                            label="Estado"
                            v-model="filters.status"
                            :items="selects.status"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por título o descripción"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
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
                <v-row class="justify-content-start">
                    <v-col cols="12">
                        <DefaultTable
                            :ref="dataTable.ref"
                            :data-table="dataTable"
                            :filters="filters"
                            @details="openFormModal(modalDetailOptions, $event, 'details', `Detalle de evento: ${$event.titulo}`)"
                            @finish="openFormModal(modalFinishOptions, $event, 'finalizar', `Finalizar evento: ${$event.titulo}`)"
                        />
                        <AulaVirtualDetailModal
                            :options="modalDetailOptions"
                            width="60vw"
                            :ref="modalDetailOptions.ref"
                            @onCancel="closeSimpleModal(modalDetailOptions)"
                            @onConfirm="closeSimpleModal(modalDetailOptions)"
                        />
                        <AulaVirtualFinishModal
                            :options="modalFinishOptions"
                            width="60vw"
                            :ref="modalFinishOptions.ref"
                            @onCancel="closeSimpleModal(modalFinishOptions)"
                            @onConfirm="closeFormModal(modalFinishOptions, dataTable, filters)"
                        />
                        <AulaVirtualEventoEnVivoFormModal
                            :options="modalEventoEnVivoFormOptions"
                            width="60vw"
                            :ref="modalEventoEnVivoFormOptions.ref"
                            @onCancel="closeSimpleModal(modalEventoEnVivoFormOptions)"
                            @onConfirm="closeSimpleModal(modalEventoEnVivoFormOptions)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
        <!-- <Fab/> -->
    </section>
</template>

<script>
import AulaVirtualFinishModal from "./AulaVirtualFinishModal";
import AulaVirtualDetailModal from "./AulaVirtualDetailModal";
import AulaVirtualEventoEnVivoFormModal from "./AulaVirtualEventoEnVivoFormModal";
const filter_text = [
    'Puedes ingresar a un evento en transcurso como espectador en "Detalles"',
    "Usa los filtros o el buscador para encontrar los eventos creados."
]
export default {
    components: {AulaVirtualDetailModal, AulaVirtualEventoEnVivoFormModal, AulaVirtualFinishModal},
    props: ['usuario_id'],
    data: () => ({
        filter_text: filter_text,
        dataTable: {
            endpoint: '/aulas_virtuales/search',
            ref: 'AulaVirtualTable',
            headers: [
                {text: "ID", value: "id", align: 'center', sortable: false},
                {text: "Tipo", value: "type", sortable: false},
                {text: "Titulo", value: "titulo"},
                {text: "Fecha de inicio", value: "custom_aulas_virtuales_fecha"},
                {text: "Estado", value: "estado", sortable: false},
                {text: "Opciones", value: "actions", align: 'center', sortable: false},
            ],
            actions: [
                {
                    text: "Detalles",
                    icon: 'mdi mdi-eye',
                    type: 'action',
                    method_name: 'details'
                },
                {
                    text: "Finalizar",
                    icon: 'fas fa-check',
                    type: 'action',
                    method_name: 'finish',
                    show_condition: 'en_transcurso'
                },
            ],
        },
        filters: {
            types: [],
            status: [],
            fecha: [],
            q: null
        },
        selects: {
            types: [],
            status: []
        },
        modalDateOptions: {
            ref: 'DateRangeFilter',
            open: false,
        },
        modalDetailOptions: {
            ref: 'AulaVirtualDetailModal',
            open: false,
            base_endpoint: '/aulas_virtuales',
            confirmLabel: 'Cerrar',
            hideCancelBtn: true
        },
        modalEventoEnVivoFormOptions: {
            ref: 'AulaVirtualEventoEnVivoFormlModal',
            open: false,
            base_endpoint: '/aulas_virtuales',
            confirmLabel: 'Guardar',
            usuario_id: null
        },
        modalDateFilter1: {
            open: false,
        },
        modalFinishOptions: {
            ref: 'AulaVirtualFinishModal',
            open: false,
            base_endpoint: '/aulas_virtuales',
            cancelLabel: 'Cerrar',
            // hideConfirmBtn: true,
        },
    }),
    mounted() {
        let vue = this
        vue.getSelects();
        if (vue.usuario_id)
            vue.modalEventoEnVivoFormOptions.usuario_id = vue.usuario_id
    },
    methods: {
        getSelects() {
            let vue = this
            const url = `/aulas_virtuales/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.types = data.data.types
                    vue.selects.status = data.data.status
                })
        },
        async activity() {
            console.log('entra');
			let vue = this;
			await axios.post("/descargarEventosEnVivo", {responseType: 'blob',
					params: {
						tipo: vue.filters.types,
						master: vue.master,
						filtro: vue.filters.status,
					}
				})
				.then((res) => {
					const downloadUrl = window.URL.createObjectURL(new Blob([res.data]));
			        const link = document.createElement('a');
			        link.href = downloadUrl;
			        link.setAttribute('download', 'Aulas_Virtuales.xlsx'); //any other extension
			        document.body.appendChild(link);
			        link.click();
			        link.remove();
				})
				.catch((err) => {
					console.log(err);
				});
        },
    }
}
</script>
