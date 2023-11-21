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

                    <v-tab href="#tab-1" :key="1" class="primary--text">
                        <v-icon>mdi-text-box-outline</v-icon>
                        <span class="ml-3">Perfil de usuario</span>
                    </v-tab>

                    <v-tab
                        href="#tab-2"
                        :key="2"
                        class="primary--text"
                        v-if="$root.isSuperUser"
                    >
                        <v-icon>mdi-text-box-edit-outline</v-icon>
                        <span class="ml-3">Cursos</span>
                    </v-tab>

                    <v-tab
                        href="#tab-3"
                        :key="3"
                        class="primary--text"
                        v-if="$root.isSuperUser"
                    >
                        <v-icon>mdi-text-box-search-outline</v-icon>
                        <span class="ml-3">Estadística</span>
                    </v-tab>

                </v-tabs>

                <v-tabs-items v-model="tabs">

                    <v-tab-item :key="1" :value="'tab-1'">
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
                                            <v-icon small>mdi-account-circle</v-icon>
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

                    </v-tab-item>

                    <v-tab-item :key="2" :value="'tab-2'" v-if="$root.isSuperUser">

                        <div v-if="courses.regular_schools"> 
                            <DefaultSection :title="school.name" v-for="(school, s_idx) in courses.regular_schools" :key="'block-school-' + s_idx">
                                <template v-slot:content>

                                    <div v-for="(course, crs_idx) in school.courses" :key="'block-course-' + crs_idx">

                                        <h6>{{ course.name }}</h6>

                                        <v-simple-table
                                            fixed-header
                                            style="max-height: 300px;"
                                          >
                                            <template v-slot:default>
                                              <thead>
                                                <tr>
                                                  <th class="text-left" width="400">
                                                    Tema
                                                  </th>
                                                  <th class="text-center">
                                                    Estado
                                                  </th>
                                                  <th class="text-center">
                                                    Nota
                                                  </th>
                                                </tr>
                                              </thead>
                                              <tbody>
                                                <tr
                                                  v-for="(topic, ti) in course.temas"
                                                  :key="'block-topic-' + ti"
                                                >
                                                  <td>{{ topic.name }}</td>
                                                  <td align="center">{{ topic.estado_str || 'No definido' }}</td>
                                                  <td align="center">{{ topic.nota || '-' }}</td>
                                                </tr>
                                              </tbody>
                                            </template>
                                          </v-simple-table>
                                        
                                    </div>

                                </template>
                            </DefaultSection>
                        </div>

                    </v-tab-item>

                    <v-tab-item :key="3" :value="'tab-3'" v-if="$root.isSuperUser">

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

                       
                    </v-tab-item>

                </v-tabs-items>

            </v-row>
        </template>
    </DefaultDialog>

</template>

<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            tabs: 1,
            profile: {
                user: [],
                criteria: [],
            },
            courses: {
                extracurricular_schools: [],
                free_schools: [],
                regular_schools: [],
                summary_user: [],
            }
        }
    },
    methods: {
        closeModal() {
            let vue = this;

            // vue.resource = {
            //     id: '',
            //     fullname: null,
            //     schools: [],
            // };

            vue.$emit('onCancel')
        },
        confirmModal() {

        },
        resetValidation() {

        },
        async loadData(resource) {
            let vue = this
            let url = `${vue.options.base_endpoint}/${resource.id}/get-profile`
            vue.showLoader();
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.profile = data.data.profile
                    vue.courses = data.data.courses

                    vue.hideLoader();
                    // setTimeout(() => {
                    // }, 3000)
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
