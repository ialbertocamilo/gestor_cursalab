<template>
    <v-container >
        <v-card>
                <v-card>
                <v-row>
                    <v-spacer></v-spacer>
                    <seccionAyuda></seccionAyuda>
                </v-row>
                    <v-card-text>
                        <v-row>
                            <v-col class="d-flex align-center" cols="12" md="5">
                                <!-- Busqueda por dni,ticket,modulo,nombre,motivo -->
                                <v-text-field
                                    v-model="search"
                                    append-icon="mdi-magnify"
                                    label="Filtrar por # de ticket - Módulo - DNI - Nombre"
                                    outline
                                    hide-details
                                    @keyup.enter="info_page.currency_page=1;getData()"
                                ></v-text-field>
                            </v-col>
                            <v-col class="d-flex align-center" cols="12" md="2">
                                <v-select
                                    :items="estados"
                                    label="Estado"
                                    hide-details
                                    single-line
                                    v-model='v_estado'
                                    @change="info_page.currency_page=1;getData()"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="5">
                                <FechaFiltro class="d-flex justify-space-around " ref="FechasFiltros"  @update-start="getData"  @update-end="getData" />
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            <!-- :search="search" -->
            <v-data-table
                class="mt-4"
                height="400"
                fixed-header
                :headers="headers"
                :items="mensajes"
                :items-per-page="items_per_page"
                hide-default-footer
                :loading="loading_table"
            >
                <template v-slot:[`item.ticket`]="{ item }">
                    <span v-text="item.ticket"></span>
                </template>
                <template v-slot:[`item.estado`]="{ item }">
                    <v-chip
                        :disabled="(item.estado=='solucionado')"
                        :color="getColor(item.estado)"
                        dark
                        @click="openDialog(item.id,item.estado)"
                    >
                        {{ item.estado }}
                    </v-chip>
                </template>
                <template  v-slot:[`item.actions`]="{ item }">
                    <v-icon
                        small
                        class="mr-2"
                        @click="ver_info(item)"
                    >
                        mdi-eye
                    </v-icon>
                </template>
                <template v-slot:no-data>
                    <span>No hay registros</span>
                </template>
            </v-data-table>
            <div class="v-data-footer">
                <div class="d-flex  justify-space-between align-center">
                    <span class="mr-2">Filas por página:</span>
                    <v-select
                        :items="items_rows_x_page"
                        item-value="q"
                        item-text="value"
                        v-model="items_per_page"
                        @change="info_page.currency_page=1;getData()"
                        :disabled="loading_table"
                        min-width
                    >
                    </v-select>
                </div>
                <div class="v-data-footer__pagination" v-text="getText()"></div>
                <div class="d-flex align-items-center">
                    <v-btn text :disabled="info_page.currency_page==1" icon @click="(info_page.currency_page--);getData()">
                        <v-icon>mdi-chevron-left</v-icon>
                    </v-btn>
                    <v-btn text :disabled="info_page.currency_page==info_page.last_page" icon @click="(info_page.currency_page++);getData()">
                        <v-icon>mdi-chevron-right</v-icon>
                    </v-btn>
                </div>
            </div>
        </v-card>
        <dialogChange :dialog="dialog" :id_change="id_change" :v_change_estado.sync="v_change_estado" @close-dialog="closeDialog" />
        <v-dialog
            v-model="dialog_info"
            max-width="800"
            >
                <v-card>
                    <v-card-title class="text-h5">
                        Infomación
                    </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="6" sm="6" md="4">
                                 <span><b>Nombre: </b>{{item_info.usuario.nombre}}</span>
                            </v-col>
                            <v-col cols="6" sm="6" md="4">
                                <span><b>Dni: </b>{{item_info.usuario.dni}}</span>
                            </v-col>
                            <v-col cols="6" sm="6" md="4">
                                <span><b>Contacto: </b>{{item_info.contacto}}</span>
                            </v-col>
                            <v-col cols="6" sm="6" md="4">
                                <span><b>Motivo: </b>{{item_info.motivo}}</span>
                            </v-col>
                            <v-row>
                                <v-col cols="12" :md="item_info.estado === 'solucionado' ? 4 : 12" sm="12" >
                                    <v-textarea
                                        name="input-7-1"
                                        label="Detalle"
                                        v-model="item_info.detalle"
                                        disabled
                                    ></v-textarea>
                                </v-col>
                                <v-col cols="12" sm="6" md="4">
                                    <v-textarea
                                        v-if="item_info.estado === 'solucionado'"
                                        name="input-7-1"
                                        label="Información Soporte"
                                        v-model="item_info.info_soporte"
                                        disabled
                                    ></v-textarea>
                                </v-col>
                                <v-col cols="12" sm="6" md="4">
                                    <v-textarea
                                        v-if="item_info.estado === 'solucionado'"
                                        name="input-7-1"
                                        label="Mensaje al usuario"
                                        v-model="item_info.msg_to_user"
                                        disabled
                                    ></v-textarea>
                                </v-col>
                            </v-row>
                        </v-row>
                    </v-card-text>
                    <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="green darken-1"
                        text
                        @click="dialog_info = false"
                    >
                        Ok
                    </v-btn>
                    </v-card-actions>
                </v-card>
        </v-dialog>
    </v-container>
</template>
<script>
import dialogChange from '../components/UsuarioAyuda/dialogChange.vue';
import seccionAyuda from '../components/UsuarioAyuda/dialogChange.vue';

import FechaFiltro from "../components/Reportes/partials/FechaFiltro.vue";

export default ({
    components: {dialogChange,seccionAyuda,FechaFiltro},
    data() {
    return {
        search: '',
        headers: [
          { text: 'Ticket', value: 'ticket' },
          { text: 'Módulo', value: 'usuario.config.etapa' },
          { text: 'DNI', value: 'usuario.dni' },
        //   { text: 'Contacto', value: 'contacto' },
          { text: 'Nombre', value: 'usuario.nombre',},
          { text: 'Motivo', value: 'motivo' },
        //   { text: 'Detalle', value: 'detalle' },
          { text: 'Fecha de registro', value: 'created_at' },
          { text: 'Estado', value: 'estado' },
          { text: 'Ver', value: 'actions', sortable: false },
        ],
        estados:['todos','pendiente','revisando','solucionado'],
        v_estado:'pendiente',
        v_change_estado:'',
        id_change:0,
        mensajes:[],
        dialog:false,
        dialog_info:false,
        item_info:{
            estado:'',
            usuario:{dni:'',nombre:''},
            contacto:'',
            fecha:'',
            motivo:'',
            info_soporte:'',
            msg_to_user:'',
        },
        verificar:true,
        info_page:{
            currency_page:1,
            next_page:2,
            last_page:0,
            total:0,
        },
        //pagination
        items_per_page:10,
        items_rows_x_page:[
            {'q':10,'value': 10 },
            {'q':25,'value': 25},
            {'q':50,'value': 50},
            {'q':100,'value': 100},
            {'q':0,'value': 'Todo'},
        ],
        loading_table:false,
    }},
    mounted() {
        this.getData();
    },
    methods: {
        async getData() {
            let vue = this;
			let FechaFiltro = this.$refs.FechasFiltros;
            let data ={
                items_per_page : vue.items_per_page,
                estado : vue.v_estado,
                in_search : vue.search,
				start: FechaFiltro.start,
				end: FechaFiltro.end,
            };
            vue.loading_table = true;
            await axios.post('/usuario_ayuda/getData?page='+vue.info_page.currency_page,data).then(res =>{
                vue.mensajes = res.data.mensajes.data;
                vue.info_page.current_page = res.data.mensajes.current_page;
                vue.info_page.last_page = res.data.mensajes.last_page;
                vue.info_page.total = res.data.mensajes.total;
                vue.items_rows_x_page[vue.items_rows_x_page.length - 1].q = res.data.mensajes.total;
                (vue.verificar) && (vue.verificarParametro());
                vue.loading_table = false;
            })
        },
        async verificarParametro(){
            this.verificar=false;
            let identificador = new URL(location.href).searchParams.get('id');
            if(identificador){
                this.search = '#'+identificador;
                await this.getData();
                let buscado = this.mensajes.find(e=> e.id==identificador);
                if(buscado){
                    this.ver_info(buscado);
                }
            }
        },
        getColor (estado) {
            let  color;
            switch (estado) {
                case 'pendiente': color = 'orange'; break;
                case 'solucionado': color = 'green'; break;
            }
            return color;
        },
        ver_info(item){
            this.item_info = Object.assign({}, item)
            this.dialog_info=true;
        },
        openDialog(id,estado){
            this.id_change = id,
            this.v_change_estado = estado;
            this.dialog=true;
        },
        closeDialog(get_data){
            (get_data) && (this.getData())
            this.dialog=false;
        },
        getText(){
            let to = this.items_per_page*this.info_page.currency_page-(this.items_per_page-1);
            let for_page = (this.info_page.currency_page==this.info_page.last_page) ? this.info_page.total : this.items_per_page*this.info_page.currency_page;
            let total = this.info_page.total;
            return `${to} - ${for_page} de ${total}`
        }
    }
})
</script>
