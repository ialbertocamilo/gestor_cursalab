<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Invitados
            </v-card-title>
        </v-card>

    </section>
</template>

<script>

export default {
    data() {
        return {
            filtersAreShown: false,
            criteria_template: [],
            dateFilterStart: {
                open: false
            },
            dateFilterEnd: {
                open: false
            },
            dataTable: {
                endpoint: '/soporte/search',
                ref: 'SoporteTable',
                headers: [
                    {text: "# Ticket", value: "id", align: 'center', sortable: true},
                    {text: "Módulo", value: "image", align: 'center', sortable: false},
                    {text: "DNI", value: "dni", align: 'center', sortable: false},
                    {text: "Nombre", value: "nombre", sortable: false},
                    {text: "Motivo", value: "reason"},
                    {text: "Estado", value: "status", align: 'center',},
                    {text: "Fecha de registro", value: "created_at", align: 'center', sortable: true},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit'
                    },
                    {
                        text: "Detalle",
                        icon: 'mdi mdi-eye',
                        type: 'action',
                        method_name: 'show'
                    },
                    {
                        text: "Logs",
                        icon: "mdi mdi-database",
                        type: "action",
                        show_condition: "is_super_user",
                        method_name: "logs"
                    }
                ],
                more_actions: [

                ]
            },
            selects: {
                modulos: [],
                estados: [],
            },
            filters: {
                q: '',
                modulo: null,
                status: null,
                starts_at: null,
                ends_at: null,
                reason: null
            },
        };
    },
    mounted() {
        let vue = this
        vue.getSelects();
    },
    methods: {
        getSelects() {
            let vue = this
            const url = `/soporte/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modulos = data.data.modulos
                    vue.selects.estados = data.data.estados
                    vue.selects.reasons = data.data.reasons;
                })
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
        clearFilters(obj, table) {
            this.filters =  {
                q: '',
                modulo: null,
                status: null,
                starts_at: null,
                ends_at: null,
                reason: null
            }

            this.refreshDefaultTable(this.dataTable, this.filters, 1)
        }
    }
}
</script>
