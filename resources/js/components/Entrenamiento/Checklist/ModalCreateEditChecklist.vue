<template>
    <v-dialog :max-width="width" v-model="value" scrollable @click:outside="closeModal">
        <v-card>
            <v-card-title class="default-dialog-title">
                {{ checklist.id == 0 ? "Crear Checklist" : "Editar Checklist" }}
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>
            <v-card-text style="height: 650px" class="pt-5">
                <v-form ref="formChecklistCreateEdit" class="checklist-form">
                    <v-row>
                        <v-col cols="12" md="12" lg="12" class="pb-0">
                            <DefaultInput clearable
                                          v-model="checklist.title"
                                          label="Título"
                                          :rules="formRules.titulo_descripcion"
                                          :counter="280"
                            />
                        </v-col>
                        <v-col cols="12" md="12" lg="12" class="pb-0">
                            <v-textarea
                                rows="2"
                                outlined
                                dense
                                hide-details="auto"
                                label="Descripción"
                                :counter="280"
                                v-model="checklist.description"
                                :rules="formRules.titulo_descripcion"
                            ></v-textarea>
                        </v-col>
                        <v-col cols="12" md="2" lg="2" class="d-flex align-center">
                            <DefaultToggle v-model="checklist.active"/>
                        </v-col>

                    </v-row>

                    <v-divider/>

                    <strong class="mt-3">Actividades:</strong>

                    <v-row>
                        <v-col cols="12" md="12" lg="12" class=" text-center">

                            <div class="box_info_checklist_1 mt-3">
                                Tener en cuenta que al agregar o quitar actividades a un checklist
                                completado por un usuario no tendrá efectos en su avance. Si un usuario ya
                                completó un checklist se mantiene su estado y porcentaje, pero sí se
                                actualiza para usuarios que aún no completan el checklist.
                            </div>
                            <transition name="fade">
                                        <span class="pt-5" v-if="actividadesEmpty" style="color: #FF5252 ">
                                            Debe agregar al menos una actividad para guardar el checklist
                                        </span>
                            </transition>
                        </v-col>
                        <v-col cols="12" md="12" lg="12" class="d-flex justify-content-center">
                            <v-btn color="primary" outlined @click="addActividad">
                                <v-icon class="icon_size">mdi-plus</v-icon>
                                Actividad
                            </v-btn>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col cols="12" md="12" lg="12">
                            <v-expansion-panels inset v-model="actividades_expanded">
                                <draggable v-model="checklist.checklist_actividades" @start="drag=true"
                                           @end="drag=false" class="custom-draggable" ghost-class="ghost">
                                    <transition-group type="transition" name="flip-list">
                                        <v-expansion-panel
                                            v-for="(actividad, i) in checklist.checklist_actividades"
                                            :key="actividad.id"
                                            :class="{'default-actividad-error': actividad.hasErrors}">
                                            <div class="item-draggable">
                                                <v-expansion-panel-header
                                                    v-slot="{ open }" class="my-2"
                                                >
                                                    <v-row no-gutters>
                                                        <v-col cols="6" style="
                                                                    text-overflow: ellipsis !important;
                                                                    overflow: hidden !important;
                                                                    width: 200px;
                                                                    white-space: nowrap;
                                                                "
                                                        >
                                                            <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                            </v-icon>
                                                            {{
                                                                actividad.activity || 'Ingrese un nombre a la actividad'
                                                            }}
                                                        </v-col>
                                                        <v-col cols="6" class="text--secondary">
                                                            <v-fade-transition leave-absolute>
                                                                <span v-if="open"></span>
                                                                <v-row v-else no-gutters style="width: 100%">
                                                                    <v-col cols="6" class="d-flex align-center">
                                                                        Califica a: {{
                                                                            actividad.type_name == "trainer_user" ? "Alumnos" : "Entrenador"
                                                                        }}
                                                                    </v-col>
                                                                    <v-col cols="4" class="d-flex align-center">
                                                                        {{
                                                                            actividad.active == 1 ? "Activo" : "Inactivo"
                                                                        }}
                                                                    </v-col>
                                                                    <v-col cols="2" class="d-flex align-center">
                                                                        <v-icon class="ml-0 mr-2 icon_size"
                                                                                color="primary"
                                                                                @click="eliminarActividad(actividad, i)">
                                                                            mdi-delete
                                                                        </v-icon>
                                                                    </v-col>
                                                                </v-row>
                                                            </v-fade-transition>
                                                        </v-col>
                                                    </v-row>
                                                </v-expansion-panel-header>

                                                <v-expansion-panel-content eager>
                                                    <v-row>
                                                        <v-col cols="12" md="12" lg="12">
                                                            <v-textarea
                                                                rows="2"
                                                                outlined
                                                                dense
                                                                hide-details="auto"
                                                                label="Actividad"
                                                                v-model="actividad.activity"
                                                                :rules="formRules.actividad"
                                                            ></v-textarea>
                                                        </v-col>
                                                        <v-col cols="12" md="6" lg="6">
                                                            <v-select
                                                                outlined
                                                                dense
                                                                hide-details="auto"
                                                                attach
                                                                label="Califica a"
                                                                :items="tipo_actividades"
                                                                v-model="actividad.type_name"
                                                                item-text="text"
                                                                item-value="value"
                                                            >
                                                            </v-select>
                                                        </v-col>
                                                        <v-col cols="12" md="6" lg="6" class="d-flex align-center">
                                                            <DefaultToggle v-model="actividad.active"/>
                                                            <!-- <DefaultToggle v-if="actividad.type_name == 'trainer_user'"  v-model="actividad.is_default" activeLabel='Evaluar automáticamente' inactiveLabel="No evaluar automáticamente" /> -->
                                                        </v-col>
                                                    </v-row>
                                                </v-expansion-panel-content>
                                            </div>
                                        </v-expansion-panel>
                                    </transition-group>
                                </draggable>
                            </v-expansion-panels>

                        </v-col>
                    </v-row>

                    <v-divider/>

                    <strong class="mt-3">Cursos asignados:</strong>

                    <v-row class="pb-0">
                        <!--   <p class="text-h6 ml-2 mb-0">Cursos asginados:
                              ({{ checklist.cursos && checklist.cursos.length ? checklist.cursos.length : 0 }})
                              <v-btn icon small @click="expand_cursos = !expand_cursos">
                                  <v-icon class="icon_size" v-if="expand_cursos">mdi-arrow-up-drop-circle</v-icon>
                                  <v-icon class="icon_size" v-else> mdi-arrow-down-drop-circle</v-icon>
                              </v-btn>
                          </p> -->
                        <v-col cols="12" md="12" lg="12">
                            <!-- <v-expand-transition> -->
                            <v-card elevation="0">
                                <!-- <v-card elevation="0" v-show="expand_cursos"> -->
                                <v-simple-table dense fixed-header>
                                    <template v-slot:default>
                                        <thead>
                                        <tr>
                                            <th class="text-left">
                                                Módulo
                                            </th>
                                            <th class="text-left">
                                                Escuela
                                            </th>
                                            <th class="text-left">
                                                Curso
                                            </th>
                                            <th class="text-left">

                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr
                                            v-for="(curso, index) in checklist.courses" :key="curso.id"
                                        >
                                            <td>{{ curso.modulo }}</td>
                                            <td>{{ curso.escuela }}</td>
                                            <td>{{ curso.curso }}</td>
                                            <td>
                                                <v-btn small icon :ripple="false" @click="removeCurso(index)">
                                                    <v-icon class="icon_size" small color="primary"
                                                            style="font-size: 1.25rem !important;">
                                                        mdi-delete
                                                    </v-icon>
                                                </v-btn>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </template>
                                </v-simple-table>
                            </v-card>
                            <!-- </v-expand-transition> -->
                        </v-col>
                    </v-row>

                    <v-divider/>

                    <strong class="mt-3">Asignar cursos:</strong>

                    <v-row class="pb-0">
                        <v-col cols="12" md="12" lg="12">
                            <v-text-field
                                outlined
                                dense
                                hide-details
                                label="Buscar curso"
                                v-model="search_text"
                                @keyup="search"
                                clearable
                                append-icon="mdi-book-search"
                                autocomplete="off"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="12" lg="12">
                            <v-autocomplete
                                return-object
                                multiple
                                outlined
                                dense
                                hide-details
                                v-model="checklist.courses"
                                :items="results_search"
                                label="Resultados de búsqueda"
                                :loading="isLoading"
                                item-text="nombre"
                                item-value="id"
                                loader-height="3"
                                ref="resultSearch"
                            >
                                <template v-slot:selection="{ item, index }">
                                    <v-chip
                                        style="font-size: 0.9rem !important; color: white !important"
                                        color="primary"
                                        v-if="index < 1"
                                        small
                                    >
                                        {{ item.nombre }}
                                    </v-chip>
                                    <span v-if="index === 1" class="grey--text caption">
                                                ( + )
                                            </span>
                                </template>
                                <template v-slot:item="data">
                                    <v-list-item-content>
                                        <!--                                                {{data.attrs}}-->
                                        <v-container
                                            class="px-0 align d-flex justify-content-start align-center"
                                            fluid
                                            style="min-height: 40px; max-height: 80px"
                                        >
                                            <v-checkbox
                                                v-model="data.attrs.inputValue"
                                            ></v-checkbox>
                                            <div>
                                                <v-list-item-title
                                                    v-html="data.item.nombre"></v-list-item-title>
                                                <v-list-item-subtitle class="list-cursos-carreras"

                                                                      v-html="data.item.carreras"></v-list-item-subtitle>
                                            </div>
                                        </v-container>
                                    </v-list-item-content>
                                </template>
                            </v-autocomplete>
                        </v-col>

                    </v-row>
                </v-form>

            </v-card-text>

            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)">
                <DefaultModalActionButton
                    @cancel="closeModal"
                    @confirm="confirm"/>
            </v-card-actions>
        </v-card>
        <DefaultAlertDialog
            :ref="modalAlert.ref"
            :options="modalAlert"
            :confirmLabel="modalAlert.confirmLabel"
            :hideCancelBtn="modalAlert.hideCancelBtn"
            @onConfirm ="modalAlert.open=false"
            @onCancel ="modalAlert.open=false"
        >
            <template v-slot:content> {{ modalAlert.contentText }}</template>
        </DefaultAlertDialog>
    </v-dialog>
</template>


<script>
import draggable from 'vuedraggable'

export default {
    components: {
        draggable,
    },
    props: ["value", "width", "checklist"],
    data() {
        return {
            drag: false,
            expand_cursos: true,
            actividades_expanded: [],
            tipo_actividades: [
                {
                    text: "Alumno",
                    value: "trainer_user"
                },
                {
                    text: "Entrenador",
                    value: "user_trainer"
                }
            ],
            dialog: false,
            file: null,
            search_text: null,
            results_search: [],
            isLoading: false,
            messageActividadesEmpty: false,
            formRules: {
                titulo_descripcion: [
                    v => !!v || 'Campo requerido',
                    v => (v && v.length <= 280) || 'Máximo 280 caracteres.',
                ],
                actividad: [
                    v => !!v || 'Campo requerido',
                ],
            },
            modalAlert: {
                ref: 'modalAlert',
                title: 'Alerta',
                contentText: 'Este checklist debe de tener por lo menos una (1) actividad "Califica a: Alumno"',
                open: false,
                endpoint: '',
                confirmLabel:"Entendido",
                hideCancelBtn:true,
            },
        };
    },
    computed: {
        actividadesEmpty() {
            return this.checklist.checklist_actividades && this.checklist.checklist_actividades.length === 0
        }
    },

    methods: {
        setActividadesHasErrorProp() {
            let vue = this
            if (vue.checklist.checklist_actividades) {
                vue.checklist.checklist_actividades.forEach(el => {
                    el = Object.assign({}, el, {hasErrors: false})
                })
            }
        },
        closeModal() {
            let vue = this;
            vue.expand_cursos = true;
            vue.actividades_expanded = [];
            vue.search_text = null;
            vue.resetValidation()
            vue.$emit("onClose");
        },
        resetValidation() {
            let vue = this;
            console.log('resetValidation')
            vue.search_text = null
            vue.results_search = []
            if (vue.$refs.formChecklistCreateEdit)
                vue.$refs.formChecklistCreateEdit.resetValidation()
        },
        confirm() {
            let vue = this;
            this.showLoader()

            const validateForm = vue.$refs.formChecklistCreateEdit.validate();
            const allIsValid = vue.moreValidaciones()

            if (!validateForm) {
                vue.showValidateActividades();
            }


            if (validateForm && !allIsValid) {
                vue.expand_cursos = true;
                vue.actividades_expanded = [];
                vue.search_text = null;
                vue.$emit("onConfirm");
            }

            this.hideLoader()
        },
        showValidateActividades() {
            let vue = this
            vue.checklist.checklist_actividades.forEach((el, index) => {
                el.hasErrors = !el.actividad || el.actividad === ''
            })
        },
        moreValidaciones() {
            let vue = this
            let errors = 0

            // validar al menos una actividad
            if (vue.checklist.checklist_actividades.length === 0) {
                let msg = 'Debe registrar al menos una actividad'
                // vue.showAlert(msg, 'warning')
                errors++
            }
            let hasActividadEntrenadorUsuario = false;
            vue.checklist.checklist_actividades.map(actividad=>{
               if( actividad.type_name=='trainer_user') hasActividadEntrenadorUsuario=true;
            });
            if(!hasActividadEntrenadorUsuario){
                this.modalAlert.open= true;
               errors++
            }
            return errors > 0
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        eliminarActividad(actividad, index) {
            let vue = this;
            if (String(actividad.id).charAt(0).includes('n')) {
                vue.actividades_expanded = []
                vue.checklist.checklist_actividades.splice(index, 1);
                return
            }
            axios
                .post(`/entrenamiento/checklists/delete_actividad_by_id`, actividad)
                .then((res) => {
                    if (res.data.error) {
                        vue.$notification.warning(`${res.data.msg}`, {
                            timer: 6,
                            showLeftIcn: false,
                            showCloseIcn: true
                        });
                    } else {
                        vue.actividades_expanded = []
                        vue.checklist.checklist_actividades.splice(index, 1);
                        vue.$notification.success(`${res.data.msg}`, {
                            timer: 6,
                            showLeftIcn: false,
                            showCloseIcn: true
                        });
                    }
                })
                .catch((err) => {
                    console.err(err);
                });
        },
        addActividad() {
            let vue = this;
            const newID = `n-${vue.checklist.checklist_actividades.length + 1}`;
            const newActividad = {
                id: newID,
                activity: "",
                active: 1,
                type_name: "trainer_user",
                checklist_id: vue.checklist.id,
                hasErrors: false,
                is_default:false
            };
            vue.checklist.checklist_actividades.unshift(newActividad);
        },
        search() {
            clearTimeout(this.timeout);
            let vue = this;
            if (vue.search_text == null || vue.search_text === "") return;
            if (vue.isLoading) return;
            this.timeout = setTimeout(function () {
                vue.isLoading = true;
                const data = {
                    filtro: vue.search_text,
                };
                axios
                    .post(`/entrenamiento/checklists/buscar_curso`, data)
                    .then((res) => {
                        vue.results_search = res.data.data;
                        vue.$nextTick(() => {
                            vue.$refs.resultSearch.focus()
                            vue.$refs.resultSearch.isMenuActive = true
                            vue.$refs.resultSearch.isMenuActive = true;
                        })
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
        removeCurso(index) {
            let vue = this;
            vue.checklist.courses.splice(index, 1)

        }
    }
};
</script>

<style>
.list-cursos-carreras {
    width: 500px;
    white-space: unset;
}

.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}

.flip-list-move {
    transition: transform 0.5s;
}

.no-move {
    transition: transform 0s;
}

.txt-white-bold {
    color: white !important;
    font-weight: bold !important;
}

.v-input__icon {
    padding-bottom: 12px;
}

.v-icon.v-icon.v-icon--link {
    color: #1976d2;
}

.icon_size .v-icon.v-icon {
    font-size: 31px !important;
}

.lista-boticas {
    list-style-type: disc;
    -webkit-columns: 3;
    -moz-columns: 3;
    columns: 3;
    list-style-position: inside;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity .5s;
}

.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */
{
    opacity: 0;
}

.custom-draggable, .custom-draggable span {
    width: 100%;
}

.v-expansion-panel-header {
    padding: 0.7rem !important;
}

.box_info_checklist_1 {
    background-color: #f3f3f3;
    border-radius: 5px;
    padding: 10px;
    color: black;
    font-weight: 500;
    text-align: justify;
}
</style>
