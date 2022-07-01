<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-row justify="start" align="start">
                <p> Entrenador: {{ resource.dni }} - {{ resource.name }} </p>
                <v-col cols="8">
                    <DefaultInput
                        clearable dense
                        v-model="filters.q"
                        label="Filtrar por nombre de alumno"
                        @onChange="filterText"
                    />
                </v-col>
                <v-col cols="4">
                    <DefaultButton
                        label="Asignar alumnos"
                        :outlined="asignar_alumnos"
                        @click="asignar_alumnos = !asignar_alumnos"
                    />
                </v-col>
            </v-row>
            <v-expand-transition>
                <v-row
                    v-show="asignar_alumnos"
                    style="border: 1px #f3f3f3 solid; border-radius: 15px"
                >
                    <v-col cols="4">
                        <DefaultInput
                            clearable
                            dense
                            v-model="searchText"
                            :label="`Buscar por nombre o DNI`"
                            @onEnter="searchAlumnos"
                        />
                    </v-col>
                </v-row>
            </v-expand-transition>
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
    data: () => ({
        resourceDefault: {},
        resource: {},
        filters: {
            q: null
        },
        searchText: null,
        asignar_alumnos: false
    }),
    methods: {
        closeModal() {

        },
        confirmModal() {

        },
        resetValidation() {

        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.options.base_endpoint}/${resource.id}/alumnos`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.resource = data.data.entrenador
                })
            return 0;
        },
        loadSelects() {

        },
        filterText() {

        },
        searchAlumnos(){

        }
    }
}
</script>
