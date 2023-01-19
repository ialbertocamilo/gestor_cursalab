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
                <AsignacionXDni
                    description='Indica cuál usuario de la plataforma es un supervisor o sube una lista.'
                    apiSearchUser="/supervisores/search-usuarios"
                    apiUploadPlantilla="/supervisores/subir-excel-usuarios"
                    :showSubidaMasiva="true"
                    ref="AsignacionXDni"
                >
                </AsignacionXDni>
            </template>
        </DefaultDialog>
    </div>
</template>
<script>
import AsignacionXDni from './AsignacionXDni.vue';
export default {
    components:{AsignacionXDni},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            modalDeleteOptions: {
                ref: 'RegisterUrlDeleteModal',
                open: false,
                resource:'',
                base_endpoint: '/register_url',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '/register_url/destroy',
                resource_id:null,
                hideCancelBtn:false,
                hideConfirmBtn:false,
            },

        }
    },

    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        async confirmModal() {
            let vue = this;
            if(vue.$refs.AsignacionXDni.usuarios_ok.length==0){
                vue.$notification.warning('Tiene que seleccionar almenos 1 usuario para continuar.', {
                    timer: 8,
                    showLeftIcn: false,
                    showCloseIcn: true,
                });
                return false;
            }
            vue.showLoader();
            await axios.post('supervisores/set-usuarios-as-supervisor',vue.$refs.AsignacionXDni.usuarios_ok).then(()=>{
                vue.queryStatus("supervisores", "segmenta_usuarios");
                vue.hideLoader();

                vue.$notification.success('Se ha asignado los supervisores correctamente.', {
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
            let vue = this;
            let AsignacionXDni = vue.$refs.AsignacionXDni;
            if(AsignacionXDni){
                AsignacionXDni.autocomplete_loading= false;
                AsignacionXDni.file= null;
                AsignacionXDni.input_filtro_usuarios_ok= "";
                AsignacionXDni.loading_filtros_usuarios_ok= false;
                AsignacionXDni.usuarios_ok= [];
                AsignacionXDni.usuarios_error= [];
                AsignacionXDni.dialog_guardar= false;
                AsignacionXDni.search= null;
                AsignacionXDni.debounce= null;
                AsignacionXDni.filtro_result= [];
            }
        },
         resetValidation() {
            let vue = this
        },
        async loadData() {
            let vue = this;
            vue.resetSelects();
            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
