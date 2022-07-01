<template>
    <section style="padding: 20px 20px">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Supervisores
                <v-spacer/>
                <default-button :label="'Subida Masiva'" text
                            @click="openFormModal(modalOptions,null,null,modalOptions.title)"/>
                <supervisor-carga-masiva-modal width="50vw"
                    :ref="modalOptions.ref"
                    :options="modalOptions"
                    @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                    @onCancel="closeFormModal(modalOptions)"
                />
                <v-btn color="primary" class="white--text" @click="store_dialog = !store_dialog">
                            <v-icon>mdi-plus</v-icon>
                            Asignar Supervisor
                        </v-btn>
            </v-card-title>
        </v-card>
        <v-card flat elevation="0">
            <v-card-text>
                <v-row>
                    <v-col cols="3">
                        <v-select
                            attach=""
                            hide-details="auto"
                            dense outlined label="Módulo"
                            :items="selects.modulos"
                            v-model="filtros.modulo"
                            @change="getAreaFiltro()"
                            clearable
                        />
                        <div v-if="loaders.dialog_modulo" style="position: absolute; top: 1rem; left: 1rem;width: 100%;display: flex;justify-content: center;">
                            <v-progress-circular indeterminate :value="20"></v-progress-circular>
                        </div>
                    </v-col>
                    <v-col cols="3">
                        <v-autocomplete
                            attach=""
                            hide-details="auto"
                            dense outlined label="Áreas asignadas"
                            no-data-text="Seleccione un módulo"
                            :items="selects.areas"
                            v-model="filtros.area"
                            @change="getData(1)"
                            clearable
                        />
                        <div v-if="loaders.filtro_area" style="position: absolute; top: 1rem; left: 1rem;width: 100%;display: flex;justify-content: center;">
                            <v-progress-circular indeterminate :value="20"></v-progress-circular>
                        </div>
                    </v-col>
                    <v-col cols="3">
                        <v-text-field
                            clearable
                            attach
                            hide-details="auto"
                            dense outlined
                            label="DNI o nombre de usuario"
                            v-model="filtros.usuario"
                            @keypress.enter="getData(1)"
                            @click:clear="getData(1,'clear')"
                        />
                    </v-col>
                    <v-col cols="3" class="text-right">
                    </v-col>
                </v-row>
            </v-card-text>
            <!--        </v-card>-->
            <!--        <v-card flat elevation="0">-->
            <v-card-text>
                <v-row no-gutters>
                    <v-col cols="3" v-show="false">
                        <v-btn color="#28314F" class="white--text">
                            <v-icon>mdi-plus</v-icon>
                            Subida masiva
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
        <v-card flat elevation="0">
            <v-card-text>
                <v-simple-table>
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th class="text-left" v-text="'Módulo'"/>
                            <th class="text-left" v-text="'Área asignada'"/>
                            <th class="text-left" v-text="'Supervisor'"/>
                            <th class="text-center" v-text="'Opciones'"/>
                        </tr>
                        </thead>
                        <tbody>
                        <tr
                            v-if="supervisores.length === 0"
                        >
                            <td colspan="4" class="text-center">No hay datos para mostrar</td>
                        </tr>
                        <tr
                            v-else
                            v-for="supervisor in supervisores"
                            :key="supervisor.id"
                        >
                            <td>{{ supervisor.modulo }}</td>
                            <td>{{ supervisor.area }}</td>
                            <td>{{ supervisor.nombre }}</td>
                            <td class="text-center">
                                <v-btn icon @click="deleteSupervisor(supervisor)">
                                    <v-icon color="red">mdi-trash-can</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
                <div class="pt-4">
                    <v-pagination
                        color="primary"
                        :total-visible="7"
                        @input="cambiar_pagina"
                        v-model="paginate.page"
                        :length="paginate.total_paginas"
                    ></v-pagination>
                </div>
            </v-card-text>
        </v-card>
        <DialogConfirm
            v-model="deleteDialog"
            width="450px"
            title="Eliminar Supervisor"
            subtitle="¿Está seguro de remover al supervisor?"
            @onConfirm="confirmDeleteSupervisor"
            @onCancel="deleteDialog = false"
        />

        <v-dialog v-model="store_dialog" @click:outside="store_dialog=false" width="1000px" scrollable>
            <v-card>
                <v-card-title>
                    Asignar supervisor
                </v-card-title>
                <v-card-text style="min-height: 500px;">
                    <v-form>
                        <v-row justify="center">
                            <v-col cols="11">
                                <v-select
                                    attach=""
                                    hide-details="auto"
                                    dense outlined label="Módulo"
                                    v-model="filtros.dialog_modulo"
                                    :items="selects.modulos"
                                    return-object
                                    @change="getAreaDialog()"
                                />
                                <div v-if="loaders.dialog_modulo" style="position: absolute; top: 1rem; left: 1rem;width: 100%;display: flex;justify-content: center;">
                                    <v-progress-circular indeterminate :value="20"></v-progress-circular>
                                </div>
                            </v-col>
                            <v-col cols="11">
                                <v-autocomplete
                                    attach=""
                                    hide-details="auto"
                                    dense outlined label="Área a asignar"
                                    :menu-props="{ auto: true, overflowY: true, maxHeight: 100 }"
                                    :items="selects.dialog_areas"
                                    v-model="filtros.dialog_area"
                                    return-object
                                    :disabled="!filtros.dialog_modulo"
                                />
                                <div v-if="loaders.dialog_area" style="position: absolute; top: 1rem; left: 1rem;width: 100%;display: flex;justify-content: center;">
                                    <v-progress-circular indeterminate :value="20"></v-progress-circular>
                                </div>
                            </v-col>
                            <v-col cols="11">
                                <v-autocomplete
                                    attach
                                    :search-input.sync="get_usuarios"
                                    hide-details="auto"
                                    :loading="isLoading"
                                    dense
                                    outlined
                                    label="DNI o nombre del supervisor"
                                    :items="usuarios"
                                    return-object
                                    no-data-text="No hay usuarios encontrados"
                                    :disabled="!filtros.dialog_area"
                                    v-model="dialog_supervisor"
                                >
                                    <template v-slot:selection="{ item}">
                                        <v-chip
                                            style="font-size: 0.9rem !important; color: white !important"
                                            color="primary"
                                            small
                                        >
                                            {{ item.text }}
                                        </v-chip>
                                    </template>
                                    <template v-slot:item="data">
                                        <v-list-item-content @click="addUsuario(data)">
                                            <v-container
                                                class="px-0 align d-flex justify-content-start align-center"
                                                fluid
                                                style="min-height: 10px; max-height: 40px"
                                            >
                                               <v-btn
                                                    icon
                                                    @click="addUsuario(data)"
                                                >
                                                    <v-icon>mdi-plus</v-icon>
                                                </v-btn>
                                                <div>
                                                    <v-list-item-title
                                                        v-html="data.item.text"/>
                                                    <v-list-item-subtitle
                                                        class="list-cursos-carreras"
                                                        v-html="data.item.cargo"/>
                                                </div>
                                            </v-container>
                                        </v-list-item-content>
                                    </template>
                                </v-autocomplete>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-card-title>
                            Resultado de la selección
                            </v-card-title>
                            <v-col cols="12">
                                <v-simple-table
                                    fixed-header
                                    :height="filtros.dialog_usuarios.length === 0 ? '100px' :
                                                filtros.dialog_usuarios.length > 10 ? '480px' :
                                                (filtros.dialog_usuarios.length*48 +48)">
                                    <template v-slot:default>
                                        <thead>
                                        <tr>

                                            <th class="text-left" v-text="'Módulo'"/>
                                            <th class="text-left" v-text="'Área'"/>
                                            <th class="text-left" v-text="'Supervisor'"/>
                                            <th class="text-left" v-text="'Opción'"/>
                                            <!--                                            <th class="text-center" v-text="'Seleccionar'"/> -->
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr
                                            v-if="filtros.dialog_usuarios.length === 0"
                                        >
                                            <td colspan="4" class="text-center">No hay datos para mostrar</td>
                                        </tr>
                                        <tr
                                            v-else
                                            v-for="usuario in filtros.dialog_usuarios"
                                            :key="usuario.id"
                                        >
                                            <td>{{ usuario.config.text }}</td>
                                            <td>{{ usuario.grupo.text }}</td>
                                            <td>
                                                {{ usuario.text }} <br>
                                                ({{ usuario.cargo }})
                                            </td>
                                             <td class="text-center">
                                                <v-btn icon @click="deleteRelation(usuario.id)">
                                                    <v-icon color="red">mdi-trash-can</v-icon>
                                                </v-btn>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </template>
                                </v-simple-table>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
                <v-card-actions class="d-flex justify-content-center">
                    <v-btn outlined @click="closeDialog">Cancelar</v-btn>
                    <v-btn class="white--text" color="#28314F" @click="saveSupervisor">Guardar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </section>
</template>
<script>
import DialogConfirm from "../components/basicos/DialogConfirm";
import SupervisorCargaMasivaModal from "../components/Supervisores/SupervisorCargaMasivaModal.vue";

export default {
    components: {DialogConfirm,SupervisorCargaMasivaModal},
    data: () => ({
        base_endpoint: '/reportes-supervisores',
        isLoading: false,
        loaders:{
            dialog_area:false,
            dialog_modulo:false,
            filtro_area:false,
        },
        search: null,
        deleteDialog: false,
        selects: {
            modulos: [],
            areas: [],
            dialog_areas: []
        },
        filtros: {
            modulo: null,
            area: null,
            dialog_modulo: null,
            dialog_area: null,
            usuario: null,
            dialog_usuarios: [],
            listado_usuarios:[],
            sel_usuario: null,
            del_usuario: null,
        },
        supervisores: [],
        usuarios: [],
        store_dialog: false,
        paginate: {page: 1, total_paginas: 1},
        isLoading: false,
        get_usuarios: null,
        debounce:null,
        dialog_supervisor:null,
        modalOptions: {
            ref: 'SupervisorCargaMasivaModal',
            open: false,
            base_endpoint: '/cargos',
            resource: '',
            confirmLabel: 'Guardar',
            title:'Subida Masiva de Supervisores',
            hideCancelBtn:true,
            hideConfirmBtn:true,
        },
    }),
    mounted() {
        this.getData(1)
        this.getSelects()
    },
    watch: {
        get_usuarios (val) {
            let vue = this;
            if (this.isLoading) return
            if (val=='' || val==' ') return
            console.log(val);
            clearTimeout(this.debounce);
			this.debounce = setTimeout(() => {
                this.isLoading = true
                vue.usuarios = []
                let url = `${vue.base_endpoint}/get-usuarios`
                axios.post(url,{
                    confid_id:vue.filtros.dialog_modulo.value,
                    q : val
                }).then(({data},) => {
                    vue.usuarios = data.usuarios
                    // vue.filtros.dialog_usuarios = data.usuarios_seleccionados
                }).catch(err => {
                    console.log(err)
                }).finally(() => (this.isLoading = false))

            },1200)
        },
    },
    methods: {
        cambiar_pagina(page) {
            let vue = this;
            vue.getData(page);
        },
        getData(page = null,clear_data=null) {
            let vue = this
            if (page)
                vue.paginate.page = page
            console.log(vue.filtros.usuario);
            let url = `${vue.base_endpoint}/search?page=${vue.paginate.page}`
            if (vue.filtros.modulo) url += `&modulo=${vue.filtros.modulo}`
            if (vue.filtros.area) url += `&area=${vue.filtros.area}`
            if (vue.filtros.usuario && clear_data!='clear') url += `&q=${vue.filtros.usuario}`
            axios.get(url)
                .then(({data}) => {
                    vue.supervisores = data.supervisores.data
                    vue.paginate.total_paginas = data.supervisores.last_page;

                })
        },
        async getSelects() {
            let vue = this
            let url = `${vue.base_endpoint}/get-list-selects`
            vue.loaders.dialog_modulo= true;
            await axios.get(url)
                .then(({data}) => {
                    vue.selects.modulos = data.modulos
                    vue.loaders.dialog_modulo= false;
                })
        },
        resetInputDialog() {
            let vue = this
            vue.filtros.dialog_modulo = null
            vue.filtros.dialog_area = null
            vue.filtros.dialog_usuarios = []
            vue.usuarios = []
            vue.isLoading = false;
            vue.loaders.filtro_area = false;
            vue.loaders.dialog_modulo = false;
            vue.dialog_supervisor = null;
        },
        closeDialog() {
            let vue = this
            vue.resetInputDialog()
            vue.store_dialog = false
        },
        async getAreaFiltro() {
            let vue = this
            vue.getData(1)
            vue.filtros.area = null
            vue.loaders.filtro_area = true;
            vue.filtros.txt_usuario = null
            let url = `${vue.base_endpoint}/get-area-modulo/${vue.filtros.modulo}/only_selected`
            await axios.get(url)
                .then(({data}) => {
                    vue.loaders.filtro_area = false;
                    vue.selects.areas = data.areas;
                }).catch(()=>{
                     vue.selects.areas = [];
                    vue.loaders.filtro_area = false;
                })
        },
        async getAreaDialog() {
            let vue = this
            vue.getData(1)
            vue.filtros.dialog_area = null;
            vue.selects.dialog_areas = [];
            vue.usuarios = [];
            let url = `${vue.base_endpoint}/get-area-modulo/${vue.filtros.dialog_modulo.value}/all`
            vue.loaders.dialog_area = true;
            await axios.get(url)
                .then(({data}) => {
                    vue.selects.dialog_areas = data.areas
                    vue.loaders.dialog_area = false;
                })
        },
        addUsuario(data){
            let idx = this.filtros.dialog_usuarios.findIndex((e)=>e.value === data.item.value);
            console.log(data);
            if(idx>-1){
                return false;
            }
            let insert = {
                value:data.item.value,
                config: this.filtros.dialog_modulo,
                grupo :  this.filtros.dialog_area ,
                text : data.item.text,
                cargo :data.item.cargo
            };
            console.log(insert);
            this.filtros.dialog_usuarios.push(insert);
            this.filtros.listado_usuarios.push(insert);
        },
        deleteRelation(usuario_id){
            let idx = this.filtros.dialog_usuarios.findIndex((e)=>e.value === usuario_id);
            (idx !=null) && ( this.filtros.dialog_usuarios.splice(idx,1));
            let idx2 = this.filtros.listado_usuarios.findIndex((e)=>e.value === usuario_id);
            (idx2 !=null) && ( this.filtros.listado_usuarios.splice(idx2,1));
        },
        deleteSupervisor(usuario) {
            let vue = this
            vue.filtros.del_usuario = usuario
            vue.deleteDialog = true
        },
        saveSupervisor() {
            let vue = this
            let url = `${vue.base_endpoint}/store-supervisor`
            const data = {
                usuarios: vue.filtros.dialog_usuarios,
                criterio_id: vue.filtros.dialog_area,
            }

            axios.post(url, data)
                .then(({data}) => {
                    vue.$notification.success(`${data.msg}`, {
                        timer: 6,
                        showLeftIcn: false,
                        showCloseIcn: true
                    });
                    vue.closeDialog()
                    vue.getData(1)
                })
        },
        confirmDeleteSupervisor() {
            let vue = this
            let url = `${vue.base_endpoint}/delete-supervisor`
            const data = {
                usuario_id: vue.filtros.del_usuario.usuario_id,
                criterio_id: vue.filtros.del_usuario.area_id,
            }

            axios.post(url, data)
                .then(({data}) => {
                    vue.$notification.success(`${data.msg}`, {
                        timer: 6,
                        showLeftIcn: false,
                        showCloseIcn: true
                    });
                    vue.filtros.del_usuario = null
                    vue.deleteDialog = false
                    vue.getData(1)
                })
        }
    }
}
</script>
<style>
.v-application--wrap {
    min-height: 0 !important;
}
</style>
