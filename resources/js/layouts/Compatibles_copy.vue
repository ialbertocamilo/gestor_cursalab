<template>
    <v-container fluid>
        <v-row>
            <v-col cols="12" md="4" sm="12">
                <v-card>
                    <v-card-title>
                        <v-row>
                            <v-col cols="12" md="4" sm="4">
                                Lista
                            </v-col>
                            <v-col cols="12" md="8" sm="8">
                                <v-select
                                :items="modulos"
                                item-text="etapa"
                                item-value="etapa"
                                label="Módulo"
                                dense
                                outlined
                                hide-details
                                v-model="slt_mod_lista"
                                ></v-select>
                            </v-col>
                        </v-row>
                        <v-text-field dense
                            outlined
                            hide-details 
                            v-model="b_text_lista" 
                            label="Buscar">
                        </v-text-field>
                    </v-card-title>
                    <v-card-text style="">
                        <div >
                            <v-list max-height="400" style="overflow:auto" dense v-if="filtroLista.length">
                                <v-list-group
                                    prepend-icon="mdi-school"
                                    v-for="escuela in filtroLista" :key="escuela.id" 
                                >
                                    <template v-slot:activator>
                                        <v-list-item-content>
                                            <v-list-item-subtitle v-text="escuela.config.etapa"></v-list-item-subtitle>
                                            <v-list-item-title  style="white-space:normal" v-text="escuela.nombre"></v-list-item-title>
                                        </v-list-item-content>
                                    </template>
                                    <v-list-group
                                        no-action
                                        sub-group
                                        v-for="curso in escuela.cursos" :key="curso.id"
                                    >
                                        <template v-slot:activator>
                                            <v-list-item-content>
                                                <v-list-item-title  style="white-space:normal" v-text="curso.nombre"></v-list-item-title>
                                            </v-list-item-content>
                                        </template>
                                        <v-list-item-group sub-group no-action>
                                            <v-list-item
                                                v-for="tema in curso.temas"
                                                :key="tema.id"
                                                link
                                                @click="buscar_coincidencia(tema,curso)"
                                            >
                                                <v-list-item-content>
                                                    <v-list-item-title class="my-2" style="white-space:normal" v-text="tema.nombre"></v-list-item-title>
                                                </v-list-item-content>
                                            </v-list-item>
                                        </v-list-item-group>
                                    </v-list-group>
                                </v-list-group>
                            </v-list>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" md="4" sm="12">
                <v-card >
                    <v-card-title>Relaciones</v-card-title>
                    <div style="overflow: auto;max-height: 366px;">
                        <v-card v-for="(group,idx_grupo) in dropGroups" :key="group.name" style="border-left:4px solid #ba68c8;" class="mx-2 mb-4 pt-0">
                            <v-card-text  class="py-0 ">
                                <v-card-subtitle v-text="group.name" style="padding-left:0;padding-top:1rem"></v-card-subtitle>
                                <draggable :list="group.children"  group="posteos" @change="cloneItem" class="list-group">
                                    <transition-group name="fade" tag="div" class="mb-2 pt-3" style="display: grid;background: #e5e5e5;justify-content: space-between;grid-template-columns: repeat(2, 1fr);border-radius:10px">
                                        <p v-for="(element,idx) in group.children" :key="element.id" class="text-center rounded-lg mx-2 px-1" style="background: chartreuse;font-size: .8125rem;">
                                            {{element.nombre}}
                                            <i class="fa fa-times close" style="font-size: large;" @click="removeAt(idx_grupo,idx,element)"></i>
                                        </p>
                                    </transition-group>
                                </draggable>
                            </v-card-text>
                        </v-card>
                    </div>
                    <v-card-actions v-if="dropGroups.length>0">
                      <v-spacer></v-spacer>
                        <v-btn color="green darken-1" text @click="guardar_compatibles()">
						Guardar
					    </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
            <v-col cols="12" md="4" sm="12">
                <v-card>
                    <v-card-title>
                        <v-row>
                            <v-col cols="12" md="4" sm="4">
                                Coincidencias
                            </v-col>
                            <v-col cols="12" md="8" sm="8" v-if="coincidencias.length>0">
                                <v-select
                                :items="modulos"
                                item-text="etapa"
                                item-value="etapa"
                                label="Módulo"
                                dense
                                outlined
                                hide-details
                                v-model="slt_mod_coinci"
                                ></v-select>
                            </v-col>
                        </v-row>
                         <v-text-field v-if="coincidencias.length>0" dense
                            outlined
                            hide-details 
                            v-model="b_text_coinci" 
                            label="Buscar">
                        </v-text-field>
                    </v-card-title>
                    <v-card-text>
                        <v-list max-height="400" style="overflow:auto" dense v-if="filtroCoinci.length">
                            <v-list-group
                                :value="false"
                                prepend-icon="mdi-school"
                                v-for="(coincidencia, index) in filtroCoinci" :key="index"
                            >
                                <template v-slot:activator>
                                    <v-list-item-content>
                                        <v-list-item-subtitle v-text="coincidencia.modulo"></v-list-item-subtitle>
                                        <v-list-item-title  style="white-space:normal" v-text="coincidencia.escuela"></v-list-item-title>
                                    </v-list-item-content>
                                </template>
                                <v-list-group
                                    :value="true"
                                    no-action
                                    sub-group
                                    v-for="(curso,index) in coincidencia.cursos" :key="index"
                                >
                                    <template v-slot:activator>
                                        <v-list-item-content>
                                            <v-list-item-title  style="white-space:normal">{{curso.nombre}} <v-icon @click="ver_carreras(curso.temas[0].curso_id)" class="ml-2">mdi-eye</v-icon></v-list-item-title>
                                        </v-list-item-content>
                                    </template>
                                    <draggable :list="curso.temas" :group="{ name: 'posteos', pull: 'clone', put: false,animation: 120}" >
                                        <transition-group name="fade" tag="div" >
                                            <v-list-item
                                                v-for="tema in curso.temas"
                                                :key="tema.id"
                                            >
                                            <p v-text="tema.nombre"  style="white-space:normal" class="px-2" :style="[tema.selec ? {'background' : 'chartreuse', 'border-radius' : '12px','cursor':'move'}:{'background' :'yellow','border-radius' : '12px','cursor':'move'}]"></p>
                                            </v-list-item>
                                        </transition-group>
                                    </draggable>
                                </v-list-group>
                            </v-list-group>
                        </v-list>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
        <!-- OVERLAY -->
        <v-overlay :value="overlay">
            <div style="display: flex !important; align-items: flex-end !important">
                <p class="text-h4 mr-4">Espere...</p>
                <v-progress-circular indeterminate size="64"></v-progress-circular>
            </div>
        </v-overlay>
        <v-dialog v-model="d_carreras" max-width="200">
            <v-card>
                <v-card-title>Carreras</v-card-title>
                <v-card-text>
                    <p v-for="carrera in carreras" :key="carrera.id" v-text="carrera.carrera.nombre"></p>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
import draggable from 'vuedraggable'
export default {
    components: {
        draggable,
    },
    data() {
		return {
            selectedItem:null,
            escuelas: [],
            modulos:[],
            carreras:[],
            coincidencias:[],
            dropGroups: [],
            slt_mod_lista:'Ambos',
            slt_mod_coinci:'Ambos',
            b_text_lista:'',
            b_text_coinci:'',
            esc_filtro:[],
            overlay:true,
            d_carreras:false,
        }
    },
    mounted(){
        this.get_escuelas();
    },
    computed:{
        filtroLista() {
            let vue = this;
            let s_mdl = vue.slt_mod_lista.toLowerCase();
            let data_r = [];
            if (vue.slt_mod_lista != 'Ambos') {
                data_r = vue.escuelas.filter(item => {
                    return (
                        item.config.etapa.toLowerCase().indexOf(s_mdl) != -1
                    );
                });
            }else{
                data_r = vue.escuelas;
            }
            if(vue.b_text_lista){
                let text = vue.b_text_lista.toLowerCase();
                data_r  = data_r.filter(item => {
                    return (
                        item.nombre.toLowerCase().indexOf(text) != -1
                    );
                });
            }
            return data_r;
        },
        filtroCoinci() {
            let vue = this;
            let s_mdl = vue.slt_mod_coinci.toLowerCase();
            let data_r = [];
            if (vue.slt_mod_coinci != 'Ambos') {
                data_r = vue.coincidencias.filter(item => {
                    return (
                        item.modulo.toLowerCase().indexOf(s_mdl) != -1
                    );
                });
            }else{
                data_r = vue.coincidencias;
            }
            if(vue.b_text_coinci){
                let text = vue.b_text_coinci.toLowerCase();
                data_r  = data_r.filter(item => {
                    return (
                        item.escuela.toLowerCase().indexOf(text) != -1
                    );
                });
                console.log(data_r);
            }
            return data_r;
        },
    },
    methods:{
        get_escuelas(){
            axios.get('/get_escuelas_posteos').then(res=>{
                let data = res.data.data;
                this.escuelas = data.escuelas;
                this.modulos=data.modulos;
                this.modulos.unshift({
                    id:0,
                    etapa:'Ambos'
                });
                console.log(this.escuelas);
                this.overlay=false;
            })
        },
        ver_carreras(curso_id){
            let vue = this;
            vue.overlay=true;
            console.log(curso_id);
            axios.get('/get_carrera_cursos/'+curso_id).then(res=>{
                vue.carreras = res.data.data;
                vue.overlay=false;
                vue.d_carreras = true;
            })
        },
        buscar_coincidencia(tema,curso){
            this.overlay=true;
            let data ={
                'nombre': tema.nombre,
                'temas':curso.temas,
            };
            this.dropGroups=[];
            this.coincidencias = [];
            axios.post('/get_coincidencias',data).then(res=>{
                let data = res.data.data;
                let relaciones = data.relaciones;
                if(data){
                    curso.temas.forEach(e => {
                        let s_rel  = [];
                        if(relaciones.length>0){
                            let e_find = relaciones.filter(element => element.id_compatible == e.id);
                            e_find.forEach(element => {
                                s_rel.push(element);
                            });
                        }
                        this.dropGroups.push(
                            {
                                tema_id:e.id,
                                curso_id:e.curso_id,
                                config_id:curso.config_id,
                                name:e.nombre,
                                children:s_rel,
                                ids_eliminados:[],
                            }
                        );
                    });
                    this.coincidencias = data.coincidencias; 
                }
                this.overlay=false;
            })
        },
        cloneItem({ added }){
            if (added) {
                let item = added.element; 
                this.change_etd_tema(item,'add');
            }
        },
        removeAt(idx_grupo,idx,element) {
            this.change_etd_tema(element,'remove');
            
            this.dropGroups[idx_grupo].ids_eliminados.push(element.id);
            this.dropGroups[idx_grupo].children.splice(idx, 1);
        },
        change_etd_tema(item,tipo){
            let idx_escuela= this.coincidencias.findIndex( e => {return e.escuela==item.curso.categoria.nombre}); 
            let idx_curso = this.coincidencias[idx_escuela].cursos.findIndex( e => { return e.nombre==item.curso.nombre});
            let idx_tema = this.coincidencias[idx_escuela].cursos[idx_curso].temas.findIndex( e => { return e.id==item.id});
            let estado = (tipo=='add') ? 1 : 0;    
            if(idx_tema  != -1 ){
                this.coincidencias[idx_escuela].cursos[idx_curso].temas[idx_tema].selec=estado;
            }
        },
        guardar_compatibles(){
            let data ={
                nuevos_compatibles : this.dropGroups
            }
            this.overlay=true;
            axios.post('/guardar_compatibles',data).then((res)=>{
                this.overlay=false;
                alert('Guardado');
            });
            this.overlay=false;
        },
    }
}
</script>
<style>

</style>