<template>
    <div>
        <header class="page-header">
            <div class="breadcrumb-holder container-fluid">
                <v-card-title>
                    Notas presenciales
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
                                            v-model="select.modulo"
                                            :items="arrays.modules"
                                            label="Módulos"
                                            item-text="name"
                                            item-value="id"
                                            dense
                                            :show-select-all="false"
                                            @onChange="loadSchools()"
                                            @onClickClear="arrays.courses = []; arrays.topics = []; select.course = null; select.evaluation_type = null; select.topics = [];"
                                          />
                                            <!-- multiple -->
                                            <!-- :count-show-values="3" -->
                                            <!-- :max-values-selected="1" -->
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultAutocomplete
                                            clearable
                                            label="Escuelas"
                                            dense
                                            v-model="select.school"
                                            :items="filteredSchools"
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
                                </v-row>
                                <v-row>
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
                                    <v-col cols="4">
                                        <DefaultAutocomplete
                                            clearable multiple
                                            label="Temas"
                                            dense
                                            v-model="select.topics"
                                            :items="arrays.topics"
                                            item-text="name"
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
                                            v-model="archivo"
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
const percentLoader = document.getElementById('percentLoader');
const number_socket = Math.floor(Math.random(6)*1000000);
export default {
    props:{
        user_id:{
            required:true
        }
    },
    data() {
        return {
            base_url: '/importar-notas',
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
                modules: [],
            },
            select: {
                module: null,
                school: null,
                course: null,
                evaluation_type: null,
                topics: []
            },
            filteredSchools: [],
        };
    },
    mounted() {
        this.getInitialData();
        window.Echo.channel(`upload-topic-grades.${this.user_id}.${number_socket}`).listen('MassiveUploadProgressEvent',result=>{
            if(percentLoader){
                if(result.percent){
                    percentLoader.innerHTML = `${result.percent}%`;
                }
            }
        })
    },
    methods: {
        getInitialData() {
            let vue = this;

            vue.$http.get(`${vue.base_url}/form-selects`)
                .then(({data}) => {
                    vue.arrays.directions = data.data.directions;
                    vue.arrays.schools = data.data.schools;
                    vue.arrays.modules = data.data.modules;
                });
        },
        async loadSchools() {
            let vue = this;

            // vue.arrays.schools = [];
            vue.filteredSchools = [];
            vue.arrays.courses = [];
            vue.arrays.topics = [];

            let alreadyAdded = []
            vue.filteredSchools = vue.arrays.schools.filter(s => {

                let subworkspaces_id = []

                s.subworkspaces.forEach((sw) => {

                    subworkspaces_id.push(sw.id)
                });

                if (subworkspaces_id.includes(vue.select.modulo) && !alreadyAdded.includes(s.id)) {

                    alreadyAdded.push(s.id)
                    return true
                }

                return false
                // s.subworkspaces.each(sw => {

                //     if (sw.id == vue.select.modulo && !alreadyAdded.includes(s.id)) {

                //         alreadyAdded.push(s.id)
                //         return true
                //     }

                //     return false
                // })

                // console.log('subworkspace')
                // console.log(subworkspace)
            })
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
            vue.select.topics = [];

            if (!vue.select.school || !vue.select.course) {
                vue.arrays.courses = [];
                vue.arrays.topics = [];
                return;
            }

            const school = vue.arrays.schools.find(school => school.id == vue.select.school);
            const course = school.courses.find(course => course.id == vue.select.course);
            const only_qualified_topics = vue.select.evaluation_type === 'assessable' ? 1 : 0;
            vue.arrays.topics = course.topics.filter(topic => topic.assessable == only_qualified_topics);
        },
        async uploadExcel() {
            let vue = this;
            if (!vue.archivo) {
                vue.showAlert("Aviso! Debe seleccionar un archivo", 'warning');
                return;
            }
            vue.showLoader()
            let formData = new FormData();
            formData.append("file", vue.archivo);
            const process_data = {  school: vue.select.school,
                                    course: vue.select.course,
                                    evaluation_type: vue.select.evaluation_type,
                                    topics: vue.select.topics };
            formData.append("process", JSON.stringify(process_data));
            formData.append("course", vue.select.course);
            formData.append("evaluation_type", vue.select.evaluation_type);
            formData.append("number_socket", number_socket);

            vue.select.topics.forEach(topic => formData.append("topics[]", topic));
            percentLoader.innerHTML = ``;
            await vue.$http.post(`${vue.base_url}/upload`, formData)
                .then(({data}) => {
                    // console.log(data.data.info);
                    const has_error_messages = (data.data) ? data.data.info.length > 0 : true;

                    if (!has_error_messages) {
                        const success_message = data.data.msg;
                        vue.queryStatus("subida_notas", "subir_notas");
                        vue.showAlert(success_message);
                        vue.hideLoader();
                        return;
                    }

                    let headers = ["DNI", "NOTA"];
                    let values =["dni", "nota", "info"];
                    vue.descargarExcelFromArray(
                        headers,
                        values,
                        data.data.info,
                        "No procesados_" + Math.floor(Math.random() * 1000),
                        "Se han encontrado observaciones. Descargar lista de observaciones"
                    );
                    vue.hideLoader();
                }).catch(err=>{
                    vue.hideLoader();
                    if(err.message){
                        alert(err.message);
                    }
                })
        },
        disabledButton() {
            let vue = this;

            if (!vue.select.school) return true;
            if (!vue.select.course) return true;
            if (vue.select.evaluation_tpye && vue.select.topics.length === 0) return true;
            // if (vue.select.topics.length === 0) return true;
            return !vue.archivo;
        },
        descargarPlantilla() {
            let vue = this;
            let headers = ["DNI", "NOTA", "INTENTOS", "VISITAS", "NÚMERO RESPUESTAS CORRECTAS",
                "NÚMERO RESPUESTAS INCORRECTAS", "FECHA DE EVALUACIÓN"];
            let values = ["dni", "nota", 'attempts','views', 'correct_answers', 'failed_answers', 'last_time_evaluated_at'];

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
