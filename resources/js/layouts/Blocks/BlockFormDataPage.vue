<template>

    <div>
        <header class="page-header mt-5 py-0 mx-8">
            <div class="breadcrumb-holder container-fluid card v-card v-sheet theme--light elevation-0">
                <v-card-title>

               <!--      <h4>Datos / </h4>
                    <h4> Rutas y cursos</h4> -->

                    <DefaultSimpleBreadcrumbs :breadcrumbs="breadcrumbs"/>
                   
                    <v-spacer/>

                    <DefaultModalButton
                        label="Segmentación"
                        @click="openFormModal(modalFormOptions)" />

                    <DefaultModalButton
                        label="Siguiente"
                        @click="openLink('/programas/crear-rutas')" />
                </v-card-title>
            </div>
        </header>

        <section class="client section-list">
            <div class="container-fluid">
                <div class="row">

                    <div class="col-sm-12">

                        <v-card class="card" elevation="0">
                            <v-card-text>
                                
                                <v-row class="justify-content-start">
                                                <!-- <v-row justify="center"> -->
                                    <v-col cols="6">
                                        <DefaultInput
                                            dense
                                            label="Nombre"
                                            placeholder="Ingrese un nombre"
                                            v-model="resource.name"
                                            show-required
                                        />
                                    </v-col>
                                    <v-col cols="6">
                                        <DefaultInput
                                            dense
                                            label="Código"
                                            placeholder="Código"
                                            v-model="resource.code"
                                            show-required
                                        />
                                    </v-col>
                                </v-row>

                                 <v-row>
                                    <v-col cols="12">
                                        <DefaultTextArea
                                            dense
                                            label="Descripción"
                                            placeholder="Ingrese una descripción"
                                            type="textarea"
                                            v-model="resource.description"
                                        />
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </div>
                </div>
            </div>

        </section>

        <section class="client section-list">
            <div class="container-fluid">
                <div class="row">

                    <div class="col-sm-12">

                        <v-card class="card" elevation="0">
                            <v-card-text class="py-4">
                                
                               
                            </v-card-text>
                        </v-card>
                    </div>
                </div>
            </div>

        </section>

        <SegmentFormModal
            :options="modalFormOptions"
            width="75vw"
            :ref="modalFormOptions.ref"
            @onCancel="closeSimpleModal(modalFormOptions)"
            @onConfirm="closeFormModal(modalFormOptions, dataTable, filters)"
        />

    </div>


</template>

<script>
import BlockDetailModal from "./BlockDetailModal";
import SegmentFormModal from "./SegmentFormModal";
// import BlockFormModal from "./BlockFormModal";

export default {
    components: {BlockDetailModal, SegmentFormModal},
    // components: {BlockDetailModal, BlockFormModal, BlockFinishModal, BlockDirectionsModal,},
    props: ['usuario_id'],
    data: () => ({

        breadcrumbs: [
            {title: 'Datos', disabled: false, href: `/programas/crear`},
            {title: 'Rutas y cursos', disabled: false, href: '/programas/crear-rutas'},
        ],

        resourceDefault: {
            name: null,
            description: null,
            position: null,
            imagen: null,
            plantilla_diploma: null,
            file_imagen: null,
            file_plantilla_diploma: null,
            config_id: this.modulo_id,
            categoria_id: this.categoria_id,
            active: false,
            requisito_id: null,
            reinicio_automatico: false,
            reinicio_automatico_dias: null,
            reinicio_automatico_horas: null,
            reinicio_automatico_minutos: 1,
        },
        resource: {},
        // rules: {
        //     name: this.getRules(['required']),
        //     position: this.getRules(['required', 'number']),
        // },
       
        selects: {
            types: [],
            statuses: []
        },
       
        modalDateOptions: {
            ref: 'DateRangeFilter',
            open: false,
        },
        modalDetailOptions: {
            ref: 'BlockDetailModal',
            open: false,
            base_endpoint: '/programas',
            confirmLabel: 'Cerrar',
            hideCancelBtn: true
        },
        modalFormOptions: {
            ref: 'SegmentFormModal',
            open: false,
            base_endpoint: '/segments',
            confirmLabel: 'Guardar',
            resource: 'segmentación',
        },
        // modalFormDuplicateOptions: {
        //     ref: 'BlockFormlModal',
        //     open: false,
        //     base_endpoint: '/programas',
        //     confirmLabel: 'Guardar',
        //     resource: 'programa',
        //     action: 'duplicate',
        // },
        modalDateFilter1: {
            open: false,
        },
       
    }),
    mounted() {
        let vue = this
        vue.getSelects();
        // if (vue.usuario_id)
        //     vue.modalFormOptions.usuario_id = vue.usuario_id
    },
    methods: {
        getSelects() {
            let vue = this
            const url = `/programas/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.types = data.data.types
                    vue.selects.statuses = data.data.statuses
                })
        },

        openFormDuplicateModal(resource) {
            let vue = this
            vue.modalDetailOptions.open = false;
            vue.openFormModal(vue.modalFormOptions, resource, 'duplicate', 'Crear programa');
        },
        //      async activity() {
        //          console.log('entra');
        // let vue = this;
        // await axios.post("/descargarEventosEnVivo", {responseType: 'blob',
        // 		params: {
        // 			tipo: vue.filters.types,
        // 			master: vue.master,
        // 			filtro: vue.filters.status,
        // 		}
        // 	})
        // 	.then((res) => {
        // 		const downloadUrl = window.URL.createObjectURL(new Blob([res.data]));
        //         const link = document.createElement('a');
        //         link.href = downloadUrl;
        //         link.setAttribute('download', 'Aulas_Virtuales.xlsx'); //any other extension
        //         document.body.appendChild(link);
        //         link.click();
        //         link.remove();
        // 	})
        // 	.catch((err) => {
        // 		console.log(err);
        // 	});
        //      },
    }
}
</script>

<!-- <style type="text/css">
    .custom-expansion-block.theme--light.v-expansion-panels .v-expansion-panel {
        background-color: inherit !important; 
        color: inherit !important; 
    }

    .custom-expansion-block .segments {

        border: 1px solid #cfcfcf;
        padding: 10px;
        margin: 10px auto;

    }
</style>
 -->