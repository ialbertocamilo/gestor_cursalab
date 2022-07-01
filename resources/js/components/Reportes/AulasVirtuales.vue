<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Reportes de conferencias
                <v-spacer/>
                <!-- <DefaultButton
                    append-icon="mdi-download"
                    outlined
                    :label="'Exportar'"
                    @click="activity()"
                /> -->
<!--                <DefaultModalButton-->
<!--                    :label="'Evento en vivo'"-->
<!--                    @click="openCRUDPage('/aulas_virtuales/create')"/>-->
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="4">
                        <!--        <v-select-->
                        <!--          v-model="filtros.tipo_evento"-->
                        <!--          :items="tipo_eventos"-->
                        <!--          label="Tipo reunión"-->
                        <!--          item-value="value"-->
                        <!--          item-text="nombre"-->
                        <!--          outlined-->
                        <!--          attach-->
                        <!--        >-->
                        <!--        </v-select>-->
                        <DefaultSelect
                            dense
                            v-model="filtros.tipo_evento"
                            :items="tipo_eventos"
                            label="Tipo reunión"
                        />
                    </v-col>
                    <!-- <v-col cols="12" sm="12" md="4" lg="4">
                              <v-select
                              v-model="filtros.estado_evento"
                              :items="estado_eventos"
                              label="Estado evento"
                              item-value="value"
                              item-text="nombre"
                              outlined
                              attach
                              multiple
                              chips
                              hide-details>
                              </v-select>
                          </v-col> -->
                    <v-col cols="4">
                        <!--        <v-menu-->
                        <!--          v-model="menu"-->
                        <!--          :close-on-content-click="false"-->
                        <!--          :nudge-right="40"-->
                        <!--          transition="scale-transition"-->
                        <!--          offset-y-->
                        <!--          min-width="290px"-->
                        <!--          attach-->
                        <!--        >-->
                        <!--          <template v-slot:activator="{ on, attrs }">-->
                        <!--            <v-text-field-->
                        <!--              v-model="date"-->
                        <!--              label="Fecha inicio"-->
                        <!--              prepend-icon="mdi-calendar"-->
                        <!--              readonly-->
                        <!--              v-bind="attrs"-->
                        <!--              v-on="on"-->
                        <!--            ></v-text-field>-->
                        <!--          </template>-->
                        <!--          <v-date-picker-->
                        <!--            v-model="date"-->
                        <!--            @input="menu = false"-->
                        <!--            locale="es-PE"-->
                        <!--          ></v-date-picker>-->
                        <!--        </v-menu>-->
                        <DefaultInputDate
                            clearable
                            dense
                            :referenceComponent="'modalDateFilter1'"
                            :options="modalDateFilter1"
                            v-model="date"
                            label="Fecha Inicio"
                        />
                    </v-col>
                    <v-col cols="4">
                        <!--                <v-menu-->
                        <!--                    v-model="menu2"-->
                        <!--                    :close-on-content-click="false"-->
                        <!--                    :nudge-right="40"-->
                        <!--                    transition="scale-transition"-->
                        <!--                    offset-y-->
                        <!--                    min-width="290px"-->
                        <!--                    attach-->
                        <!--                >-->
                        <!--                    <template v-slot:activator="{ on, attrs }">-->
                        <!--                        <v-text-field-->
                        <!--                            v-model="date2"-->
                        <!--                            label="Fecha fin"-->
                        <!--                            prepend-icon="mdi-calendar"-->
                        <!--                            readonly-->
                        <!--                            v-bind="attrs"-->
                        <!--                            v-on="on"-->
                        <!--                        ></v-text-field>-->
                        <!--                    </template>-->
                        <!--                    <v-date-picker-->
                        <!--                        v-model="date2"-->
                        <!--                        @input="menu2 = false"-->
                        <!--                        locale="es-PE"-->
                        <!--                    ></v-date-picker>-->
                        <!--                </v-menu>-->
                        <DefaultInputDate
                            clearable
                            dense
                            :referenceComponent="'modalDateFilter2'"
                            :options="modalDateFilter2"
                            v-model="date2"
                            label="Fecha Fin"
                        />
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <DefaultButton
                            @click="descargarEventos"
                            label="Descargar Eventos"
                        />
                        <DefaultButton
                            @click="descargarAsistentes"
                            label="Descargar Asistentes"
                        />
                        <!--        <b-btn @click="descargarEventos()">Descargar Eventos</b-btn>-->
                        <!--        <b-btn @click="descargarAsistentes()">Descargar Asistentes</b-btn>-->
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
    </section>
</template>

<script>
export default {
    props: ["API_REPORTES"],
    data() {
        return {
            modalDateFilter1: {
                open: false,
            },
            modalDateFilter2: {
                open: false,
            },
            date: null,
            date2: null,
            menu: false,
            menu2: false,
            tipo_eventos: [
                {id: 0, nombre: "Todos"},
                {id: 1, nombre: "Aulas virtuales"},
                {id: 2, nombre: "Evento en vivo"},
            ],
            estado_eventos: [
                {id: 0, nombre: "Ninguno"},
                {id: 1, nombre: "Pre-reserva"},
                {id: 2, nombre: "Agendado"},
                {id: 3, nombre: "En transcurso"},
                {id: 4, nombre: "Finalizado"},
            ],
            filtros: {
                tipo_evento: 0,
                estado_evento: 0,
            },
        };
    },
    methods: {
        descargarEventos() {
            let vue = this;
            let hora = "";
            let hora2 = "";
            if (vue.date == vue.date2) {
                hora = `00:00:00`;
                hora2 = "23:59:59";
            } else {
                hora = "00:00:00";
                hora2 = "00:00:00";
            }
            axios
                .post(this.API_REPORTES + "aulas_virtuales", {
                    tipo_evento: vue.filtros.tipo_evento,
                    fecha_inicio: vue.date === null ? "" : `${vue.date} ${hora}`,
                    fecha_fin: vue.date2 === null ? "" : `${vue.date2} ${hora2}`,
                })
                .then((res) => {
                    if (!res.data.error) vue.$emit("descargar_reporte", res);
                    else {
                        alert("Se ha encontrado el siguiente error : " + res.data.error);
                        this.hideLoader()
                    }
                })
                .catch((error) => {
                    console.log(error);
                    console.log(error.message);
                    alert("Se ha encontrado el siguiente error : " + error);
                    this.hideLoader()
                });
        },
        descargarAsistentes() {
            let vue = this;
            let hora = "";
            let hora2 = "";
            if (vue.date == vue.date2) {
                hora = "00:00:00";
                hora2 = "23:59:59";
            } else {
                hora = "00:00:00";
                hora2 = "00:00:00";
            }
            axios
                .post(this.API_REPORTES + "asistentes_eventos", {
                    tipo_evento: vue.filtros.tipo_evento,
                    fecha_inicio: vue.date === null ? "" : `${vue.date} ${hora}`,
                    fecha_fin: vue.date2 === null ? "" : `${vue.date2} ${hora2}`,
                })
                .then((res) => {
                    if (!res.data.error) vue.$emit("descargar_reporte", res);
                    else {
                        alert("Se ha encontrado el siguiente error : " + res.data.error);
                        this.hideLoader()
                    }
                })
                .catch((error) => {
                    console.log(error);
                    console.log(error.message);
                    alert("Se ha encontrado el siguiente error : " + error);
                    this.hideLoader()
                });
        },
    },
    created() {
        console.log("End point ", this.API_REPORTES);
    },
};
</script>
