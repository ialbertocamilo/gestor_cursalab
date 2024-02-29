<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="moduloForm">
                <v-row justify="space-around">
                    <v-col cols="11" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            v-model="resource.nombre"
                            :items="[]"
                            label="Modalidad"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="11" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.nombre"
                            label="Nombre"
                            maxlength="120"
                            :max="120"
                            hint="Máximo 120 caracteres"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="11" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.nombre"
                            label="Nombre Ciclo 0"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="11" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.logo"
                            label="Descripción"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="11" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.logo"
                            label="Color"
                        />
                    </v-col>
                </v-row>
                <!--   <v-row justify="space-around" align="start" align-content="center">
                      <v-col cols="5" class="d-flex justify-content-center">
                          <DefaultAutocomplete clearable
                                               :items="selects.cargos"
                                               v-model="resource.cargo"
                                               open-up
                                               label="Cargo"
                          />
                      </v-col>
                      <v-col cols="5" class="d-flex justify-content-center">
                          <DefaultButton block label="Matrícula"/>
                      </v-col>
                  </v-row> -->
                <v-row
                    justify="space-around"
                    align="startcenter"
                    align-content="center"
                >
                    <v-col cols="5" class="d-flex justify-content-start">
                        <DefaultToggle
                            v-model="resource.estado"
                            label="¿Activo?"
                        />
                    </v-col>
                    <v-col cols="5" class="d-flex justify-content-start" />
                </v-row>
            </v-form>
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
        width: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            resourceDefault: {
                config_id: null,
                botica_id: null,
                nombre: "",
                dni: null,
                password: null,
                sexo: null,
                cargo: null,
                estado: false
            },
            resource: {},
            selects: {
                genres: ["M", "F"],
                modules: [],
                boticas: [],
                groups: [],
                cargos: []
                // [
                //     {id: 1, nombre: "MASCULINO"},
                //     {id: 2, nombre: "FEMENINO"}
                // ]
            }
        };
    },
    methods: {
        closeModal() {
            let vue = this;
            // vue.options.open = false
            vue.resetSelects();
            vue.$emit("onCancel");
        },
        confirmModal() {
            let vue = this;
            vue.$emit("onConfirm");
            // TODO: Validar moduloForm
        },
        resetSelects() {
            let vue = this;
            // Selects independientes
            vue.selects.modules = [];
            vue.selects.cargos = [];
            // Selects dependientes
            vue.selects.boticas = [];
            vue.selects.groups = [];
        },
        async loadData(resource) {
            let vue = this;
            Object.assign(vue.resource, vue.resourceDefault);
            if (resource && resource.id) {
                let url = `${vue.options.base_endpoint}/${resource.id}/search`;
                await vue.$http.get(url).then(({ data }) => {
                    vue.selects.modules = data.data.modules;
                    vue.selects.cargos = data.data.cargos;
                    if (resource) vue.resource = data.data.usuario;
                });
            }
            return 0;
        },
        loadSelects() {
            let vue = this;
            if (vue.resource.config_id) vue.loadBoticas();
        },
        loadBoticas() {
            let vue = this;
            console.log(
                "Buscar boticas del modulo_id :: ",
                vue.resource.config_id
            );
            let url = `/boticas/search-no-paginate?config_id=${vue.resource.config_id}`;
            vue.$http.get(url).then(({ data }) => {
                vue.selects.boticas = data.data.boticas;
            });
        }
    }
};
</script>