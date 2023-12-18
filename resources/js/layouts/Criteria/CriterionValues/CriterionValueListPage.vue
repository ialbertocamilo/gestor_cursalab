<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/>
<!--                Criterios-->
                <v-spacer/>
                <DefaultModalButton color="default" icon_name="mdi-upload" :label="'Subida de valores de criterio'" @click="openFormModal(modalUploadOptions)" v-if="$root.isSuperUser && criterion_type != 'date' "/>

                <!-- {{ criterion_type }} -->
                
                <DefaultModalButton :label="'Valor de criterio'" @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">

<!--                    <v-col cols="3">-->
<!--                        <DefaultSelect-->
<!--                            clearable dense-->
<!--                            :items="selects.modulos"-->
<!--                            v-model="filters.modulo"-->
<!--                            label="Módulos"-->
<!--                            @onChange="refreshDefaultTable(dataTable, filters, 1)"-->
<!--                        />-->
<!--                    </v-col>-->
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="openFormModal(modalOptions, $event)"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Cambiar de estado al criterio')"
            />

            <CriterioFormModal
                width="40vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalOptions)"
            />

            <CriterionValueUploadModal
                width="40vw"
                :ref="modalUploadOptions.ref"
                :options="modalUploadOptions"
                :criterion_id="criterion_id"
                @onConfirm="closeFormModal(modalUploadOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalUploadOptions)"
            />

        </v-card>
    </section>
</template>


<script>
import CriterioFormModal from "./CriterionValueFormModal";
import CriterionValueUploadModal from "./CriterionValueUploadModal";

export default {
    props: ['criterion_id', 'criterion_name', 'criterion_type'],
    components: {CriterioFormModal, CriterionValueUploadModal},
    data() {
        let vue = this

        return {
            breadcrumbs: [
                {title: 'Criterios', text: `${this.criterion_name}`, disabled: false, href: `/criterios`},
                {title: 'Valores', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: '/criterios/' + vue.criterion_id + '/valores/search',
                ref: 'CriterioTable',
                headers: [
                    // {text: "Módulo", value: "order", align: 'center', sortable: false},
                    // {text: "Módulo", value: "image", align: 'center', sortable: false},
                    {text: "Valor", value: "name", sortable: false},
                    {text: "Cantidad de usuarios", align: 'center', value: "users_count", sortable: false},
                    // {text: "Cantidad de Usuarios", align: 'center', value: "usuarios_count"},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {text: "Editar", icon: 'mdi mdi-pencil', type: 'action', method_name: 'edit'},
                ],
                more_actions: [

                ]
            },
            selects: {
                modulos: []
            },
            filters: {
                q: '',
                modulo: null,
                tipo_criterio: null
            },
            modalOptions: {
                ref: 'CriterioFormModal',
                open: false,
                base_endpoint: '/criterios/' + vue.criterion_id + '/valores',
                resource: 'Valor de Criterio',
                confirmLabel: 'Guardar',
            },
            modalUploadOptions: {
                ref: 'CriterionValueUploadModal',
                open: false,
                base_endpoint: '/criterios/' + vue.criterion_id + '/subida-masiva',
                resource: 'Subida de valores de criterio',
                confirmLabel: 'Subir',
            },
            modalDeleteOptions: {
                ref: 'CriterioDeleteModal',
                open: false,
                base_endpoint: '/criterios/' + vue.criterion_id + '/valores',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
        }
    },
    mounted() {
        let vue = this
        vue.getSelects();
        vue.filters.tipo_criterio = vue.tipo_criterio_id
    },
    methods: {
        getSelects() {
            // let vue = this
            // const url = '/tipo-criterios/' + vue.tipo_criterio_id + `/criterios/get-list-selects`
            // vue.$http.get(url)
            //     .then(({data}) => {
            //         vue.selects.modulos = data.data.modulos
            //     })
        },

        activity() {
            console.log('activity')
        },
        confirmModal() {
        },
    }

}
</script>
