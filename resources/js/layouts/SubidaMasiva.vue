<template>
    <section class="section-list">
        <v-card class="mx-12" elevation="2">
            <v-alert
                dense
                type="info"
                v-show="s_alert"
                v-html="msg_alert"
                >
            </v-alert>

            <v-row>
                <v-col cols="12" md="5" sm="5">
                    <v-select
                        class="ml-4"
                        attach
                        required
                        dense
                        hide-details
                        @change ="change_select"
                        item-text="nombre"
                        item-value="id"
                        :items="items_masivos"
                        v-model="s_masivo"
                    ></v-select>
                </v-col>
                <v-col cols="12" md="7" sm="7">
                    <div class="mr-3 text-right">
                        <a :href="url_plantilla" class="btn btn-primary" download>Descargar plantilla</a>
                    </div>
                </v-col>
            </v-row>
            <mUsuarios
                :key="1"
                v-show="s_masivo==1 || s_masivo==6"
                :process="s_masivo"
                emitir-alert="show_alert_msg"
                :q_error="info_error.q_err_usu"
                @update_q_error="onUpdate_q_error" />
            <mDesactivar :key="2" v-show="s_masivo==2" @emitir-alert="show_alert_msg" :q_error="info_error.q_err_desct_usu" @update_q_error="onUpdate_q_error" />
            <mActivar :key="3" v-show="s_masivo==3" @emitir-alert="show_alert_msg" :q_error="info_error.q_err_activ_usu" @update_q_error="onUpdate_q_error"  />
            <mCarreras :key="4" v-show="s_masivo==4" @emitir-alert="show_alert_msg" :q_error="info_error.q_err_cambio" @update_q_error="onUpdate_q_error" />
            <mCursos :key="5" v-show="s_masivo==5" @emitir-alert="show_alert_msg" :q_error="info_error.q_err_cur_tem_eva" @update_q_error="onUpdate_q_error"  />
        </v-card>
    </section>
</template>
<script>
  import mUsuarios from "./../components/SubidaMasiva/usuarios/SubidaUsuarios.vue";
  import mCursos from "./../components/SubidaMasiva/cursos/SubidaCursos.vue";
  import mCarreras from "../components/SubidaMasiva/carreras/SubidaCarreras.vue";
  import mActivar from "../components/SubidaMasiva/activar/ActivarUsuarios.vue";
  import mDesactivar from "../components/SubidaMasiva/desactivar/DesactivarUsuarios.vue";

export default {
  props:['info_error'],
  components:{ mUsuarios , mCursos , mCarreras ,mActivar,mDesactivar},
  data() {
    return {
      overlay: true,
      s_alert:false,
      url_plantilla:'/templates/Plantilla_usuarios.xlsx',
      msg_alert:'',
      loader_text: "Cargando",
      s_masivo : 1,
      items_masivos:[
          {nombre:'Creación de usuarios',id:1},
          {nombre:'Desactivar(Cesar) usuarios',id:2},
          {nombre:'Activar Usuarios',id:3},
          {nombre:'Actualización de usuarios',id:4},
          {nombre:'Subida de cursos',id:5},
          {nombre:'Actualización de usuarios',id:6},
      ],
    };
  },
  methods:{
      show_alert_msg(mensaje){
            this.s_alert=true;
            this.msg_alert = mensaje;
      },
      change_select(){
           this.s_alert=false;
            this.msg_alert = '';
          switch (this.s_masivo) {
            case 1:
                this.url_plantilla = '/templates/Plantilla_usuarios.xlsx';
            break;
            case 2:
                this.url_plantilla = '/templates/Plantilla_cesar_usuarios.xlsx';
            break;
            case 3:
                this.url_plantilla = '/templates/Plantilla_activar_usuarios.xlsx';
            break;
            case 4:
                this.url_plantilla = '/templates/Plantilla_usuarios.xlsx';
            break;
            case 5:
                this.url_plantilla = '/templates/Plantilla_cursos_temas.xlsx';
            break;
            case 6:
                this.url_plantilla = '/templates/Plantilla_usuarios.xlsx';
              break;
          }
      },
      onUpdate_q_error(obj){
          switch (obj.tipo) {
                case 'usuarios':
                  this.info_error.q_err_usu += obj.q_error;
                  break;
                case 'cesados':
                  this.info_error.q_err_desct_usu += obj.q_error;
                  break;
                case 'activos':
                    this.info_error.q_err_activ_usu += obj.q_error;
                break;
                case 'cursos':
                    this.info_error.q_err_cur_tem_eva += obj.q_error;
                break;
                case 'carreras':
                    this.info_error.q_err_cambio += obj.q_error;
                break;
              default:
                  break;
          }
      }
  }
}
</script>
