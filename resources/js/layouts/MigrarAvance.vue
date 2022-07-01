<template>
    <section style="margin: 0px 20px;background: #f2f6f8;"  class="px-3 section-list">
        <v-card flat elevation="0" class="rounded-lg">
            <v-card-title>
                Migrar avance
                <v-spacer />
                <dialogInfo />
            </v-card-title>
        </v-card>
        <v-card flat elevation="0" class="mt-4 rounded-lg">
            <v-card-text>
                <v-col md="4" style="padding-top:0">
                    <v-autocomplete
                        outlined
                        color="#5458ea"
                        v-model="modulo_id"
                        label="Módulo"
                        :items="modulos"
                        item-text="etapa"
                        item-value="id"
                        @change="getListCategorias()"
                    ></v-autocomplete>
                </v-col>
                <v-row>
                    <!-- CARD ORIGEN -->
                    <v-col md="6" class="pr-9">
                        <v-col md="12">
                            <v-autocomplete
                                outlined
                                v-model="categoria_id_origen"
                                color="#5458ea"
                                label="Escuela origen"
                                :items="categorias_origen"
                                item-text="nombre"
                                item-value="id"
                                @change="getListCursos('origen')"
                            ></v-autocomplete>
                        </v-col>
                        <v-col md="12">
                            <v-autocomplete
                                outlined
                                color="#5458ea"
                                v-model="curso_origen"
                                return-object
                                :items="cursos_origen"
                                item-text="nombre"
                                item-value="id"
                                label="Curso origen"
                                @change="getListTemas('origen')"
                            ></v-autocomplete>
                        </v-col>
                        <div>
                            <v-simple-table class="ml-4">
                                <template v-slot:default>
                                    <tbody>
                                        <tr
                                            v-for="tema in temas_origen"
                                            :key="tema.id"
                                            class="line_tr"
                                        >
                                            <td  v-text="tema.txt_ev+' - '+tema.nombre"></td>
                                            <td class="d-flex justify-content-center align-center">
                                                 <label class="container_check">
                                                    <input type="checkbox" v-model="tema.checkbox" :disabled="enable_disable_checkbox" @click="first_element($event,tema.id,tema.txt_ev)">
                                                    <span :id="'origen_'+tema.id"  style="border-radius: 3px;" class="checkmark"></span>
                                                </label>
                                            </td>
                                            <div class="line_join_checkbox" :id="String(tema.id)"></div>
                                        </tr>
                                    </tbody>
                                </template>
                            </v-simple-table>
                        </div>
                        <div class="mt-6">
                            <h5 class="ml-4">Segmentación</h5>
                            <v-simple-table class="ml-4" fixed-header :height="(curriculas_origen.length == 0 ? '0px' : '300px')">
                                <template v-slot:default>
                                <thead>
                                    <tr>
                                    <th style="color:#5458ea;">
                                        Carrera
                                    </th>
                                    <th style="color:#5458ea;">
                                        Ciclo
                                    </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="line_tr"
                                        v-for="ca in curriculas_origen"
                                        :key="ca.id"
                                    >
                                        <td>{{ ca[0].carrera }}</td>
                                        <td>
                                            <span v-for="(ci,idx) in ca" :key="ci.id" v-text="(idx == ca.length -1) ? ' '+ci.ciclo : `${ci.ciclo},`"></span>
                                        </td>
                                    </tr>
                                </tbody>
                                </template>
                            </v-simple-table>
                        </div>
                    </v-col>
                    <!-- CARD DESTINO -->
                    <v-col  md="6" class="pl-9" :disabled="dsb_card">
                        <v-col md="12" >
                            <v-autocomplete
                                color="#5458ea"
                                outlined
                                v-model="categoria_id_destino"
                                label="Escuela destino"
                                :items="categorias_destino"
                                item-text="nombre"
                                item-value="id"
                                @change="getListCursos('destino')"
                            ></v-autocomplete>
                        </v-col>
                        <v-col md="12">
                            <v-autocomplete
                                outlined
                                color="#5458ea"
                                label="Curso Destino"
                                v-model="curso_destino"
                                return-object
                                :items="cursos_destino"
                                item-text="nombre"
                                item-value="id"
                                @change="getListTemas('destino')"
                            ></v-autocomplete>
                        </v-col>
                        <div>
                            <v-simple-table class="ml-4">
                                <template v-slot:default>
                                    <tbody>
                                        <tr
                                            class="line_tr"
                                            v-for="tema in temas_destino"
                                            :key="tema.id"
                                        >
                                            <td>
                                                <label class="container_check">
                                                    <input type="checkbox"  v-model="tema.checkbox" :disabled="!enable_disable_checkbox" @click="second_element($event,tema.id,tema.txt_ev)" >
                                                    <span :id="'destino_'+tema.id" style="border-radius: 3px;" class="checkmark"></span>
                                                </label>
                                            </td>
                                            <td  class="text-end" v-text="tema.txt_ev+' - '+tema.nombre"></td>
                                        </tr>
                                    </tbody>
                                </template>
                            </v-simple-table>
                        </div>
                        <div class="mt-6">
                            <h5 class="ml-4">Segmentación</h5>
                            <v-simple-table class="ml-4" fixed-header :height="(curriculas_destino.length == 0 ? '0px' : '300px')">
                                <template v-slot:default>
                                    <thead>
                                        <tr>
                                            <th style="color:#5458ea;font-size:.875rem">Carrera</th>
                                            <th style="color:#5458ea;font-size:.875rem">Ciclo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="line_tr"
                                            v-for="ca in curriculas_destino"
                                            :key="ca.id"
                                        >
                                            <td>{{ ca[0].carrera }}</td>
                                            <td>
                                                <span v-for="(ci,idx) in ca" :key="ci.id" v-text="(idx == ca.length -1) ? ' '+ci.ciclo : `${ci.ciclo},`"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </template>
                            </v-simple-table>
                        </div>
                    </v-col>
                </v-row>
                <v-row class="d-flex justify-content-center mt-4">
                    <div >
                        <v-btn style="color:white" color="#5458ea" @click="migrar_temas()" >Migrar <v-icon right>mdi-login-variant</v-icon></v-btn>
                    </div>
                </v-row>
            </v-card-text>
        </v-card>
    </section>
</template>
<script>
import dialogInfo from './../components/MigrarAvance/dialog_info.vue';
export default {
    components: {dialogInfo},
    data(){
        return {
            modulos:[],
            modulo_id:0,
            categorias_origen:[],
            categoria_id_origen:0,
            categorias_destino:[],
            categoria_id_destino:0,
            cursos_origen:[],
            curso_origen:{
                nombre: '',
                id:0,
            },
            cursos_destino:[],
             curso_destino:{
                nombre: '',
                id:0,
            },
            list_group_1:true,
            list_group_2:true,
            temas_origen:[],
            temas_destino:[],
            curriculas_origen:[],
            curriculas_destino:[],
            first_position:{
                x:0,
                y:0,
            },
            second_position:{
                x:0,
                y:0
            },
            element_selected:{
                id:0,
                tipo:'',
            },
            enable_disable_checkbox:false,
            dsb_card:true,
            temas_a_migrar :[],
            lastScrollTop:0,
        };
    },
    async mounted() {
        this.getData();
    },
    created () {
        window.addEventListener('scroll', this.handleScroll);
        window.addEventListener('resize', this.handleResize);
    },
    methods: {
        handleResize(e){
            console.log('resize');
        },
        handleScroll(e) {
            // Any code to be executed when the window is scrolled
            let a = document.getElementsByClassName('line_join_checkbox');
            let st = window.pageYOffset || document.documentElement.scrollTop;
            // let down = (st > this.lastScrollTop) ? true : false ;
            let y = window.scrollY;
            if(a.length>0){
                for (let i = 0; i < a.length; i++) {
                    let child = document.getElementById(a[i].id+'_child');
                    if(child){
                        let top = parseFloat(child.getAttribute('top_o')) - y;
                        if(top){
                            child.style.top = top+"px";
                        }
                    }
                }
            }
            this.lastScrollTop = st <= 0 ? 0 : st;
        },
        async getData(){
             this.showLoader()
            let vue = this;
            this.delete_all_line();
            await axios.get('/migrar_avance/getData').then((res)=>{
                vue.modulos = res.data.modulos;
                this.hideLoader()
            }).catch((err)=>{
                this.hideLoader()
            })
        },
        async getListCategorias(){
            this.delete_all_line();
            this.resete_data('all');
            await axios.get(`/migrar_avance/list_categorias/${this.modulo_id}`).then((res)=>{
                this.categorias_origen = res.data.categorias;
                // this.categorias_destino = res.data.categorias;
            })
        },
        async getListCursos(tipo){
            let categoria_id;
            this.delete_all_line();
            if(tipo=='origen'){
                this.resete_data('origen');
                categoria_id = this.categoria_id_origen;
            }else{
                this.resete_data('destino');
                this.get_duplicates_data('cursos');
                // categoria_id = this.curso_origen.id;
                return false;
            }
            await axios.get(`/migrar_avance/list_cursos/${categoria_id}`).then((res)=>{
                if(tipo=='origen'){
                    this.cursos_origen = res.data.cursos;
                }else{
                    this.cursos_destino = res.data.cursos;
                }

            })
        },
        async getListTemas(tipo){
            let curso_id = (tipo=='origen') ? this.curso_origen.id : this.curso_destino.id;
            this.delete_all_line();
            this.reset_checkbox(tipo);
            this.showLoader()
            await axios.get(`/migrar_avance/list_temas/${curso_id}`).then((res)=>{
                if(tipo=='origen'){
                    this.curriculas_origen = res.data.curriculas;
                    this.temas_origen = res.data.temas;
                    this.get_duplicates_data('categorias');
                    this.hideLoader()
                }else{
                    this.temas_destino = res.data.temas;
                    if(this.temas_destino.length>0){
                        setTimeout(() => {
                            this.temas_destino.forEach(td=> {
                                    let to = this.temas_origen.find(to => to.id ==td.duplicado_id);
                                    if(to){
                                        let pos1 =this.getOffset(document.getElementById(`origen_${to.id}`))
                                        this.first_element(pos1,to.id,to.tipo_ev)
                                        let pos2 =this.getOffset(document.getElementById(`destino_${td.id}`))
                                        this.second_element(pos2,td.id,td.tipo_ev);
                                        this.temas_origen[this.temas_origen.findIndex(e =>e.id == to.id)].checkbox = true;
                                        this.temas_destino[this.temas_destino.findIndex(e =>e.id == td.id)].checkbox = true;
                                    }
                            });
                            this.hideLoader()
                        }, 1000);
                    }
                    this.curriculas_destino = res.data.curriculas;
                    if(this.curriculas_destino.length == 0){
                        alert('El curso destino no cuenta con una matricula');
                    }
                }
            }).catch((err)=>{
                this.hideLoader()
            })
        },
        getOffset(el) {
            const rect = el.getBoundingClientRect();
            //    clientX : rect.left + window.scrollX +10,
            //     clientY : rect.top + window.scrollY +10
            return {
                clientX : rect.left + window.scrollX +10,
                clientY : rect.top +10
            };
        },
        async get_duplicates_data(tipo){
            this.showLoader()
            await axios.get(`/migrar_avance/get_duplicates_data/${tipo}/${this.curso_origen.id}/${this.categoria_id_destino}`).then((res)=>{
                if(res.data.data.length ==0){
                    alert('No existen duplicados');
                    return false;
                }
                this.dsb_card = false;
                switch (tipo) {
                    case 'categorias': this.categorias_destino = res.data.data ; break;
                    case 'cursos': this.cursos_destino = res.data.data ; break;
                }
                this.hideLoader()
            }).catch((err)=>{
                this.hideLoader()
            })
        },
        first_element({clientX,clientY},tema_id,tipo_ev){
            console.log(clientX,clientY);
            let f_tema = this.temas_a_migrar.find(e => e.id_origen == tema_id);
            if(f_tema){
                document.getElementById(tema_id).innerHTML="";
                this.temas_destino[this.temas_destino.findIndex(e =>e.id == f_tema.id_destino)].checkbox = false;
                this.temas_a_migrar.splice(f_tema.idx, 1);
                return false;
            }
            let idx = this.temas_a_migrar.length;
            this.temas_a_migrar.push({
                id_origen:tema_id,
                idx
            });
            this.first_position.x = clientX;
            this.first_position.y = clientY;
            this.element_selected.id = tema_id;
            this.element_selected.tipo_ev = tipo_ev;
            this.enable_disable_checkbox = true;
        },
        second_element({clientX,clientY},tema_id,tipo_ev){
            let id_origen = this.temas_a_migrar[this.temas_a_migrar.length-1].id_origen;
            if(this.element_selected.tipo_ev != tipo_ev){
                alert('Deben tener el mismo tipo de evaluación ');
                return false;
            }
            if(this.temas_a_migrar.find(e => e.id_destino == tema_id)){
                this.$nextTick(() => {
                    this.temas_origen[this.temas_origen.findIndex(e =>e.id == id_origen)].checkbox = false;
                    this.temas_destino[this.temas_destino.findIndex(e =>e.id == tema_id)].checkbox = true;
                })
                this.temas_a_migrar.splice(-1,1);
                this.enable_disable_checkbox = false;
                alert('Este tema ya ha sido escogido');
                return false;
            }
            this.second_position.x = clientX;
            this.second_position.y = clientY;
            this.temas_a_migrar[this.temas_a_migrar.length-1].id_destino = tema_id;
            let line = this.create_line(id_origen);
            let t1 = document.getElementById(this.element_selected.id);
            t1.appendChild(line);
            this.enable_disable_checkbox = false;
        },
        delete_all_line(){
            let a = document.getElementsByClassName('line_join_checkbox');
            if(a.length>0){
                for (let i = 0; i < a.length; i++) {
                    a[i].innerHTML="";
                }
            }
        },
        create_line(id_origen){
            let line = document.createElement("div");
            let length = Math.sqrt(((this.second_position.x - this.first_position.x) * (this.second_position.x - this.first_position.x)) + ((this.second_position.y - this.first_position.y) * (this.second_position.y - this.first_position.y)));
            let cx = ((this.first_position.x + this.second_position.x) / 2) - (length / 2);
            let cy = ((this.first_position.y + this.second_position.y) / 2) - (1 / 2);
            let deg = Math.atan2((this.first_position.y - this.second_position.y), (this.first_position.x  - this.second_position.x)) * (180 / Math.PI);
            line.style.left = cx + "px";
            line.style.top = cy + "px";
            line.style.width = length + "px";
            line.style.height = 2 + "px";
            line.style.position = "fixed";
            line.style.zIndex = "1"
            line.style.border = "1px solid #5458ea";
            line.style.transform = "rotate(" + deg + "deg)";
            line.setAttribute("id", id_origen+'_child');
            let st = window.pageYOffset || document.documentElement.scrollTop;
            let down = (st > this.lastScrollTop) ? true : false ;
            let y = window.scrollY;
            let top = (down) ? (parseFloat(cy) - y) : (parseFloat(cy) + y);
            line.setAttribute("top_o", top);
            return line;
        },
        resete_data(type){
            let vue =this;
            if(type=='all'){
                vue.categorias_origen=[];
                vue.categorias_destino=[];
            }
            if(type=='all' || type =='origen'){
                vue.cursos_origen = [];
                vue.temas_origen = [];
                vue.curriculas_origen=[];
            }
            if(type=='all' || type =='destino'){
                vue.cursos_destino = [];
                vue.temas_destino = [];
                vue.curriculas_destino=[];
            }
            this.temas_a_migrar = [];
            this.reset_checkbox('destino');
            this.reset_checkbox('origen');
        },
        reset_checkbox(tipo){
            if(tipo=='origen'){
                this.temas_destino.forEach(t => {
                    t.checkbox = false;
                });
            }else{
                this.temas_origen.forEach(t => {
                    t.checkbox = false;
                });
            }
        },
        migrar_temas(){
            if(this.curriculas_origen.length==0 || this.curriculas_destino.length==0){
                alert('Ambas cursos deben tener curriculas');
                return false;
            }
            if(this.temas_a_migrar.length==0){
                alert('Es necesario hacer relacionar los temas');
                return false;
            }
             this.showLoader()
            axios.post('/migrar_avance/migrar_temas',{
                temas_a_migrar : this.temas_a_migrar,
                curriculas_destino : this.curriculas_destino,
                curso_origen: this.curso_origen,
                curso_destino : this.curso_destino,
            }).then((res)=>{
                alert('Migración completa');
                this.hideLoader()
            }).catch((err)=>{
                this.hideLoader()
            })
        }
    },
}
</script>
<style>
 /* Customize the label (the container) */
.container_check {
  display: block;
  position: relative;
  /* padding-left: 35px; */
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 22px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

/* Hide the browser's default checkbox */
.container_check input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

/* Create a custom checkbox */
.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 20px;
  width: 20px;
  background-color: gray;
}

/* On mouse-over, add a grey background color */
.container_check:hover input ~ .checkmark {
  background-color: #ccc;
}

/* When the checkbox is checked, add a blue background */
.container_check input:checked ~ .checkmark {
  background-color: #5458ea;
}

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

/* Show the checkmark when checked */
.container_check input:checked ~ .checkmark:after {
  display: block;
}
.line_tr{
    border-bottom: 1px solid #5458ea !important;
}

.v-label--active{
    font-weight: bold;
    font-size: larger;
    background: white;
    padding-left: 3px;
    padding-right: 3px;
}
td{
    color:#333d5d !important;
}
.v-select__slot input{
    font-size:.875rem !important;
}
/* Style the checkmark/indicator */
/* .container_check .checkmark:after {
  left: 9px;
  top: 5px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 3px 3px 0;
  -webkit-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  transform: rotate(45deg);
}  */
</style>
