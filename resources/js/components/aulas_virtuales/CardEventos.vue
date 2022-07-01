<template>
    <div>
        <v-card color="#F0F0F0" shaped>
            <v-list-item two-line>
                <v-list-item-avatar
                    tile
                    size="80"
                    :color="tipo == 1 ? '#0CA789' : '#F43737'"
                    class="mr-4"
                    style="border-radius: 10px !important"
                >
                    <v-icon
                        class="mdi-48px"
                        color="#FFFFFF"
                        style="height: 80px !important; width: 80px !important"
                    >{{ tipo == 1 ? "mdi-headset" : "mdi-video" }}
                    </v-icon
                    >
                    <!-- <v-btn class="ma-2" outlined block x-large style="height:80px !important;">
                    </v-btn>-->
                </v-list-item-avatar>
                <v-list-item-content>
                    <v-row>
                        <v-col cols="12" sm="12" md="4" lg="4" class="mt-4">
                            <v-list-item-title
                                style="
                  white-space: break-spaces !important;
                  font-weight: bold !important;
                  font-size: 1.35rem !important;
                "
                            >{{ evento.titulo }}
                            </v-list-item-title
                            >
                            <v-list-item-subtitle class="mt-1">
                                {{ evento.descripcion }}
                            </v-list-item-subtitle>
                            <v-list-item-subtitle class="mt-1" v-if="evento.estado === 4 && !evento.report_generated_at">
                                <v-icon class="mx-1">mdi-rotate-3d-variant</v-icon>Se están sincronizando los datos del evento
                            </v-list-item-subtitle>
                        </v-col>
                        <v-col cols="12" sm="12" md="4" lg="4" class="mt-3">
                            <v-icon class="mr-1 mb-2">mdi-calendar</v-icon>
                            {{ evento.fecha }}
                            <v-icon class="ml-4 mr-1 mb-2">mdi-clock</v-icon>
                            {{ evento.hora }} ({{ evento.duracion }} min.)
                            <p
                                class="text-left"
                                style="
                  font-size: 1rem !important;
                  margin-bottom: 0px !important;
                "
                            >
                                Creado por:
                            </p>
                            <p class="text-left" style="font-size: 1rem !important">
                                {{ evento.dni == null ? "Email" : "DNI" }}:
                                {{ evento.dni == null ? evento.email : evento.dni }}
                                <br/>
                                {{ evento.nombre }}
                            </p>
                        </v-col>
                        <v-col cols="12" sm="12" md="4" lg="4" class="mt-3">
                            <v-row>
                                <v-col cols="12" sm="12" md="12" lg="12" class="text-center">
                                    <v-btn
                                        outlined
                                        color="#ff0000"
                                        @click="getDetallesxEvento()"
                                        class="text-negrita"
                                    >
                                        <v-icon left class="mb-2">mdi-magnify</v-icon>
                                        Detalles
                                    </v-btn>
                                </v-col>
                                <v-col
                                    cols="12"
                                    sm="12"
                                    md="12"
                                    lg="12"
                                    v-if="evento.tipo_evento == 2 && evento.estado == 2"
                                >
                                    <v-btn
                                        block
                                        color="#0f00ff"
                                        class="ingresar"
                                        @click="(estado = 3), (dialog = true)"
                                    >INICIAR EVENTO
                                    </v-btn
                                    >
                                </v-col>
                                <v-col
                                    cols="12"
                                    sm="12"
                                    md="12"
                                    lg="12"
                                    v-if="evento.tipo_evento == 2 && evento.estado == 3"
                                >
                                    <v-btn
                                        block
                                        color="#ff0000"
                                        class="ingresar"
                                        @click="(estado = 4), (dialog = true)"
                                    >FINALIZAR EVENTO
                                    </v-btn
                                    >
                                </v-col>
                                <v-col
                                    cols="12"
                                    sm="12"
                                    md="12"
                                    lg="12"
                                    v-if="evento.estado == 4"
                                >
                                    <v-btn block color="#a0a0a0" disabled>FINALIZADO</v-btn>
                                </v-col>
                            </v-row>
                        </v-col>
                    </v-row>
                </v-list-item-content>
            </v-list-item>
        </v-card>

        <v-dialog v-model="dialog" max-width="650px" @click:outside="dialog=false">
            <v-card>
                <v-card-title class="headline">Confirmar</v-card-title>
                <v-card-text v-if="estado == 3" class="f-size-1-05rem"
                >¿Está seguro de iniciar la transmisión?
                </v-card-text
                >
                <v-card-text v-if="estado == 4" class="f-size-1-05rem"
                >¿Está seguro de finalizar la transmisión?
                </v-card-text
                >
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="green darken-1"
                        large
                        @click="(dialog = false), (estado = null)"
                        class="text-negrita text-white"
                    >Cancelar
                    </v-btn
                    >
                    <v-btn
                        color="green darken-1"
                        outlined
                        large
                        @click="cambiarEstadoEvento()"
                        v-if="estado == 3"
                        class="text-negrita"
                    >INICIAR
                    </v-btn
                    >
                    <v-btn
                        large
                        color="green darken-1"
                        outlined
                        @click="cambiarEstadoEvento"
                        v-if="estado == 4"
                        class="text-negrita"
                    >FINALIZAR
                    </v-btn
                    >
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="dialog_detalles" @click:outside="dialog_detalles=false" max-width="650px">
            <v-card>
                <v-card-title class="headline">
                    Detalles del evento
                    <v-spacer></v-spacer>
                    <v-btn icon @click="dialog_detalles = false">
                        <v-icon>mdi-close-thick</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text>
                    <v-list-item three-line>
                        <v-list-item-content>
                            <v-list-item-title>{{
                                    detalle_evento.evento.titulo
                                }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                                {{ detalle_evento.evento.descripcion }} <br/>
                                <v-icon class="mb-1">mdi-calendar</v-icon>
                                {{ detalle_evento.evento.fecha_inicio }} -
                                <v-icon class="mb-1">mdi-clock</v-icon>
                                {{ detalle_evento.evento.duracion }} min.
                            </v-list-item-subtitle>
                            <v-list-item-subtitle
                                class="mt-1"
                                v-if="detalle_evento.evento.tipo_evento_id == 1"
                            >
                                Creador: {{ detalle_evento.creador.nombre }}
                            </v-list-item-subtitle>
                            <v-list-item-subtitle class="mt-3">
                                <v-btn small color="#0f00ff">
                                    <a
                                        :href="detalle_evento.evento.link_zoom"
                                        v-if="detalle_evento.evento.tipo_evento_id == 1"
                                        style="text-decoration: none; color: white"
                                        target="_blank"
                                    >Ingresar</a
                                    >
                                    <a
                                        :href="
                      'https://vimeo.com/' + detalle_evento.evento.link_vimeo
                    "
                                        v-if="detalle_evento.evento.tipo_evento_id == 2"
                                        style="text-decoration: none; color: white"
                                        target="_blank"
                                    >Ingresar</a
                                    >
                                </v-btn>
                            </v-list-item-subtitle>
                        </v-list-item-content>
                    </v-list-item>

                    <v-list-item>
                        <v-list-item-content>
                            <v-list-item-title>Asistentes</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-simple-table
                        dense
                        v-if="detalle_evento.evento.tipo_evento_id == 1"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th class="text-left">DNI</th>
                                <th class="text-left">Nombre</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="item in detalle_evento.asistentes" :key="item.dni">
                                <td>{{ item.dni }}</td>
                                <td>{{ item.nombre }}</td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                    <v-simple-table
                        dense
                        v-if="detalle_evento.evento.tipo_evento_id == 2"
                    >
                        <template v-slot:default>
                            <thead>
                            <tr>
                                <th class="text-left">Módulo</th>
                                <th class="text-left">Carrera</th>
                            </tr>
                            </thead>
                            <tbody>
                            <template v-for="(value, name) in detalle_evento.asistentes">
                                <tr v-for="(carrera, index2) in value" :key="index2">
                                    <td v-if="index2 == 0" :rowspan="value.length">
                                        {{ name }}
                                    </td>
                                    <td>{{ carrera.nombre }}</td>
                                </tr>
                            </template>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
export default {
    props: ["evento", "tipo", "loading"],
    data() {
        return {
            dialog: false,
            dialog_detalles: false,
            estado: null,
            detalle_evento: {
                evento: {},
                asistentes: {},
                creador: {}
            }
        };
    },
    methods: {
        cambiarEstadoEvento() {
            let vue = this;
            axios
                .post("cambiarEstadoEvento", {
                    evento_id: vue.evento.evento_id,
                    estado: vue.estado
                })
                .then(res => {
                    //   console.log(res);
                    vue.$emit("update_state");
                    vue.dialog = false;
                })
                .catch(err => {
                    console.log(err);
                });
        },
        getDetallesxEvento() {
            let vue = this;
            axios
                .get("getDetallesxEvento/" + vue.evento.evento_id)
                .then(res => {
                    vue.detalle_evento.evento = res.data.evento
                    vue.detalle_evento.asistentes = res.data.asistentes
                    vue.detalle_evento.creador = res.data.creador
                    vue.dialog_detalles = true;
                })
                .catch(err => {
                    console.log(err);
                });
        }
    }
};
</script>
<style>
.f-size-1-05rem {
    font-size: 1.05rem !important;
}

.text-negrita {
    font-weight: bold !important;
}
</style>
