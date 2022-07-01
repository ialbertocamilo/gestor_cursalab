<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Errores
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3">
                        <DefaultSelect clearable dense
                                       :items="selects.platforms"
                                       v-model="filters.platform"
                                       label="Plataforma"
                                       @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultSelect clearable dense
                                       :items="selects.statuses"
                                       v-model="filters.status"
                                       label="Estado"
                                       @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput clearable dense
                                      v-model="filters.q"
                                      label="Buscar por ID, mensaje o archivo..."
                                      @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable :ref="dataTable.ref"
                          :data-table="dataTable"
                          :filters="filters"
                          @edit="openFormModal(modalOptions, $event, 'edit', `Detalle de Error # ${$event.id}`)"
            />

            <ErrorFormModal width="60vw"
                              :ref="modalOptions.ref"
                              :options="modalOptions"
                              @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                              @onCancel="closeFormModal(modalOptions)"
            />

        </v-card>
    </section>
</template>


<script>
import ErrorFormModal from "./ErrorFormModal";

export default {
    components: {ErrorFormModal},
    data() {

        let uri = window.location.search.substring(1); 
        let params = new URLSearchParams(uri);
        let q = params.get("q");

        return {
            dataTable: {
                endpoint: '/errores/search',
                ref: 'ErrorTable',
                headers: [
                    {text: "ID", value: "id", align: 'center', sortable: true},
                    {text: "Usuario", value: "user", align: 'center', sortable: false},
                    {text: "Error encontrado", value: "custom_error", sortable: false},
                    {text: "Estado", value: "status", align: 'center', sortable: false},
                    {text: "Fecha", value: "created_at", align: 'center', sortable: true},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Detalle",
                        icon: 'mdi mdi-eye',
                        type: 'action',
                        method_name: 'edit'
                    },
                ],
                more_actions: [
                   
                ]
            },
            selects: {
                platforms: [],
                statuses: []
            },
            filters: {
                q: q,
                platform: null,
                status: null
            },
            modalOptions: {
                ref: 'ErrorFormModal',
                open: false,
                base_endpoint: '/errores',
                resource: 'Error',
                cancelLabel: 'Cerrar',
                confirmLabel: 'Actualizar',
            },

        }
    },
    mounted() {
        let vue = this
        vue.getSelects();
    },
    methods: {
        getSelects() {
            let vue = this
            const url = `/errores/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.platforms = data.data.platforms
                    vue.selects.statuses = data.data.statuses
                })
        },

        activity() {
            console.log('activity')
        },
        confirmModal() {
            // TODO: Call store or update USER
        },
    }

}
</script>
