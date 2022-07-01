<template>
    <!--    <v-card class="usuario-sΩection-matricula">-->

    <div class="usuario-section-matricula">
        <v-row justify="space-around" align="start" align-content="center">
            <v-col cols="6" class="d-flex justify-content-center">
                <v-chip class="ma-2" color="primary" label>
                    GRUPO: {{ usuario.botica ? usuario.botica.criterio.valor : 'Selecciona una botica' }}
                </v-chip>
            </v-col>
            <v-col cols="6" class="d-flex justify-content-center" v-if="usuario.matricula_presente">
                <v-chip class="ma-2" color="primary" label>
                    Carrera: {{ usuario.matricula_presente.carrera.nombre }}
                </v-chip>
            </v-col>
            <v-col cols="6" class="d-flex justify-content-center" v-else>
                <DefaultAutocomplete
                    label="Carrera"
                    :items="carreras"
                    v-model="usuario.carrera"
                    @onChange="loadCiclos"
                    return-object
                    clearable
                    no-data-text="Seleccione primero un módulo"
                    :rules="rules.carrera"
                />
            </v-col>
        </v-row>
        <v-row justify="space-around" align="start" align-content="center">
            <v-col cols="12" class="d-flex justify-content-center">
                <v-simple-table class="w-100">
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th class="text-center" v-text="'Secuencia'"/>
                            <th class="text-center" v-text="'Grupo'"/>
                            <th class="text-center" v-text="'Carrera'"/>
                            <th class="text-center" v-text="'Ciclo'"/>
                            <th class="text-center" v-text="'Estado'"/>
                        </tr>
                        </thead>
                        <tbody v-if="usuario.ciclos && usuario.ciclos.length === 0">
                        <tr>
                            <td colspan="5" class="text-center">
                                <p class="text-h7 font-weight-bold pt-4">Seleccione una carrera</p>
                            </td>
                        </tr>
                        </tbody>
                        <tbody v-else>
                        <tr
                            v-for="(item, index) in usuario.ciclos"
                            :key="item.id"
                        >
                            <td align="center">{{ item.secuencia }}</td>
                            <td align="center">{{ item.grupo }}</td>
                            <td align="center">{{ item.carrera }}</td>
                            <td align="center">{{ item.ciclo }}</td>
                            <td align="center">
                                <DefaultToggle v-model="item.estado" @onChange="switchCiclos(index, item.estado)"/>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </v-col>
        </v-row>
    </div>
    <!--    </v-card>-->

</template>


<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        usuario: {
            type: Object,
            required: true
        },
        carreras: {
            type: Object | Array,
            required: true
        },

    },
    data() {
        return {
            selects: {
                carreras: [],
            },
            rules: {
                carrera: this.getRules(['required'])
            }
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            vue.$emit('onConfirm')
        },
        loadData() {

        },
        loadSelects() {

        },
        loadCiclos() {
            let vue = this
            // if (!vue.usuario.carrera || !vue.usuario.botica) {
            if (!vue.usuario.carrera) {
                vue.usuario.ciclos = []
                return
            }

            const carrera_id = vue.usuario.carrera.id
            const botica_id = vue.usuario.botica ? vue.usuario.botica.id : null
            let url = `${vue.options.base_endpoint}/${carrera_id}/${botica_id}/ciclos-x-carrera`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.usuario.ciclos = data.data.ciclo_final
                })
        },
        switchCiclos(index, estado) {
            let vue = this;
            if (estado) {
                vue.usuario.ciclos.forEach((ciclo, key) => {
                    if (index > 0 && ciclo.secuencia === 0) {
                        ciclo.estado = false;
                        return;
                    }
                    if (key <= index) {
                        ciclo.estado = true;
                    }
                    if (index === 0 && key !== 0) {
                        ciclo.estado = false;
                    }

                });
            } else {
                vue.usuario.ciclos.forEach((ciclo, key) => {
                    if (key > index) {
                        ciclo.estado = false;
                    }
                });
            }
        },
    }
}
</script>
<style>
.usuario-section-matricula {
    width: 100%;
}
</style>
