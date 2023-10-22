<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
    >
        <template v-slot:content>
            <v-row v-if="resource.segmentacion">
                <v-col cols="12">
                    <DefaultFormLabel
                        label="Detalle de segmentación"
                    />
                    <v-simple-table style="width: 100%" dense>
                        <template v-slot:default>
                            <thead>
                            <tr class="primary">
                                <th class="text-left text-white" v-text="'Módulo'"/>
                                <th class="text-left text-white" v-text="'Carreras'"/>
                            </tr>
                            </thead>
                            <!--                            <tbody v-if="resource.categorias.length === 0">-->
                            <!--                            <tr>-->
                            <!--                                <td colspan="2" class="text-center">-->
                            <!--                                    <p class="text-h7 font-weight-bold pt-4">No tiene cursos asignados</p>-->
                            <!--                                </td>-->
                            <!--                            </tr>-->
                            <!--                            </tbody>-->
                            <tbody>
                            <tr class="text-left my-1" v-for="(modulo, index) in resource.segmentacion" :key="index">
                                <td class="py-4" v-text="modulo.modulo_nombre"></td>
                                <td v-if="modulo.carreras.length > 0">
                                    <v-chip
                                        small
                                        class="m-1"
                                        color="#C0C1ED"
                                        v-for="(carrera, index3) in modulo.carreras"
                                        v-text="carrera.carrera_nombre"
                                        :key="index3"/>
                                </td>
                                <td v-else>
                                    <v-chip
                                        small
                                        class="m-1"
                                        color="#C0C1ED"
                                        v-text="`Todos seleccionados`"
                                    />
                                </td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-col>
            </v-row>
            <DefaultDivider/>
            <v-row>
                <v-col cols="12">
                    <DefaultFormLabel
                        label="Detalle de estados"
                    />
                    <v-row>
                        <v-col v-if="resource.resumen_estado" cols="5">
                            <p class="mb-0">Resumen de estados (sobre el total):</p>
                            <p class="mb-0"
                               v-text="`Alcanzados = ${resource.resumen_estado.alcanzados || 'Data no encontrada.'}`">
                                Alcanzados = 50</p>
                            <p class="mb-0"
                               v-text="`No Alcanzados = ${resource.resumen_estado.no_alcanzados || 'Data no encontrada.'}`"></p>
                            <p class="mb-0"
                               v-text="`Pendientes = ${resource.resumen_estado.pendientes || 'Data no encontrada.'}`"></p>
                            <p class="mb-0"
                               v-text="`Objetivo = ${resource.resumen_estado.objetivo || 'Data no encontrada.'}`"></p>
                        </v-col>
                        <v-col cols="7" class="d-flex flex-column">
                            <!--
                                                        <GeneralGraphic
                                                            :graphic_data="grafico.envios"
                                                        /> -->

                        </v-col>
                    </v-row>

                </v-col>
            </v-row>
            <DefaultDivider/>
            <v-row>
                <v-col cols="12">
                    <DefaultFormLabel
                        label="Detalle de envío"
                    />
                    <v-simple-table style="width: 100%" dense>
                        <template v-slot:default>
                            <thead>
                            <tr class="primary">
                                <th class="text-center text-white" v-text="'Lote'"/>
                                <th class="text-left text-white" v-text="'Horario (aprox.)'"/>
                                <th class="text-center text-white" v-text="'Estado'"/>
                                <th class="text-center text-white" v-html="'Objetivo'"/>
                                <!-- <th class="text-center text-white" v-html="'Usuarios <br> alcanzados'"/>
                                <th class="text-center text-white" v-html="'Usuarios <br>no alcanzados'"/>
                                <th class="text-center text-white" v-text="'Efectividad'"/> -->
                            </tr>
                            </thead>
                            <!--                            <tbody v-if="resource.categorias.length === 0">-->
                            <!--                            <tr>-->
                            <!--                                <td colspan="2" class="text-center">-->
                            <!--                                    <p class="text-h7 font-weight-bold pt-4">No tiene cursos asignados</p>-->
                            <!--                                </td>-->
                            <!--                            </tr>-->
                            <!--                            </tbody>-->
                            <tbody>
                            <tr class="text-left my-1" v-for="(lote, index) in resource.lotes" :key="index">
                                <td class="text-center">{{ index + 1 }}</td>
                                <td class="text-left" v-text="lote.datetime"></td>
                                <td class="text-center" v-text="lote.estado"></td>
                                <td class="text-center" v-text="lote.quantity"></td>
                                <!-- <td class="text-center">9 de 10</td>
                                <td class="text-center">1</td>
                                <td class="text-center">
                                    <DefaultStaticProgressLinear
                                        :text="`90%`"
                                    />
                                </td> -->
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-col>
            </v-row>
        </template>
    </DefaultDialog>
</template>
<script>
import DefaultStaticProgressLinear from "../../components/globals/DefaultStaticProgressLinear";

import GeneralGraphic from "../General/GeneralGraphic";

export default {
    components: {DefaultStaticProgressLinear, GeneralGraphic},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String

    },
    data() {
        return {
            // grafico: {
            //     envios: {
            //         overlay: false,
            //         ref: 'envios',
            //         last_update: null,
            //         series: [{
            //             name: "Detalle de envío",
            //             data: [],
            //         }],
            //         chartOptions: {
            //             plotOptions: {
            //                 hideOverflowingLabels: true
            //             },
            //             title: {
            //                 text: 'DETALLE DE ENVÍO',
            //                 style: {
            //                     fontWeight: 'bold',
            //                     fontFamily: 'Tahoma',
            //                 },
            //             },
            //             subtitle: {
            //                 text: '---------',
            //                 style: {
            //                     fontFamily: 'Tahoma',
            //                 },
            //             },
            //             chart: {
            //                 id: 'envios',
            //                 type: 'bar',
            //                 width: "100%",
            //                 toolbar: {
            //                     show : true,
            //                     tools: {
            //                         download: false,
            //                         selection: true,
            //                         zoom: true,
            //                         zoomin: true,
            //                         zoomout: true,
            //                         pan: false,
            //                         reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
            //                     },
            //                 },
            //             },
            //             xaxis: {
            //                 categories: [],
            //             },
            //         },
            //     }
            // }
            resourceDefault: {
                id: 0,
                segmentacion: [],
                resumen_estado: {
                    alcanzados: 0,
                    no_alcanzados: 0,
                    pendientes: 0,
                    objetivo: 0
                },
                lotes: []
            },
            resource: {},
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.resetFormValidation('ModuloForm')
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            console.log(resource.id, resource);
            let url = `${vue.options.base_endpoint}/detalle/${resource.id}`
            await vue.$http.get(url).then(({data}) => {
                vue.resource = data;
                console.log(data);
            })

            // // vue.grafico.envios.series.data =  [44, 55, 13, 33]
            // // vue.grafico.envios.chartOptions.xaxis.categories = ['Apple', 'Mango', 'Orange', 'Watermelon']

            // return 0;
        },
        loadSelects() {
            let vue = this

        },
    }
}
</script>
