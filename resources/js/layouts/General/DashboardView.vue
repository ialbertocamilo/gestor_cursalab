<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>

                <div v-if="showDetail">
                    <span class="btn_select_media text-muted" @click="showDetail = false">Dashboard</span>
                    <span class="fas fa-chevron-right mx-2"></span>
                    <span class="text-body">Gestor de almacenamiento y usuarios.</span>
                </div>
                <div v-else>
                    Dashboard
                </div>

                <v-spacer/>
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-4">
            <v-card-text>

                <!-- === MODULO Y DETALLES === -->
                <v-row  :class="`${ !showDetail ? 'd-flex' : 'd-none' }`">
                    <v-col cols="5" class="align-self-center">
                        <DefaultSelect
                            dense
                            label="Módulo"
                            :items="selects.modulo"
                            v-model="filters.modulo"
                            item-text="name"
                            item-value="id"
                            @onChange="getEstadisticas"
                        />
                    </v-col>
                    <v-col  cols="6" offset="1"
                            class="d-flex justify-space-between align-items-center">
                        <div class="d-flex align-items-center w-75">
                            <div class="d-flex flex-column w-75">
                                <p class="font-weight-bold mb-0">Almacenamiento general</p>
                                <div class="my-2">
                                    <span class="fa-2x" v-text="workspace_status.size_medias_storage+' usados'"></span>
                                    de <span v-text="workspace_status.size_medias_limit+' Gb' "></span>
                                </div>

                                <v-progress-linear
                                        :color="workspace_status.size_medias_porcent.exceded ? 'red' : 'primary' "
                                        :value="workspace_status.size_medias_porcent.porcent"
                                        height="20"
                                        rounded
                                    >
                                     <div class="d-flex justify-content-end" 
                                          :style="{ width: (workspace_status.size_medias_porcent.porcent < 10) ? '12%' :workspace_status.  size_medias_porcent.porcent +'%'}">
                                        <strong 
                                            class="text-white text-right"
                                            v-text="workspace_status.size_medias_porcent.porcent + '%'">
                                        </strong>
                                    </div>
                                </v-progress-linear>

                            </div>
                        </div>

                        <div class="d-flex align-items-center w-75">
                            <div class="d-flex flex-column w-75">
                                <p class="font-weight-bold mb-0">Total usuarios activos</p>
                                <div class="my-2">
                                    <span class="fa-2x" v-text="workspace_status.users_count_actives"></span>
                                    de <span v-text="workspace_status.users_count_limit"></span> disponibles.
                                </div>

                                <v-progress-linear
                                        :color="workspace_status.users_count_porcent.exceded ? 'red' : 'primary' "
                                        :value="workspace_status.users_count_porcent.porcent"
                                        height="20"
                                        rounded
                                    >
                                    <div class="d-flex justify-content-end" 
                                        :style="{ width: (workspace_status.users_count_porcent.porcent < 10) ? '12%' :workspace_status.users_count_porcent.porcent +'%'}">
                                        <strong 
                                            class="text-white text-right"
                                            v-text="workspace_status.users_count_porcent.porcent + '%'">
                                        </strong>
                                    </div>
                                </v-progress-linear>

                            </div>
                        </div>

                        <div class="w-25">
                            <a href class="ml-1" @click.prevent="showDetail = true">
                                Ver detalle <span class="ml-2 fas fa-arrow-right"></span>
                            </a>
                        </div>
                    </v-col>
                </v-row>
                <!-- === MODULO Y DETALLES === -->

                <!-- === ALMACENAMIENTO === -->
                <v-row :class="`${ showDetail ? 'd-flex' : 'd-none' }`">
                    <v-col 
                        cols="5" 
                        class="d-flex justify-content-start align-self-center">
                        <span class="fa-lg text-body">Visión del espacio y usuarios del workspace</span>
                    </v-col>
                    <v-col cols="7"
                        class="d-flex justify-content-end align-self-center">
                        <v-btn 
                            color="primary" 
                            @click="openFormModal(modalGeneralStorageOptions, null, 'status', 'Aumentar mi plan')">
                            <span class="mdi mdi-cloud-outline fa-lg mr-2"></span>
                            Aumentar mi plan
                        </v-btn>
                    </v-col>
                </v-row>
                <!-- === ALMACENAMIENTO === -->

            </v-card-text>
        </v-card>

        <section v-show="!showDetail">
            <v-card flat class="elevation-0 mb-4 bg-transparent">
                <v-row>
                    <v-col  v-for="(value, key) in apiData.estadisticas" :key="key">
                        <CardIndicator
                            :icon="value.icon || 'mdi-book-open'"
                            :icon-color="value.color || 'primary'"
                            :amount="value.value || 0"
                            :label="value.title || 'Cargando...'"
                        />
                    </v-col>
                </v-row>
            </v-card>
            <v-card flat class="elevation-0 mb-4">
                <v-row class="p-3">
                    <v-col cols="6" class="d-flex flex-column">
                        <GeneralGraphic
                            :graphic_data="apiData.graficos.evaluacionesPorFecha"
                            @refreshCache="getEvaluacionesPorFecha(true)"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex flex-column">
                        <GeneralGraphic
                            :graphic_data="apiData.graficos.visitas"
                            @refreshCache="getVisitas(true)"
                        />
                    </v-col>
    <!--                <v-col cols="12" class="d-flex flex-column">-->
    <!--                    <GeneralGraphic-->
    <!--                        :graphic_data="apiData.graficos.topBoticas"-->
    <!--                        @refreshCache="getTopBoticas(true)"-->
    <!--                    />-->
    <!--                </v-col>-->
                </v-row>
            </v-card>
        </section>

        <section v-show="showDetail" class="pt-0">
            <v-card flat class="elevation-0 mb-4">
                <v-card-text>
                    <v-row>
                        <v-col cols="7" class="px-8 py-6 border-right">
                            <v-card-title class="p-0 font-weight-bold">Almacenamiento general</v-card-title>
                            <div class="my-3 d-flex justify-space-between">
                                <span 
                                    class="fa-2x"
                                    :class="workspace_status.size_medias_porcent.exceded ? 'text-danger' : 'text-primary-sub'" 
                                    v-text="workspace_status.size_medias_porcent.porcent+'% '+'utilizado'">
                                </span>

                                <span>
                                    <span class="font-weight-bold">
                                        Total utilizado:
                                        <span v-text="workspace_status.size_medias_storage"></span>
                                    </span>
                                    / 
                                    <span v-text="workspace_status.size_medias_limit+' Gb' "></span>
                                </span>
                            </div>

                            <v-progress-linear
                                    :color="workspace_status.size_medias_porcent.exceded ? 'red' : 'primary' "
                                    :value="workspace_status.size_medias_porcent.porcent"
                                    height="20"
                                    rounded
                                >
                            </v-progress-linear>

                            <p class="my-6 font-weight-bold">Detalle del almacenamiento</p>

                            <ul class="px-0 pb-0">
                                <li v-for="route in workspace_status.routes_redirects" :key="route.label"
                                    class="d-flex align-items-center justify-content-between mb-3 grey lighten-5 rounded p-2">
                                    <span v-text="route.label"></span> 
                                    <span v-text="route.size"></span>
                                   <!--  <v-btn 
                                        class="ml-2" 
                                        text 
                                        color="primary" 
                                        @click="setStorageUrl(route.url, route.filters)">
                                        <v-icon>
                                            mdi-open-in-new
                                        </v-icon>
                                    </v-btn> -->

                                </li>
                            </ul>
                            
                        </v-col>
                        <v-col cols="5" class="px-8 py-6">
                            <div class="grey lighten-4 rounded p-4">
                                <div class="d-flex align-items-center">
                                    <span 
                                        class="mdi mdi-account-multiple-outline text-primary-sub fa-4x mr-4"
                                        :class="workspace_status.users_count_porcent.exceded ? 'text-danger' : 'text-primary-sub'"
                                        ></span>
                                    <div class="d-flex flex-column">    
                                        <v-card-title class="p-0 font-weight-bold">Total Usuarios</v-card-title>
                                        
                                        <div>
                                            <span 
                                                class="fa-2x my-2" 
                                                v-text="workspace_status.users_count_actives"
                                                :class="workspace_status.users_count_porcent.exceded ? 'text-danger' : 'text-primary-sub'"
                                                ></span>
                                            <span>de <span v-text="workspace_status.users_count_limit"></span> disponibles</span>
                                        </div>
                                    </div>
                                </div>
                                <v-progress-linear
                                    class="my-2"
                                    :color="workspace_status.users_count_porcent.exceded ? 'red' : 'primary' "
                                    :value="workspace_status.users_count_porcent.porcent"
                                    height="20"
                                    rounded
                                    >
                                </v-progress-linear>

                                <ul class="px-0 mb-0 mt-4">
                                    <li class="d-flex justify-content-between mb-1">
                                        <span>Usuarios Activos</span>
                                        <div>
                                            <span v-text="workspace_status.users_count_actives"></span>
                                            <v-btn 
                                                text 
                                                color="primary"
                                                @click="setStorageUrl(workspace_status.route_user_actives.url, 
                                                                      workspace_status.route_user_actives.filters)">
                                                <v-icon>mdi-open-in-new</v-icon>
                                            </v-btn>
                                        </div>
                                    </li>

                                    <li class="d-flex justify-content-between">
                                        <span>Usuarios Inactivos</span>
                                        <div>
                                            <span v-text="workspace_status.users_count_inactives"></span>
                                            <v-btn 
                                                text 
                                                color="primary"
                                                @click="setStorageUrl(workspace_status.route_user_inactives.url, 
                                                                      workspace_status.route_user_inactives.filters)">
                                                <v-icon>mdi-open-in-new</v-icon>
                                            </v-btn>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </v-col>
                    </v-row>

                </v-card-text>
            </v-card>
        </section>

        <!-- MODAL ALMACENAMIENTO -->
        <GeneralStorageModal
            :ref="modalGeneralStorageOptions.ref"
            :options="modalGeneralStorageOptions"
            width="45vw"
            @onCancel="closeFormModal(modalGeneralStorageOptions)"
            @onConfirm="closeFormModal(modalGeneralStorageOptions), 
                        openFormModal(modalGeneralStorageEmailSendOptions, null, 'status', 'Solicitud enviada')"
        />
        <!-- MODAL ALMACENAMIENTO -->

        <!-- MODAL EMAIL ENVIADO -->
        <GeneralStorageEmailSendModal
            :ref="modalGeneralStorageEmailSendOptions.ref"
            :options="modalGeneralStorageEmailSendOptions"
            width="35vw"
            @onCancel="closeFormModal(modalGeneralStorageEmailSendOptions)"
            @onConfirm="closeFormModal(modalGeneralStorageEmailSendOptions)"
        />
        <!-- MODAL EMAIL ENVIADO -->

    </section>
</template>
<script>

import CardIndicator from "./CardIndicator";
import GeneralGraphic from "./GeneralGraphic";
import GeneralStorageModal from './GeneralStorageModal.vue';
import GeneralStorageEmailSendModal from './GeneralStorageEmailSendModal.vue';

export default {
    components: { CardIndicator, GeneralGraphic, GeneralStorageModal, GeneralStorageEmailSendModal },
    data() {
        return {
            modalGeneralStorageOptions: {
                ref: 'GeneralStorageModal',
                open: false,
                showCloseIcon: true,
                base_endpoint: '/general',
                confirmLabel:'Enviar',
                persistent: true
            },
            modalGeneralStorageEmailSendOptions: {
                ref: 'GeneralStorageEmailSendModal',
                open: false,
                showCloseIcon: true,
                hideCancelBtn: true,
                confirmLabel:'Entendido',
                persistent: false
            },
            showDetail: false,
            workspace_status: {
                size_medias_storage: "0 Gb",
                size_medias_limit: 0,

                users_count_actives: 0,
                users_count_inactives: 0,
                users_count_limit: 0,

                users_count_porcent: {
                    porcent: 0,
                    exceded: false
                },
                size_medias_porcent: {
                    porcent: 0,
                    exceded: false
                },
                routes_redirects: []
            },
            apiData: {
                estadisticas: {
                    usuarios: {},
                    usuarios_activos: {},
                    cursos: {},
                    temas: {},
                    temas_evaluables: {}
                },
                graficos: {
                    evaluacionesPorFecha: {
                        overlay: true,
                        ref: 'evaluacionesPorFecha',
                        last_update: null,
                        series: [{
                            name: "Pruebas realizadas",
                            data: []
                        }],
                        chartOptions: {
                            title: {
                                text: 'Evaluaciones',
                                style: {
                                    fontWeight: 'bold',
                                    fontFamily: 'Tahoma',
                                },
                            },
                            subtitle: {
                                text: 'Cantidad de evaluaciones realizadas por fecha',
                                style: {
                                    fontFamily: 'Tahoma',
                                },
                            },
                            chart: {
                                id: 'evaluacionesPorFecha',
                                type: 'line',
                                width: "100%",
                                toolbar: {
                                    show: true,
                                    tools: {
                                        download: false,
                                        selection: true,
                                        zoom: true,
                                        zoomin: true,
                                        zoomout: true,
                                        pan: false,
                                        reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
                                    },
                                },
                            },
                            xaxis: {
                                categories: [],
                            },
                        },
                    },
                    visitas: {
                        overlay: true,
                        ref: 'visitas',
                        last_update: null,
                        series: [{
                            name: "Visitas",
                            data: []
                        }],
                        chartOptions: {
                            title: {
                                text: 'Visitas',
                                style: {
                                    fontWeight: 'bold',
                                    fontFamily: 'Tahoma',
                                },
                            },
                            subtitle: {
                                text: 'Cantidad de visitas realizadas por fecha',
                                style: {
                                    fontFamily: 'Tahoma',
                                },
                            },
                            chart: {
                                id: 'visitas',
                                type: 'line',
                                width: "100%",
                                toolbar: {
                                    show: true,
                                    tools: {
                                        download: false,
                                        selection: true,
                                        zoom: true,
                                        zoomin: true,
                                        zoomout: true,
                                        pan: false,
                                        reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
                                    },
                                },
                            },
                            xaxis: {
                                categories: [],
                            },
                        },
                    },
                    topBoticas: {
                        overlay: true,
                        ref: 'topBoticas',
                        last_update: null,
                        series: [{
                            name: "Usuarios aprobados",
                            data: []
                        }],
                        chartOptions: {
                            plotOptions: {
                                hideOverflowingLabels: true
                            },
                            title: {
                                text: 'TOP 10 BOTICAS CON MÁS APROBADOS',
                                style: {
                                    fontWeight: 'bold',
                                    fontFamily: 'Tahoma',
                                },
                            },
                            subtitle: {
                                text: 'Cantidad de usuarios aprobados por botica',
                                style: {
                                    fontFamily: 'Tahoma',
                                },
                            },
                            chart: {
                                id: 'topBoticas',
                                type: 'bar',
                                width: "100%",
                                toolbar: {
                                    show : true,
                                    tools: {
                                        download: false,
                                        selection: true,
                                        zoom: true,
                                        zoomin: true,
                                        zoomout: true,
                                        pan: false,
                                        reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
                                    },
                                },
                            },
                            xaxis: {
                                categories: [],
                            },
                        },
                    }
                }
            },
            filters: {
                modulo: null
            },
            selects: {
                modulo: [
                    {name: 'Todos', id: null}
                ],
            },
        }
    },
    mounted() {
        this.getModulos()
        this.getEstadisticas(false);
        this.getWorkspaceData();
    },
    methods: {
        getWorkspaceData() {
            let vue = this;

            vue.$http.get('/general/workspace-current-status').then(({data}) => {
                vue.workspace_status = data.data;
            });
        },
        getModulos() {
            let vue = this
            vue.$http.get('/general/modulos')
                .then(({data}) => {
                    data.data.modulos.forEach(el => {
                        vue.selects.modulo.push(el)
                    })
                })
        },
        getEstadisticas(change = true) {
            let vue = this
            vue.getCardsInfo()
            vue.getEvaluacionesPorFecha()
            vue.getVisitas()
            vue.getTopBoticas()
            if (change) vue.queryStatus("dashboard", "uso_filtro");
        },
        getCardsInfo(refresh = false) {
            let vue = this
            let url = `/general/cards-info?modulo_id=${vue.filters.modulo || ''}`
            if (refresh)
                url += `&refresh=true`
            let estadisticas = vue.apiData.estadisticas
            vue.$http.get(url)
                .then(({data}) => {
                    let response_data = data.data.data
                    estadisticas.usuarios = response_data.totales.usuarios
                    estadisticas.usuarios_activos = response_data.totales.usuarios_activos
                    estadisticas.temas = response_data.totales.temas
                    estadisticas.temas_evaluables = response_data.totales.temas_evaluables
                    estadisticas.cursos = response_data.totales.cursos
                })
        },
        getEvaluacionesPorFecha(refresh = false) {
            let vue = this

            let url = `/general/evaluaciones-por-fecha?modulo_id=${vue.filters.modulo || ''}`
            if (refresh)
                url += `&refresh=true`
            let graphic_data = vue.apiData.graficos.evaluacionesPorFecha
            graphic_data.overlay = true
            vue.$http.get(url)
                .then(({data}) => {
                    let response_data = data.data.data
                    graphic_data.last_update = response_data.last_update
                    graphic_data.series = [{
                        data: response_data.values
                    }]
                    graphic_data.chartOptions = {
                        xaxis: {
                            categories: response_data.labels
                        }
                    }
                    graphic_data.overlay = false
                })
        },
        getVisitas(refresh = false) {
            let vue = this
            let url = `/general/visitas-por-fecha?modulo_id=${vue.filters.modulo || ''}`
            if (refresh)
                url += `&refresh=true`
            let graphic_data = vue.apiData.graficos.visitas
            graphic_data.overlay = true
            vue.$http.get(url)
                .then(({data}) => {
                    let response_data = data.data.data
                    graphic_data.last_update = response_data.last_update
                    graphic_data.series = [{
                        data: response_data.values
                    }]
                    graphic_data.chartOptions = {
                        xaxis: {
                            categories: response_data.labels
                        }
                    }
                    graphic_data.overlay = false

                })
        },
        getTopBoticas(refresh = false) {
            let vue = this
            let url = `/general/top-boticas?modulo_id=${vue.filters.modulo || ''}`
            if (refresh)
                url += `&refresh=true`
            let graphic_data = vue.apiData.graficos.topBoticas
            graphic_data.overlay = true
            vue.$http.get(url)
                .then(({data}) => {
                    let response_data = data.data.data

                    console.log(response_data)
                    graphic_data.last_update = response_data.last_update
                    graphic_data.series = [{
                        data: response_data.values
                    }]
                    graphic_data.chartOptions = {
                        xaxis: {
                            categories: response_data.labels
                        }
                    }
                    graphic_data.overlay = false
                })
        }
    }
}
</script>
<style lang="scss">
.v-card__title.title_prim {
    font-family: "Nunito", sans-serif;
    font-size: 20px !important;
    font-weight: 700;
    color:#1A2128;
    letter-spacing: 0.1px;
}
</style>
