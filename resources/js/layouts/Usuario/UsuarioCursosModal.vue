<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
    >
        <template v-slot:content>
            <v-row justify="space-around" class="mt-5" no-gutters>

                <v-col cols="12" class="d-flex justify-content-center">
                    <v-chip class="mr-2 my-2" color="primary" label>
                        Grupo: {{ resource.nom_grupo || '' }}
                    </v-chip>
                    <v-chip class="ma-2" color="primary" label>
                        Carrera: {{ resource.nom_carrera || '' }}
                    </v-chip>
                    <v-chip class="ma-2" color="primary" label>
                        Ciclo: {{ resource.nom_ciclo || '' }}
                    </v-chip>
                </v-col>
            </v-row>
            <v-row justify="space-around" class="mt-5 mx-3">
                <v-col cols="12">
                    <!-- <v-simple-table style="width: 100%" dense> -->
                    <!-- <template v-slot:default> -->
                    <!--        <thead>
                           <tr class="primary">
                               <th class="text-left text-white" v-text="'Escuela'"/>
                               <th class="text-left text-white" v-text="'Cursos'"/>
                           </tr>
                           </thead> -->
                    <template v-if="resource.categorias.length === 0">
                        <!-- <tr> -->
                        <div class="text-center">
                            <p class="text-h7 font-weight-bold pt-4">No tiene cursos asignados</p>
                        </div>
                        <!-- </tr> -->
                    </template>
                    <template v-else>
                        <!--     <template v-for="escuela in resource.categorias">
                                <tr class="text-left" v-for="(curso, index2) in escuela.cursos" :key="curso.id">
                                    <td v-if="index2 === 0" :rowspan="escuela.cursos.length">
                                        {{ escuela.nombre }}
                                    </td>
                                    <td>{{ curso.nom_curso }}</td>
                                </tr>
                            </template> -->

                        <v-tabs vertical>
                            <v-tab v-for="escuela in resource.categorias" :key="escuela.id"
                                   class="justify-content-start" :title="escuela.nombre">
                                <v-icon left>
                                    mdi-school
                                </v-icon>
                                {{ escuela.nombre }}
                            </v-tab>

                            <v-tab-item v-for="escuela in resource.categorias" :key="escuela.id"
                                        class="ml-10 elevation-1">
                                <v-card flat class="">
                                    <v-card-text>
                                        <v-simple-table class="simple-table">
                                            <template v-slot:default>
                                                <tbody>
                                                <tr
                                                    v-for="(curso, index2) in escuela.cursos"
                                                    :key="curso.id"
                                                >
                                                    <td class="px-0 mx-0">
                                                        <v-icon right> mdi-book</v-icon>
                                                    </td>
                                                    <td>{{ curso.nom_curso }}</td>
                                                </tr>
                                                </tbody>
                                            </template>
                                        </v-simple-table>
                                    </v-card-text>
                                </v-card>
                            </v-tab-item>

                        </v-tabs>
                    </template>
                    <!-- </template> -->
                    </v-simple-table>
                </v-col>
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
            resource: {
                nom_grupo: '',
                nom_carrera: null,
                nom_ciclo: null,
                usuario_nombre: null,
                categorias: [],
            }
        }
    },
    methods: {
        closeModal() {
            let vue = this;
            vue.$emit('onCancel')
        },
        confirmModal() {

        },
        resetValidation() {

        },
        loadData(resource) {
            let vue = this
            let url = `${vue.options.base_endpoint}/${resource.id}/courses-by-user`
            vue.$http.get(url).then(({data}) => {
                vue.resource = data
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
