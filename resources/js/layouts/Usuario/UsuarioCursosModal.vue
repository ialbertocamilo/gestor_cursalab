<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
    >
        <template v-slot:content>
            <!--            <v-row justify="space-around" class="mt-5" no-gutters v-if="resource.courses.length>0">-->

            <!--                <v-col cols="12" class="d-flex justify-content-center">-->
            <!--                    <v-chip class="mr-2 my-2" color="primary" label>-->
            <!--                        {{ resource.courses[0].name || '' }}-->
            <!--                    </v-chip>-->
            <!--                </v-col>-->
            <!--            </v-row>-->
            <v-row justify="space-around" class="mt-5 mx-3">
                <v-col cols="12">
                    <template v-if="resource.schools.length === 0">
                        <div class="text-center">
                            <p class="text-h7 font-weight-bold pt-4">No tiene cursos asignados</p>
                        </div>
                    </template>
                    <template v-else>
                        <!--                        <v-tabs vertical>-->
                        <!--                            <v-tab v-for="block in resource.courses[0].blocks" :key="block.id"-->
                        <!--                                   class="justify-content-start" :title="block.name">-->
                        <!--                                <v-icon left>mdi-school</v-icon>-->
                        <!--                                {{ block.name }} ({{ block.courses_count }})-->
                        <!--                            </v-tab>-->

                        <!--                            <v-tab-item v-for="block in resource.courses[0].blocks" :key="block.id"-->
                        <!--                                        class="ml-10 elevation-1">-->
                        <!--                                <v-card flat class="">-->
                        <!--                                    <v-card-text>-->
                        <!--                                        <v-simple-table class="simple-table">-->
                        <!--                                            <template v-slot:default>-->
                        <!--                                                <tbody>-->
                        <!--                                                <tr v-for="course in block.courses" :key="course.id">-->
                        <!--                                                    <td class="px-0 mx-0">-->
                        <!--                                                        <v-icon right> mdi-book</v-icon>-->
                        <!--                                                    </td>-->
                        <!--                                                    <td>{{ course.name }}</td>-->
                        <!--                                                </tr>-->
                        <!--                                                </tbody>-->
                        <!--                                            </template>-->
                        <!--                                        </v-simple-table>-->
                        <!--                                    </v-card-text>-->
                        <!--                                </v-card>-->
                        <!--                            </v-tab-item>-->

                        <!--                        </v-tabs>-->
                        <v-tabs vertical>
                            <!--                            <v-tab v-for="school in resource.courses[0].schools" :key="school.id"-->
                            <v-tab v-for="school in resource.schools" :key="school.id"
                                   class="justify-content-start" :title="school.name">
                                <v-icon left>mdi-school</v-icon>
                                {{ school.categoria }} ({{ school.cursos.length }})
                            </v-tab>

                            <v-tab-item v-for="schools in resource.schools" :key="schools.id"
                                        class="ml-10 elevation-0">
                                <v-card outlined>
                                    <v-card-text>
                                        <v-simple-table class="simple-table">
                                            <template v-slot:default>
                                                <tbody>
                                                <tr v-for="course in schools.cursos" :key="course.id">
                                                    <td class="px-0 mx-0">
                                                        <v-icon right> mdi-book</v-icon>
                                                    </td>
                                                    <td>{{ course.nombre }}</td>
                                                </tr>
                                                </tbody>
                                            </template>
                                        </v-simple-table>
                                    </v-card-text>
                                </v-card>
                            </v-tab-item>

                        </v-tabs>
                    </template>
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
                id: '',
                fullname: null,
                schools: [],
            }
        }
    },
    methods: {
        closeModal() {
            let vue = this;

            vue.resource = {
                id: '',
                fullname: null,
                schools: [],
            };

            vue.$emit('onCancel')
        },
        confirmModal() {

        },
        resetValidation() {

        },
        loadData(resource) {
            let vue = this
            let url = `${vue.options.base_endpoint}/${resource.id}/courses-by-user`
            vue.showLoader();
            vue.$http.get(url)
                .then(({data}) => {
                    vue.resource = data.data.user

                    setTimeout(() => {
                        vue.hideLoader();
                    }, 1000)
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
