<template>
    <div>
        <DefaultDialog
            :options="options"
            :width="width"
            @onCancel="closeModal"
            @onConfirm="confirmModal"
            :showCardActions="options.showCardActions"
        >
            <template v-slot:content>
                <v-row class="mb-4">
                    Relaciona los supervisores y usuarios mediante criterios
                </v-row>
                <v-row>
                    <div class="container-autocomplete">
                        <DefaultAutocomplete
                            :items="select.tipo_criterios" 
                            v-model="tipo_criterios" 
                            placeholder="Tipo de criterios"
                            multiple
                            :count-show-values="3"
                        />
                    </div>
                </v-row>
                <v-row justify="space-around">
                    <img src="/img/segmentacion.jpg" width="350" class="my-7">
                </v-row>
            </template>
        </DefaultDialog>
    </div>
</template>
<script>
export default {
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
                tipo_criterios:[],
            },
            tipo_criterios:null,
            resourceDefault: {
                id: null,
            },
            resource: {},
        }
    },
    methods:{
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        async confirmModal() {
            let vue = this;
            vue.showLoader();
            const data ={
                resources:vue.tipo_criterios,
                supervisor:vue.resource.id,
                type:'criterios'
            }
            
            await axios.post('supervisores/set-data-supervisor',data).then(()=>{
                vue.hideLoader();
                vue.$notification.success('Se ha asignado los criterios correctamente.', {
                    timer: 15,
                    showLeftIcn: false,
                    showCloseIcn: true,
                });
                vue.resetSelects();
                vue.$emit('onConfirm')
            }).catch(()=>{
                alert('Hubo un error al procesar la data.');
                vue.hideLoader();
            })
        },
        resetSelects() {
            let vue = this
            vue.tipo_criterios=null;
        },
         resetValidation() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this;
            vue.$nextTick(() => {
                vue.resource = resource;
            })
            await axios.get(`supervisores/tipo-criterios`).then((e)=>{
                vue.select.tipo_criterios = e.data.data;
            })
            await axios.get(`supervisores/get-data/${resource.id}/criterios`).then((e)=>{
                vue.tipo_criterios = e.data.data;
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
.container-autocomplete{
    border: 1px solid #DADAED;
    width: 100%;
    height: fit-content;
}
</style>