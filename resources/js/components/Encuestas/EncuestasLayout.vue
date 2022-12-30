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
                        returnObject
                    />
                </div>
                <div  class="row col-sm-12 mb-3 mx-0 px-0 my-0 py-0" style="justify-content: end;">
                  <div class="col-sm-6">
                      <FechaFiltro ref="FechasFiltros"   
                          label-start="Fecha inicial" 
                          label-end="Fecha final"/>
                  </div>
                </div>
                <div class="row col-sm-12 mt-4 m-0 p-0 justify-center">
                  <div class="col-sm-4">
                    <b-button :disabled="!filters.schools[0] || !filters.modules[0]" block variant="primary" @click="searchData()">Consultar</b-button>
                  </div>
                </div>
              </div>
              <!-- Resumen -->
              <div class="m-5" v-if="poll_searched">
                <h2 class="text-center">Resumen</h2>
                <div class="container-fluid">
                  <div>
                    <ResumenEncuesta :resume="resume" />
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
            <ModalBloqueReport 
              :modalOptions="modalOptions"
              :download_list="download_list"
              @saveReport="saveReport"
              @closeModal="closeModal"
            />
        </v-card>
  </section>
</template>

<script>
const FileSaver = require("file-saver");
import FechaFiltro from "../Reportes/partials/FechaFiltro.vue";
import ResumenEncuesta from './ResumenEncuesta.vue';
import ModalBloqueReport from './ModalBloqueReport.vue';

export default {
  components:{FechaFiltro,ResumenEncuesta,ModalBloqueReport},
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
          courses_selected:[],
          date:{
            start:null,
            end:null
          }
      },
      poll_searched:false,
      resume:{
        questions_type_califica:[]
      },
      types_pool_questions:[],
      modalOptions: {
        title:'Generador de Reportes por bloques',
        ref: 'TableDownload',
        open: true,
        base_endpoint: '',
        persistent:true,
        showCardActions:false,
        hideCancelBtn:true
      },
      download_list:[],
    };
  },
  mounted(){
    this.loadInitialData();
    this.reportsBaseUrl = this.getReportsBaseUrl()
    // this.showLoader();
  },
  methods: {
    async loadInitialData(){
      let vue = this;
      vue.poll_searched = false;
      await axios.get('/resumen_encuesta/initial-data').then(({data})=>{
        vue.polls = data.data.polls;
        vue.modules = data.data.modules;
      })
    },
    async loadSchools(){
      let vue = this;
      vue.poll_searched = false;
      vue.courses = [];
      vue.schools = [];
      vue.filters.courses = [];
      vue.filters.schools = [];
      await axios.get('/resumen_encuesta/schools/'+vue.filters.poll.id).then(({data})=>{
        vue.schools = data.data.schools;
        if(vue.schools.length==0){
          vue.showAlert('Esta encuesta no tiene ningún curso asociado.','warning');
        }
      })
    }, 
    async loadCourses(){
      let vue = this;
      vue.poll_searched = false;
      vue.filters.courses = [];
      await axios.post('/resumen_encuesta/courses',{
        poll_id:vue.filters.poll.id,
        schools: vue.filters.schools
      }).then(({data})=>{
        vue.courses = data.data.courses;
        if(vue.courses.length==0){
          vue.showAlert('Esta encuesta no tiene ningún curso asociado.','warning');
        }
      })
    },
    async searchData(){
      let vue = this;
      vue.poll_searched = true;
      vue.types_pool_questions = [];
      vue.showLoader();
      vue.filters.courses_selected = vue.filters.courses.length > 0 ? vue.filters.courses.map(c=>c.id) : vue.courses.map(c=>c.id)
      vue.filters.date.start =  vue.$refs.FechasFiltros.start;
      vue.filters.date.end =  vue.$refs.FechasFiltros.end;
      await axios.post('/resumen_encuesta/poll-data',vue.filters).then(({data})=>{
        vue.types_pool_questions = data.data.data.types_pool_questions;
        vue.resume = data.data.data.resume;
        vue.hideLoader();
      })
      .catch((er)=>{
        console.log(er);
        vue.showtAlertError();
      })
    },  
    async cargarGrupos() {
      let vue = this;
      vue.Grupos = [];
      vue.grupo = "";
      if (!vue.curso) return false;
      var res = await axios.post("cambia_grupo", {
        curso: vue.curso,
      });
      vue.Grupos = res.data;
    },
    async downloadReportPollQuestion(type_poll_question){
      let vue = this;
      vue.filters.type_poll_question = type_poll_question;
      vue.filters.courses_selected = vue.filters.courses.length > 0 ? vue.filters.courses : vue.courses; 
      const groupby_courses_by_school = vue.groupArrayOfObjects(vue.filters.courses_selected,'school_id','get_array'); //Function in mixin.js
      //If the selected schools are greater than 20, the data will be downloaded in parts
      const chunk_courses_by_school = vue.sliceIntoChunks(groupby_courses_by_school,10);//Function in mixin.js
      if (chunk_courses_by_school.length == 1) {
        vue.showLoader();
        await this.callApiReport(vue.filters.courses_selected.map(c => c.id));
      }else{
        for (const array_courses of chunk_courses_by_school){
          let get_all_courses_id = [];
          const content = 'Contiene '+array_courses.length+' escuela(s).';
          //message in tooltip (list of name's schools)
          let schools_name = '';
          
          for (const courses of array_courses) {
            get_all_courses_id = [...get_all_courses_id,...courses.map(c => c.id)];
            const school = this.schools.find(school => school.id == courses[0].school_id);
            schools_name = schools_name.length == 0 ? school.name : schools_name+', '+school.name  
          }
          this.download_list.push({
            content,
            schools: schools_name,
            status:'pending',
            url:'',
            new_name:'',
            courses_id : get_all_courses_id 
          });
          this.modalOptions.open = true;
        }
        this.verifyStatusDownload();
      }
    },
    verifyStatusDownload(){
      let vue = this;
      const find_donwload_pending =  vue.download_list.find(dl => dl.status == 'pending');
      if(find_donwload_pending){
        vue.callApiReport(find_donwload_pending.courses_id);
      }else{
        //if all donwload list is complete.
        vue.modalOptions.showCardActions = true;
      }
      return true;
    },  
    change_status_download(data){
      let vue = this;
      const find_index = vue.download_list.findIndex(dl => dl.status == 'pending');
      let urlReporte = `${vue.reportsBaseUrl}/${data.ruta_descarga}`;
      vue.download_list[find_index].url=urlReporte;
      vue.download_list[find_index].new_name = data.new_name;
      vue.download_list[find_index].status= (data.alert) ? 'no_data' : 'complete';
      vue.verifyStatusDownload();
      return true;
    },  
    async callApiReport(courses_id){
      let vue = this;
      vue.filters.courses_selected = courses_id;
      await axios.post(`${vue.reportsBaseUrl}/exportar/poll-questions`,vue.filters).then(({data})=>{
        if(vue.download_list.length>0){
          this.change_status_download(data);
          return true;
        }
        if(data.alert){
          vue.hideLoader();
          vue.showAlert(data.alert,'warning');
          return false;
        }
        if(data.error){
          vue.showtAlertError();
          return false;
        }
        let urlReporte = `${vue.reportsBaseUrl}/${data.ruta_descarga}`
        this.saveReport(urlReporte, data.new_name);
        vue.hideLoader();
      }).catch(()=>{
        vue.showtAlertError();
      }) 
    },
    showtAlertError(){
      this.hideLoader();
      this.showAlert('Ha ocurrido un problema. Contáctate con el equipo de soporte.','warning');
    },
    saveReport({url,new_name}){
      // La extension la define el back-end, ya que el quien crea el archivo
      FileSaver.saveAs(url,new_name)
    },
    closeModal(){
      this.modalOptions.open = false;
      this.download_list = [];
    }
  },
};
</script>

<style></style>
