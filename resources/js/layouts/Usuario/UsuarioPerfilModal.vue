<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
    >
        <template v-slot:content>

            <v-row justify="space-around" class="mt-5 mx-3">

                <v-tabs fixed-tabs v-model="tabs">

                   <!--  <v-tab href="#tab-1" :key="1" class="primary--text">
                        <v-icon>mdi-account</v-icon>
                        <span class="ml-3">Perfil de usuario</span>
                    </v-tab> -->

                    <v-tab
                        href="#tab-2"
                        :key="2"
                        class="primary--text"
                    >
                        <v-icon>mdi-chart-areaspline-variant</v-icon>
                        <span class="ml-3">Progreso de usuario</span>
                    </v-tab>

                   <!--  <v-tab
                        href="#tab-3"
                        :key="3"
                        class="primary--text"
                        v-if="$root.isSuperUser"
                    >
                        <v-icon>mdi-text-box-search-outline</v-icon>
                        <span class="ml-3">Estadística</span>
                    </v-tab> -->

                </v-tabs>

                <v-tabs-items v-model="tabs" class="w-90">

                <!--     <v-tab-item :key="1" :value="'tab-1'">
                        <v-row class="--justify-content-center pt-4">
                            <v-col cols="12">

                                <v-card v-if="profile.user">
                                    <v-img
                                      :src="profile.background"
                                      class="white--text align-end"
                                      gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)"
                                      height="200px"
                                    >
                                      <v-card-title v-text="profile.user.fullname"></v-card-title>
                                    </v-img>

                                    <div class="py-2">

                                        <v-chip
                                            class="mx-2 mt-2"
                                            color="indigo"
                                            text-color="white"
                                            v-for="(criterion, c_idx) in profile.criteria"
                                            :title="criterion.tipo"
                                            small
                                            :key="'crit-' + c_idx"
                                        >
                                          <v-avatar left >
                                            <v-icon small>mdi-square</v-icon>
                                          </v-avatar>
                                          {{ criterion.valor }}
                                        </v-chip>
                       
                                    </div>
                                  </v-card>
                                
                            </v-col>

                        </v-row>
                        <v-row class="justify-content-center">
                            <v-col cols="6">
                               
                            </v-col>
                            <v-col cols="6">
                                
                            </v-col>
                        </v-row>

                    </v-tab-item> -->

                    <v-tab-item :key="2" :value="'tab-2'" class="---w-90">

                        <div v-if="courses.regular_schools" class="mt-3"> 
                            <DefaultSection :title-default="false" v-for="(school, s_idx) in courses.regular_schools" :key="'block-school-' + s_idx">
                                <template v-slot:title>
                                    <div class="pt-4">
                                        <span class="text-h6">{{ school.name }} <small>({{ school.courses.length }} {{ school.courses.length > 1 ? 'cursos' : 'curso'  }} / {{ school.porcentaje }}% avance)</small></span>
                                    </div>
                                </template>
                                <template v-slot:content>

                                    <div v-for="(course, crs_idx) in school.courses" :key="'block-course-' + crs_idx" class="my-4">

                                        <!-- :color="'#1F7087'" -->
                                        <!-- dark -->
                                        <v-card
                                            class="rounded-0"
                                        >
                                            <div class="d-flex flex-no-wrap justify-space-between">
                                              <div>
                                                <v-card-title
                                                  class="subtitle-1 text-bold text--darken-2 grey--text"
                                                >
                                                  <!-- v-text="course.name" -->
                                                    <strong>{{ course.name }}</strong>
                                                </v-card-title>

                                                <!-- <v-card-subtitle v-text="item.artist"></v-card-subtitle> -->
                                                
                                                <div class="mx-2">
                                                    <v-chip
                                                        class="mx-1 px-2 rounded-0 --mt-2"
                                                        :title="'Estado del curso'"
                                                        small
                                                        outlined
                                                    >
                                                        <!-- color="primary" -->
                                                        <!-- text-color="white" -->
                                                      <!-- <v-avatar left >
                                                        <v-icon small>mdi-square</v-icon>
                                                      </v-avatar> -->
                                                      <span class="pr-2">Estado:</span> {{ course.estado_str }}
                                                    </v-chip>

                                                    <v-chip
                                                        class="mx-1 px-2 rounded-0 --mt-2"
                                                        :title="course.nota_sistema"
                                                        small
                                                        outlined
                                                        v-if="course.nota"
                                                    >
                                                        <!-- color="primary" -->
                                                        <!-- text-color="white" -->
                                                      <!-- <v-avatar left >
                                                        <v-icon small>mdi-square</v-icon>
                                                      </v-avatar> -->
                                                      <span class="pr-2">Nota promedio:</span> {{ course.nota }}
                                                    </v-chip>

                                                    <v-chip
                                                        class="mx-1 px-2 rounded-0 --mt-2"
                                                        :title="course.nota_sistema"
                                                        small
                                                        outlined
                                                        v-if="course.tag_ciclo"
                                                    >
                                                       {{ course.tag_ciclo }}
                                                    </v-chip>
                                                </div>



                                                <v-card-actions>
                                            
                                                    <!-- outlined
                                                    rounded -->
                                                    <!-- small -->
                                                  <a
                                                    class="ml-2 mt-2 text-primary"
                                                    @click="course.show_topics = !course.show_topics"
                                                  >
                                                    {{ course.show_topics ? 'Ocultar temas': 'Ver temas' }} ({{ course.temas.length }})
                                                    <v-icon small color="primary">{{ course.show_topics ? 'mdi-chevron-up': 'mdi-chevron-down' }}</v-icon>
                                                  </a>
                                                </v-card-actions>
                                              </div>

                                              <v-avatar
                                                class="ma-3"
                                                tile
                                                width="200"
                                                height="100"
                                              >
                                                <!-- size="125" -->
                                                <v-img :src="course.image"></v-img>
                                              </v-avatar>
                                            </div>

                                            <div class="mx-3 pb-2" v-show="course.show_topics">

                                                <v-simple-table light class="-----theme-light rounded-0"
                                                  >
                                                    <!-- fixed-header -->
                                                    <!-- style="max-height: 300px;" -->
                                                    <template v-slot:default>
                                                        <thead>
                                                            <tr>
                                                                <th class="text-left" width="400">Tema</th>
                                                                <th class="text-center">Visitas</th>
                                                                <th class="text-center">Estado</th>
                                                                <th class="text-center">Nota</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr
                                                              v-for="(topic, ti) in course.temas"
                                                              :key="'block-topic-' + ti"
                                                            >
                                                                <td>{{ topic.name }}</td>
                                                                <td align="center">{{ topic.visitas || '-' }}</td>
                                                                <td align="center">{{ topic.estado_str || 'No definido' }}</td>
                                                                <!-- <td align="center"><span :title="topic.nota_sistema">{{ topic.nota || '-' }}</span></td> -->
                                                                <td align="center">
                                                                    <span title="Ver detalle" v-if="topic.respuestas.length > 0 && topic.nota">
                                                                        <a href="javascript:;" @click="showEvaluationDetail(topic)">{{ topic.nota || '-' }}</a>
                                                                    </span>
                                                                    <span v-else>{{ topic.nota || '-' }}</span>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </template>
                                                </v-simple-table>

                                            </div>

                                        </v-card>
                                        
                                    </div>

                                </template>
                            </DefaultSection>
                        </div>

                    </v-tab-item>

                    <!-- <v-tab-item :key="3" :value="'tab-3'" v-if="$root.isSuperUser">

                        <DefaultSection title="Configuración de sistema de calificación" v-if="$root.isSuperUser">
                            <template v-slot:content>
                                <v-row justify="space-around">
                                    <v-col cols="6">
                                        
                                    </v-col>
                                    <v-col cols="6" class="d-flex">
                                        
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>

                       
                    </v-tab-item> -->

                </v-tabs-items>

            </v-row>

            <UsuarioEvaluationModal
                width="65vw"
                :ref="modalEvaluationOptions.ref"
                :options="modalEvaluationOptions"
                :current_topic="current_topic"
                @onCancel="closeFormModal(modalEvaluationOptions)"
            />

        </template>

    </DefaultDialog>

</template>

<script>

import UsuarioEvaluationModal from "./UsuarioEvaluationModal";

export default {
    components: {UsuarioEvaluationModal},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            tabs: 2,
            current_topic: [],
            profile: {
                user: [],
                criteria: [],
            },
            courses: {
                extracurricular_schools: [],
                free_schools: [],
                regular_schools: [],
                summary_user: [],
            },
            modalEvaluationOptions: {
                ref: 'UsuarioEvaluationModal',
                open: false,
                base_endpoint: '/usuarios',
                cancelLabel: 'Cerrar',
                hideConfirmBtn: true,
                title: 'Detalle de evaluación',
                persistent: true
            },
        }
    },
    methods: {
        closeModal() {
            let vue = this;

            vue.tabs = 1
            vue.profile = []
            vue.courses = []
            vue.current_topic = []
            // vue.courses =  {
            //     extracurricular_schools: [],
            //     free_schools: [],
            //     regular_schools: [],
            //     summary_user: [],
            // };

            vue.$emit('onCancel')
        },
        confirmModal() {

        },
        resetValidation() {
        },
        showEvaluationDetail(topic) {
            let vue = this;

            vue.current_topic = topic;
            vue.modalEvaluationOptions.open = true;
        },
        async loadData(resource) {

            let vue = this
            
            vue.profile = []
            vue.courses = []
            vue.current_topic = []
            
            let url = `${vue.options.base_endpoint}/${resource.id}/get-profile`
            vue.showLoader();

            await vue.$http.get(url)
                .then(({data}) => {
                    vue.profile = data.data.profile
                    vue.courses = data.data.courses
                    // vue.questions = data.data.questions

                    vue.hideLoader();
                })
                .catch(e => {
                    vue.hideLoader();
                });
        },
        loadSelects() {

        }
    }
}
</script>

<style>
.simple-table .v-data-table__wrapper {
    min-height: 50px;
    max-height: 450px;
    overflow-x: auto;
    overflow-y: auto;
}
</style>
