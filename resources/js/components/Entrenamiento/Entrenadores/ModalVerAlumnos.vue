<template>
    <v-dialog :max-width="width" v-model="value" @click:outside="closeModal">
        <v-card elevation="0">
            <v-card-title class="default-dialog-title">
                Alumnos de {{ entrenador.name }} - {{entrenador.document}}
                <v-spacer></v-spacer>
                <v-btn icon :ripple="false" color="white" @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>

            <v-card-text>
                <v-row class="my-2">
                    <v-col cols="12" md="8" lg="8">
                        <v-text-field
                            outlined
                            dense
                            hide-details="auto"
                            :label="`Filtrar por nombre de alumno`"
                            v-model="txt_filtrar_alumnos"
                            autocomplete="off"
                            clearable
                        >
                        </v-text-field>
                    </v-col>
                    <v-col
                        cols="12"
                        md="4"
                        lg="4"
                        class="text-right"
                    >
                        <v-btn
                            color="primary"
                            :outlined="entrenador.asignar_alumnos"
                            @click="entrenador.asignar_alumnos = !entrenador.asignar_alumnos"
                        >
                            Agregar alumnos
                        </v-btn
                        >
                    </v-col>
                </v-row>
                <v-expand-transition>
                    <v-row
                        v-show="entrenador.asignar_alumnos"
                        class="mb-4"
                    >
                        <v-col cols="12" md="12" lg="12" class="py-0 my-0">
                            <hr>
                            <v-text-field
                                outlined
                                dense
                                hide-details="auto"
                                :label="`Buscar por nombre o DNI`"
                                @keyup="search"
                                v-model="search_text"
                                autocomplete="off"
                                clearable
                            >
                            </v-text-field>
                        </v-col>
                        <!-- <v-col cols="12" md="8" lg="8" class="pb-0 mb-0"></v-col> -->
                        <v-col cols="12" md="10" lg="10" class="pb-0 mb-0">
                            <v-autocomplete
                                v-model="alumno_seleccionados"
                                outlined
                                dense
                                :items="results_search"
                                :loading="isLoading"
                                item-text="text"
                                item-value="document"
                                clearable
                                no-data-text="No hay resultados"
                                return-object
                                :disabled="autocomplete_disabled"
                                multiple
                                label="Resultados"
                                persistent-hint
                                :hint="`${alumno_seleccionados.length} seleccionado(s)`"
                                small-chips
                                ref="resultSearch"
                            >
                                <template v-slot:selection="data">
                                    <v-chip small v-bind="data.attrs" :input-value="data.selected">
                                        {{ data.item.name }}
                                    </v-chip>
                                </template>
                            </v-autocomplete>
                        </v-col>

                        <v-col cols="12" md="2" lg="2" class="d-flex justify-end pb-0 mb-0">
                            <v-btn outlined @click="agregarAlumno">Agregar</v-btn>
                        </v-col>
                    </v-row>
                </v-expand-transition>

                <hr>
                <v-row
                    style="
                        font-weight: 600;
                    "
                >
                    <v-col cols="12" md="2" lg="2" class="d-flex justify-start"> DNI</v-col>
                    <v-col cols="12" md="5" lg="5" class="d-flex justify-start"> Nombres y Apellidos</v-col>
                    <v-col cols="12" md="3" lg="3" class="d-flex justify-start"> Workspace</v-col>
                    <!-- <v-col cols="12" :md="isMaster? '2' : '4'" :lg="isMaster ? '2' : '4'" class="d-flex justify-center">
                        Estado
                    </v-col> -->
                    <v-col cols="12" md="2" lg="2" class="d-flex justify-center" v-if="isMaster"> Eliminar</v-col>
                </v-row>
                <v-virtual-scroll
                    :bench="10"
                    :items="txt_filter_alumnos"
                    height="400"
                    item-height="40"
                    style="overflow-x: hidden"
                >
                    <template v-slot:default="{ item }">
                        <v-row class="" style="border-top: 1px solid #e0e0e0;">
                            <v-col cols="12" md="2" lg="2" class="py-2">
                                {{ item.document }}
                            </v-col>
                            <v-col cols="12" md="5" lg="5" class="py-2">
                                {{ item.name }}
                            </v-col>
                            <v-col cols="12" md="3" lg="3" class="py-2">
                                {{ item.botica }}
                            </v-col>
                            <!-- <v-col cols="12" :md="isMaster? '2' : '4'" :lg="isMaster ? '2' : '4'"
                                   class="d-flex justify-center py-2">
                                <v-switch
                                    inset
                                    hide-details
                                    v-model="item.estado"
                                    @change="cambiarEstado(item)"
                                    :disabled="item.loading"
                                ></v-switch>
                            </v-col> -->
                            <v-col cols="12" md="2" lg="2" class="pt-0 text-center" v-if="isMaster">
                                <v-btn icon color="primary" style="" @click="dialog_delete= true; data_eliminar = item">
                                    <v-icon>mdi-trash-can</v-icon>
                                </v-btn>
                            </v-col>
                        </v-row>
                    </template>
                </v-virtual-scroll>
            </v-card-text>
        </v-card>
        <v-dialog v-model="dialog_delete" persistent width="450">

            <v-card>
                <v-card-title class="default-dialog-title">
                    Eliminar alumno de la lista
                </v-card-title>
                <v-card-text class="py-3">
                    <v-row>
                        <v-col cols="12" md="12" lg="12">
                            Se eliminará a <strong>{{ data_eliminar ? data_eliminar.name : '' }}</strong> con <strong>DNI {{ data_eliminar ? data_eliminar.document : '' }}</strong> de la lista de alumnos de {{ entrenador.name }}.
                            <br>
                            <br>
                            <div class="text-center">
                                <strong >¿Está seguro de realizar esta acción?</strong>
                            </div>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions class="d-flex justify-center">
                    <v-btn outlined color="primary" @click="dialog_delete =false; data_eliminar = null">
                        Cancelar
                    </v-btn>
                    <v-btn  color="primary" @click="eliminarRelacionEntrenadorAlumno">
                        Confirmar
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-dialog>
</template>


<script>
export default {
    props: ["value", "width", "entrenador", "isMaster"],
    data() {
        return {
            dialog: false,
            file: null,
            txt_filtrar_alumnos: null,
            alumno_seleccionados: [],
            autocomplete_disabled: false,
            results_search: [],
            isLoading: false,
            timeout: null,
            search_text: null,
            dialog_delete: false,
            data_eliminar: null
        };
    },
    computed: {
        txt_filter_alumnos() {
            let vue = this;
            if (vue.txt_filtrar_alumnos === null) {
                return vue.entrenador.alumnos;
            }
            if (vue.entrenador.alumnos)
                return vue.entrenador.alumnos.filter((alumno) => {
                    if(alumno.name != '' && alumno.name != null && alumno.document != '' && alumno.document != null)
                    return (
                        alumno.name.toLowerCase().includes(vue.txt_filtrar_alumnos.toLowerCase()) ||
                        alumno.document.toLowerCase().includes(vue.txt_filtrar_alumnos.toLowerCase())
                    );
                });
        }
    },
    methods: {
        closeModal() {
            let vue = this;
            vue.resetSelects()
            vue.$emit("close");
        },
        confirm() {
            let vue = this;
            vue.$emit("onConfirm");
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        resetSelects(){
            let vue = this
            vue.txt_filtrar_alumnos = null
            vue.search_text = null
            vue.alumno_seleccionados = []
            vue.results_search = []
        },
        subirExcel() {
            let vue = this;
            let data = new FormData();
            data.append("archivo", vue.file);

            axios
                .post("/entrenamiento/entrenadores/asignar_masivo", data)
                .then((res) => {
                    vue.$emit("refreshTable")
                    console.log(res);
                })
                .catch((err) => {
                    console.log(err);
                });
        },
        cambiarEstado(alumno) {
            let vue = this;
            alumno.loading = true;
            const data = {
                entrenador: vue.entrenador.document,
                alumno: alumno.document,
                estado: alumno.estado
            };
            axios
                .post(`/entrenamiento/entrenadores/cambiar_estado_entrenador_alumno`, data)
                .then((res) => {
                    if (res.data.error) {
                        vue.$notification.warning(`${res.data.msg}`, {
                            timer: 6,
                            showLeftIcn: false,
                            showCloseIcn: true
                        });
                    } else {
                        vue.$notification.success(`${res.data.msg}`, {
                            timer: 6,
                            showLeftIcn: false,
                            showCloseIcn: true
                        });
                    }
                    setTimeout(() => {
                        alumno.loading = false;
                    }, 1500);
                })
                .catch((err) => {
                    console.log(err);
                });
        },
        agregarAlumno() {
            let vue = this;
            vue.autocomplete_disabled = true;
            vue.isLoading = true;

            const data = {
                entrenador_id: vue.entrenador.id,
                entrenador_dni: vue.entrenador.document,
                alumnos: vue.alumno_seleccionados,
                filtro: "",
                estado: 1
            };
            axios
                .post(`/entrenamiento/entrenadores/asignar`, data)
                .then((res) => {
                    vue.entrenador.alumnos = res.data.alumnos;
                    vue.autocomplete_disabled = false;
                    vue.isLoading = false;
                    vue.$emit("refreshTable")
                })
                .catch((err) => {
                    console.log(err);
                    vue.autocomplete_disabled = false;
                    vue.isLoading = false;
                });
        },
        remove(item) {
            const index = this.alumno_seleccionados.indexOf(item.name);
            if (index >= 0) this.alumno_seleccionados.splice(index, 1);
        },
        search() {
            clearTimeout(this.timeout);
            let vue = this;
            if (vue.search_text == null || vue.search_text == "") return;
            if (vue.isLoading) return;
            this.timeout = setTimeout(function () {
                vue.isLoading = true;
                const data = {
                    entrenador_id: vue.entrenador.id,
                    filtro: vue.search_text,
                    config_id: vue.entrenador.config_id
                };
                axios
                    .post(`/entrenamiento/entrenadores/buscar_alumno`, data)
                    .then((res) => {
                        console.log(res)
                        vue.alumno_seleccionados = []
                        vue.results_search = res.data.alumnos;
                        vue.$nextTick(() => {
                            vue.$refs.resultSearch.focus()
                            vue.$refs.resultSearch.isMenuActive = true
                        })
                        vue.search_text = null
                        // vue.results_search = []
                        setTimeout(() => {
                            vue.isLoading = false;
                        }, 1500);
                    })
                    .catch((err) => {
                        console.log(err);
                        vue.isLoading = false;
                    });
            }, 1000);
        },
        eliminarRelacionEntrenadorAlumno() {
            let vue = this;
            const data = {
                entrenador: vue.entrenador,
                alumno: vue.data_eliminar
            }
            axios.post(`/entrenamiento/entrenadores/eliminar_relacion_entrenador_alumno`, data)
                .then(res => {
                    let response = res.data
                    if (!response.error) {
                        let indexAlumno = vue.entrenador.alumnos.findIndex(alumno => alumno.id === vue.data_eliminar.id);
                        vue.entrenador.alumnos.splice(indexAlumno, 1)
                    }
                    vue.$emit("showSnackbar", {msg: response.msg, color: 'primary'});
                    vue.$emit("refreshTable")

                    vue.data_eliminar = false
                    vue.dialog_delete = false;
                })
                .catch(err => {
                    console.log(err)
                    vue.data_eliminar = false
                    vue.dialog_delete = false;
                })
        }
    }
};
</script>

<style>
.txt-white-bold {
    color: white !important;
    font-weight: bold !important;
}

.v-input__icon {
    /*padding-bottom: 12px;*/
}

.v-icon.v-icon.v-icon--link {
    color: #1976d2;
}

/*.v-icon.v-icon {*/
/*    font-size: 31px !important;*/
/*}*/

.v-input--selection-controls {
    margin: unset !important;
    padding-top: unset !important;
}

.no-border-top {
    border-top: unset !important;
}
</style>
