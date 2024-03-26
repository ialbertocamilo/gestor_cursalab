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
                                    <div style="width: 80px;">
                                        <apexcharts type="radialBar" height="100" :options="graphic_data_advance.chartOptions" :series="graphic_data_advance.series"/>
                                    </div>
                                    <div>
                                        <span class="d-flex fw-bold mb-1" style="font-size: 24px;">70%</span>
                                        <span>Cumplimiento de inducción</span>
                                    </div>
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>
                    <v-col cols="4">
                        <v-card style="overflow: hidden;">
                            <v-card-text class="py-0">
                                <div class="d-flex align-center" style="height: 73px;">
                                    <div>
                                        <span class="d-flex fw-bold mb-1" style="font-size: 24px;">700/1200</span>
                                        <span>Cantidad de colaboradores</span>
                                    </div>
                                </div>
                            </v-card-text>
                            <div class="progress_bar_card">
                                <v-progress-linear
                                    :color="'#973DE7'"
                                    height="6"
                                    :value="67"
                                    rounded
                                >
                                </v-progress-linear>
                            </div>
                        </v-card>
                    </v-col>
                    <v-col cols="4">
                        <v-card style="overflow: hidden;">
                            <v-card-text class="py-0">
                                <div class="d-flex align-center" style="height: 73px;">
                                    <div>
                                        <span class="d-flex fw-bold mb-1" style="font-size: 24px;">70/150</span>
                                        <span>Procesos en curso</span>
                                    </div>
                                </div>
                            </v-card-text>
                            <div class="progress_bar_card">
                                <v-progress-linear
                                    :color="'#973DE7'"
                                    height="6"
                                    :value="28"
                                    rounded
                                >
                                </v-progress-linear>
                            </div>
                        </v-card>
                    </v-col>
                </v-row>
                <v-row justify="start">
                    <v-col cols="5">
                        <v-card>
                            <v-card-text>
                                <div>
                                    <span class="text_default fw-bold">Gráfico circular</span>
                                </div>
                                <div>
                                    <apexcharts
                                        height="450"
                                        :options="graphic_data.chartOptions"
                                        :series="graphic_data.series"
                                    />
                                </div>
                            </v-card-text>
                        </v-card>
                    </v-col>
                    <v-col cols="7">
                        <v-card>
                            <v-card-text>
                                <div>
                                    <span class="text_default fw-bold">Cumplimiento mensual de procesos</span>
                                </div>
                                <div style="height: 400px">
                                    <apexcharts
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
            dataTable: {
                endpoint: '/induccion/dashboard/search',
                ref: 'DashboardTable',
                headers: [
                    {text: "Supervisores", value: "name", align: 'start', sortable: true},
                    {text: "Departamento", value: "department", align: 'center', sortable: false},
                    {text: "Evaluador", value: "evaluador", align: 'center', sortable: false},
                    {text: "Puesto", value: "puesto", align: 'center', sortable: false},
                    {text: "Estado", value: "status", align: 'center', sortable: false},
                ],
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
                        categories: ['Ene', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dic'],
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
                series: [70],
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
                series: [15,20,25,40],
                chartOptions: {
                    chart: {
                        id: "vuechart-example",
                        type: "donut",
                        // width: 200,
                        height: 400,
                    },
                    colors: ['#161BB6', '#5458EA', '#E7CCFF', '#6C02CA' ],
                    labels: ["Bienvenido a nuestra empresa", "Orientación sobre políticas y procedimientos", "Etapa 3", "Etapa 4"],
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
                    // responsive: [
                    //     {
                    //         breakpoint: 300,
                    //         options: {
                    //             chart: {
                    //                 width: 200
                    //             },
                    //             legend: {
                    //                 position: "bottom"
                    //             }
                    //         }
                    //     }
                    // ]
                },
            },
        }
    }
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
