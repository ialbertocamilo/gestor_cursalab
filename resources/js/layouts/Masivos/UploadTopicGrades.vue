<template>
    <div>
        <header class="page-header">
            <div class="breadcrumb-holder container-fluid">
                <v-card-title>
                    Pruebas Presenciales
                </v-card-title>
            </div>
        </header>
        <section class="client section-list">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <v-card class=" card">
                            <v-card-text>
                                <v-row class="px-4">
                                    <v-col cols="12" md="12" lg="12">
                                        <p class="label_form">
                                            <strong>Indicaciones</strong>
                                        </p>
                                        <ul>
                                            <li v-for="(direction, index) in arrays.directions" :key="index">
                                                {{ direction }}
                                            </li>
                                            <a class="mt-2 pt-2 pr-8" href="#" @click="descargarPlantilla">
                                                <i class="fas fa-file-download"></i> Descargar plantilla
                                            </a>
                                        </ul>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="4">
                                        <DefaultAutocomplete
                                            clearable
                                            label="Escuelas"
                                            dense
                                            v-model="select.school"
                                            :items="arrays.schools"
                                            item-text="name"
                                            :show-select-all="false"
                                            @onChange="loadCourses()"
                                            @onClickClear="arrays.courses = []; arrays.topics = []; select.course = null; select.evaluation_type = null; select.topics = [];"
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultAutocomplete
                                            clearable
                                            label="Cursos"
                                            dense
                                            v-model="select.course"
                                            :items="arrays.courses"
                                            item-text="name"
                                            :show-select-all="false"
                                            @onClickClear=" arrays.topics = []; select.evaluation_type = null; select.topics = [];"
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultAutocomplete
                                            clearable
                                            label="Tipo de evaluación"
                                            dense
                                            v-model="select.evaluation_type"
                                            :items="arrays.evaluation_types"
                                            item-text="name"
                                            :show-select-all="false"
                                            @onChange="loadTopics()"
                                            @onClickClear="arrays.topics = []; select.topics = [];"
                                        />
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="8">
                                        <DefaultAutocomplete
                                            clearable multiple
                                            label="Temas"
                                            dense
                                            v-model="select.topics"
                                            :items="arrays.topics"
                                            item-text="name"
                                            :show-select-all="false"
                                            :count-show-values="3"
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <v-file-input
                                            dense
                                            ref="file"
                                            color="primary"
                                            label="Seleccione su archivo"
                                            placeholder="Seleccione su archivo"
                                            outlined
                                            @change="handleFileUpload"
                                        />
                                    </v-col>
                                    <v-col cols="12" md="12" lg="12" class="d-flex justify-center"
                                           style="align-items: end">
                                        <v-btn outlined :disabled="disabledButton()" @click="uploadExcel">
                                            Subir
                                        </v-btn>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    data() {
        return {
            base_url: '/masivos/importar-notas',
            archivo: null,
            arrays: {
                evaluation_types: [
                    {id: 'assessable', name: 'Evaluable'},
                    {id: 'not-assessable', name: 'No evaluable'},
                ],
                directions: [],
                schools: [],
                courses: [],
                topics: [],
            },
            select: {
                school: null,
                course: null,
                evaluation_type: null,
                topics: []
            },
        };
    },

    mounted() {
        this.getInitialData();
    },
    methods: {
        getInitialData() {
            let vue = this;

            vue.$http.get(`${vue.base_url}/form-selects`)
                .then(({data}) => {
                    vue.arrays.directions = data.data.directions;
                    vue.arrays.schools = data.data.schools;
                });
        },
        loadCourses() {
            let vue = this;

            if (!vue.select.school) {
                vue.arrays.courses = [];
                return;
            }

            const school = vue.arrays.schools.find(school => school.id == vue.select.school)
            vue.arrays.courses = school.courses;
        },
        loadTopics() {
            let vue = this;

            if (!vue.select.school || !vue.select.course ) {
                vue.arrays.courses = [];
                vue.arrays.topics = [];
                return;
            }

            const school = vue.arrays.schools.find(school => school.id == vue.select.school);
            const course = school.courses.find(course => course.id == vue.select.course);
            const only_qualified_topics = vue.select.evaluation_type === 'assessable' ? 1: 0;
            vue.arrays.topics = course.topics.filter(topic => topic.assessable == only_qualified_topics);
        },
        uploadExcel() {
            let vue = this;
            if (!vue.archivo) {
                vue.showAlert("Aviso! Debe seleccionar un archivo", 'warning');
                return;
            }
            vue.showLoader()
            let formData = new FormData();
            formData.append("file", vue.archivo);
            formData.append("course", vue.select.coursee);
            vue.select.topics.forEach(topic => formData.append("topics[]", topic));

            vue.$http.post(`${vue.base_url}/upload`, formData)
                .then(({data}) => {
                    console.log(data);
                    /* if (data.info.length > 0) {
                         let headers = ["DNI", "NOTA (Opcional)"];
                         let values = ["dni", "nota"];
                         vue.descargarExcelFromArray(
                             headers,
                             values,
                             data.info,
                             "No procesados_" + Math.floor(Math.random() * 1000),
                             "Se han encontrado observaciones. Descargar lista de observaciones"
                         );
                     } else {
                         vue.showAlert("Se ha procesado correctamente el excel.");
                     }
                     vue.hideLoader()*/
                })
                .catch((err) => {

                    vue.hideLoader()
                });
        },
        disabledButton() {
            let vue = this;

            if (!vue.select.school) return true;
            if (!vue.select.course) return true;
            if (vue.select.topics.length === 0) return true;
            return !vue.archivo;
        },
        handleFileUpload() {
            let vue = this;
            vue.archivo = this.$refs.file.files[0];
        },
        descargarPlantilla() {
            let vue = this;
            // let headers = ["DNI", "NOTA","FECHA DE EVALUACIÓN (FORMATO: 27/09/2021)","Hora (FORMATO 24 horas ejemplo: 14:28)"];
            // let values = ["dni", "nota","last_ev","hora"];
            //   Comment para hacer merge
            let headers = ["DNI", "NOTA"];
            let values = ["dni", "nota"];
            vue.descargarExcelFromArray(
                headers,
                values,
                [],
                "Plantilla_NotasPresenciales",
                "¿Descargar plantilla?",
                true
            );
        },

    },
};
</script>

<style>
.v-application--wrap {
    min-height: unset !important;
}
</style>
