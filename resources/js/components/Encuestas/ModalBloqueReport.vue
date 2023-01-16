<template>
    <DefaultDialog :options="modalOptions"
        @onCancel="()=>{}"
        @onConfirm="closeModal()"
        :showCardActions="modalOptions.showCardActions"
        :width="'70vw'"
        :noPaddingCardText="true"
        v-if="download_list.length>0"
    >
    <template v-slot:content>
        <v-card-subtitle>
            <span class="mb-4" style="color: rgba(51, 61, 93, 0.6);">Debido a la gran cantidad de datos solicitados, tu reporte se está generando por bloques.<b>
                Por favor no cierres esta ventana hasta completar la carga.
            </b></span>
        </v-card-subtitle>
        <InfoTable :headers="['Bloques','Estado de descarga']">
            <template slot="content">
                <div class="row" v-for="(donwload, index) in download_list" :key="index">
                    <div class="col-sm-6 d-flex align-center" style="border: 1px solid #EDF1F4;">
                        <v-expansion-panels flat>
                            <v-expansion-panel>
                                <v-expansion-panel-header>
                                    <span style="color:#333D5D">
                                        {{ `Bloque ${index+1}: Se están analizando` }} <b>{{ donwload.content}} escuela(s)</b>
                                    </span>
                                </v-expansion-panel-header>
                                <v-expansion-panel-content>
                                    <v-simple-table>
                                        <template v-slot:default>
                                            <tbody>
                                                <tr
                                                    v-for="(school, index) in donwload.schools"
                                                    :key="index"
                                                >
                                                    <td class="w-100">{{ school }}</td>
                                                </tr>
                                            </tbody>
                                        </template>
                                    </v-simple-table>
                                </v-expansion-panel-content>
                            </v-expansion-panel>
                        </v-expansion-panels>
                    </div>
                    <div class="col-sm-6" style="border: 1px solid #EDF1F4;">
                        <div class="mt-3 w-10" v-if="donwload.status=='pending'">
                            <span style="color:#5458EA">Pendiente</span> 
                        </div>
                        <div class="mt-3 w-10 d-flex" v-if="donwload.status=='processing'">
                            <span style="color:#5458EA">Descargando</span>
                            <v-icon class="ml-2" color="#5458EA">fas fa-circle-notch fa-spin</v-icon> 
                        </div>
                        <div class="mt-3 w-10 d-flex justify-space-between align-center" v-if="donwload.status=='complete'">
                            <span style="color:#5458EA">
                                ¡Listo para descarga!
                                <v-icon color="#5458EA" small>mdi-checkbox-marked-circle</v-icon>
                            </span>
                            <v-icon 
                                color="#5458EA"
                                @click="saveReport(donwload)"
                              >
                                mdi-download
                            </v-icon>
                        </div>
                        <div class="mt-3 w-10" v-if="donwload.status=='no_data'">
                            <span style="color:#5458EA">Luego del análisis no se han encontrado resultados.</span>
                        </div>
                    </div>
                </div>
            </template>
        </InfoTable>
        <v-card-subtitle>
            <span class="mb-4" style="color: rgba(51, 61, 93, 0.6);">
                Luego de la descarga de este reporte podrás cerrar la ventana.
            </span>
        </v-card-subtitle>
    </template>
    </DefaultDialog>
</template>
<script>
    import InfoTable from './InfoTable.vue';
    export default {
        components:{InfoTable},
        props:{
            download_list:{
                type:Array,
                default:[]
            },
            modalOptions:{
                type:Object,
                default:{}
            }
        },
        methods:{
            saveReport(donwload){
                this.$emit('saveReport',donwload);
            },
            closeModal(){
                this.$emit('closeModal');
            }
        }
    }
</script>