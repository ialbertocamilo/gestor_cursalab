<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
        <v-card-title>
            Intentos Masivos
        </v-card-title>
    </v-card>

        <v-card flat>
            <v-card-text class="pb-0">
            <v-row>
                <v-col cols="12" md="6" lg="6">

                    <v-alert
              border="top"
              colored-border
              elevation="2"
              color="primary"
              class="pb-0"
            >
                <small><strong>A TOMAR EN CUENTA</strong></small>

                <ul class="mt-3">
                    <li><small>Se listan los usuarios luego de seleccionar el curso o tema evaluable.</small></li>
                    <li><small>En el filtro "Usuarios", las opciones:</small>
                        <ul>
                            <li><small>"Solo desaprobados" mostrará a los usuarios que desaprobaron el tema.</small></li>
                            <li><small>"Todos" mostrará a todos los usuarios que realizaron una evaluación del tema (aprobados y desaprobados).</small></li>
                        </ul>
                    </li>
                    <li><small>Solo se otorgará nuevamente intentos de los usuarios, no cambia la última mayor nota, respuestas, etc.</small></li>
                </ul>
            </v-alert>
                </v-col>
                <v-col cols="12" md="6" lg="6">

                    <v-row>
                <v-col cols="12" md="12" lg="12">
                    <v-select
                        outlined
                        dense
                        attach=""
                        label="Usuarios"
                        hide-details="auto"
                        :items="items.tipo_reinicio"
                        v-model="select.tipo_reinicio"
                        item-text="text"
                        item-value="id"
                        :disabled="select.disabledAll"
                        return-object
                    >
                    </v-select>
                </v-col>
            </v-row>
            <v-row>

                <v-col cols="12" md="6" lg="6">
                    <v-autocomplete
                        outlined
                        dense
                        attach=""
                        label="Módulos"
                        hide-details="auto"
                        :items="items.modulos"
                        v-model="select.modulo"
                        item-text="name"
                        item-value="id"
                        :disabled="select.disabledAll"
                        clearable
                        ref="autocomplete_modulos"
                    >
                    </v-autocomplete>
                </v-col>

                <v-col cols="12" md="6" lg="6">
                    <v-autocomplete
                        attach=""
                        label="Escuela"
                        outlined
                        dense
                        hide-details="auto"
                        :items="items.escuelas"
                        v-model="select.escuela"
                        item-text="name"
                        item-value="id"
                        @change="buscarCursos"
                        :disabled="select.disabledAll"
                        clearable
                        ref="autocomplete_escuelas"
                    >
                    </v-autocomplete>
                </v-col>
                <v-col cols="12" md="6" lg="6">
                    <v-autocomplete
                        attach=""
                        label="Cursos"
                        outlined
                        dense
                        hide-details="auto"
                        :items="items.cursos"
                        v-model="select.curso"
                        item-text="name"
                        item-value="id"
                        @change="buscarTemas"
                        :disabled="select.disabledAll"
                        no-data-text="No hay cursos para la escuela seleccionado"
                        clearable
                        ref="autocomplete_cursos"
                    >
                    </v-autocomplete>
                </v-col>
                <v-col cols="12" md="6" lg="6">
                    <v-autocomplete
                        attach=""
                        label="Temas"
                        outlined
                        dense
                        hide-details="auto"
                        :items="items.temas"
                        v-model="select.tema"
                        item-text="name"
                        item-value="id"
                        @change="temas"
                        :disabled="select.disabledAll"
                        no-data-text="No hay temas para el curso seleccionado"
                        clearable
                        ref="autocomplete_temas"
                    >
                    </v-autocomplete>
                </v-col>
                <v-col cols="12" md="12" lg="12" class="text-center">
                    <v-btn color="#2c32e4" outlined @click="reiniciarValoresAll"> Cancelar </v-btn>

                    <v-btn
                        class="white-text"
                        color="primary"
                        @click="validarReinicio"
                        :disabled="!select.curso || select.disabledAll"
                    >
                        Buscar usuarios
                    </v-btn>
                </v-col>
            </v-row>

                </v-col>
            </v-row>
        </v-card-text>

            <!-- Detalle de reinicio masivo -->

            <v-row>
                <v-col cols="12" md="12" lg="12" class="text-center py-0">
                    <v-expand-transition>
                        <v-card v-show="expand" elevation="0">
                            <v-card-text class="pb-0">
                                <v-row class="">

                                    <v-overlay absolute :value="overlay.estado"
                                               color="#5a48ea"
                                               opacity="0.60">
                                        <div
                                            style="display: flex; flex-direction: column; align-items: center"
                                            class="text-center justify-center overlay-curricula"
                                        >
                                            <v-progress-circular indeterminate size="60"></v-progress-circular>
                                            <p class="text-h6">
                                                {{ overlay.texto }}
                                            </p>
                                        </div>
                                    </v-overlay>

                                    <v-col cols="12" md="12" lg="12" class="mt-3-- text-center">

                                        <v-row>
                                            <v-col cols="12" md="12" lg="12" class="pt-1 text-left">
                                                <v-card class="mx-auto elevation-0">
                                                    <v-card-subtitle class="pa-0">
                                                        <v-row>
                                                            <v-col cols="12" md="6" lg="6" class="pt-1 text-left">
                                                                <v-text-field
                                                                v-model="search"
                                                                label="Buscar usuario por id, nombre o apellidos"
                                                                class=""
                                                                v-show="data_validar.count_usuarios"
                                                                ></v-text-field>
                                                            </v-col>
                                                            <v-col cols="12" md="6" lg="6" class="pt-1 text-right align-center">
                                                                <v-btn
                                                                    color="primary"
                                                                    class="white-text"
                                                                    @click="confirmarReinicio"
                                                                    :disabled="btn.disabled_continuar || cantidad_seleccionados == 0"
                                                                >
                                                                    Reiniciar seleccionados
                                                                </v-btn>
                                                            </v-col>
                                                        </v-row>
                                                    </v-card-subtitle>
                                                    <v-card-title
                                                        class="white--text py-2 primary"
                                                    >
                                                        <v-row >
                                                            <v-col cols="1" md="1" lg="1" class="text-center py-0">
                                                                <v-checkbox
                                                                    class="check-all"
                                                                    v-model="allSelected"
                                                                    color="white"
                                                                    hide-details="auto"
                                                                    @click="ckBoxAll"
                                                                    :ripple="false"
                                                                ></v-checkbox>
                                                            </v-col>
                                                            <v-col cols="2" md="2" lg="2" class="text-center py-0"> ID </v-col>
                                                            <v-col cols="5" md="5" lg="5" class="text-left py-0">
                                                                Usuario
                                                            </v-col>
                                                            <v-col cols="2" md="2" lg="2" class="text-center py-0"> Nota </v-col>
                                                            <v-col cols="2" md="2" lg="2" class="text-center py-0">
                                                                # Intentos
                                                            </v-col>
                                                        </v-row>
                                                    </v-card-title>
                                                    <v-card-text v-if="data_validar.pruebas.length == 0">
                                                        <v-row>
                                                            <v-col
                                                                cols="12"
                                                                md="12"
                                                                lg="12"
                                                                class="text-center"
                                                            >
                                                                - No se encontraron usuarios para realizar el reinicio -
                                                            </v-col>
                                                        </v-row>
                                                    </v-card-text>
                                                    <v-card-text v-else-if="data_validar.pruebas.length > 0"
                                                                 class="pr-0">
                                                        <v-virtual-scroll
                                                            :items="filteredList"
                                                            :item-height="50"
                                                            :height="
                                                                data_validar.pruebas.length < 9
                                                                    ? data_validar.pruebas.length * 50
                                                                    : 400
                                                            "
                                                            class="mx-auto"
                                                            style="overflow-x: hidden !important"
                                                        >
                                                            <template v-slot:default="{ item }">
                                                                <v-row :style="{ color: item.passed === 1 ? 'black' : 'black' }"
                                                                       style="border-bottom: 1px solid #e0e0e0;"
                                                                       class="d-flex align-end">
                                                                    <v-col cols="1" md="1" lg="1" >
                                                                        <v-checkbox
                                                                            v-model="item.selected"
                                                                            hide-details="auto"
                                                                            dense
                                                                            :ripple="false"
                                                                            class="my-0"
                                                                        ></v-checkbox>
                                                                    </v-col>
                                                                    <v-col cols="2" md="2" lg="2"
                                                                        class="text-center d-flex justify-center"
                                                                        style="align-items: center"
                                                                    >
                                                                        {{ item.user.document }}
                                                                    </v-col>
                                                                    <v-col cols="5" md="5" lg="5"
                                                                        class="text-left d-flex"
                                                                        style="align-items: center"
                                                                    >
                                                                        {{ item.user.name }}
                                                                        {{ item.user.lastname }}
                                                                        {{ item.user.surname }}
                                                                        <!-- item.usuario.apellido_paterno -->
                                                                        <!-- item.usuario.apellido_materno -->
                                                                    </v-col>
                                                                    <v-col cols="2" md="2" lg="2"
                                                                        class="text-center d-flex justify-center"
                                                                        style="align-items: center"
                                                                    >
                                                                        {{ item.grade }}
                                                                    </v-col>
                                                                    <v-col cols="2" md="2" lg="2"
                                                                        class="text-center d-flex justify-center"
                                                                        style="align-items: center"
                                                                    >
                                                                        {{ item.attempts }}
                                                                    </v-col>
                                                                </v-row>
                                                            </template>

                                                        </v-virtual-scroll>
                                                            <!-- <v-card-text > -->
                                                                <v-row v-show="data_validar.count_usuarios">

                                                                        <v-col cols="12" md="4" lg="4" class="text-center">
                                                                            <strong>Usuarios encontrados:</strong>
                                                                            {{ data_validar.count_usuarios }}
                                                                        </v-col>
                                                                        <v-col cols="12" md="4" lg="" class="text-center">
                                                                            <strong>Nota mínima aprobatoria:</strong>
                                                                            {{ data_validar.mod_eval ? data_validar.mod_eval.nota_aprobatoria : "-" }}
                                                                        </v-col>
                                                                        <v-col cols="12" md="4" lg="" class="text-center">
                                                                            <strong>Máximo de intentos:</strong>
                                                                            {{ data_validar.mod_eval ? data_validar.mod_eval.nro_intentos : "-" }}
                                                                        </v-col>
                                                                    </v-row>
                                                            <!-- </v-card-text> -->
                                                    </v-card-text>

                                                </v-card>

                                                <!-- <v-divider></v-divider> -->
                                            </v-col>
                                        </v-row>
                                    </v-col>

                                </v-row>
                            </v-card-text>
                        </v-card>
                    </v-expand-transition>
                </v-col>
            </v-row>
            <DialogConfirm
                v-if="select.curso"
                v-model="dialogDelete"
                :width="550"
                :title="'Confirmar reinicio de intentos'"
                :subtitle="`<strong>Curso:</strong> ${select.curso_txt}<br><strong>Tema:</strong> ${select.tema_txt}<br><strong>Usuarios seleccionados:</strong> ${cantidad_seleccionados}<br><br>Tenga en cuenta que este cambio no se puede revertir.`"
                @onCancel="closeDialog"
                @onConfirm="reiniciar"
            />
            <!-- /Detalle de reinicio masivo -->
        </v-card>
    </section>
</template>

<script>
  import DialogConfirm from "../../components/basicos/DialogConfirm.vue";
  export default {
    components: {
        DialogConfirm,
    },
    props: ["admin"],
    data() {
        return {
            allSelected: false,
            expand: false,
            baseData: {
                modulos: [],
                escuelas: [],
            },
            items: {
                modulos: [],
                escuelas: [],
                cursos: [],
                temas: [],
                tipo_reinicio: [
                    { id: 1, text: "Solo desaprobados" },
                    { id: 2, text: "Todos" },
                ],
            },
            select: {
                disabledAll: false,
                modulo: null,
                modulo_txt: "-",
                escuela: null,
                escuela_txt: "-",
                curso: null,
                curso_txt: "-",
                tema: null,
                tema_txt: "-",
                tipo_reinicio: { id: 1, text: "Solo desaprobados" },
                usuarios_selected: [],
            },
            btn: {
                disabled_reiniciar: true,
                disabled_continuar: false,
            },
            data_validar: {
                count_usuarios: null,
                pruebas: [],
                mod_eval: null,
            },
            overlay: {
                estado: false,
                texto: "",
            },
            notificacion: {
                show: false,
                type: "success",
                texto: "",
            },
            dialogDelete: false,
            search:'',
        };
    }
    ,
    watch: {
        'select.curso' : function (newVal, oldVal) {
            let vue = this;
            vue.select.curso_txt = vue.items.cursos.find(
                (curso) => curso.id == vue.select.curso
            ).name;
        }
    }
    ,
    computed: {
        cantidad_seleccionados() {

            let vue = this;
            let seleccionados = vue.data_validar.pruebas.filter(
                (element) => element.selected == true
            );

            return seleccionados.length
        }
        ,
        filteredList() {

            let vue = this;
            if (!vue.search) return vue.data_validar.pruebas;

            return vue.data_validar.pruebas.filter(item => {
                let nombre = item.user.name;
                let search = vue.search.toLowerCase();
                return item.user.document
                                .toLowerCase()
                                .includes(search) || nombre.toLowerCase()
                                                           .includes(search);
            })
        }
    }
    ,
    mounted() {
        this.getData();
    }
    ,
    methods: {
        getData() {
            let vue = this;
            axios
                .get("/intentos-masivos/reinicios_data")
                .then((res) => {
                    vue.baseData.modulos = res.data.modules;
                    vue.baseData.escuelas = res.data.schools;
                    vue.items.modulos = res.data.modules;
                    vue.items.escuelas = res.data.schools;
                })
                .catch((err) => {
                    console.log(err);
                });
        }
        ,
        // buscarEscuela() {
        //     let vue = this;
        //     vue.select.escuela = null;
        //     vue.select.curso = null;
        //     vue.select.tema = null;
        //
        //     let escuelas = vue.baseData
        //                       .escuelas
        //                       .filter(esc => esc.config_id == vue.select.modulo);
        //
        //     vue.items.escuelas = escuelas;
        //     vue.select.modulo_txt = vue.baseData.modulos.find(
        //         (modulo) => modulo.id == vue.select.modulo
        //     ).etapa;
        //     this.$nextTick(() => {
        //         this.$refs.autocomplete_escuelas.focus();
        //     });
        // },
        buscarCursos() {

            let vue = this;
            vue.select.curso = null;
            vue.select.tema = null;
            if (!vue.select.escuela) {
                vue.items.cursos = [];
                return;
            }

            let url = `/intentos-masivos/buscarCursosxEscuela/${vue.select.escuela}`;
            axios
                .get(url)
                .then((res) => {
                    vue.items.cursos = res.data.cursos;
                    // vue.select.escuela_txt = vue.items.escuelas.find(
                    //     (escuela) => escuela.id == vue.select.escuela
                    // ).name;

                    this.$nextTick(() => {
                        this.$refs.autocomplete_cursos.focus();
                    });
                })
                .catch((err) => {
                    console.log(err);
                });
        },
        buscarTemas() {
            let vue = this;
            vue.select.tema = null;
            if (!vue.select.curso) {
                vue.items.temas = [];
                return;
            }

            let url = `/intentos-masivos/buscarTemasxCurso/${vue.select.curso}`;
            axios
                .get(url)
                .then((res) => {
                    vue.items.temas = res.data.posteos;
                    // vue.select.curso_txt = vue.items.cursos.find(
                    //     (curso) => curso.id == vue.select.curso
                    // ).nombre;
                    vue.btn.disabled_reiniciar = false;
                    this.$nextTick(() => {
                        this.$refs.autocomplete_temas.focus();
                    });
                })
                .catch((err) => {
                    console.log(err);
                });
        },
        temas() {
            let vue = this;
            const temaIndex = vue.items.temas.findIndex((tema) => tema.id == vue.select.tema);
            if (temaIndex != -1) vue.select.tema_txt = vue.items.temas[temaIndex].name;
            else vue.select.tema_txt = null;
            vue.btn.disabled_reiniciar = false;
        },
        confirmarReinicio() {
            let vue = this;
            vue.dialogDelete = true;
        },
        closeDialog() {
            let vue = this;
            vue.dialogDelete = false;
        },
        ckBoxAll() {
            let vue = this;
            vue.data_validar.pruebas.forEach((element) => {
                element.selected = vue.allSelected;
            });
        },
        validarReinicio() {

            let vue = this;
            vue.expand = true;
            vue.select.disabledAll = true;
            vue.btn.disabled_reiniciar = true;

            let data = {
                curso: vue.select.curso,
                modulo: vue.select.modulo,
                tema: vue.select.tema,
                accion: "validar",
                tipo: vue.select.tipo_reinicio,
                admin: vue.admin,
            };

            axios
                .post("/intentos-masivos/validarReinicio", data)
                .then((res) => {

                    vue.data_validar.count_usuarios = res.data.data.count_usuarios;
                    vue.data_validar.pruebas = res.data.data.pruebas;
                    vue.data_validar.mod_eval = res.data.mod_eval;
                    vue.overlay.texto = "";
                    vue.search = '';

                    if (vue.data_validar.count_usuarios == 0) {
                        vue.btn.disabled_continuar = true;
                        // vue.showAlert("No se encontraron usuarios para realizar el reinicio.", 'warning')
                        // vue.mostrarNotificacion(
                        // 	"info",
                        // 	"No se encontraron usuarios para realizar el reinicio."
                        // );
                    }
                })
                .catch((err) => {
                    console.log(err);
                    vue.overlay.estado = false;
                    vue.overlay.texto = "";
                });
        },
        reiniciar() {

            let vue = this;
            vue.closeDialog();
            vue.overlay.estado = true;
            vue.overlay.texto = "Este proceso puede tomar más de un minuto. No actualice la página.";
            let data = {
                modulo: vue.select.modulo,
                curso: vue.select.curso,
                tema: vue.select.tema,
                accion: "reiniciar",
                tipo: vue.select.tipo_reinicio,
                admin: vue.admin,
                usuarios: vue.data_validar.pruebas.filter((element) => element.selected == true),
            };
            axios
                .post("/intentos-masivos/reiniciarIntentosMasivos", data)
                .then((res) => {
                    setTimeout(() => {

                        vue.overlay.estado = false;
                        vue.overlay.texto = "";
                        vue.queryStatus("intentos_masivos", "agregar_intentos");
                        vue.showAlert(res.data.msg, 'success')
                        // vue.mostrarNotificacion("success", res.data.msg);
                        vue.reiniciarValoresAll();

                    }, 2000);
                })
                .catch((err) => {
                    console.log(err);
                    vue.overlay.estado = false;
                    vue.overlay.texto = "";
                });
        },
        mostrarNotificacion(type, texto) {
            let vue = this;
            vue.notificacion.type = type;
            vue.notificacion.texto = texto;
            vue.notificacion.show = true;
            setTimeout(() => {
                vue.notificacion.show = false;
            }, 10000);
        },
        reiniciarValoresAll() {

            let vue = this;
            vue.expand = false;
            vue.select.disabledAll = false;
            vue.btn.disabled_reiniciar = false;
            vue.btn.disabled_continuar = false;
            vue.allSelected = false;

            // vue.select.modulo = null;
            // vue.select.escuela = null;
            // vue.select.curso = null;
            // vue.select.tema = null;

            vue.items.modulos = vue.baseData.modulos;
            // vue.items.escuelas = [];
            // vue.items.cursos = [];
            // vue.items.temas = [];

            vue.data_validar.count_usuarios = null;
            vue.data_validar.pruebas = [];
            vue.data_validar.mod_eval = null;
        },
    },
  };
</script>

<style>
  .v-application--is-ltr .v-alert__icon {
    margin-top: 5px !important;
  }
  /*.v-text-field .v-input__control,
  .v-text-field .v-input__slot,
  .v-text-field fieldset {
    border-radius: 0 !important;
    border-color: #dee2e6;
  }*/
  .v-input--selection-controls {
    margin-top: unset;
    margin-bottom: 5px !important;
  }
  .white-text {
    color: white !important;
  }
  .box {
    border: solid 1px;
    margin-top: 8px;
    margin-left: 8px;
    margin-right: 8px;
  }
  .box_p {
    display: flex;
    flex-direction: column;
  }
  .titulo {
    justify-content: start;
    display: flex;
    font-weight: bold;
  }
  .seleccion {
    display: flex;
    justify-content: start;
    padding-left: 5px;
  }

  /* FIXED HEAD  */
  .tableFixHead {
    /* overflow: auto; */
    max-height: 40vh;
  }
  .tableFixHead thead th {
    position: sticky;
    top: 0;
    z-index: 1;
  }

  /* Just common table stuff. Really. */
  .tabled_fixhead {
    border-collapse: collapse;
    width: 100%;
  }
  .th_fixhead,
  .td_fixhead {
    padding: 8px 16px;
  }
  .td_fixhead {
    font-weight: bold;
  }
  .th_fixhead {
    color: white;
    background: #796aee;
    font-weight: bold;
  }
  .check-all .v-icon.notranslate{
    color: white;
    top: 5px;
  }
</style>
