<template>
  <div>
    <div class="row col-sm-9 p-4">
      <div class="col-sm-6">
        <!-- {{Encuestas}} -->
        <label>Encuesta</label>
        <select v-model="encuesta" @change="cargarModulos" class="form-control">
          <option value="">- Selecciona una Encuesta -</option>
          <option v-for="item in Encuestas" :key="item.id" :value="item.id">{{ item.titulo }}</option>
        </select>
      </div>
      <div class="col-sm-6">
        <label>Módulo</label>
        <select v-model="modulo" @change="cargarCursos" :disabled="!Modulos[0]" class="form-control">
          <option value="">- Selecciona un Modulo -</option>
          <option v-for="item in Modulos" :key="item.id" :value="item.id">{{ item.etapa }}</option>
        </select>
      </div>
      <div class="col-sm-6">
        <label>Curso</label>
        <select v-model="curso" @change="cargarGrupos" :disabled="!Cursos[0]" class="form-control">
          <option value="">- Selecciona un Curso -</option>
          <option v-for="item in Cursos" :key="item.id" :value="item.id">{{ item.nombre }}</option>
        </select>
      </div>
      <div class="col-sm-6">
        <label>Grupo</label>
        <select v-model="grupo" :disabled="!Grupos[0]" class="form-control">
          <option value="">- Selecciona un Grupo -</option>
          <option v-for="item in Grupos" :key="item.grupo" :value="item.id">{{ item.grupo }}</option>
        </select>
      </div>
      <div class="col-sm-6 mt-4">
        <b-button block variant="primary">Consultar</b-button>
      </div>
    </div>
    <!-- Resumen -->
    <div class="m-5">
      <h1 class="text-center">Resumen</h1>
      <!-- Table -->
      <div class="container-fluid">
        <b-table :items="items" responsive striped hover bordered outlined head-variant="dark"></b-table>
        <div>
          <h2>Detalle de encuestas</h2>
          <div class="p-3 row">
            <div class="col-4">Calificacion</div>
            <div class="col-6">
              <b-button class="ml-2" variant="primary"> <b-icon icon="eye" aria-hidden="true"></b-icon> Ver </b-button>
              <b-button class="ml-2" variant="success">Descargar <b-icon icon="arrow-down-circle" aria-hidden="true"></b-icon> </b-button>
            </div>
          </div>
          <div class="p-3 row">
            <div class="col-4">Respuestas en texto</div>
            <div class="col-6">
              <button class="ml-2 btn btn-primary">Ver</button>
              <button class="ml-2 btn btn-success">Descargar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["Encuestas"],
  data() {
    return {
      encuesta: "",
      modulo: "",
      curso: "",
      grupo: "",
      Modulos: [],
      Cursos: [],
      Grupos: [],
      items: [{ name: "louis", lastname: "rosas", age: "22" }],
    };
  },
  methods: {
    async cargarModulos() {
      this.Modulos = [];
      this.Cursos = [];
      this.Grupos = [];
      this.modulo = "";
      this.curso = "";
      this.grupo = "";
      if (!this.encuesta) return false;
      var res = await axios.post("cambiar_encuesta_mod", {
        encuesta: this.encuesta,
      });
      this.Modulos = res.data;
    },
    async cargarCursos() {
      this.Cursos = [];
      this.Grupos = [];
      this.curso = "";
      this.grupo = "";
      if (!this.modulo) return false;
      var res = await axios.post("cambia_curso", {
        mod: this.modulo,
        encuesta: this.encuesta,
      });
      this.Cursos = res.data;
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
  },
};
</script>

<style></style>
