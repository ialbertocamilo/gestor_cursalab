<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="form_notificacion" lazy-validation
                    @submit.prevent="confirmModal()">
                <v-card-text class="pt-0 pb-0">

                    <v-row>
                        <v-col cols="12" class="d-flex justify-content-center">
                            <DefaultInput clearable
                                          v-model="nueva_notificacion.titulo"
                                          :rules="rules.titulo"
                                          label="Título"
                                          placeholder="Ingresa un título"
                            />
                        </v-col>
                        <v-col cols="12" md="12" lg="12">
                            <v-textarea
                                label="Texto de la notificación (opcional) "
                                outlined
                                dense
                                hide-details="auto"
                                placeholder="Ingresa un texto"
                                rows="2"
                                v-model="nueva_notificacion.texto"
                            ></v-textarea>
                        </v-col>
                    </v-row>

                    <v-divider/>

                    <strong class="my-3">Previsualización:</strong>

                    <v-divider/>

                    <v-row>
                        <v-col cols="12" md="6" lg="6" class="pa-0">
                            <v-card elevation="0">
                                <v-container fluid class="container-custom-preview">
                                    <v-img
                                        src="https://www.gstatic.com/mobilesdk/190403_mobilesdk/android.png"
                                        class="image-prev"
                                    >
                                        <v-list-item two-line class="box-notificacion">
                                            <v-list-item-content>
                                                <v-list-item-title class="texto-negrita notificacion_texto">
                                                    {{ nueva_notificacion.titulo }}
                                                </v-list-item-title>
                                                <v-list-item-subtitle>
                                                    {{ nueva_notificacion.texto }}
                                                </v-list-item-subtitle>
                                            </v-list-item-content>
                                        </v-list-item>
                                    </v-img>
                                    <p class="os-preview texto-negrita">Android</p>
                                </v-container>
                            </v-card>
                        </v-col>
                        <v-col cols="12" md="6" lg="6" class="pa-0">
                            <v-card elevation="0">
                                <v-container fluid class="container-custom-preview">
                                    <v-img src="https://www.gstatic.com/mobilesdk/190403_mobilesdk/iphone.png">
                                        <v-list-item two-line class="box-notificacion">
                                            <v-list-item-content>
                                                <v-list-item-title class="texto-negrita notificacion_texto">
                                                    {{ nueva_notificacion.titulo }}
                                                </v-list-item-title>
                                                <v-list-item-subtitle>
                                                    {{ nueva_notificacion.texto }}
                                                </v-list-item-subtitle>
                                            </v-list-item-content>
                                        </v-list-item>
                                    </v-img>
                                    <p class="os-preview texto-negrita">iOS</p>
                                </v-container>
                            </v-card>
                        </v-col>
                    </v-row>
                    <!--       <v-row>
                              <v-col cols="12" md="2" lg="2" class="--vertical-align">
                                  <label
                                      class="form-control-label texto-negrita"
                                      style="font-size: 1.15em !important"
                                      >Destinatarios</label
                                  >
                              </v-col>
                              <v-col cols="12" md="10" lg="10"> </v-col>
                          </v-row>
       -->
                    <v-divider/>

                    <strong class="my-3">Destinatarios:</strong>

                    <v-row class="">
                        <v-col cols="12" md="12" lg="12" class="--vertical-align">
                            <table class="table table-hover">
                                <thead class="">
                                <tr>
                                    <th style="width: 5% !important">
                                        <input type="checkbox"
                                               v-model="cbxAll"
                                               @change="allModules()"/>
                                    </th>
                                    <th class="text-left" style="width: 20% !important">
                                        Módulo
                                    </th>
                                    <th class="text-left" style="width: 75% !important">
                                        Seleccionar puesto (opcional)
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(module, index) in modules" :key="index">
                                    <td>
                                        <input type="checkbox"
                                               v-model="module.modulo_selected"/>
                                    </td>
                                    <td>
                                        {{ module.nombre }}
                                    </td>
                                    <td>
                                        <v-autocomplete
                                            :items="module.carreras"
                                            multiple
                                            chips
                                            dense
                                            v-model="module.carreras_selected"
                                            hide-details="auto"
                                            item-text="nombre"
                                            item-value="id"
                                            return-object
                                            :disabled="!module.modulo_selected"
                                        >
                                            <template v-slot:prepend-item>
                                                <v-list-item ripple @click="toggle(index)">
                                                    <v-list-item-action>
                                                        <v-icon
                                                            :color="
                                                                    module.carreras_selected.length > 0
                                                                    ? 'indigo darken-4' : ''
                                                                "
                                                        >
                                                            {{ icon(index) }}
                                                        </v-icon>
                                                    </v-list-item-action>
                                                    <v-list-item-content>
                                                        <v-list-item-title>
                                                            Seleccionar todas las carreras
                                                        </v-list-item-title>
                                                    </v-list-item-content>
                                                </v-list-item>
                                                <v-divider class="mt-2"></v-divider>
                                            </template>
                                            <template v-slot:selection="{ item, index }">
                                                <v-chip v-if="index < 3">
                                                    <span>{{ item.nombre }}</span>
                                                </v-chip>
                                                <span v-if="index === 3"
                                                      class="grey--text caption">
                                                        (+{{ module.carreras_selected.length - 3 }} carrera{{
                                                        module.carreras_selected.length - 3 > 1 ? "s" : ""
                                                    }})
                                                    </span>
                                            </template>
                                        </v-autocomplete>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </v-col>
                    </v-row>

                </v-card-text>

                <!--  <v-divider></v-divider>

                 <v-card-actions class="d-flex justify-center">
                     <v-btn
                         color="#727b84"
                         class="text-white text-capitalize size-1rem"
                         @click="closeModal()"
                     >
                         Cancelar
                     </v-btn>
                     <v-btn
                         color="#2c32e4"
                         class="text-white text-capitalize size-1rem"
                         type="submit"
                         :disabled="!existeDestinatarios"
                     >
                         Enviar
                     </v-btn>
                 </v-card-actions> -->
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
        width: String,
        modules: {
            type: Object | Array,
            default: []
        }
    },

    // props: ["creador_id"],
    data() {
        return {
            dialog: false,
            hide_overlay: true,
            btn_disabled: false,
            nueva_notificacion: {
                titulo: "",
                texto: "",
                destinatarios: "",
                // creador_id: this.creador_id.id,
            },
            rules: {
                titulo: [(v) => !!v || "El título no puede ir vacío."],
            },

            notificaciones: [],
            cbxAll: false,
            overlay: false,
            loader_text: "",
            // paginate: {
            //     page: 1,
            //     total_paginas: 1,
            // },
        };
    },
    created() {
        let vue = this;
        // vue.getData();
    },
    computed: {

        existeDestinatarios() {

            let vue = this;
            let existe = false;

            for (let i = 0; i < vue.modules.length; i++) {

                if (vue.modules[i].modulo_selected &&
                    vue.modules[i].carreras_selected.length > 0) {

                    existe = true;
                    break;
                }
            }
            return existe;
        },
    },
    methods: {
        // getData() {
        //     let vue = this;
        //     axios
        //         .get("/notificaciones_push/getData?page=" + vue.paginate.page)
        //         .then((res) => {
        //             vue.modules = res.data.modules;
        //             vue.notificaciones = res.data._notificaciones;
        //             vue.paginate.total_paginas = res.data.paginate.total_paginas;
        //         })
        //         .catch((err) => {
        //             vue.$notification.error("Error de servidor.", {
        //                 timer: 10,
        //                 showLeftIcn: false,
        //                 showCloseIcn: true,
        //             });
        //         });
        // },
        allModules() {
            let vue = this;
            vue.modules.forEach((module) => {
                module.modulo_selected = vue.cbxAll;
            });
        },
        seleccionarTodasLasCarreras(index) {
            let vue = this;
            return vue.modules[index].carreras_selected.length === vue.modules[index].carreras.length;
        },
        seleccionarAlgunasCarreras(index) {
            let vue = this;
            return vue.modules[index].carreras_selected.length > 0 &&
                !vue.seleccionarTodasLasCarreras;
        },
        icon(index) {
            let vue = this;
            if (vue.seleccionarTodasLasCarreras(index)) return "mdi-close-box";
            if (vue.seleccionarAlgunasCarreras(index)) return "mdi-minus-box";
            return "mdi-checkbox-blank-outline";
        },
        toggle(index) {
            let vue = this;
            vue.$nextTick(() => {
                if (vue.seleccionarTodasLasCarreras(index)) {
                    vue.modules[index].carreras_selected = [];
                } else {
                    vue.modules[index].carreras_selected = vue.modules[index].carreras.slice();
                }
            });
        },
        generarJson() {
            let vue = this;
            let cadena = "[";
            vue.modules.forEach((mod) => {

                cadena +=
                    '{"modulo_id":' + mod.id + ', "modulo_nombre": "' + mod.nombre + '", "carreras": [';

                if (mod.carreras_selected) {
                    mod.carreras_selected.forEach((carr) => {
                        cadena += '{"carrera_id":' + carr.id + ', "carrera_nombre": "' + carr.nombre + '" },';
                    });
                    cadena = cadena.substring(0, cadena.length - 1);
                }

                cadena += "]},";
            });
            cadena = cadena.substring(0, cadena.length - 1);

            cadena += "]";

            vue.nueva_notificacion.destinatarios = cadena;
        },
        showOverlay(text) {
            let vue = this;
            vue.overlay = true;
            vue.loader_text = text;
        },
        hideOverlay() {
            let vue = this;
            vue.overlay = false;
            vue.loader_text = "";
        },
        async confirmModal() {

            let vue = this
            vue.showLoader()

            // let validar = this.$refs.form_notificacion.validate();
            vue.btn_disabled = true;
            const validateModuleSelected = await vue.validateModuleSelected();
            if (!validateModuleSelected) {
                vue.btn_disabled = false;
                vue.showAlert('Debe seleccionar al menos un módulo', 'warning');
                vue.hideLoader()
                return;
            }

            vue.generarJson();
            const {titulo, texto, destinatarios} = vue.nueva_notificacion;

            const data = {
                titulo,
                texto,
                selected_detail: destinatarios,
                destinatarios: vue.modules
                    .filter((module) => module.modulo_selected)
                    .map((mod) => {
                        return {criterion_value_id: mod.id, carreras_selected: mod.carreras_selected}
                    })
            }

            vue.$http
                .post(
                    "/notificaciones_push/enviarNotificacionCustom",
                    // vue.nueva_notificacion
                    data
                )
                .then((response) => {
                    vue.closeModal();
                    vue.showAlert(response.data.title);
                    vue.$emit('onConfirm');
                    this.hideLoader();

                })
                .catch(() => {
                    vue.hideLoader();
                    vue.btn_disabled = false;
                });
        },

        validateModuleSelected() {
            let vue = this;

            return !(vue.modules.filter(module => module.modulo_selected === true).length === 0);
        },

        closeModal() {
            let vue = this;
            vue.nueva_notificacion.titulo = "";
            vue.nueva_notificacion.texto = "";
            vue.nueva_notificacion.destinatarios = [];
            vue.modules.forEach((mod) => {
                mod.carreras_selected = [];
                mod.modulo_selected = false;
            });
            vue.cbxAll = false;
            vue.dialog = false;
            vue.$emit('onCancel')
        },

        resetValidation() {
            let vue = this
        },
        loadData() {
            let vue = this

        },
        loadSelects() {

        },
    },

}
</script>

<style>
input[type="checkbox"] {
    transform: scale(1.5);
}

.notificationCenter {
    z-index: 1000 !important;
}

.os-preview {
    color: #939393;
}

.container-custom-preview {
    display: flex;
    justify-content: center;
    flex-direction: column;
    align-items: center;
}

.box-notificacion {
    background: white;
    border: black;
    border-radius: 2px;
    margin-top: 20%;
    padding: 0 8px 0 8px;
    margin-right: 7% !important;
    margin-left: 7% !important;
}

.img-prev {
    background-repeat: no-repeat;
    background-size: 100%;
    display: flex;
    margin: 4px;
    display: flex;
    justify-content: center;
    align-items: center;
}

/*.vertical-align {
    display: flex !important;
    align-items: center !important;
    justify-content: end;
}*/
/*.theme--light.v-application {
    background: transparent !important;
}
.font-family {
    font-family: "Roboto", sans-serif !important;
}
.text--white {
    color: #fff !important;
}
.element-bold {
    font-weight: 400 !important;
}
.size-1rem {
    font-size: 1rem !important;
}
.size-0_7rem {
    font-size: 0.9rem !important;
}*/
.texto-negrita {
    font-weight: bold !important;
}

.notificacion_texto {
    white-space: normal;
    text-align: left;
    font-size: 14px !important;
}
</style>
