<template>
    <v-main>
        <!-- Resumen del reporte -->
        <ResumenExpand :dialog="infoDialog" @onCancel="infoDialog = false" titulo="Resumen del reporte">

            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item
                titulo="Grupo sistema"
                subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
            />
            <list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
            <list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
            <list-item titulo="Documento, Apellidos y Nombres, Género" subtitulo="Datos personales" />
            <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
            <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            <list-item
                titulo="Estado"
                subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
            />
        </ResumenExpand>

        <v-card-text>
            <v-alert
              border="top"
              colored-border
              elevation="2"
              color="primary"
              class="text-center"
            >
                <p class="text-h6 text--primary text-center pt-3">
                    <v-icon color="primary" style="top: -5px;" @click="infoDialog = true">mdi-information</v-icon>
                    USUARIOS MATRICULADOS
                </p>

                <small>En este reporte se obtienen todos los usuarios matriculados en la plataforma.</small>
            </v-alert>

            <v-divider class="mt-7"></v-divider>

                <form class=" " @submit.prevent="exportUsuariosDW">
                    <v-row class="justify-content-center">

                        <v-col cols="12" md="4">
                            <DefaultSelect clearable
                                           :items="Modulos"
                                           v-model="modulo"
                                           label="Módulo"
                                           dense
                                           return-object
                                           :count-show-values="3"
                                           :show-select-all="false"
                                           item-text="etapa"
                                           item-value="id"
                                           @onChange="moduloChange"
                            />
                        </v-col>

                        <!-- Carrera -->
                        <v-col cols="12" md="4">
                            <DefaultSelect
                                attach
                                solo
                                chips
                                clearable
                                multiple
                                dense
                                hide-details="false"
                               :items="Carreras"
                               v-model="carrera"
                               :label="!Carreras[0] ? 'Selecciona un módulo ' : 'Carreras'"
                                :background-color="!Carreras[0] ? 'grey lighten-3' : 'light-blue lighten-5'"
                               return-object
                               :count-show-values="2"
                               :show-select-all="false"
                               item-text="text"
                               item-value="value"
                               :loading="loadingCarreras"
                                :disabled="!Carreras[0]"
                               @onChange="carreraChange" />
                        </v-col>
    <!-- 				</v-row>

                    <v-row class="justify-content-center"> -->

                    <!-- Grupos -->
                    <v-col cols="12" md="4">
                        <DefaultSelect
                            attach
                            solo
                            chips
                            clearable
                            multiple
                            dense
                            hide-details="false"
                            item-text="text"
                           item-value="value"
                           v-model="grupo"
                            :items="Grupos"
                            :label="!Grupos[0] || !carrera[0] ? 'Selecciona una carrera ' : 'Áreas'"
                            :background-color="!Grupos[0] || !carrera[0] ? 'grey lighten-3' : 'light-blue lighten-5'"
                           return-object
                           :count-show-values="5"
                           :show-select-all="false"
                           :loading="loadingGrupos"
                            :disabled="!Grupos[0] || !carrera[0]"
                           />

                    </v-col>

                    </v-row>

                    <v-row class="justify-content-center">
                        <v-col cols="12">
                            <UsuariosFiltro ref="UsuariosFiltroComponent" @emitir-cambio="moduloChange" />
                        </v-col>
                    </v-row>

                    <div class="mb-4 d-flex justify-content-center">
                        <v-btn color="primary" type="submit" class="col-4">
                            <v-icon class="mr-2" small>mdi-download</v-icon>
                            Descargar
                        </v-btn>

                    </div>

                    <v-divider class=""></v-divider>

                </form>

                <div  class="d-flex justify-content-center py-8">
                    <v-img max-width="250" class="text-center" src="/img/guides/hiring.svg"></v-img>
                </div>

            <!-- </v-row> -->
        </v-card-text>

    </v-main>
</template>
<script>
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import UsuariosFiltro from "./partials/UsuariosFiltro.vue";
export default {
    components: { UsuariosFiltro, ResumenExpand, ListItem },
    props: {
        Modulos: Array,
        API_REPORTES: "",
        API_FILTROS: ""
    },
    data() {
        return {
            Carreras: [],
            Grupos: [],
            modulo: "",
            carrera: "",
            grupo: "",
            loadingCarreras: false,
            loadingGrupos: false,
            infoDialog: false
        };
    },
    methods: {
        exportUsuariosDW() {
            $("#pageloader").fadeIn();
            let UFC = this.$refs.UsuariosFiltroComponent;
            let params = {
                modulo: this.modulo,
                carrera: this.carrera,
                grupo: this.grupo,
                UsuariosActivos: UFC.UsuariosActivos,
                UsuariosInactivos: UFC.UsuariosInactivos
            };
            axios
                .post(this.API_REPORTES + "usuarios", params)
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
        },
        moduloChange() {

            console.log('---------------------', this.$root.workspaceId)
            this.carrera = [];
            this.Carreras = [];
            this.grupo = [];
            this.Grupos = [];
            if (!this.modulo) return false;
            this.loadingCarreras = true;
            let UFC = this.$refs.UsuariosFiltroComponent;
            axios
                .post(this.API_FILTROS + "cambia_modulo_carga_carrera", {
                    mod: this.modulo.id,
                    UsuariosActivos: UFC.UsuariosActivos,
                    UsuariosInactivos: UFC.UsuariosInactivos,
                })
                .then((res) => {
                    console.log(res.data);
                    res.data.forEach((el) => {
                        console.log(el);
                        let NewJSON = {};
                        NewJSON.value = el.id;
                        NewJSON.text = el.nombre;
                        this.Carreras.push(NewJSON);
                    });
                    this.loadingCarreras = false;
                })
                .catch((err) => {
                    console.log(err);
                    this.loadingCarreras = false;
                });
        },
        carreraChange() {
            this.grupo = [];
            this.Grupos = [];
            if (!this.modulo) return false;
            this.loadingGrupos = true;
            let JsonCarreras = this.carrera.map((val) => val.value).join(",");
            let UFC = this.$refs.UsuariosFiltroComponent;
            axios
                .post(this.API_FILTROS + "cambia_carrera_carga_grupo", {
                    mod: this.modulo.id,
                    carre: JsonCarreras,
                    UsuariosActivos: UFC.UsuariosActivos,
                    UsuariosInactivos: UFC.UsuariosInactivos,
                })
                .then((res) => {
                    console.log(res);
                    res.data.forEach((el) => {
                        let NewJSON = {};
                        NewJSON.text = el.grupo;
                        NewJSON.value = el.id;
                        this.Grupos.push(NewJSON);
                    });
                    this.loadingGrupos = false;
                })
                .catch((err) => {
                    this.loadingGrupos = false;
                    console.log(err);
                });
        }
    }
};
</script>

<style></style>
