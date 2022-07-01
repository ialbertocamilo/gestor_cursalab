<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
    >
        <template v-slot:content>
            <v-card tile elevation="0" class="my-3">
                <v-card-text>
                    <v-row>
                        <v-col cols="2">
                            <DefaultFormLabel
                                label="Título:"
                            />
                        </v-col>
                        <v-col cols="10">
                            {{ resource.title }}
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="2">
                            <DefaultFormLabel
                                label="Descripción:"
                            />
                        </v-col>
                        <v-col cols="10">
                            {{ resource.descripcion }}
                        </v-col>
                    </v-row>
                    <DefaultDivider/>
                    <v-row>
                        <v-col cols="7" class="d-flex flex-column">
                            <v-row no-gutters>
                                <v-col cols="4">
                                    <DefaultFormLabel
                                        label="Fecha y hora:"
                                    />
                                </v-col>
                                <v-col cols="8">
                                    {{ resource.fecha_hora }}
                                </v-col>
                            </v-row>
                            <v-row no-gutters>
                                <v-col cols="4">
                                    <DefaultFormLabel
                                        label="Duración:"
                                    />
                                </v-col>
                                <v-col cols="8">
                                    {{ resource.duracion }}min.
                                </v-col>
                            </v-row>
                            <v-row no-gutters>
                                <v-col cols="4">
                                    <DefaultFormLabel
                                        label="Autor:"
                                    />
                                </v-col>
                                <v-col cols="8">
                                    {{ resource.nombre_creador }} <br/>
                                    {{ resource.id_creador }}
                                </v-col>
                            </v-row>
                        </v-col>
                        <v-col cols="5">
                            <div class="aulas-virtuales-spectator-box">
                                <DefaultFormLabel
                                    label="Espectador:"
                                />
                                Puedes ingresar al evento como espectador.<br/>
                                Recuerda verificar la fecha y hora de inicio.
                                <div class="d-flex justify-content-center mt-2">
                                    <DefaultButton
                                        :disabled="isEndedOrCanceled(resource)"
                                        label="Ingresar"
                                        @click="ingresar"
                                    />
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                    <DefaultDivider/>
                    <v-row justify="start" align="start">
                        <v-col cols="12">
                            <DefaultFormLabel
                                label="Asistentes"
                            />
                            <DefaultSimpleTable
                                v-if="resource.tipo_evento_id === 1">
                                <template slot="content">
                                    <thead>
                                    <tr>
                                        <th>DNI</th>
                                        <th>Nombre</th>
                                    </tr>
                                    </thead>
                                    <tbody
                                        v-if="resource.invitados && resource.invitados.length === 0">
                                    <tr>
                                        <td colspan="2"
                                            class="text-center">
                                            <p
                                                class="text-h7 font-weight-bold pt-4"
                                                v-text="'No se encontraron invitados para este evento'"/>
                                        </td>
                                    </tr>
                                    </tbody>
                                    <tbody v-else>
                                    <tr
                                        v-for="(item, index) in resource.invitados"
                                        :key="item.id"
                                    >
                                        <td>{{ item.dni }}</td>
                                        <td>{{ item.nombre }}</td>
                                    </tr>
                                    </tbody>
                                </template>
                            </DefaultSimpleTable>
                            <DefaultSimpleTable
                                v-else>
                                <template slot="content">
                                    <thead>
                                    <tr>
                                        <th>Módulo</th>
                                        <th>Carreras</th>
                                    </tr>
                                    </thead>
                                    <tbody
                                        v-if="resource.invitados && resource.invitados.length === 0">
                                    <tr>
                                        <td colspan="2"
                                            class="text-center">
                                            <p
                                                class="text-h7 font-weight-bold pt-4"
                                                v-text="'No se encontraron invitados para este evento'"/>
                                        </td>
                                    </tr>
                                    </tbody>
                                    <tbody v-else>
                                    <template v-for="modulo in resource.invitados">
                                        <tr v-for="(carrera, index2) in modulo.carreras"
                                            :key="carrera.id"
                                            class="text-left"
                                        >
                                            <td v-if="index2 === 0"
                                                :rowspan="modulo.carreras.length"
                                            >
                                                {{ modulo.nombre }}
                                            </td>
                                            <td>{{ carrera.nombre }}</td>
                                        </tr>
                                    </template>
                                    </tbody>
                                </template>
                            </DefaultSimpleTable>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>
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
            resourceDefault: {
                title: null,
                description: null,
                create_by: {
                    dni: null,
                    name: null,
                    nombre: null,
                    email: null
                },
                link: null,
                tipo_evento_id: null,
                invitados: []
            },
            resource: {},
        }
    },
    methods: {
        isEndedOrCanceled(event){
            if (!event.estado) return false
            return [4,5].includes(event.estado);
        },
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            vue.$emit('onConfirm')
        },
        resetValidation() {

        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.options.base_endpoint}/${resource.id}/details`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.resource = data.data.evento
                })
            return 0;
        },
        loadSelects() {

        },
        ingresar() {
            let vue = this
            window.open(vue.resource.link);
        }
    }
}
</script>

<style lang="scss">
.aulas-virtuales-invitados-table {
    .v-data-table__wrapper {
        max-height: 35vh;
    }
}

.aulas-virtuales-spectator-box {
    font-size: 0.9rem;
    border: 1px solid #ddd;
    height: 100%;
    border-radius: 6px;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    justify-content: start;
    padding: 14px 19px 12px 19px;

    .v-btn {
        width: min-content;
    }
}
</style>
