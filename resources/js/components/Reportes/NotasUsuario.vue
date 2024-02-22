<template>
    <v-main>
        <ResumenExpand>
            <template v-slot:resumen>
                Consulta el avance del usuario por cada tema desarrollado, dentro de los cursos que tiene
                asignado.
            </template>

            <!-- <list-item titulo="Tipo : Modalidad de escuela" subtitulo="R: Regular | E: Extracurricular | L: Libre" /> -->
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
        <b-input-group size="" class="mb-2 col-6 mt-4">
            <b-form-input
                class="col-7"
                v-model="search"
                placeholder="Documento"
                oninput="javascript: if (this.value.length > 50) this.value = this.value.slice(0, 50);"
                @keyup.enter="buscarNotasUsuario"
            ></b-form-input>
            <b-input-group-append>
                <b-input-group-append>
                    <b-button
                        variant="primary"
                        class="text-light"
                        @click="buscarNotasUsuario"
                        v-bind:disabled="this.search.length >= 4 ? false : true"
                    >
                        <b-icon icon="search" class="mr-2"></b-icon>
                        Consultar
                    </b-button>
                </b-input-group-append>
            </b-input-group-append>
        </b-input-group>
        <!-- Datos Usuario -->
        <div v-if="Usuario">
            <!-- <div class="text-h7 pl-3 mt-4 text-secondary">Datos personales</div> -->
            <v-simple-table class="border">
                <template v-slot:default>
                    <thead>
                        <tr class="text-grey font-weight-bold">
                            <th>Módulo</th>
                            <th>Nombre de usuario</th>
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
            <!-- <div class="text-h7 pl-3 mt-7 mb-5 text-secondary">
                Avance del usuario
            </div> -->
            <v-list dense>
                <v-subheader class="border text-body-2 align-center">
                    <v-row class="text-grey font-weight-bold" no-gutters>
                        <v-col class="col-curso px-4">Curso</v-col>
                        <v-col class="col-tipo_calificacion px-4">Sistema de calificación</v-col>
                        <v-col class="col-nota_prom px-4">Nota</v-col>
                        <v-col class="col-visitas px-4">Visitas</v-col>
                        <v-col class="col-reinicios px-4">Reinicios</v-col>
                        <v-col class="col-resultado px-4">Resultado</v-col>
                    </v-row>
                </v-subheader>
                <!-- For - Cursos -->
                <v-list-group
                    v-for="(item, index) in Cursos"
                    :key="index"
                    v-model="item.active"
                    :prepend-icon="item.action"
                    no-action
                    class="border notas-usuarios-list"
                    active-class="blue lighten-4 pl-2"
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
                                    v-if="item['convalidado_de'] && index === 'curso'"
                                    v-text="datoCurso"
                                    v-b-tooltip.hover
                                    :title="'Convalidado de: ' + item['convalidado_de']"
                                    class="text-body-2 white-space-normal" />

                                <v-list-item-title
                                    v-else
                                    v-text="datoCurso"
                                    class="text-body-2 white-space-normal" />

                            </v-list-item>
                        </v-list-item-content>
                    </template>
                    <v-list max-width="">
                        <v-subheader class="pl-0 pr-14">
                            <v-row class="text-center text-weight-bold text-body-2 align-center" no-gutters>
                                <v-col class="tema-col-tema">Tema</v-col>
                                <v-col class="tema-col-tipo_calificacion">Sistema de calificación</v-col>
                                <v-col class="tema-col-nota">Nota</v-col>
                                <v-col class="tema-col-score">Score</v-col>
                                <v-col class="tema-col-correctas">Correctas</v-col>
                                <v-col class="tema-col-incorrectas">Incorrectas</v-col>
                                <v-col class="tema-col-visitas">Visitas</v-col>
                                <v-col class="tema-col-reinicios">Reinicios</v-col>
                                <v-col class="tema-col-ultima_evaluacion">Últ. Eval</v-col>
                            </v-row>
                        </v-subheader>
                        <!-- For Temas -->
                        <v-list-group
                            v-for="(tema, index) in item.temas"
                            :key="index"
                            class="teal lighten-5 flex-wrap"
                        >
                            <template v-slot:activator>
                                <v-row no-gutters>
                                    <v-col
                                        v-for="(dato, index) in tema"
                                        :key="index"
                                        class="flex-wrap"
                                        :class="'tema-col-' + index"
                                        v-show="mostrarTema(index)"
                                    >
                                        <v-list-item-title
                                            v-text="dato"
                                            class="flex-wrap text-body-2 white-space-normal"
                                            :class="index == 'tema' ? 'text-left' : 'text-center'"
                                        ></v-list-item-title>
                                    </v-col>
                                </v-row>
                            </template>
                            <v-list v-if="tema.prueba">
                                <v-subheader>
                                    <v-row class="text-center" no-gutters>
                                        <v-col cols="">Pregunta</v-col>
                                        <v-col cols="">Respuesta Marcada</v-col>
                                        <v-col cols="">Respuesta Correcta</v-col>
                                    </v-row>
                                </v-subheader>
                                <v-list-item
                                    class="text-center px-2 border-top"
                                    v-for="(prueba, index) in tema.prueba"
                                    :key="index"
                                >
                                    <v-list-item-title
                                        v-if="prueba.pregunta"
                                        class="text-body-2 prueba-text">
                                        <small v-html="prueba.pregunta"></small>
                                    </v-list-item-title>
                                    <v-list-item-title
                                        v-if="prueba.respuesta_usuario"
                                        class="text-body-2 prueba-text">
                                        <small v-html="prueba.respuesta_usuario"></small>
                                    </v-list-item-title>
                                    <v-list-item-title
                                        v-if="prueba.respuesta_ok"
                                        class="text-body-2 prueba-text">
                                        <small v-html="prueba.respuesta_ok"></small>
                                    </v-list-item-title>
                                </v-list-item>
                            </v-list>
                            <div v-else class="prueba-nondata">No hay datos</div>
                        </v-list-group>
                    </v-list>
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

        if (param_dni)
        {
            vue.search = param_dni

            this.buscarNotasUsuario()
            // console.log('VINO Documento')
        }
        // vue.getSelects();
        // vue.filters.tipo_criterio = vue.tipo_criterio_id
    },
    methods: {
        async buscarNotasUsuario() {
            if (this.search.length < 4) return false;

            let vue = this
            // Show loading spinner
            this.showLoader()

            $("#pageloader-text").text(
                `Por favor espera a que se genere tu reporte (Puede haber reportes en
            cola procesándose).`
            );
            this.Cursos = "";
            this.Usuario = "";
            let courses_assigned =[];
            await axios.get(`/users/${this.search}/current-courses`).then(({data})=>{
                courses_assigned = data.data.courses_id;
            })
            let urlReport = `${this.$props.reportsBaseUrl}/exportar/notas_usuario_v3`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        document: this.search,
                        course_ids:courses_assigned
                    }
                })
                vue.queryStatus("reportes", "consultar")

                if (response.data.alert) {
                    this.Alert = response.data.alert
                }

                this.Cursos = response.data.courses
                this.Usuario = response.data.user

            } catch (ex) {

            }
            this.hideLoader()

        },
        titulosCurso(index) {
            let indexTitulos = ["modalidad", "tipo_calificacion", "curso", "nota_prom", "visitas", "reinicios", "resultado"];
            if (indexTitulos.includes(index)) return true;
        },
        mostrarTema(index) {
            let indexTitulos = ["prueba"];
            if (!indexTitulos.includes(index)) return true;
        },
        obt_texto_tooltip(text){
            if(text == 'extra') text = 'extracurricular';
            return text.toUpperCase();
        }
        // columnSize(index) {
        // 	switch (index) {
        // 		case "tema":
        // 			return "5";
        // 			break;
        // 		default:
        // 			return "";
        // 			break;
        // 	}
        // }
    }
}
</script>
<style lang="scss">
    .col-modalidad {
        flex: 0 0 5%;
        max-width: 5%;
    }
    .col-curso {
        flex: 0 0 25%;
        max-width: 25%;
    }
    .col-tipo_calificacion {
        flex: 0 0 18%;
        max-width: 18%;
    }
    .col-nota_prom {
        flex: 0 0 13%;
        max-width: 13%;
    }
    .col-visitas {
        flex: 0 0 13%;
        max-width: 13%;
    }
    .col-reinicios {
        flex: 0 0 13%;
        max-width: 13%;
    }
    .col-resultado {
        flex: 0 0 14%;
        max-width: 14%;
    }
    // Temas
    .tema-col-tema {
        flex: 0 0 20%;
        max-width: 20%;
    }

    .tema-col-tipo_calificacion {
        flex: 0 0 10%;
        max-width: 10%;
    }

    .tema-col-nota,
    .tema-col-score,
    .tema-col-correctas,
    .tema-col-incorrectas,
    .tema-col-visitas,
    .tema-col-reinicios,
    .tema-col-ultima_evaluacion {
        flex: 0 0 10%;
        max-width: 10%;
    }

    .tema-col-resultado {
        text-transform: capitalize;
    }
    /*  */
    .prueba-text {
        font-size: 12px !important;
        font-weight: normal;
        height: 100%;
        white-space: normal;
        padding: 0.5rem 0;
    }
    .prueba-nondata {
        text-align: center;
        height: 30px;
        line-height: 30px;
        font-weight: 600;
        color: black;
        background: #e4e4e4;
    }
    .white-space-normal {
        white-space: normal;
    }
    .header-icon {
        justify-content: center;
        display: flex;
        align-items: center;
    }
</style>
