<template>
  <div class="text-center">
    <v-dialog
      v-model="dialog"
      width="850"
    >
      <template v-slot:activator="{ on, attrs }">
        <v-btn color="indigo" outlined v-bind="attrs" v-on="on" class="no-uppercase">
            Requisitos 
            <v-icon right color="indigo">
               mdi-information
            </v-icon>
        </v-btn>
      </template>
      <v-card>
        <v-card-title class="text-h7" style="background:#5458ea;color:white">
            Requisitos
            <v-spacer></v-spacer>
            <v-btn text icon @click="dialog=false">
                <v-icon right color="white">
                mdi-close
                </v-icon>
            </v-btn>
        </v-card-title>
        <v-card-text>
            <v-row>
                <v-col md="6">
                    <v-card>
                        <v-card-text>
                                <span>Requisitos</span>
                                <ul>
                                    <li class="my-3" v-for="(req,idx) in list_requisitos" :key="idx" v-text="req"></li>
                                </ul>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col  md="6">
                    <v-card>
                        <v-card-text>
                            <span>Pasos para migrar</span>
                            <ul>
                                <li class="my-3" v-for="(ef,idx) in list_efectos" :key="idx" v-text="ef"></li>
                            </ul>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            style="color:white"
            color="#5458ea"
            @click="dialog = false"
          >
            Cerrar
          </v-btn>
          <v-spacer></v-spacer>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
  export default {
    data () {
      return {
        dialog: false,
        list_requisitos:[
            'El curso debe ser un duplicado',
            'Los temas a migrar deben tener el mismo tipo de evaluación',
            'Solo se migrarán los temas relacionados'
        ],
        list_efectos:[
            'Mover el curso (Opcional)',
            'Segmentar el curso movido(Opcional)',
            'Duplicar el curso en la sección de escuelas',
            'Segmentar el curso duplicado',
            'Seleccionar el módulo del curso original',
            'Seleccionar la escuela del curso original',
            'Seleccionar el curso original',
            'Se listarán Solo las escuelas en donde se ha duplicado el curso original',
            'Seleccionar la escuela destino',
            'Seleccionar el curso destino',
            'Se hará la selección automática de los temas',
            'Finalizar dando click al botón migrar'
        ],
      }
    },
  }
</script>
<style>
.no-uppercase {
     text-transform: none;
}
</style>