<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Estadísticas
                <v-spacer/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="5">
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
                </v-row>

            </v-card-text>
        </v-card>
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
        <!-- <Fab/> -->
    </section>
</template>
<script>
import CardIndicator from "./CardIndicator";
import GeneralGraphic from "./GeneralGraphic";
// const cardInfo = {
//     title: '',
//     icon: '',
//     color: 'primary',
//     value: 0
// }
export default {
    components: {CardIndicator, GeneralGraphic},
    data() {
        return {
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
        this.getEstadisticas()
    },
    methods: {
        getModulos() {
            let vue = this
            vue.$http.get('/general/modulos')
                .then(({data}) => {
                    data.data.modulos.forEach(el => {
                        vue.selects.modulo.push(el)
                    })
                })
        },
        getEstadisticas() {
            let vue = this
            vue.getCardsInfo()
            vue.getEvaluacionesPorFecha()
            vue.getVisitas()
            vue.getTopBoticas()
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
