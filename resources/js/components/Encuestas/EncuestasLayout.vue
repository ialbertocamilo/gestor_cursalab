<template>
  <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
              Resultados de encuestas 
            </v-card-title>
        </v-card>
        <!-- FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
              <div class="row col-sm-12 p-4">
                <div class="col-sm-6">
                  <!-- {{Encuestas}} -->
                  <label>Encuesta</label>
                  <DefaultAutocomplete
                        v-model="filters.poll"
                        :items="polls"
                        label=""
                        item-text="titulo"
                        item-value="id"
                        dense
                        returnObject
                        @onChange="loadSchools"
                    />
                </div>
                <div class="col-sm-6">
                  <label>Módulo</label> 
                  <DefaultAutocomplete
                      v-model="filters.modules"
                      :items="modules"
                      label=""
                      item-text="name"
                      item-value="id"
                      dense
                      multiple
                    />
                </div>
                <div class="col-sm-6 mb-3">
                    <b-form-text text-variant="muted">Escuela</b-form-text>
                    <DefaultAutocomplete
                        :disabled="!schools[0]"
                        v-model="filters.schools"
                        :items="schools"
                        label=""
                        item-text="name"
                        item-value="id"
                        dense
                        multiple
                        @onChange="loadCourses"
                    />
                </div>
                <div class="col-sm-6 mb-3">
                    <b-form-text text-variant="muted">Curso</b-form-text>
                    <DefaultAutocomplete
                        :disabled="!courses[0]"
                        v-model="filters.courses"
                        :items="courses"
                        label=""
                        item-text="name"
                        item-value="id"
                        dense
                        multiple
                    />
                </div>
                <div  class="row col-sm-12 mb-3 mx-0 px-0 my-0 py-0" style="justify-content: end;">
                  <div class="col-sm-6">
                      <FechaFiltro ref="FechasFiltros"   
                          label-start="Fecha inicial" 
                          label-end="Fecha final"/>
                  </div>
                </div>
                <!-- <div class="col-sm-6">
                  <label>Grupo</label>
                  <select v-model="grupo" :disabled="!Grupos[0]" class="form-control">
                    <option value="">- Selecciona un Grupo -</option>
                    <option v-for="item in Grupos" :key="item.grupo" :value="item.id">{{ item.grupo }}</option>
                  </select>
                </div> -->
                <div class="col-sm-12 mt-4">
                  <b-button :disabled="!filters.schools[0] || !filters.modules[0]" block variant="primary" @click="searchData()">Consultar</b-button>
                </div>
              </div>
              <!-- Resumen -->
              <div class="m-5" v-if="poll_searched">
                <h2 class="text-center">Resumen</h2>
                <div class="container-fluid">
                  <div>
                    <div class="my-4">
                      <h5 v-if="resume.count_users" v-text="`Total de encuestados: ${resume.count_users}`"></h5>
                      <DefaultSimpleTable v-if="resume.questions_type_califica.length > 0">
                          <template slot="content">
                              <thead>
                                <tr>
                                    <th>Aspecto Evaluado</th>
                                    <th>Promedio</th>
                                    <th>T2B</th>
                                    <th>MB</th>
                                    <th>B</th>
                                    <th>R</th>
                                    <th>M</th>
                                    <th>MM</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                    v-for="(question, index) in resume.questions_type_califica"
                                    :key="index"
                                >
                                    <td class="w-40" v-text="question.titulo"></td>
                                    <td class="w-10" v-text="question.prom"></td>
                                    <td class="w-10" v-text="question.percent_califica_tb2+'%'"></td>
                                    <td class="w-10" v-text="question.percent_califica_mb+'%'"></td>
                                    <td class="w-10" v-text="question.percent_califica_b+'%'"></td>
                                    <td class="w-10" v-text="question.percent_califica_r+'%'"></td>
                                    <td class="w-10" v-text="question.percent_califica_m+'%'"></td>
                                    <td class="w-10" v-text="question.percent_califica_mm+'%'"></td>
                                </tr>
                              </tbody>
                          </template>
                      </DefaultSimpleTable>
                    </div>
                    <h5>Detalle de encuestas</h5>
                    <div class="p-3 row" v-for="(type_poll_question) in types_pool_questions" :key="type_poll_question.id">
                      <div class="col-4" v-text="type_poll_question.name"></div>
                      <div class="col-6">
                        <b-button class="ml-2" variant="success"
                          @click="downloadReportPollQuestion(type_poll_question)"
                        >Descargar <b-icon icon="arrow-down-circle" aria-hidden="true"></b-icon> </b-button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </v-card-text>
        </v-card>
  </section>
</template>

<script>
const FileSaver = require("file-saver");
import FechaFiltro from "../Reportes/partials/FechaFiltro.vue"
export default {
  components:{FechaFiltro},
  props: ["Encuestas"],
  data() {
    return {
      reportsBaseUrl: '',
      polls:[],
      modules:[],
      groups:[],
      schools: [],
      courses: [],
      //
      filters:{
          poll:0,
          groups:[],
          modules:[],
          schools: [],
          courses: [],
          type_poll_question:{},
          courses_id_selected:[],
          date:{
            start:null,
            end:null
          }
      },
      poll_searched:false,
      resume:{
        questions_type_califica:[]
      },
      types_pool_questions:[]
    };
  },
  mounted(){
    this.loadInitialData();
    this.reportsBaseUrl = this.getReportsBaseUrl()
  },
  methods: {
    async loadInitialData(){
      this.poll_searched = false;

      await axios.get('/resumen_encuesta/initial-data').then(({data})=>{
        this.polls = data.data.polls;
        this.modules = data.data.modules;
      })
    },
    async loadSchools(){
      this.poll_searched = false;
      this.courses = [];
      this.schools = [];
      this.filters.courses = [];
      this.filters.schools = [];
      await axios.get('/resumen_encuesta/schools/'+this.filters.poll.id).then(({data})=>{
        this.schools = data.data.schools;
        if(this.schools.length==0){
          this.showAlert('Esta encuesta no tiene ningún curso asociado.','warning');
        }
      })
    }, 
    async loadCourses(){
      this.poll_searched = false;
      this.filters.courses = [];
      await axios.post('/resumen_encuesta/courses',{
        poll_id:this.filters.poll.id,
        schools: this.filters.schools
      }).then(({data})=>{
        this.courses = data.data.courses;
        if(this.courses.length==0){
          this.showAlert('Esta encuesta no tiene ningún curso asociado.','warning');
        }
      })
    },
    async searchData(){
      this.poll_searched = true;
      this.types_pool_questions = [];
      this.showLoader();
      this.filters.courses_id_selected = this.filters.courses.length > 0 ? this.filters.courses : this.courses.map((c)=>c.id) 
      this.filters.date.start =  this.$refs.FechasFiltros.start;
      this.filters.date.end =  this.$refs.FechasFiltros.end;
      await axios.post('/resumen_encuesta/poll-data',this.filters).then(({data})=>{
        this.types_pool_questions = data.data.data.types_pool_questions;
        this.resume = data.data.data.resume;
        this.hideLoader();
      })
      .catch((er)=>{
        console.log(er);
        this.showtAlertError();
      })
    },  
    async cargarGrupos() {
      this.Grupos = [];
      this.grupo = "";
      if (!this.curso) return false;
      var res = await axios.post("cambia_grupo", {
        curso: this.curso,
      });
      this.Grupos = res.data;
    },
    async downloadReportPollQuestion(type_poll_question){
      this.showLoader();
      this.filters.type_poll_question = type_poll_question;
      this.filters.courses_id_selected = this.filters.courses.length > 0 ? this.filters.courses : this.courses.map((c)=>c.id) 
      await axios.post(`${this.reportsBaseUrl}/exportar/poll-questions`,this.filters).then(({data})=>{
        if(data.alert){
          this.hideLoader();
          this.showAlert(data.alert,'warning');
          return false;
        }
        if(data.error){
          this.showtAlertError();
          return false;
        }
        let urlReporte = `${this.reportsBaseUrl}/${data.ruta_descarga}`
        // La extension la define el back-end, ya que el quien crea el archivo
        FileSaver.saveAs(urlReporte, data.new_name)
        this.hideLoader();
      }).catch(()=>{
        this.showtAlertError();
      })
    },
    showtAlertError(){
      this.hideLoader();
      this.showAlert('Ha ocurrido un problema. Contáctate con el equipo de soporte.','warning');
    }
  },
};
</script>

<style></style>
