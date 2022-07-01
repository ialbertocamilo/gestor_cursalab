<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Incidencias
                <v-spacer/>
                <!-- <DefaultActivityButton :label="'Actividad'" @click="activity"/> -->
                <DefaultModalButton :label="'Detalle'"
                                    @click="infoDialog = true"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
     

            <DefaultTable :ref="dataTable.ref"
                          :data-table="dataTable"
                          :filters="filters"
                          @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar incidencia')"
            />


            <DefaultDeleteModal :options="modalDeleteOptions"
                                :ref="modalDeleteOptions.ref"
                                @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
                                @onCancel="closeFormModal(modalDeleteOptions)"
            />

        </v-card>

        <ResumenExpand :dialog="infoDialog" @onCancel="infoDialog = false" title="Detalle de incidencias">
             Se considera una incidencia cuando:
                <ul class="ml-2 mt-2">
                    <li>Proceso 1: La suma de aprobados, realizados, revisados y desaprobados pasa la cantidad de temas asignados que posee el curso</li>
                    <li>Proceso 2: La suma de aprobados, realizados, revisados, desaprobados es igual a asignados y el estado es diferente de "aprobado", "enc_pend" o "desaprobado"</li>
                    <li>Proceso 3: Hay registros duplicados en la tabla resumen_x_curso</li>
                    <li>Proceso 4: Hay registros duplicados en la tabla resumen_general</li>
                    <li>Proceso 5: Hay registros duplicados en la tabla visitas</li>
                    <li>Proceso 6: Hay registros duplicados en la tabla pruebas</li>
                    <li>Proceso 7: La cantidad de asignados que tiene el usuario es diferente al que aparece en resumen_general</li>
                    <li>Proceso 8: El usuario tiene cursos asignados de otros módulos</li>
                    <li>Proceso 9: Tiene un registro de resumen_x_curso de un curso que no tiene asignado</li>
                    <li>Proceso 10: El usuario tiene 100% pero la cantidad de asignados es diferente al total completado en la tabla resumen_general</li>
                    <li>Proceso 11: El usuario tiene una nota promedio pero no tiene un registro en la tabla resumen_x_curso</li>
                    <li>Proceso 12: Hay usuarios sin registro en la tabla resumen_general</li>
                    <li>Proceso 13: Si la prueba no tiene visitas</li>
                    <li>Proceso 14: La escuela tiene cursos inactivos</li>
                    <li>Proceso 15: Hay cursos activos en escuelas inactivas</li>
                    <li>Proceso 16: El reinicio por tema no tiene curso_id</li>
                    <li>Proceso 17: El reinicio no tiene admin_i</li>
                    <li>Proceso 18: La visita tiene el post_id o curso_id vacio</li>
                    <li>Proceso 19: Hay pruebas(sin reseto) sin intentos y con nota</li>
                    <li>Proceso 20: Hay aprobados con nota desaprobatoria</li>
                    <li>Proceso 21: Hay desaprobados con nota aprobatoria</li>
                    <li>Proceso 22: Hay visitas con sumatoria 0 o null y el campo estado_tema tiene valor</li>
                    <li>Proceso 23: Las visitas de resumen_x_curso no coincide con la tabla visitas </li>
                </ul>
        </ResumenExpand>
        

    </section>
</template>


<script>
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import ResumenExpand from "../Reportes/Filtros/partials/ResumenExpand.vue";

export default {
    components: {DefaultDeleteModal, ResumenExpand},
    data() {
        return {
            infoDialog: false,
            dataTable: {
                endpoint: '/incidencias/search',
                ref: 'IncidenciaTable',
                headers: [
                    {text: "ID", value: "id", align: 'center', sortable: true},
                    {text: "Tablas involucradas", value: "tipo", sortable: false},
                    {text: "Mensaje", value: "mensaje", sortable: false},
                    {text: "# Afectados", align: 'center', value: "total", sortable: false},
                    {text: "Fecha", value: "created_at", align: 'center', sortable: true},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    // {
                    //     text: "Editar",
                    //     icon: 'mdi mdi-pencil',
                    //     type: 'action',
                    //     method_name: 'edit'
                    // },
         
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                ],
                more_actions: [
                    // {
                    //     text: "Actividad",
                    //     icon: 'fas fa-file',
                    //     type: 'action',
                    //     method_name: 'activity'
                    // },
                ]
            },
            selects: {
                modules: []
            },
            filters: {
                q: '',
                module: null
            },
         
            modalDeleteOptions: {
                ref: 'IncidenciaDeleteModal',
                open: false,
                base_endpoint: '/incidencias',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
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
            // const url = `/incidencias/get-list-selects`
            // vue.$http.get(url)
            //     .then(({data}) => {
            //         vue.selects.modules = data.data.modules
            //     })
        },
        // reset(user) {
        //     let vue = this
        //     vue.consoleObjectTable(user, 'User to Reset')
        // },
        activity() {
            console.log('activity')
        },
        confirmModal() {
            // TODO: Call store or update USER
        },
    }

}
</script>
