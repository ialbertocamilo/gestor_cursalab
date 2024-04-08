<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Dashboard
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start" class="align-items-center">
                    <v-col cols="4">
                        <v-card>
                            <v-card-text class="py-0">
                                <div class="d-flex align-center">
                                    <div class="d-flex align-center" style="flex: 1;">
                                        <div style="width: 80px;">
                                            <apexcharts type="radialBar" height="100" :options="graphic_data_advance.chartOptions" :series="graphic_data_advance.series"/>
                                        </div>
                                        <div>
                                            <span class="d-flex fw-bold mb-1" style="font-size: 24px;">0%</span>
                                            <span>Cumplimiento de inducción</span>
                                        </div>
                                    </div>
                                    <div style="font-size: 22px;">
                                        <a href="/exportar/node"><span class="mdi mdi-arrow-right-circle"></span></a>
                                    </div>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>
                    <v-col cols="4">
                        <v-card style="overflow: hidden;">
                            <v-card-text class="py-0">
                                <div class="d-flex align-center">
                                    <div class="d-flex align-center" style="height: 73px; flex: 1;">
                                        <div>
                                            <span class="d-flex fw-bold mb-1" style="font-size: 24px;">{{users_active}}/{{users_total}}</span>
                                            <span>Colaboradores activos</span>
                                        </div>
                                    </div>
                                    <div style="font-size: 22px;">
                                        <a href="/usuarios"><span class="mdi mdi-arrow-right-circle"></span></a>
                                    </div>
                                </div>
                            </v-card-text>
                            <div class="progress_bar_card">
                                <v-progress-linear
                                    :color="'#973DE7'"
                                    height="6"
                                    :value="users_bar"
                                    rounded
                                >
                                </v-progress-linear>
                            </div>
                        </v-card>
                    </v-col>
                    <v-col cols="4">
                        <v-card style="overflow: hidden;">
                            <v-card-text class="py-0">
                                <div class="d-flex align-center">
                                    <div class="d-flex align-center" style="height: 73px; flex: 1;">
                                        <div>
                                            <span class="d-flex fw-bold mb-1" style="font-size: 24px;">{{process_progress}}/{{process_total}}</span>
                                            <span>Procesos en curso</span>
                                        </div>
                                    </div>
                                    <div style="font-size: 22px;">
                                        <a href="/procesos"><span class="mdi mdi-arrow-right-circle"></span></a>
                                    </div>
                                </div>
                            </v-card-text>
                            <div class="progress_bar_card">
                                <v-progress-linear
                                    :color="'#973DE7'"
                                    height="6"
                                    :value="process_bar"
                                    rounded
                                >
                                </v-progress-linear>
                            </div>
                        </v-card>
                    </v-col>
                </v-row>
                <v-row justify="start">
                    <v-col cols="5" md="12" lg="6">
                        <v-card>
                            <v-card-text>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span class="text_default fw-bold">Gráfico circular</span>
                                    </div>
                                    <div>
                                        <DefaultAutocomplete
                                            label="Proceso"
                                            v-model="process"
                                            :items="selects.lista_procesos"
                                            item-text="title"
                                            item-value="id"
                                            dense
                                            @input="searchProcess"
                                        />
                                    </div>
                                </div>
                                <div style="height: 450px;">
                                    <apexcharts
                                        v-if="show_graphic_data"
                                        height="450"
                                        :options="graphic_data.chartOptions"
                                        :series="graphic_data.series"
                                        ref="graphic_donut"
                                    />
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>
                    <v-col cols="7" md="12" lg="6">
                        <v-card>
                            <v-card-text>
                                <div>
                                    <span class="text_default fw-bold">Cumplimiento mensual de procesos</span>
                                </div>
                                <div class="d-flex mt-2" style="column-gap: 20px;">
                                    <div>
                                        <DefaultAutocomplete
                                            label="Periodo"
                                            v-model="filter_bars.periodo"
                                            :items="selects.lista_periodo"
                                            item-text="title"
                                            item-value="id"
                                            dense
                                            @input="filterGraphicBar"
                                        />
                                    </div>
                                    <div>
                                        <DefaultAutocomplete
                                            label="Área"
                                            v-model="filter_bars.area"
                                            :items="selects.lista_area"
                                            item-text="title"
                                            item-value="id"
                                            dense
                                            @input="filterGraphicBar"
                                        />
                                    </div>
                                    <div>
                                        <DefaultAutocomplete
                                            label="Sede"
                                            v-model="filter_bars.sede"
                                            :items="selects.lista_sede"
                                            item-text="title"
                                            item-value="id"
                                            dense
                                            @input="filterGraphicBar"
                                        />
                                    </div>
                                    <div>
                                        <DefaultAutocomplete
                                            label="Cargo"
                                            v-model="filter_bars.cargo"
                                            :items="selects.lista_cargo"
                                            item-text="title"
                                            item-value="id"
                                            dense
                                            @input="filterGraphicBar"
                                        />
                                    </div>
                                </div>
                                <div style="height: 400px">
                                    <apexcharts
                                        v-if="show_graphic_data_bars"
                                        height="400" type="bar"
                                        :options="graphic_data_bars.chartOptions"
                                        :series="graphic_data_bars.series"
                                    />
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <v-card>
                            <v-card-text>
                                <DefaultTable
                                    :ref="dataTable.ref"
                                    :data-table="dataTable"
                                    :filters="filters"
                                />
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
    </section>
</template>
<script>

export default {

    data() {
        return {
            process: null,
            process_progress: 0,
            process_total: 0,
            process_bar: 0,
            users_active: 0,
            users_total: 0,
            users_bar: 0,
            show_graphic_data: false,
            show_graphic_data_bars: false,
            filter_bars: {
                periodo: null,
                sede: null,
                cargo: null,
                area: null
            },
            dataTable: {
                endpoint: '/induccion/dashboard/search',
                ref: 'DashboardTable',
                headers: [
                    {text: "Supervisores", value: "fullname", align: 'start', sortable: true},
                    {text: "Módulo", value: "module", align: 'center', sortable: false},
                    {text: "Documento", value: "document", align: 'center', sortable: false},
                    {text: "Estado", value: "status", align: 'center', sortable: false},
                ],
            },
            filters: {
                q: '',
            },
            graphic_data_bars: {
                series: [
                    {
                    name: '',
                    data: [440, 550, 270, 560, 310, 580, 630, 600, 860, 270, 560, 310]
                    }
                ],
                chartOptions: {
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: {
                            show: false
                        }
                    },
                    colors: ['#C381FE'],
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            borderRadius: 7,
                            borderRadiusApplication: 'end',
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    },
                    yaxis: {
                        title: {
                            text: 'x100'
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                            return val
                            }
                        }
                    }
                },
            },
            graphic_data_advance: {
                series: [0],
                chartOptions: {
                    chart: {
                        height: 100,
                        type: 'radialBar',
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                    radialBar: {
                        startAngle: 0,
                        endAngle: 360,
                        hollow: {
                            margin: 0,
                            size: '70%',
                            background: '#fff',
                            image: undefined,
                            imageOffsetX: 0,
                            imageOffsetY: 0,
                            position: 'front',
                            dropShadow: {
                                enabled: false,
                            }
                        },
                        track: {
                            background: '#EEF1F3',
                            strokeWidth: '67%',
                            margin: 0, // margin is in pixels
                            dropShadow: {
                                enabled: false,
                            }
                        },

                        dataLabels: {
                            show: true,
                            name: {
                                show: false,
                            },
                            value: {
                                formatter: function(val) {
                                    return parseInt(val)+'%';
                                },
                                color: '#111',
                                fontSize: '14px',
                                show: true,
                                offsetY: 6,
                            }
                        }
                    }
                    },
                    fill: {
                        type: 'solid',
                        colors: ['#5458EA']
                    },
                    stroke: {
                        lineCap: 'round'
                    },
                },
            },
            graphic_data: {
                series: [],
                chartOptions: {
                    chart: {
                        id: "vuechart-example",
                        type: "donut",
                        // width: 200,
                        height: 400,
                    },
                    colors: ['#161BB6', '#5458EA', '#E7CCFF', '#6C02CA' ],
                    labels: [],
                    legend: {
                        width: '250px',
                        fontSize: '13px',
                        fontFamily: '"Nunito", sans-serif !important',
                        fontWeight: 400,
                        itemMargin: {
                            vertical: 10
                        },
                        formatter: function(seriesName, opts) {
                            return '<div class="legend-info">' + '<span>' + seriesName + '</span>' + '<span class="fw-bold">' + opts.w.globals.series[opts.seriesIndex] + '%</span>' + '</div>'
                        }
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    tooltip: {
                        enabled: false,
                    },
                    plotOptions: {
                        pie: {
                            size: "35%",
                            donut: {
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '22px',
                                        fontFamily: '"Nunito", sans-serif !important',
                                        fontWeight: 600,
                                        color: undefined,
                                        offsetY: -10,
                                        formatter: function (val) {
                                        return val
                                        }
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '32px',
                                        fontFamily: '"Nunito", sans-serif !important',
                                        fontWeight: 700,
                                        color: undefined,
                                        offsetY: 16,
                                        formatter: function (val) {
                                        return val
                                        }
                                    },
                                    total: {
                                        show: true,
                                        showAlways: true,
                                        label: 'Total',
                                        fontSize: '13px',
                                        fontFamily: '"Nunito", sans-serif !important',
                                        fontWeight: 600,
                                        color: '#373d3f',
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => {
                                            return a + b
                                            }, 0)
                                        }
                                    }
                                }
                            }
                        }
                    },
                },
            },
            selects: {
                lista_procesos: [],
            }
        }
    },
    mounted() {
        let vue = this
        vue.loadInfo();
    },
    methods: {
        loadInfo() {
            let vue = this
            const url = `/induccion/dashboard/info`
            vue.$http.get(url)
                .then(({data}) => {
                    console.log(data.data);
                    let _data = data.data
                    vue.process_progress = _data.process_progress
                    vue.process_total = _data.process_total
                    vue.process_bar = _data.process_bar
                    vue.users_active = _data.users_active
                    vue.users_total = _data.users_total
                    vue.users_bar = _data.users_bar
                    vue.selects.lista_procesos = _data.processes
                    if(_data.processes && _data.processes.length > 0){
                        vue.process = _data.processes[0].id
                        this.searchProcess()
                    }
                    vue.graphic_data_bars.series[0] = {'data': _data.data_graphic_bars}
                    setTimeout(() => {
                        vue.show_graphic_data_bars = true
                    }, 100);
                })
        },
        searchProcess() {
            let vue = this
            const url = `/induccion/dashboard/search/process/`+vue.process
            vue.$http.get(url)
                .then(({data}) => {
                    let _data = data.data
                    vue.show_graphic_data = false
                    if(_data.length > 0) {
                        vue.graphic_data.chartOptions.labels = []
                        vue.graphic_data.series = []
                        let labels = []
                        _data.forEach(element => {
                            labels.push(element.title)
                            vue.graphic_data.series.push(element.percentage)
                        });
                        vue.graphic_data.chartOptions.labels = labels
                        setTimeout(() => {
                            vue.show_graphic_data = true
                        }, 100);
                    }
                })
        },
        filterGraphicBar() {
            let vue = this

        }
    },
}
</script>
<style lang="scss">
.apexcharts-legend {
    max-width: 250px;
}
.apexcharts-legend-series {
    position: relative;
    span.apexcharts-legend-marker {
        height: 30px !important;
        width: 4px !important;
        position: absolute;
    }
    span.apexcharts-legend-text {
        padding-left: 15px;
        margin-left: 0;
        .legend-info {
            display: flex;
            flex-direction: column;
        }
    }
}
.progress_bar_card {
    width: 100%;
}
</style>
