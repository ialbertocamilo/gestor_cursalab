<template>
    <div>
        <a href="#" class="btn btn-secondary btn-bigicon" style="color: white;" @click="get_data()">
            <i class="fas fa-copy"></i>
            <br> <span class="icon-title">Duplicar Contenido</span>
        </a>
        <v-dialog max-width="500px" v-model="dialog_d" scrollable>
            <v-card>
                <v-card-title>Duplicar escuelas</v-card-title>
                <v-stepper non-linear>
                    <v-stepper-header>
                        <v-stepper-step
                        editable
                        step="1"
                        >
                        Anotaciones
                        </v-stepper-step>
                        <v-divider></v-divider>
                        <v-stepper-step
                        editable
                        step="2"
                        >
                        Elegir
                        </v-stepper-step>
                        <v-divider></v-divider>
                        <v-stepper-step
                        editable
                        step="3"
                        >
                        Guardar
                        </v-stepper-step>
                    </v-stepper-header>
                    <v-stepper-items>
                        <v-stepper-content step="1">
                                <v-card style="height: 100%;overflow: auto;">
                                    <v-card-subtitle><b> Para duplicar es necesario tener en cuentas los sgts. puntos:</b></v-card-subtitle>
                                    <ul style="text-align: left;">
                                        <!-- <li class="mt-2" style="font-size: 1.05rem;">
                                            Debes asignar los requisitos manualmente de cursos y temas.
                                        </li> -->
                                        <li class="mt-2" style="font-size: 1.05rem;">
                                            No se duplicará toda escuela, curso, tema que ya se encuentre en dicha empresa destino.
                                        </li>
                                        <li class="mt-2" style="font-size: 1.05rem;">
                                            Asignar segmentación manualmente (no se duplican segmentaciones).
                                        </li>
                                        <li class="mt-2" style="font-size: 1.05rem;">
                                            Recuerda desactivar el estado de la escuela.
                                        </li>
                                    </ul>
                                </v-card>
                        </v-stepper-content>
                        <v-stepper-content step="2">
                                <v-select
                                    v-model="selectionType"
                                    :items="s_type"
                                    item-value="value"
                                    item-text="nombre"
                                    label="Tipo de selección"
                                ></v-select>
                                <v-card style="height: 400px;overflow: auto;">
                                    <v-card-subtitle>
                                        <b>Elige las escuelas, cursos y temas que se duplicarán.</b>
                                    </v-card-subtitle>
                                    <v-treeview
                                        selectable
                                        selected-color="green"
                                        :items="categorias"
                                        item-key="new_id"
                                        v-model="s_categorias"
                                        :selection-type="selectionType"
                                    >
                                    </v-treeview>
                                </v-card>
                        </v-stepper-content>
                        <v-stepper-content step="3">
                            <v-card style="height: 400px;overflow: auto;">
                                <v-card-subtitle>
                                    <b>Elige en que empresa(s) se copiara la(s) escuelas().</b>
                                </v-card-subtitle>
                                <v-treeview
                                    selectable
                                    item-disabled="locked"
                                    selected-color="green"
                                    :items="configs"
                                    item-key="id"
                                    v-model="s_configs"
                                >
                                </v-treeview>
                            </v-card>
                            <v-card-actions>
                                <v-btn @click="save_copy_escuelas()">Guardar</v-btn>
                            </v-card-actions>
                        </v-stepper-content>
                    </v-stepper-items>
                </v-stepper>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
export default {
    props: ["config_id"],
    data() {
		return {
            dialog_d:false,
            s_categorias:[],
            s_configs:[],
            categorias:[],
            configs:[],
            s_type:[
                {nombre:'Múltiple',value:'leaf'},
                {nombre:'Unitario',value:'independent'},
            ],
            selectionType:'leaf',
        }
    },
    methods:{
        get_data(){
            this.s_categorias = [];
            this.categorias=[];
            this.s_configs=[];
            axios.get('/duplicar/get_data/categorias/'+this.config_id).then((e)=>{
                this.categorias = e.data.data.categorias;
                this.configs = e.data.data.configs;
                this.dialog_d = true;
            })
        },
        save_copy_escuelas(){
            this.showLoader()
            let data = {
                'duplicar': this.s_categorias,
                'configs' : this.s_configs,
                'tipo'    : 'categorias'
            };
            axios.post('/duplicar/save_copy',data).then((e)=>{
                this.dialog_d = false;
                this.hideLoader()
                vue.$notification.warning(`Duplicación exitosa.`, {
                    timer: 6,
                    showLeftIcn: false,
                    showCloseIcn: true
                });
            })
        }
    }
}
</script>
<style >
.v-application--wrap {
	min-height: 0;
}
.theme--light.v-application {
	background: transparent;
	color: rgba(0, 0, 0, 0.87);
}
.text-one-line {
	white-space: nowrap;
}
.text-size-bold-white {
	font-size: 1.23em !important;
	font-weight: bold !important;
	color: white !important;
}
.fondo-th {
	background: #343a40 !important;
}
</style>
