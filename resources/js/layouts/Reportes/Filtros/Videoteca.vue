<template>
    <v-main>
        <!-- Resumen del reporte -->
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el registro de visitas a los títulos de la Videoteca.
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item
                titulo="Grupo sistema"
                subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
            />
            <list-item titulo="Grupo" subtitulo="Grupo al que pertenece el usuario" />
            <list-item titulo="Botica" subtitulo="Botica en la que se ubica el usuario" />
            <list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />
            <list-item titulo="Carrera (Usuario)" subtitulo="Carrera actual en la que se encuentra" />
            <list-item titulo="Videoteca" subtitulo="Nombre del título en la Videoteca" />
            <list-item
                titulo="Visitas"
                subtitulo="Cantidad de veces que el usuario visualiza un título de la Videoteca"
            />
            <list-item
                titulo="Última visita"
                subtitulo="Fecha de la última visita realizada al título de la Videoteca"
            />
        </ResumenExpand>
        <!-- Formulario del reporte -->
        <form class="row col-md-8 col-xl-5" @submit.prevent="ExportarVideoteca">
            <!-- Grupos -->
<!--            <div class="col-12">
                <b-form-text text-variant="muted">Videoteca</b-form-text>
                <v-select
                    attach
                    solo
                    chips
                    clearable
                    multiple
                    hide-details="false"
                    v-model="videotecaSelected"
                    :items="VideotecaList"
                    item-value="id"
                    item-text="nombre"
                    label="Selecciona uno de la lista"
                    :background-color="!videotecaSelected ? '' : 'light-blue lighten-5'"
                ></v-select>
            </div>-->

            <div class="col-sm-12 mb-3 mt-4">
                <div class="col-sm-8 pl-0">
                    <button type="submit" class="btn btn-md btn-primary btn-block text-light">
                        <i class="fas fa-download"></i>
                        <span>Descargar</span>
                    </button>
                </div>
            </div>
        </form>
    </v-main>
</template>
<script>
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
export default {
    components: { ResumenExpand, ListItem },
    props: ["VideotecaList", "API_REPORTES", "API_FILTROS"],
    data() {
        return {
            videotecaSelected: ""
        };
    },
    methods: {
        ExportarVideoteca() {
            $("#pageloader").fadeIn();
            let params = {
                videotecaSelected: this.videotecaSelected
            };
            axios
                .post(this.API_REPORTES + "videoteca", params)
                .then((res) => {
                    if (!res.data.error) this.$emit("emitir-reporte", res);
                    else {
                        alert("Se ha encontrado el siguiente error : " + res.data.error);
                        $("#pageloader").fadeOut();
                    }
                })
                .catch((error) => {
                    console.log(error);
                    console.log(error.message);
                    alert("Se ha encontrado el siguiente error : " + error);
                    $("#pageloader").fadeOut();
                });
        }
    },
    created() {
        this.videotecaSelected = this.VideotecaList.map((el) => el.id);
    }
};
</script>

<style></style>
