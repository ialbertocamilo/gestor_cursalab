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

                <!-- <div v-if="courses.regular_schools">  -->
                    <DefaultSection :title="current_topic.name" class="w-90">
                        <template v-slot:content>

                
                            <div class="mx-3 pb-2">

                                <v-simple-table light class="-----theme-light rounded-0"
                                  >
                                    <!-- fixed-header -->
                                    <!-- style="max-height: 300px;" -->
                                    <template v-slot:default>
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th class="text-left">Pregunta</th>
                                                <th class="text-center">Puntos</th>
                                                <!-- <th class="text-center">Nota</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                              v-for="(row, r) in current_topic.respuestas"
                                              :key="'block-topic-' + r"
                                            >
                                                <td align="center">{{ r + 1 }}</td>
                                                <td class="py-2">
                                                    <span class="mb-1">{{ row.pregunta }}</span> <br>
                                                    <span :class="row.respuesta.es_correcta ? 'text-primary' : 'text-red'">- {{ row.respuesta.marcada }}</span> <br v-if="!row.respuesta.es_correcta">
                                                    <span v-if="!row.respuesta.es_correcta" class="--text-blue"><strong>- Correcta: </strong>{{ row.respuesta.correcta }}</span>
                                                </td>
                                                <td align="center" :class="row.respuesta.es_correcta ? 'text-bold' : 'text-line-through'">{{ row.respuesta.puntos }}</td>
                                            </tr>
                                        </tbody>
                                    </template>
                                </v-simple-table>

                            </div>


                        </template>
                    </DefaultSection>
                <!-- </div> -->


            </v-row>
        </template>

    </DefaultDialog>

</template>

<script>

// import UsuarioEvaluationModal from "./UsuarioEvaluationModal";

export default {
    // components: {UsuarioEvaluationModal},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String,
        current_topic: {
            type: Object | Array,
            required: true
        }
    },
    data() {
        return {
            // tabs: 1,
            // current_topic: null,
            // profile: {
            //     user: [],
            //     criteria: [],
            // },
            // courses: {
            //     extracurricular_schools: [],
            //     free_schools: [],
            //     regular_schools: [],
            //     summary_user: [],
            // },
            
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
            
            // let vue = this
            // let url = `${vue.options.base_endpoint}/${resource.id}/get-profile`
            // vue.showLoader();

            // await vue.$http.get(url)
            //     .then(({data}) => {
            //         vue.profile = data.data.profile
            //         vue.courses = data.data.courses
            //         vue.questions = data.data.questions

            //         vue.hideLoader();
            //     })
            //     .catch(e => {
            //         vue.hideLoader();
            //     });
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
