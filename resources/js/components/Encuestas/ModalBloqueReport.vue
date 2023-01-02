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
        <v-card-title>
        <span class="mb-4">Debido a la gran cantidad de datos solicitados, tu reporte se está generando por bloques.<b>
            Por favor no cierres esta ventana hasta completar la carga.
        </b></span>
        <DefaultSimpleTable class="mb-4">
            <template slot="content">
                <thead>
                <tr>
                    <th>Bloques</th>
                    <th>Estado</th>
                </tr>
                </thead>
                <tbody>
                <tr
                    v-for="(donwload, index) in download_list"
                    :key="index"
                >
                    <td class="w-70" >
                        <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                            <span 
                                v-bind="attrs"
                                v-on="on" 
                                v-text="`Bloque ${index+1}: ${donwload.content}`">
                            </span>
                        </template>
                        <span v-text="donwload.schools"></span>
                        </v-tooltip>
                    </td>
                    <td class="w-10" v-if="donwload.status=='pending'">
                        <span class="ml-1">Descargando..</span> 
                    </td>
                    <td class="w-10" v-if="donwload.status=='complete'">
                        <DefaultButton
                            label="Descargar"
                            @click="saveReport(donwload)"
                        />
                    </td>
                    <td class="w-10" v-if="donwload.status=='no_data'">
                        <span class="ml-1">No se encontraron datos en este bloque.</span>
                    </td>
                </tr>
                </tbody>
            </template>
        </DefaultSimpleTable>
        </v-card-title>
    </template>
    </DefaultDialog>
</template>
<script>
    export default {
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