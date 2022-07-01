<template>
<!--  <v-app class="container-fluid">-->
<!--    <b-card no-body>-->
<!--      <b-tabs card active-nav-item-class="font-weight-bold text-primary mt-2" content-class="mt-3">-->
<!--        <b-tab title="Aulas virtuales" active lazy>-->
          <AulasVirtuales @descargar_reporte="crearReporte" :API_FILTROS="API_FILTROS" :API_REPORTES="API_REPORTES" />
<!--        </b-tab>-->
<!--        -->
<!--      </b-tabs>-->
<!--    </b-card>-->
<!--    &lt;!&ndash; End &ndash;&gt;-->
<!--  </v-app>-->
</template>

<script>
const FileSaver = require("file-saver");
const moment = require("moment");
moment.locale("es");
import AulasVirtuales from '../components/Reportes/AulasVirtuales'
export default {
  components: {
    AulasVirtuales
  },
  data() {
    return {
      API_FILTROS : process.env.MIX_API_FILTROS,
      API_REPORTES: process.env.MIX_API_REPORTES,
    }
  },
  methods: {
    async crearReporte(res) {
      try {
        console.log(res);
        var dateNow = res.data.createAt; //Este es el nombre del excel creado con Date.Now()
        var extension = res.data.extension;
        var modulo = res.data.modulo;
        // Excel Nombre : Nombre del modulo + Fecha convertida + Hora convertida + Extension
        let ExcelNuevoNombre = modulo + moment(res.createAt).format("L") + " " + moment(res.createAt).format("LT");
        // La extension la define el back-end, ya que el quien crea el archivo
        FileSaver.saveAs(`/storage/${dateNow + extension}`, ExcelNuevoNombre + extension);
      } catch (error) {
        console.log(error);
        return error;
      }
    },
  },
}
</script>
