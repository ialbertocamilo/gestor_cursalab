<template>
    <v-main>
        <ResumenExpand>
            <template v-slot:resumen>
                Consulta el avance del usuario por cada tema desarrollado, dentro de los cursos que tiene
                asignado.
            </template>

            <list-item titulo="Tipo : Modalidad de escuela" subtitulo="R: Regular | E: Extracurricular | L: Libre" />
            <list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" />
            <list-item titulo="Sistema de calificación" subtitulo="El sistema de calificación asignado al curso" />
            <list-item titulo="Tema" subtitulo="Tema dentro de cada curso" />
            <list-item titulo="Nota" subtitulo="Nota correspondiente a un tema evaluable y calificado" />
            <list-item
                titulo="Resultado"
                subtitulo="Resultado de cada evaluación, considerando la nota mínima aprobatoria configurada"
            />
            <list-item
                titulo="Última evaluación"
                subtitulo="Fecha de la última evaluación realizada de cada tema"
            />
        </ResumenExpand>
        <!-- Formulario del reporte -->
        <v-row>
            <b-input-group size="" class="mb-2 col-6 mt-4">
                <b-form-input
                    class="col-7"
                    v-model="search"
                    type="number"
                    placeholder="Documento"
                    oninput="javascript: if (this.value.length > 15) this.value = this.value.slice(0, 15);"
                    @keyup.enter="buscarNotasUsuario"
                ></b-form-input>
                <b-input-group-append>
                    <b-input-group-append>
                        <b-button
                            variant="primary"
                            class="text-light button"
                            @click="buscarNotasUsuario"
                            v-bind:disabled="this.search.length >= 8 ? false : true"
                        >
                            <b-icon icon="search" class="mr-2"></b-icon>
                            Consultar
                        </b-button>
                    </b-input-group-append>
                </b-input-group-append>
            </b-input-group>
            <b-input-group class="mb-2 col-6 mt-4 d-flex justify-content-start">
                <b-button
                    @click="downloadReport()"
                    v-bind:disabled="this.search.length >= 8 ? false : true"
                    variant="primary"
                    class="text-light button">
                    Descargar
                </b-button>
            </b-input-group>
        </v-row>
        <!-- Datos Usuario -->
        <div v-if="Usuario">
            <div class="text-h7 pl-3 mt-4 text-secondary">Datos personales</div>
            <v-simple-table class="border">
                <template v-slot:default>
                    <thead>
                        <tr class="text-grey font-weight-bold">
                            <th>Modulo</th>
                            <th>Nombres</th>
                            <th>Documento</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        <tr>
                            <td>{{ Usuario.module.name }}</td>
                            <td>{{ `${Usuario.user.name} ${Usuario.user.lastname} ${Usuario.user.surname}` }}</td>
                            <td>{{ Usuario.user.document }}</td>
                        </tr>
                    </tbody>
                </template>
            </v-simple-table>
        </div>
        <!-- Tabla ~ Cursos-->
        <div v-if="Cursos">
            <div class="text-h7 pl-3 mt-7 mb-5 text-secondary">
                Historial del usuario
            </div>
            <v-list dense>
                <v-subheader class="border text-body-2 align-center">
                    <v-row class="text-grey font-weight-bold" no-gutters>
                        <v-col class="col-schools_names px-4">Escuelas</v-col>
                        <v-col class="col-course_name px-4">Curso</v-col>
                        <v-col class="col-tipo_calificacion px-4">Sistema de calificación</v-col>
                        <v-col class="col-grade px-4">Nota</v-col>
                        <v-col class="col-course_status px-4">Estado</v-col>
                    </v-row>
                </v-subheader>
                <!-- For - Cursos -->
                <v-list-group
                    v-for="(item, index) in Cursos"
                    :key="index"
                    v-model="item.active"
                    :prepend-icon="item.action"
                    no-action
                    :ripple="false"
                    class="border notas-usuarios-list"
                >
                    <template v-slot:activator>
                        <v-list-item-content
                            v-for="(datoCurso, index) in item"
                            :key="index"
                            v-show="titulosCurso(index)"
                            :class="'col-' + index"
                        >
                            <v-list-item>
                                <v-list-item-title
                                    v-if="index != 'modalidad'"
                                    v-text="datoCurso"
                                    class="text-body-2 white-space-normal" />

                                <v-list-item-title
                                    v-else
                                    class="text-body-2 white-space-normal">
                                    <b-badge v-text="datoCurso.charAt(0).toUpperCase()"
                                             n v-b-tooltip.hover
                                             :title="obt_texto_tooltip(datoCurso)"></b-badge>
                                </v-list-item-title>
                            </v-list-item>
                        </v-list-item-content>
                    </template>

                </v-list-group>
            </v-list>
        </div>
        <v-alert v-if="Alert"
                 dense text
                 color="warning"
                 width="80%"
                 class="mx-3 py-5">
            {{ Alert }}
        </v-alert>
        <!-- Alert -->
    </v-main>
</template>
<script>

import ListItem from "./partials/ListItem.vue"
import ResumenExpand from "./partials/ResumenExpand.vue"

export default {
    components: { ResumenExpand, ListItem },
    props: {
        workspaceId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            search: "",
            Cursos: "",
            Usuario: null,
            Alert: ""
        };
    },
    mounted() {
        let vue = this

        // console.log('window location search')
        let uri = window.location.search.substring(1);
        let params = new URLSearchParams(uri);
        let param_dni = params.get("dni");

        if (param_dni) {
            vue.search = param_dni

            this.buscarNotasUsuario()
            // console.log('VINO Documento')
        }
        // vue.getSelects();
        // vue.filters.tipo_criterio = vue.tipo_criterio_id
    },
    methods: {
        async buscarNotasUsuario() {
            if (this.search.length < 8) return false;

            // Show loading spinner
            this.showLoader()

            $("#pageloader-text").text(
                `Por favor espera a que se genere tu reporte (Puede haber reportes en
            cola procesándose).`
            );
            this.Cursos = "";
            this.Usuario = "";

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/historial_usuario`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        document: this.search
                    }
                })

                if (response.data.alert) {
                    this.Alert = response.data.alert
                }

                this.Cursos = response.data.courses
                this.Usuario = response.data.user

            } catch (ex) {

            }
            this.hideLoader()

        },
        async downloadReport() {
            let vue = this
            // show loading spinner

            this.showLoader()

            // Perform request to download report

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/historial_usuario`

            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        type: 'excel',
                        document: this.search
                    }
                })

                // When there are no results notify user,
                // download report otherwise

                if (response.data.alert) {
                    this.showAlert(response.data.alert, 'warning')
                } else {
                    vue.queryStatus("reportes", "descargar_reporte_historial_usuario");
                    // Emit event to parent component
                    response.data.new_name = this.generateFilename(
                        'Historial usuario',
                        this.search
                    )
                    response.data.selectedFilters = {
                        'Documento': this.search
                    }
                    this.$emit('emitir-reporte', response)
                }

            } catch (ex) {
                console.log(ex.message)
            }

            // Hide loading spinner

            this.hideLoader()
        },
        titulosCurso(index) {
            let indexTitulos = ["schools_names","course_name", "tipo_calificacion", "grade", "course_status"];
            if (indexTitulos.includes(index)) return true;
        }
    }
}
</script>
<style lang="scss" scoped>

    .col-schools_names {
        flex: 0 0 20%;
        max-width: 20%;
    }

    .col-course_name {
        flex: 0 0 20%;
        max-width: 20%;
    }

    .col-tipo_calificacion {
        flex: 0 0 15%;
        max-width: 15%;
    }

    .col-topic_name {
        flex: 0 0 20%;
        max-width: 20%;
    }

    .col-grade {
        flex: 0 0 20%;
        max-width: 20%;
    }

    .col-course_status {
        flex: 0 0 20%;
        max-width: 20%;
    }

    .button {
        height: 36px;
    }

    .white-space-normal {
        white-space: normal;
    }

</style>
<style>
.v-list-group__header__append-icon {
    display: none
}
</style>
