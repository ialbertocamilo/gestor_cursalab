<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Reportes
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 --mb-4">
            <v-tabs vertical>
              
              <v-tab class="justify-content-start py-9">
                <v-icon left>mdi-bookmark-check</v-icon> Calificaciones
              </v-tab>
              
              <v-tab class="justify-content-start py-9">
                <v-icon left>mdi-account-multiple</v-icon> Matriculados
              </v-tab>

              <v-tab class="justify-content-start py-9">
                <v-icon left>mdi-map-marker</v-icon> Visitas
              </v-tab>

              <v-tab class="justify-content-start py-9">
                <v-icon left>mdi-refresh</v-icon> Reinicios
              </v-tab>

              <v-tab class="justify-content-start py-9">
                <v-icon left>mdi-upload</v-icon> Archivos subidos
              </v-tab>
             
              <v-tab-item>
                <v-card flat>
                  <v-card-text>
                    <NotasUsuario :API_REPORTES="API_REPORTES"/>
                  </v-card-text>
                </v-card>
              </v-tab-item>

              <v-tab-item>
                <v-card flat>
                  <v-card-text>
                    <Usuarios :Modulos="Modulos" :API_FILTROS="API_FILTROS" :API_REPORTES="API_REPORTES"
                        @emitir-reporte="crearReporte" />
                  </v-card-text>
                </v-card>
              </v-tab-item>

              
              <v-tab-item>
                <v-card flat>
                  <v-card-text>
                    <Visitas :Modulos="Modulos" :API_FILTROS="API_FILTROS" :API_REPORTES="API_REPORTES" 
                        @emitir-reporte="crearReporte" />
                  </v-card-text>
                </v-card>
              </v-tab-item>
              
              <v-tab-item>
                <v-card flat>
                  <v-card-text>
                    <Reinicios :Admins="Admins" :API_FILTROS="API_FILTROS" :API_REPORTES="API_REPORTES"
                        @emitir-reporte="crearReporte" />
                  </v-card-text>
                </v-card>
              </v-tab-item>

            
              <v-tab-item>
                <v-card flat>
                  <v-card-text>
                    <UsuarioUploads :API_FILTROS="API_FILTROS" :API_REPORTES="API_REPORTES"
                        @emitir-reporte="crearReporte" />
                  </v-card-text>
                </v-card>
              </v-tab-item>
            </v-tabs>
        </v-card>
     <!-- </v-app> -->
    </section>
</template>
<script>
const FileSaver = require("file-saver");
const moment = require("moment");
moment.locale("es");
// import NotasTema from "../components/Reportes/NotasTema";
import UsuarioUploads from "./Filtros/UsuarioUploads";
import NotasUsuario from "./Filtros/NotasUsuario";
import Reinicios from "./Filtros/Reinicios";
import Usuarios from "./Filtros/Usuarios";
import Visitas from "./Filtros/Visitas";
// import NotasCurso from "../components/Reportes/NotasCurso";
// import EvaAbiertas from "../components/Reportes/EvaAbiertas";
// import VersionesUsadas from "../components/Reportes/VersionesUsadas";
// import AvanceCurricula from "../components/Reportes/AvanceCurricula";
// import Vademecum from "../components/Reportes/Vademecum.vue";
// import Videoteca from "../components/Reportes/Videoteca.vue";
// import TemasNoEvaluables from "../components/Reportes/TemasNoEvaluables.vue";
// import ChecklistDetallado from "../components/Reportes/ChecklistDetallado.vue";
// import ChecklistGeneral from "../components/Reportes/ChecklistGeneral.vue";
// import Ranking from "../components/Reportes/Ranking.vue";

import {mapState} from "vuex";

export default {
    components: {
        UsuarioUploads,
        NotasUsuario,
        Usuarios,
        Visitas,
        Reinicios,
    },
    data() {
        return {
            value: "",
            Modulos: [],
            Admins: [],
            VademecumList: [],
            VideotecaList: [],
            // URL DE LAS APIS
            API_FILTROS: process.env.MIX_API_FILTROS,
            API_REPORTES: process.env.MIX_API_REPORTES,
        };
    },
    methods: {
        async crearReporte(res) {
            try {
                var dateNow = res.data.createAt; //Este es el nombre del excel creado con Date.Now()
                var extension = res.data.extension;
                var modulo = res.data.modulo;
                // Excel Nombre : Nombre del modulo + Fecha convertida + Hora convertida + Extension
                let ExcelNuevoNombre =
                    modulo + moment(res.createAt).format("L") + " " + moment(res.createAt).format("LT");
                // La extension la define el back-end, ya que el quien crea el archivo
                FileSaver.saveAs(`/storage/${dateNow + extension}`, ExcelNuevoNombre + extension);
                $("#pageloader").fadeOut();
            } catch (error) {
                console.log(error);
                $("#pageloader").fadeOut();
                return error;
            }
        }
    },
    computed: {
        ...mapState(["User"])
    },
    async beforeCreate() {
        var res = await axios("/exportar/obtenerdatos");
        this.Modulos = res.data.modulos;
        this.Admins = res.data.users;
        this.$store.commit("setUser", res.data.user[0]);
        this.VademecumList = res.data.vademecums;
    }
};
</script>
<style>
/*input[type="date"]::-webkit-calendar-picker-indicator {
    -webkit-appearance: none;
    display: none;
}

input[type="date"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0; 
}

input[type="number"] {
    -moz-appearance: textfield; 
}

label {
    margin: 0;
}*/

/* button {
color: #ffffff !important;
} */
/*.max-w-1920 {
    max-width: 1920px;
}

.v-label {
    display: contents !important;
}

.info-icon {
    color: darkgrey !important;
    font-size: 20px !important;
}*/

/* Tooltips */
[tooltip] {
    /* margin: 20px; */
    position: relative;
}

[tooltip]::before {
    content: "";
    position: absolute;
    top: -6px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 4px 6px 0 6px;
    border-style: solid;
    border-color: rgba(0, 0, 0, 0.7) transparent transparent transparent;
    z-index: 100;
    opacity: 0;
}

[tooltip]::after {
    content: attr(tooltip);
    position: absolute;
    left: 50%;
    top: -6px;
    transform: translateX(-50%) translateY(-100%);
    background: rgba(0, 0, 0, 0.7);
    text-align: left;
    color: #fff;
    padding: 6px 4px;
    font-size: 14px;
    font-family: Roboto, sans-serif;
    font-weight: 500;
    min-width: 150px;
    width: 250px;
    max-width: 250px;
    border-radius: 5px;
    pointer-events: none;
    opacity: 0;
}

[tooltip-position="left"]::before {
    left: 0%;
    top: 50%;
    margin-left: -12px;
    transform: translatey(-50%) rotate(-90deg);
}

[tooltip-position="top"]::before {
    left: 50%;
}

[tooltip-position="bottom"]::before {
    top: 100%;
    margin-top: 8px;
    transform: translateX(-50%) translatey(-100%) rotate(-180deg);
}

[tooltip-position="right"]::before {
    left: 100%;
    top: 50%;
    margin-left: 1px;
    transform: translatey(-50%) rotate(90deg);
}

[tooltip-position="left"]::after {
    left: 0%;
    top: 50%;
    margin-left: -8px;
    transform: translateX(-100%) translateY(-50%);
}

[tooltip-position="top"]::after {
    left: 50%;
}

[tooltip-position="bottom"]::after {
    top: 100%;
    margin-top: 8px;
    transform: translateX(-50%) translateY(0%);
}

[tooltip-position="right"]::after {
    left: 100%;
    top: 50%;
    margin-left: 8px;
    transform: translateX(0%) translateY(-50%);
}

[tooltip]:hover::after,
[tooltip]:hover::before {
    opacity: 1;
}
</style>
