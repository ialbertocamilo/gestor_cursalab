<template>
    <section class="section-list">
        <DefaultFilter v-model="open_advanced_filter"
                       @filter="advanced_filter(dataTable, filters, 1)"
        >
            <template v-slot:content>
                <v-row justify="center">
                    <!--                    <v-col cols="12">-->
                    <!--                        <DefaultAutocomplete-->
                    <!--                            clearable-->
                    <!--                            placeholder="Seleccione una Carrera"-->
                    <!--                            label="Carrera"-->
                    <!--                            :items="selects.carreras"-->
                    <!--                            v-model="filters.carrera"-->
                    <!--                        />-->
                    <!--                    </v-col>-->
                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Entrenadores
                <v-spacer/>
                <DefaultActivityButton :label="'Actividad'"/>
                <DefaultModalButton :label="'Asignar alumnos'"/>
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre o documento..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="4">
                        <DefaultButton label="Filtros"
                                       icon="mdi-filter"
                                       @click="open_advanced_filter = !open_advanced_filter"/>
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @alumnos="openFormModal(modalOptions, $event, 'ver_alumnos', 'Alumnos')"
            />
            <EntrenadorVerAlumnosModal
                :ref="modalOptions.ref"
                :options="modalOptions"
                width="60vw"
            />
        </v-card>
    </section>
</template>

<script>
import EntrenadorVerAlumnosModal from "./EntrenadorVerAlumnosModal";

export default {
    components: {EntrenadorVerAlumnosModal},
    data() {
        return {
            dataTable: {
                endpoint: '/entrenadores/search',
                ref: 'EntrenadorTable',
                headers: [
                    {text: "Doc. Identidad", value: "dni", align: 'start', sortable: false},
                    {text: "Entrenador", value: "name", align: 'start'},
                    {text: "Cantidad de Alumnos", value: "count_active_students", align: 'center'},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Detalle",
                        icon: 'mdi mdi-eye',
                        type: 'action',
                        method_name: 'alumnos'
                    },
                ],
            },
            modalOptions: {
                ref: 'EntrenadorVerAlumnosModal',
                open: false,
                base_endpoint: '/entrenadores',
                confirmLabel: 'Guardar',
            },
            filters: {
                q: null
            }
        }
    }

};
</script>
