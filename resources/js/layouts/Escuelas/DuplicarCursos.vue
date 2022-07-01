<template>
    <div>
        <v-dialog max-width="500px" v-model="modalCursosDuplicar.dialog" scrollable @click:outside="closeModal">
            <v-card>
                <v-card-title>Duplicar cursos - temas - evaluaciones</v-card-title>
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
                                            No se duplicará todo curso, tema que ya se encuentre en dicha escuela destino.
                                        </li>
                                        <li class="mt-2" style="font-size: 1.05rem;">
                                            Asignar segmentación manualmente (no se duplican segmentaciones).
                                        </li>
                                        <li class="mt-2" style="font-size: 1.05rem;">
                                            Recuerda desactivar el estado del curso.
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
                                        <b>Elige los cursos y temas que se duplicarán.</b>
                                    </v-card-subtitle>

                                    <v-treeview
                                        selectable
                                        selected-color="green"
                                        :items="cursos"
                                        item-key="new_id"
                                        v-model="s_cursos"
                                        :selection-type="selectionType"
                                    >
                                    </v-treeview>
                                </v-card>
                        </v-stepper-content>
                        <v-stepper-content step="3">
                            <div>
                                <v-text-field
                                    v-model="search"
                                    label="Busca por escuela"
                                    hide-details
                                    filled
                                ></v-text-field>
                            </div>
                            <v-card style="height: 400px;overflow: auto;">
                                <v-card-subtitle>
                                    <b>Elige en que escuela(s) se copiara el/los curso(s).</b>
                                </v-card-subtitle>
                                 <v-treeview
                                    selectable
                                    selected-color="green"
                                    :items="configs"
                                    item-key="new_id"
                                    v-model="s_configs"
                                    :search="search"
                                    :filter="filter"
                                >
                                </v-treeview>
                            </v-card>
                            <v-card-actions>
                                <v-btn @click="save_duplicar()">Guardar</v-btn>
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
    props: ['modalCursosDuplicar'],
    data() {
		return {
            s_cursos:[],
            s_configs:[],
            cursos:[],
            configs:[],
            s_type:[
                {nombre:'Múltiple',value:'leaf'},
                {nombre:'Unitario',value:'independent'},
            ],
            selectionType:'leaf',
            search:null,
            caseSensitive: false,
        }
    },
    computed: {
      filter () {
        return this.caseSensitive
          ? (item, search, textKey) => item[textKey].indexOf(search) > -1
          : undefined
      },
    },
    methods:{
        async get_data(){
            let vue = this;
            vue.s_cursos = [];
            vue.cursos=[];
            vue.s_configs=[];
            await axios.get('/duplicar/get_data/cursos/'+vue.modalCursosDuplicar.categoria_id).then((e)=>{
                vue.cursos = e.data.data.cursos;
                vue.configs = e.data.data.configs;
                vue.dialog_d = true;
                vue.hideLoader();
            }).catch((e)=>{
                vue.hideLoader();
            })
        },
        save_duplicar(){
            this.showLoader();
            let data = {
                'duplicar': this.s_cursos,
                'configs' : this.s_configs,
                 'tipo'    : 'cursos',
            };
            axios.post('/duplicar/save_copy',data).then((e)=>{
                this.dialog_d = false;
                this.hideLoader()
                this.showAlert('Duplicación exitosa.');
                this.closeModal();
            })
        },
        closeModal() {
            let vue = this
            vue.s_cursos = [];
            vue.cursos=[];
            vue.s_configs=[];
            vue.$emit('onCancel')
        },
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
