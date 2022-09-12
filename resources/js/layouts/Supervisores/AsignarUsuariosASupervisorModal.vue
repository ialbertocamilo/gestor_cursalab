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
                    description='Indica que usuarios serán supervisados.'
                    apiSearchUser="/supervisores/search-usuarios"
                    apiUploadPlantilla="/supervisores/subir-excel-usuarios"
                    :showSubidaMasiva="true"
                    ref="AsignacionUsuarioSupervisor"
                >
                </AsignacionXDni>
            </template>
        </DefaultDialog>
    </div>
</template>
<script>
import AsignacionXDni from './AsignacionXDni.vue';

export default {
    components: {AsignacionXDni},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            resourceDefault: {
                id: null,
            },
            resource: {},
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        async confirmModal() {
            let vue = this;
            vue.showLoader();
            const data = {
                resources: vue.$refs.AsignacionUsuarioSupervisor.usuarios_ok,
                supervisor: vue.resource.id,
                type: 'dni'
            }

            await axios.post('supervisores/set-data-supervisor', data).then(() => {
                vue.hideLoader();
                vue.$notification.success('Se ha asignado correctamente los usuarios.', {
                    timer: 15,
                    showLeftIcn: false,
                    showCloseIcn: true,
                });
                vue.resetSelects();
                vue.$emit('onConfirm')
            }).catch(() => {
                alert('Hubo un error al procesar la data.');
                vue.hideLoader();
            })
        },
        resetSelects() {
            let vue = this;
            let AsignacionUsuarioSupervisor = vue.$refs.AsignacionUsuarioSupervisor;
            if (AsignacionUsuarioSupervisor) {
                AsignacionUsuarioSupervisor.autocomplete_loading = false;
                AsignacionUsuarioSupervisor.file = null;
                AsignacionUsuarioSupervisor.input_filtro_usuarios_ok = "";
                AsignacionUsuarioSupervisor.loading_filtros_usuarios_ok = false;
                AsignacionUsuarioSupervisor.usuarios_ok = [];
                AsignacionUsuarioSupervisor.usuarios_error = [];
                AsignacionUsuarioSupervisor.dialog_guardar = false;
                AsignacionUsuarioSupervisor.search = null;
                AsignacionUsuarioSupervisor.debounce = null;
                AsignacionUsuarioSupervisor.filtro_result = [];
            }
        },
        resetValidation() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this;
            vue.resetSelects();
            vue.$nextTick(() => {
                vue.resource = resource;
            })
            await vue.$http.get(`supervisores/get-data/${resource.id}/dni`)
                .then((e) => {
                    vue.$refs.AsignacionUsuarioSupervisor.usuarios_ok = e.data.data;
                })
            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
