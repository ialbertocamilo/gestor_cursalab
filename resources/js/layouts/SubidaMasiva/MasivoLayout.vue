<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Procesos masivos
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12">
                        <v-alert
                            dense
                            type="info"
                            v-show="s_alert"
                            v-html="msg_alert"
                            >
                        </v-alert>
                    </v-col>
                </v-row>
                <v-row class="justify-content-start">
                    <v-col cols="12" md="5" sm="5">
                        <DefaultSelect
                            class="ml-4"
                            @onChange="change_select"
                            item-text="nombre"
                            item-value="id"
                            :items="list_massive_processes"
                            v-model="process_id"
                        />
                    </v-col>
                    <v-col cols="12" md="7" sm="7">
                        <div class="mr-3 text-right">
                            <a :href="url_template" class="btn btn-primary" download>Descargar plantilla <i class="fas fa-download ml-2"></i></a>
                        </div>
                    </v-col>
                </v-row>
                <mUsuarios :number_socket="number_socket" :key="1" v-show="process_id==1" @emitir-alert="show_alert_msg" @download-excel-observations="downloadExcelObservations" />
                <ActivarUsuarios :number_socket="number_socket" :key="2" v-show="process_id==2" @emitir-alert="show_alert_msg" />
                <InactivarUsuarios :number_socket="number_socket" :key="3" v-show="process_id==3" @emitir-alert="show_alert_msg" />
            </v-card-text>
        </v-card>
    </section>
</template>
<script>
import mUsuarios from "../../components/SubidaMasiva/usuarios/SubidaUsuarios.vue";
import ActivarUsuarios from "../../components/SubidaMasiva/activar/ActivarUsuarios.vue";
import InactivarUsuarios from "../../components/SubidaMasiva/desactivar/DesactivarUsuarios.vue"
const percentLoader = document.getElementById('percentLoader');
export default {
  components:{ mUsuarios,ActivarUsuarios,InactivarUsuarios },
  props:{
    user_id:{
        required:true
    }
  },    
  data() {
    return {
      number_socket:Math.floor(Math.random(6)*1000000),
      info_error:0,
      overlay: true,
      s_alert:false,
      url_template:'/masivos/download-template-user',
      msg_alert:'',
      loader_text: "Cargando",
      process_id : 1,
      list_massive_processes:[
          {id:1,nombre:'Creación/Actualización de usuarios',url_template:'/masivos/download-template-user'},
          {id:2,nombre:'Activar Usuarios',url_template:'/templates/Plantilla_activar_usuarios.xlsx'},
          {id:3,nombre:'Desactivar(Cesar) usuarios',url_template:'/templates/Plantilla_cesar_usuarios.xlsx'},
        //   {id:4,nombre:'Actualización de usuarios',url_template:''},
        //   {id:5,nombre:'Subida de cursos',url_template:''},
      ],
    };
  },
  mounted(){
        window.Echo.channel(`upload-massive.${this.user_id}.${this.number_socket}`).listen('MassiveUploadProgressEvent',result=>{
            if(percentLoader){
                if(result.percent){
                    percentLoader.innerHTML = `${result.percent}%`;
                }
            }
        })
    },
  methods:{
      show_alert_msg(message){
            this.s_alert=true;
            this.msg_alert = message;
      },
      change_select(){
            let vue = this;
            this.s_alert=false;
            this.msg_alert = '';
            vue.url_template = vue.list_massive_processes.find(mp=>mp.id==vue.process_id).url_template;
      },
      downloadExcelObservations({errores,headers}){
        // let values =["dni", "nota", "info"];
        let vue = this;
        const values = errores.map(error => error.row.map(row => row || ''));
        let comments = [];
        errores.forEach((error,index)=>{
            error.errors_index.forEach((error_index) => {
                comments.push({
                    cell_name:`${this.abc[error_index.index]}${index+2}`,
                    message:error_index.message
                });
            });
        });
        vue.descargarExcelwithValuesInArray({
            headers,
            values,
            comments,
            filename:"No procesados_" + Math.floor(Math.random() * 1000),
        });
      }
  }
}
</script>