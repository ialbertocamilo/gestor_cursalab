<template>
    <section class="section-list ">
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

            <v-card-title>
                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/>
                <v-spacer/>
                <DefaultActivityButton
                    :label="'Importar Evaluación'"
                    @click="openFormModal(modalTemaPreguntasImport,null,null,modalTemaPreguntasImport.title)"
                />
                <DefaultModalButton
                    @click="openFormModal(modalOptions, null, 'create')"
                    :label="'Pregunta'"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="4">
                        <DefaultInput
                            learable dense
                            v-model="filters.q"
                            label="Buscar por pregunta ..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                        />
                    </v-col>

                </v-row>
                <v-row>
                    <div class="col-md-8">
                        <div class="alert alert-info mx-2 mt-2" role="alert">
                            - Cuando un tema es "Evaluable" y de tipo "Calificada" se deben agregar opciones a las preguntas. <br>
                            - Cuando un tema es "Evaluable" y de tipo "Abierta" no se consideran las opciones, solo se muestran las preguntas para que los usuarios respondan con un texto. <br>
                            - Al eliminar todas las preguntas el tema se convierte a un tema no evaluable.<br>
                            <span v-if="evaluation_type == 'calificada'">
                                - El total de puntos que se puede acumular en las preguntas obligatorias es de 20 puntos. <br>
                                - El total de puntos solo considera las preguntas activas.
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4 pr-5 pt-2">
                        <table class="table table-striped table-dark" v-if="evaluation_type">
                          <tbody>
                            <tr>
                              <td>Obligatorio: </td>
                              <td>{{ evaluation_data_sum_required }} puntos</td>
                            </tr>
                            <tr>
                              <td>No Obligatorios: </td>
                              <td>{{ evaluation_data_sum_not_required }} puntos</td>
                            </tr>
                            <tr>
                              <td>Total: </td>
                              <td>{{ evaluation_data_sum }} puntos</td>
                            </tr>
                          </tbody>
                        </table>
                    </div>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="openFormModal(modalOptions, $event, 'edit')"
                @delete="deleteTemaPregunta($event)"
            />
            <TemaPreguntaFormModal
                width="60vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="refreshDefaultTable(dataTable, filters)"
                @onCancel="closeFormModal(modalOptions)"
                :evaluable="evaluable"
                :evaluation_type="evaluation_type"
            />
            <TemaPreguntasImport
                width="50vw"
                :ref="modalTemaPreguntasImport.ref"
                :options="modalTemaPreguntasImport"
                @onConfirm="closeFormModal(modalTemaPreguntasImport, dataTable, filters);refreshDefaultTable(dataTable, filters) "
                @onCancel="closeFormModal(modalTemaPreguntasImport);refreshDefaultTable(dataTable, filters) "
            />
            <DialogConfirm
                v-model="modalDeleteOptions.open"
                width="450px"
                title="Eliminar pregunta"
                subtitle="¿Está seguro de eliminar la pregunta?"
                @onConfirm="confirmDelete"
                @onCancel="modalDeleteOptions.open = false"
            />
        </v-card>
    </section>
</template>

<script>
// import TemaPreguntaFormModal from "./TemaPreguntaFormModalNew";
import TemaPreguntaFormModal from "./TemaPreguntaFormModal";
import TemaPreguntasImport from "./TemaPreguntasImport";
import DialogConfirm from "../../components/basicos/DialogConfirm";

export default {
    components: {TemaPreguntaFormModal, TemaPreguntasImport, DialogConfirm},
    props: [
        'modulo_id',
        'modulo_name',
        'categoria_id',
        'categoria_name',
        'curso_id',
        'curso_name',
        'tema_id',
        'tema_name',
        'evaluable',
        'evaluation_type',
        'evaluation_data_sum',
        'evaluation_data_sum_required',
        'evaluation_data_sum_not_required',
    ],
    data() {
        let vue = this
        return {
            breadcrumbs: [
                {
                    title: 'Escuelas',
                    text: `${this.categoria_name}`,
                    disabled: false,
                    href: `/escuelas`
                },
                {
                    title: 'Cursos',
                    text: `${this.curso_name}`,
                    disabled: false,
                    href: `/escuelas/${this.categoria_id}/cursos`
                },
                {
                    title: 'Temas',
                    text: `${this.tema_name}`,
                    disabled: false,
                    href: `/escuelas/${this.categoria_id}/cursos/${this.curso_id}/temas`
                },
                {title: 'Evaluaciones', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: `/escuelas/${vue.categoria_id}/cursos/${vue.curso_id}/temas/${vue.tema_id}/preguntas/search`,
                ref: 'temaPreguntasTable',
                headers: [
                    {text: "Pregunta", value: "custom_tema_preguntas_pregunta", align: 'start', sortable: false},
                    {text: "Tipo", value: "tipo_pregunta", align: 'center', sortable: false},
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
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                    // {
                    //     text: "Actividad",
                    //     icon: 'fas fa-file',
                    //     type: 'action',
                    //     method_name: 'activity'
                    // },
                ],
            },
            selects: {
                modules: []
            },
            filters: {
                q: '',
            },
            modalOptions: {
                ref: 'TemaPreguntasModal',
                open: false,
                base_endpoint: `/escuelas/${vue.categoria_id}/cursos/${vue.curso_id}/temas/${vue.tema_id}/preguntas`,
                resource: 'Pregunta',
                confirmLabel: 'Guardar'
            },
            modalDeleteOptions: {
                open: false,
            },
            modalTemaPreguntasImport: {
                ref: 'TemaPreguntasImport',
                open: false,
                base_endpoint: `/escuelas/${vue.categoria_id}/cursos/${vue.curso_id}/temas/${vue.tema_id}/preguntas/import`,
                resource: '',
                confirmLabel: 'Guardar',
                title: 'Importar Evaluación',
                hideCancelBtn: true,
                hideConfirmBtn: true,
            },

            delete_model: null
        }
    },
    mounted() {
        let vue = this
        // vue.getSelects();
    },
    methods: {
        getSelects() {
            // let vue = this
            // const url = `/escuelas/get-selects`
            // vue.$http.get(url)
            //     .then(({data}) => {
            //         vue.selects.modules = data.data.modules
            //         vue.modalOptions.selects.modules = data.data.modules
            //     })
        },
        activity() {
            console.log('activity')
        },
        deleteTemaPregunta(usuario) {
            let vue = this
            vue.delete_model = usuario
            vue.modalDeleteOptions.open = true
        },
        confirmDelete() {
            let vue = this
            let url = `/escuelas/${vue.categoria_id}/cursos/${vue.curso_id}/temas/${vue.tema_id}/preguntas/${vue.delete_model.id}`

            vue.$http.delete(url)
                .then(({data}) => {
                    vue.showAlert(data.data.msg)
                    vue.refreshDefaultTable(vue.dataTable, vue.filters)
                    vue.delete_model = null
                    vue.modalDeleteOptions.open = false
                })
        }
    }

}
</script>
