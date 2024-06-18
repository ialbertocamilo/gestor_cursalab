<template>
    <v-app>
        <section class="section-list">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h5>Editar Pregunta</h5>
                            <v-spacer />
                                <v-btn
                                    color="primary"
                                    fab
                                    x-small
                                    @click="back_page()"
                                >
                                <v-icon>mdi-backspace</v-icon>
                            </v-btn>
                        </div>
                        <div class="card-body">
                            <v-card elevation="0">
                                <v-card-title>

                                    <v-alert
                                        border="left"
                                        dense
                                        transition="fade-transition"
                                        :type="alert_backend.tipo_alert"
                                        text
                                        v-model="alert_backend.alert"
                                        close-text="Cerrar alerta"
                                        class="mx-8 text-left"
                                    >
                                        {{ alert_backend.message_alert }} <strong>{{
                                            alert_backend.titulo
                                        }}</strong>
                                    </v-alert>
                                    <v-alert
                                        border="left"
                                        dense
                                        transition="fade-transition"
                                        :type="alert_frontend.tipo_alert"
                                        text
                                        v-model="alert_frontend.alert"
                                        close-text="Cerrar alerta"
                                        class="mx-8 text-left"
                                        style="position: fixed;width: 50%;z-index: 27;"
                                    >
                                        {{ alert_frontend.message_alert }}
                                    </v-alert>
                                </v-card-title>
                                <v-card-text>
                                    <v-row>
                                        <v-col cols="12" md="2" lg="2" class="vertical-align text-right"
                                                :class="alignLabel()">
                                            <label
                                                class="form-control-label texto-negrita label-size">Título</label>
                                        </v-col>
                                        <v-col cols="12" md="10" lg="10" class="vertical-align text-right">
                                            <v-container>
                                                <editor
                                                    :api-key="$root.getEditorAPIKey()"
                                                    v-model="pregunta.pregunta"
                                                    :init="{
                                    content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                    height: 175,
                                    menubar: false,
                                    language: 'es',
                                    force_br_newlines : true,
                                    force_p_newlines : false,
                                    forced_root_block : '',
                                    plugins: ['lists image preview anchor', 'code', 'paste','link'],
                                    toolbar:
                                        'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code | link',
                                    images_upload_handler: images_upload_handler,
                                }"
                                                />
                                            </v-container>
                                        </v-col>
                                    </v-row>
                                    <v-row v-if="evaluable !== 'abierta'">
                                        <v-col cols="12" md="2" lg="2" class="vertical-align text-right"
                                                :class="alignLabel()">
                                            <label class="form-control-label texto-negrita label-size">Agregar
                                                respuesta</label>
                                        </v-col>
                                        <v-col cols="12" md="10" lg="10" class="vertical-align text-right">
                                            <v-container>
                                                <editor
                                                    :api-key="$root.getEditorAPIKey()"
                                                    v-model="opcOpcion"
                                                    :init="{
                                    content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                    height: 175,
                                    menubar: false,
                                    language: 'es',
                                    force_br_newlines : true,
                                    force_p_newlines : false,
                                    forced_root_block : '',
                                    plugins: ['lists image preview anchor', 'code', 'paste','link'],
                                    toolbar:
                                        'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code | link',
                                    images_upload_handler: images_upload_handler,
                                }"
                                                />
                                            </v-container>
                                        </v-col>
                                        <v-col cols="12" md="2" lg="2"></v-col>

                                        <v-col cols="12" md="10" lg="10" class="text-center">
                                            <v-btn class="white-text font-bold" color="primary"
                                                    @click="agregarOpcion()"
                                            ><i class="fa fa-plus mr-2"></i> Agregar
                                            </v-btn
                                            >
                                        </v-col>
                                        <v-col cols="12" md="2" lg="2"></v-col>
                                        <v-col cols="12" md="10" lg="10">
                                            <table class="table table-hover datatable" style="overflow-x: scroll">
                                                <thead>
                                                <tr class="td-size">
                                                    <th class="text-left" style="width: 10%">#</th>
                                                    <th class="text-left" style="width: 70%">Respuesta</th>
                                                    <th class="text-left" style="width: 10%">¿Correcta?</th>
                                                    <th class="text-left" style="width: 10%">Acción</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr
                                                    class="text-left td-size"
                                                    v-for="(respuesta, index2) in pregunta.rptas_json"
                                                    :key="index2"
                                                >
                                                    <td>{{ respuesta.id }}</td>
                                                    <td v-html="respuesta.opc"></td>
                                                    <td class="d-flex justify-center switch-pregunta">
                                                        <v-checkbox
                                                            @change="cambiarCorrecta(respuesta.id, respuesta.correcta)"
                                                            v-model="respuesta.correcta"
                                                        ></v-checkbox>
                                                    </td>
                                                    <td>
                                                        <v-btn icon small @click="borrarOpcion(respuesta.id)">
                                                            <v-icon>mdi-delete</v-icon>
                                                        </v-btn>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col cols="12" md="2" lg="2" class="vertical-align text-right"
                                                :class="alignLabel()">
                                            <label
                                                class="form-control-label texto-negrita label-size">Estado</label>
                                        </v-col>
                                        <v-col cols="12" md="2" lg="2">
                                            <v-switch class="ml-10" v-model="pregunta.estado"></v-switch>
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <button class="btn btn-secondary mr-1" @click="cancelarCambios()">Cancelar
                                    </button>
                                    <button class="btn btn-primary" @click="guardarPregunta()">Guardar</button>
                                </v-card-actions>
                            </v-card>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </v-app>
</template>


<script>
import Editor from "@tinymce/tinymce-vue";

export default {
    components: {
        editor: Editor,
    },
    props: ['pregunta_id', 'post_id', 'curso_id', 'evaluable'],
    data() {
        return {
            opcOpcion: null,
            pregunta: {
                pregunta: "",
                id: null,
                rptas_json: [],
                rpta_ok: null,
                estado: null,
                nuevasRptas: "",
                post_id: 0,
                curso_id: 0
            },
            alert_backend: {
                tipo_alert: "info",
                message_alert: "",
                alert: false,
                titulo: "",
            },
            alert_frontend: {
                tipo_alert: "info",
                message_alert: "",
                alert: false,
            },
        }
    },
    mounted() {
        console.log();
        if(this.pregunta_id >0){
            this.getInitialData();
        }
    },
    methods: {
        getInitialData() {
            let vue = this;
            axios
                .get("/evaluaciones/preguntas/getInitalData/" + vue.pregunta_id)
                .then((res) => {
                    vue.pregunta.id = res.data.pregunta.id;
                    vue.pregunta.pregunta = res.data.pregunta.pregunta;
                    vue.pregunta.rpta_ok = res.data.pregunta.rpta_ok;
                    vue.pregunta.rptas_json = res.data.pregunta.rptas_json;
                    vue.pregunta.estado = res.data.pregunta.estado;
                    vue.pregunta.post_id = vue.post_id;
                    vue.pregunta.curso_id = vue.curso_id;
                })
                .catch((err) => {

                });
        },
        alignLabel() {
            switch (this.$vuetify.breakpoint.name) {
                case "xs":
                    return "";
                case "sm":
                    return "";
                case "md":
                    return "justify-end";
                case "lg":
                    return "justify-end";
                case "xl":
                    return "justify-end";
            }
        },
        cancelarCambios() {
            let vue = this;
            window.history.go(-1);
        },
        generarJson() {
            let vue = this;
            let cadena = "{";
            if (vue.pregunta.rptas_json.length > 0) {
                vue.pregunta.rptas_json.forEach((element) => {
                    cadena += '"' + element.id + '":"' + element.opc.replaceAll('"', "'").replace(/(?:\r\n|\r|\n)/g, '<br>') + '",';
                });
                cadena = cadena.substring(0, cadena.length - 1);
            }
            cadena += "}";
            vue.pregunta.nuevasRptas = cadena;
            /*vue.pregunta.nuevasRptas = vue.pregunta.rptas_json;*/
        },
        guardarPregunta() {
            let vue = this;
            vue.generarJson();
            vue.pregunta.curso_id = vue.curso_id;
            vue.pregunta.post_id = vue.post_id;
            if (vue.verificarExisteRptaCorrecta() != null) {
                axios.post(`/evaluaciones/preguntas/createOrUpdate`, vue.pregunta)
                    .then(res => {
                        if (!res.data.error) {
                            window.location = `/cursos/${vue.pregunta.curso_id}/posteos/${vue.pregunta.post_id}/preguntas`
                        }
                    })
                    .catch(err => {
                        console.log(err)
                    })
            }

        },
        verificarExisteRptaCorrecta() {
            let vue = this;
            if (vue.evaluable === 'calificada') {
                let existe = vue.pregunta.rptas_json.find((rpta) => rpta.correcta == true);
                if (!existe) {
                    vue.$notification.warning("Debe marcar una respuesta correcta.", {
                        timer: 6,
                        showLeftIcn: false,
                        showCloseIcn: true
                    });
                }
                return existe;
            }
            return true;
        },
        show_alert_frontend(tipo, message) {
            let vue = this;
            vue.alert_frontend.tipo_alert = tipo;
            vue.alert_frontend.message_alert = message;
            vue.alert_frontend.alert = true;
            setTimeout(() => {
                vue.alert_frontend.alert = false;
            }, 6500);
        },
        images_upload_handler(blobInfo, success, failure) {
            console.log(blobInfo.blob());
            let formdata = new FormData();
            formdata.append("image", blobInfo.blob(), blobInfo.filename());
            formdata.append("model_id", null);

            axios
                .post("/upload-image/evaluaciones", formdata)
                .then((res) => {
                    success(res.data.location);
                })
                .catch((err) => {
                    console.log(err)
                    failure("upload failed!");
                });
        },
        borrarOpcion(respuesta_id) {
            let vue = this;
            let respuesta = vue.pregunta.rptas_json.findIndex((rpta) => rpta.id == respuesta_id);
            vue.pregunta.rptas_json.splice(respuesta, 1);
            vue.pregunta.rptas_json.forEach((rpta, index) => {
                rpta.id = index + 1;
                if (rpta.correcta) {
                    this.pregunta.rpta_ok = rpta.id;
                }
            });
        },
        agregarOpcion() {
            let vue = this;
            let nuevaOpcion = {
                id: vue.pregunta.rptas_json.length + 1,
                opc: vue.opcOpcion,
                correcta: false,
            };
            vue.pregunta.rptas_json.push(nuevaOpcion);
            vue.opcOpcion = "";
        },
        cambiarCorrecta(respuesta_id, estado) {
            let vue = this;
            vue.pregunta.rptas_json.forEach((rpta) => {
                if (rpta.id === respuesta_id) {
                    rpta.correcta = estado;
                    vue.pregunta.rpta_ok = estado ? parseInt(rpta.id) : 0;
                } else {
                    rpta.correcta = false;
                }
            });
        },
        back_page(){
            window.location.href = `/cursos/${this.curso_id}/posteos/${this.post_id}/preguntas`;
        }
    }
}

</script>
<style>
.switch-pregunta {
    max-height: 20px !important;
    align-items: center;
    padding-top: 23px !important;
}

.vertical-align {
    display: flex !important;
    align-items: center !important;
}
</style>
