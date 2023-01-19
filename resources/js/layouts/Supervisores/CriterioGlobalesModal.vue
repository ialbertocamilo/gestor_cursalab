<template>
     <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="changeLabel('cancel')"
        @onConfirm="changeLabel('confirm')"
        :showCardActions="options.showCardActions"
        :eventCloseModalFromIcon="'closeModalFromIcon'"
        @closeModalFromIcon="closeModal"
    >
        <template v-slot:content>
            <v-stepper v-model="step" elevation="0">
                <v-stepper-header>
                    <v-stepper-step step="1">
                        Selecciona Supervisores
                    </v-stepper-step>
                    <v-divider></v-divider>
                    <v-stepper-step step="2">
                        Selecciona los criterios
                    </v-stepper-step>
                </v-stepper-header>
                <v-stepper-content step="1">
                    <AsignacionXDni
                        description='Elige a los supervisores.'
                        apiSearchUser="/supervisores/search-usuarios"
                        apiUploadPlantilla="/supervisores/subir-excel-usuarios"
                        :showSubidaMasiva="false"
                        ref="AsignacionCriteriosGlobales"
                        :load_data_default="true"
                    >
                    </AsignacionXDni>
                </v-stepper-content>
                <v-stepper-content step="2">
                    <v-row class="mb-4">
                        Relaciona los supervisores y usuarios mediante criterios
                    </v-row>
                    <v-row>
                        <v-col cols="12">
                            <DefaultAutocomplete
                                :items="select.tipo_criterios"
                                v-model="tipo_criterios"
                                placeholder="Tipo de criterios"
                                multiple
                                :count-show-values="7"
                            />
                        </v-col>
                    </v-row>
                    <v-row justify="space-around">
                        <img src="/img/segmentacion.jpg" width="350" class="my-7">
                    </v-row>
                </v-stepper-content>
            </v-stepper>
        </template>
    </DefaultDialog>
</template>
<script>
import AsignacionXDni from './AsignacionXDni';
export default {
    components:{AsignacionXDni},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data(){
        return {
            select:{
                tipo_criterios:[]
            },
            tipo_criterios:[],
            step:1
        }
    },
    methods: {
        closeModal(){
            let vue = this;
            vue.$emit('onCancel');
        },
        async changeLabel(from){
            let vue = this;
            switch (from) {
                case 'cancel':
                     if(vue.step==1){
                        vue.step = 2;
                        vue.$emit('onCancel',vue.step);
                    }else{
                        vue.step = 1;
                        vue.$emit('onCancel',vue.step);
                    }
                break;
                case 'confirm':
                    if(vue.step==1){
                        if(vue.$refs.AsignacionCriteriosGlobales.usuarios_ok.length==0){
                            vue.$notification.warning('Tiene que seleccionar almenos 1 usuario para continuar.', {
                                timer: 8,
                                showLeftIcn: false,
                                showCloseIcn: true,
                            });
                            break;
                        }
                        vue.step = 2;
                        vue.$emit('onConfirm',vue.step);
                    }else{
                        if(vue.tipo_criterios.length==0){
                            vue.$notification.warning('Tiene que seleccionar almenos 1 criterio para guardar.', {
                                timer: 8,
                                showLeftIcn: false,
                                showCloseIcn: true,
                            });
                            break;
                        }
                        let data = {
                            'supervisores':vue.$refs.AsignacionCriteriosGlobales.usuarios_ok,
                            'tipo_criterios':vue.tipo_criterios
                        };
                        vue.showLoader();
                        await axios.post('supervisores/set-criterio-globales-supervisor',data)
                            .then(({data})=>{
                                const _data = data.data.data;
                                vue.queryStatus("supervisores", "segmenta_criterios");
                                vue.hideLoader();

                                const msg1 = 'Se ha asignado los criterios a los supervisores correctamente.';
                                vue.showAlert(msg1);

                                if (_data.errors.length > 0){
                                    const msg2 = `(${_data.errors.length}) usuarios no tienen ninguno de los criterios seleccionados`;
                                    vue.showAlert(msg2, 'warning');
                                }

                                vue.resetSelects();
                                vue.$emit('onConfirm',3)
                            }).catch(()=>{
                                alert('Hubo un error al procesar la data.');
                                vue.hideLoader();
                            })
                    }
                break;
            }

        },
        confirmModal() {
            let vue = this;
             vue.$emit('onConfirm')
        },
        resetSelects() {
            let vue = this
             let AsignacionCriteriosGlobales = vue.$refs.AsignacionCriteriosGlobales;
             if(AsignacionCriteriosGlobales){
                 AsignacionCriteriosGlobales.autocomplete_loading= false;
                 AsignacionCriteriosGlobales.file= null;
                 AsignacionCriteriosGlobales.input_filtro_usuarios_ok= "";
                 AsignacionCriteriosGlobales.loading_filtros_usuarios_ok= false;
                 AsignacionCriteriosGlobales.usuarios_ok= [];
                 AsignacionCriteriosGlobales.usuarios_error= [];
                 AsignacionCriteriosGlobales.dialog_guardar= false;
                 AsignacionCriteriosGlobales.search= null;
                 AsignacionCriteriosGlobales.debounce= null;
                 AsignacionCriteriosGlobales.filtro_result= [];
                 vue.tipo_criterios=[];
                 AsignacionCriteriosGlobales.search='';
                 vue.step =1;
             }
        },
         resetValidation() {
            let vue = this;
        },
        async loadData() {
            let vue = this;
            vue.step=1;
            vue.$emit('onCancel',vue.step);
            vue.resetSelects();
            await axios.get(`supervisores/tipo-criterios`).then((e)=>{
                vue.select.tipo_criterios = e.data.data;
            })
            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
<style>
.notificationCenter{
    z-index: 300 !important;
}
</style>
