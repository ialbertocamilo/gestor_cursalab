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
                <!-- <div class="col-sm-6">
                  <label>Grupo</label>
                  <select v-model="grupo" :disabled="!Grupos[0]" class="form-control">
                    <option value="">- Selecciona un Grupo -</option>
                    <option v-for="item in Grupos" :key="item.grupo" :value="item.id">{{ item.grupo }}</option>
                  </select>
                </div> -->
                <!-- :disabled="!(filters.courses.length > 0) || !(filters.courses.schools > 0)" -->
                <div class="col-sm-12 mt-4">
                  <b-button  block variant="primary" @click="searchData()">Consultar</b-button>
                </div>
              </div>
              <!-- Resumen -->
              <div class="m-5" v-if="poll_searched">
                <h1 class="text-center">Resumen</h1>
                <div class="container-fluid">
                  <!-- <b-table :items="items" responsive striped hover bordered outlined head-variant="dark"></b-table> -->
                  <div>
                    <h2>Detalle de encuestas</h2>
                    <div class="p-3 row" v-for="(type_poll_question) in types_pool_questions" :key="type_poll_question.id">
                      <div class="col-4" v-text="type_poll_question.name"></div>
                      <div class="col-6">
                        <!-- <b-button class="ml-2" variant="primary"> <b-icon icon="eye" aria-hidden="true"></b-icon> Ver </b-button> -->
                        <b-button class="ml-2" variant="success"
                          @click="downloadReportPollQuestion(type_poll_question.id)"
                        >Descargar <b-icon icon="arrow-down-circle" aria-hidden="true"></b-icon> </b-button>
                      </div>
                    </div>
                    <!-- <div class="p-3 row">
                    </div>
                    <div class="p-3 row">
                      <div class="col-4">Respuestas en texto</div>
                      <div class="col-6">
                        <button class="ml-2 btn btn-primary">Ver</button>
                        <button class="ml-2 btn btn-success">Descargar</button>
                      </div>
                    </div> -->
                  </div>
                </div>
              </div>
            </v-card-text>
        </v-card>
  </section>
</template>

<script>
export default {
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
          type_question_id:0,
          courses_id_selected:[],
      },
      poll_searched:false,
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
      })
    },
    async searchData(){
      this.poll_searched = true;
      this.types_pool_questions = [];
      await axios.post('/resumen_encuesta/poll-data',{
        poll_id:this.filters.poll.id,
        schools: this.filters.schools
      }).then(({data})=>{
        this.types_pool_questions = data.data.data.types_pool_questions;
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
    async downloadReportPollQuestion(type_question_id){
      this.filters.type_question_id = type_question_id;
      this.filters.courses_id_selected = this.filters.courses.length > 0 ? this.filters.courses : this.courses.map((c)=>c.id) 
      await axios.post(`${this.reportsBaseUrl}/exportar/poll-questions`,this.filters).then(()=>{

      })
    }
  },
};
</script>

<style></style>
