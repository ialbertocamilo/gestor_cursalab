<template>
    <v-container>
        <v-card>
            <v-card-title>
                <v-btn color="blue-grey lighten-4" fab small @click="volverAtras()">
                    <v-icon>mdi-arrow-left</v-icon>
                </v-btn
                >
                <span class="ml-4 headline">{{ crear > 0 ? "Crear Usuario" : "Editar Usuario" }} </span>
                <!-- <v-spacer></v-spacer> -->
            </v-card-title>
            <v-form ref="form" autocomplete="off">
                <v-card-text class="pt-0">
                    <v-container>
                        <v-row v-if="!crear">
                            <v-col cols="12" md="1" lg="1"></v-col>
                            <v-col cols="12" md="10" lg="10">
                                <span class="float-right">Grupo de sistema: {{ usuario.grupo_sistema_nombre }}</span>
                            </v-col>
                            <v-col cols="12" md="1" lg="1"></v-col>
                        </v-row>
                        <v-row>
                            <!--  -->
                            <v-col cols="12" md="1" lg="1"></v-col>

                            <v-col cols="12" md="1" lg="1" class="vertical-align">
                                <label class="form-control-label texto-negrita" style="font-size: 1.15em !important"
                                >Módulo</label
                                >
                            </v-col>
                            <v-col cols="12" md="4" lg="4">
                                <v-select
                                    attach
                                    placeholder="Seleccione un módulo"
                                    outlined
                                    item-value="id"
                                    item-text="etapa"
                                    :items="modulos"
                                    v-model="modulo_select"
                                    @change="changeModulo()"
                                    :rules="rules.modulo"
                                    :disabled="!crear"
                                ></v-select>
                            </v-col>

                            <!-- 		<v-col cols="12" md="1" lg="1" class="vertical-align">
                                        <label class="form-control-label texto-negrita" style="font-size: 1.15em !important"
                                            >Grupo Sistema</label
                                        >
                                    </v-col>

                                    <v-col cols="12" md="4" lg="4">
                                        <v-select
                                            attach
                                            outlined
                                            readonly
                                            placeholder="Seleccione un Grupo de Sistema"
                                            item-value="id"
                                            item-text="nombre"
                                            :items="grupo_sistema"
                                            v-model="grupo_sistema_select"
                                            @change="changeGrupoSistema()"
                                            :rules="rules.grupo_sistema"
                                        ></v-select>
                                    </v-col> -->


                            <!--  -->

                            <!--  -->
                            <!-- <v-col cols="12" md="1" lg="1"> </v-col> -->
                            <!-- <v-col cols="12" md="1" lg="1"> </v-col> -->


                            <v-col cols="12" md="1" lg="1" class="vertical-align">
                                <label class="form-control-label texto-negrita" style="font-size: 1.15em !important"
                                >Botica</label
                                >
                            </v-col>
                            <v-col cols="12" md="4" lg="4">
                                <v-autocomplete
                                    attach
                                    outlined
                                    placeholder="Seleccione una botica"
                                    v-model="usuario.botica_id"
                                    :rules="rules.botica"
                                    item-value="botica_id"
                                    item-text="botica_nombre"
                                    :items="boticas_items"
                                    @change="cambiarCriterioNombre()"
                                    :disabled="!crear"
                                >
                                </v-autocomplete>
                                <!-- <v-text-field
                                    attach
                                    label="Botica"
                                    outlined
                                    v-model="usuario.botica"
                                    :rules="rules.botica"
                                    :placeholder="crear > 0 ? 'Ingrese la botica' : ''"
                                ></v-text-field> -->
                            </v-col>
                            <v-col cols="12" md="1" lg="1"></v-col>
                            <!--  -->
                            <!--  -->
                            <v-col cols="12" md="1" lg="1"></v-col>

                            <v-col cols="12" md="1" lg="1" class="vertical-align">
                                <label class="form-control-label texto-negrita" style="font-size: 1.15em !important"
                                >Nombres</label
                                >
                            </v-col>
                            <v-col cols="12" md="4" lg="4">
                                <v-text-field
                                    attach
                                    aria-placeholder="Ingrese los Nombres y Apellidos"
                                    outlined
                                    v-model="usuario.nombre"
                                    :rules="rules.nombre"
                                    :placeholder="crear > 0 ? 'Ingrese los nombre y apellidos' : ''"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" md="1" lg="1" class="vertical-align">
                                <label class="form-control-label texto-negrita" style="font-size: 1.15em !important"
                                >Documento de Identidad</label
                                >
                            </v-col>
                            <v-col cols="12" md="4" lg="4">
                                <v-text-field
                                    attach
                                    outlined
                                    v-model="usuario.dni"
                                    :rules="rules.dni"
                                    :placeholder="crear > 0 ? 'Ingrese el documento de identidad' : ''"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="1" lg="1"></v-col>
                            <!--  -->
                            <!--  -->
                            <v-col cols="12" md="1" lg="1"></v-col>

                            <v-col cols="12" md="1" lg="1" class="vertical-align">
                                <label class="form-control-label texto-negrita" style="font-size: 1.15em !important"
                                >Contraseña</label
                                >
                            </v-col>
                            <v-col cols="12" md="4" lg="4">
                                <v-text-field
                                    attach
                                    aria-placeholder="Ingrese la contraseña"
                                    outlined
                                    type="text"
                                    v-model="usuario.password"
                                    :placeholder="crear > 0 ? 'Ingrese la contraseña' : ''"
                                    :rules="crear > 0 ? rules.password : []"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="1" lg="1" class="vertical-align">
                                <label class="form-control-label texto-negrita" style="font-size: 1.15em !important"
                                >Género</label
                                >
                            </v-col>
                            <v-col cols="12" md="4" lg="4">
                                <v-select
                                    attach
                                    outlined
                                    placeholder="Seleccione un género"
                                    item-value="id"
                                    item-text="value"
                                    :items="genero_options"
                                    v-model="usuario.genero"
                                    :rules="rules.genero"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="1" lg="1"></v-col>

                            <!--  -->
                            <!--  -->
                            <v-col cols="12" md="1" lg="1"></v-col>
                            <v-col cols="12" md="1" lg="1" class="vertical-align">
                                <label class="form-control-label texto-negrita" style="font-size: 1.15em !important"
                                >Cargo</label
                                >
                            </v-col>
                            <v-col cols="12" md="4" lg="4">
                                <v-autocomplete
                                    :menu-props="{ top: true, offsetY: true }"
                                    attach
                                    outlined
                                    placeholder="Seleccione un cargo"
                                    v-model="usuario.cargo"
                                    :rules="rules.cargo"
                                    item-value="cargo_nombre"
                                    item-text="cargo_nombre"
                                    :items="cargos"
                                >
                                </v-autocomplete>
                                <!-- <v-text-field
                                    attach
                                    outlined
                                    v-model="usuario.cargo"
                                    :rules="rules.cargo"
                                    :placeholder="crear > 0 ? 'Ingrese el cargo' : ''"
                                ></v-text-field> -->
                            </v-col>

                            <v-col cols="12" md="1" lg="1"></v-col>

                            <v-col cols="12" md="4" lg="4">
                                <v-btn @click="dialog = true" color="#2c32e4" block class="color-white">
                                    Matrícula
                                </v-btn>
                            </v-col>

                            <!-- 					<v-col cols="12" md="1" lg="1" class="vertical-align">
                                                    <label class="form-control-label texto-negrita" style="font-size: 1.15em !important"
                                                        >Matrícula</label
                                                    >
                                                </v-col> -->
                            <v-col cols="12" md="1" lg="1"></v-col>
                            <v-col cols="12" md="1" lg="1"></v-col>

                            <v-col cols="12" md="1" lg="1" class="vertical-align">
                                <label class="form-control-label texto-negrita" style="font-size: 1.15em !important"
                                >Estado</label
                                >
                            </v-col>


                            <v-col cols="12" md="3" lg="3">
                                <v-switch
                                    v-model="usuario.estado"
                                    color="#2c32e4"
                                    :label="usuario.estado ? 'ACTIVO' : 'INACTIVO'"
                                ></v-switch>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-container class="text-center">
                        <v-btn
                            color="#2c32e4"
                            class="color-white"
                            @click="guardarUsuario()"
                            :loading="loading"
                            :disabled="loading"
                        >
                            Guardar
                        </v-btn>
                    </v-container>
                </v-card-actions>
            </v-form>
        </v-card>

        <v-dialog v-model="dialog" @click:outside="dialog=false" width="60%" >
            <v-card>
                <v-card-title>
                    Nuevas Matrículas
                    <v-spacer></v-spacer>
                    <v-btn icon @click="dialog = !dialog">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text>
                    <v-row v-if="crear > 0">
                        <p class="texto-descripcion ml-4 d-flex align-items-center">
                            <strong> Grupo: </strong>
                            <v-chip class="ma-2" color="#BBDEFB" label>
                                {{
                                usuario.criterio_nombre == null
                                ? "Seleccione un módulo y una botica"
                                : usuario.criterio_nombre
                                }}
                                <!-- {{ grupo_texto }} -->
                            </v-chip>
                        </p>
                        <!-- <v-select
                            label="Grupo"
                            :items="grupos"
                            item-text="valor"
                            item-value="id"
                            :disabled="editar > 0"
                            v-model="grupo_select"
                            @change="getCarrerasxGrupo()"
                        >
                        </v-select> -->
                        <v-col cols="12" sm="2" md="5" lg="5">
                            <strong> Carrera: </strong>
                            <v-select
                                attach
                                outlined
                                :placeholder="
									usuario.criterio_nombre === null || usuario.modulo === null
										? 'Seleccione un módulo y una botica'
										: 'Seleccione una carrera'
								"
                                disabledlabel="Grupo"
                                :items="carreras"
                                item-text="nombre"
                                item-value="id"
                                :disabled="editar > 0"
                                v-model="carrera_select"
                                @change="cargarCiclos()"
                                dense
                            >
                            </v-select>
                        </v-col>
                        <!-- <v-col cols="12" sm="2" md="2" lg="2" class="vertical-align">
                            <v-btn @click="cargarCiclos()">Agregar</v-btn>
                        </v-col> -->
                    </v-row>
                    <v-row v-if="editar > 0">
                        <p class="texto-descripcion ml-4 d-flex align-items-center">
                            <strong> Grupo: </strong>
                            <v-chip class="ma-2" color="#BBDEFB" label>
                                {{ usuario.criterio_nombre }}
                                <!-- {{ grupo_texto }} -->
                            </v-chip>
                        </p>

                        <p class="texto-descripcion d-flex align-items-center">
                            <strong> Carrera: </strong>
                            <v-chip class="ma-2" color="#BBDEFB" label>
                                {{ carrera_texto }}
                            </v-chip>
                        </p>
                    </v-row>
                    <v-row>
                        <v-col cols="12" md="12" lg="12">
                            <v-simple-table>
                                <template v-slot:default>
                                    <thead>
                                    <tr style="background: black !important">
                                        <th class="text-left color-white">Secuencia</th>
                                        <th class="text-left color-white">Grupo</th>
                                        <th class="text-left color-white">Carrera</th>
                                        <th class="text-left color-white">Ciclo</th>
                                        <th class="text-left color-white">Estado</th>
                                    </tr>
                                    </thead>
                                    <tbody v-if="ciclos.length == 0">
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <p class="text-h7 texto-negrita pt-4">Seleccione una carrera</p>
                                        </td>
                                    </tr>
                                    </tbody>
                                    <tbody v-else>
                                    <tr v-for="(item, index) in ciclos" :key="item.ciclo">
                                        <td>{{ item.secuencia }}</td>
                                        <td>{{ item.grupo }}</td>
                                        <td>{{ item.carrera }}</td>
                                        <td>{{ item.ciclo }}</td>
                                        <td>
                                            <v-switch
                                                v-model="item.estado"
                                                :label="item.estado ? 'ACTIVO' : 'INACTIVO'"
                                                @change="activar_desactivar(index, item.estado)"
                                            ></v-switch>
                                        </td>
                                    </tr>
                                    </tbody>
                                </template>
                            </v-simple-table>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions class="d-flex justify-center">
                    <v-btn class="color-white" color="#2c32e4" @click="dialog = !dialog">Ok</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>
<script>
export default {
    props: ["crear", "editar", "usuario_id", "url_host", "user_data"],
    data() {
        return {
            dialog: false,
            ciclos: [],
            modulo_select: 0,
            grupo_select: 0,
            carrera_select: 0,
            carrera_texto: 0,
            grupo_texto: 0,
            grupo_sistema_select: 0,
            grupos: [],
            grupos_x_carrera: [],
            carreras: [],
            modulos: [],
            grupo_sistema: [],
            boticas: [],
            boticas_items: [],
            cargos: [],
            loading: false,
            usuario: {
                id: 0,
                nombre: "",
                modulo: null,
                carrera_id: null,
                grupo_sistema: null,
                grupo_sistema_nombre: "",
                dni: "",
                password: "",
                genero: null,
                cargo: "",
                botica: "",
                estado: true,
                carrera_id: null,
                grupo_id: null,
                criterio_id: null,
                botica_id: null,
                criterio_nombre: null
            },
            alert: {
                error: false,
                msg: '',
                dni_duplicado: '',
            },
            rules: {
                modulo: [(v) => !!v || "El módulo es requerido"],
                genero: [(v) => !!v || "El género es requerido"],
                password: [(v) => !!v || "La contraseña es requerida"],
                grupo_sistema: [(v) => !!v || "El grupo de matrícula es requerido"],
                nombre: [
                    (v) => !!v || "El nombre es requerido",
                    (v) => /^[a-zA-ZáéíóúñÁÉÍÓÚÑ ]+$/i.test(v) || "No se permiten números"
                ],
                botica: [(v) => !!v || "El campo botica es requerido"],
                cargo: [(v) => !!v || "El campo cargo es requerido"],
                dni: [
                    (v) => !!v || "El DNI es requerido",
                    (v) => (v && v.length > 7) || "El DNI debe tener más de 8 dígitos",
                    (v) => /^[0-9]+$/i.test(v) || "No se permiten letras",
                    (v) => this.checkDuplicateDni(),
                ]
            },
            genero_options: [
                {id: 1, value: "MASCULINO"},
                {id: 2, value: "FEMENINO"}
            ],
            valid_form: true,

        };
    },
    async mounted() {
        let vue = this;
        await vue.getInitialData();
        await vue.setData();
    },
    methods: {
        setData() {
            let vue = this;
            if (vue.usuario_id != 0) {
                vue.usuario.id = vue.usuario_id;
                vue.usuario.nombre = vue.user_data.nombre;
                vue.modulo_select = vue.user_data.config_id;
                vue.usuario.dni = vue.user_data.dni;
                vue.usuario.botica = vue.user_data.botica;
                vue.usuario.cargo = vue.user_data.cargo;
                vue.usuario.genero = vue.user_data.sexo == "M" ? 1 : 2;
                vue.usuario.estado = vue.user_data.estado;
                vue.grupo_sistema_select = vue.user_data.grupo_id;
                vue.usuario.grupo_sistema_nombre = vue.user_data.grupo_sistema_nombre;
                vue.grupo_select = vue.user_data.grupo;
                vue.usuario.criterio_id = parseInt(vue.user_data.grupo);
                vue.usuario.botica_id = vue.user_data.botica_id;
                vue.usuario.criterio_nombre = vue.user_data.grupo_nombre;
                vue.boticas_items = vue.boticas.filter((botica) => botica.config_id == vue.modulo_select);
            }
        },
        async getInitialData() {
            let vue = this;
            await axios.get("/usuarios/getInitialData/" + vue.usuario_id).then((res) => {
                // console.log(res.data);
                // if (res.data.matriculas_pasadas.length > 0) {
                vue.ciclos = res.data.matriculas_pasadas;
                vue.carrera_texto = res.data.carrera_usuario;
                vue.grupo_texto = res.data.grupo_usuario;
                vue.grupos_x_carrera = res.data.grupos_x_carrera;
                // }
                vue.grupos = res.data.grupos;
                vue.modulos = res.data.modulos;
                vue.grupo_sistema = res.data.grupo_sistema;
                vue.boticas = res.data.boticas;
                vue.cargos = res.data.cargos;
            });
        },

        changeGrupoSistema() {
            let vue = this;
            vue.usuario.grupo_sistema = vue.grupo_sistema_select;
        },
        changeModulo() {
            let vue = this;
            vue.usuario.modulo = vue.modulo_select;
            vue.boticas_items = vue.boticas.filter((botica) => botica.config_id == vue.usuario.modulo);
        },
        async cambiarCriterioNombre() {
            let vue = this;
            vue.usuario.criterio_nombre = await vue.boticas.find(
                (botica) => botica.botica_id == vue.usuario.botica_id
            ).criterio.valor;
            vue.grupo_select = await vue.boticas.find(
                (botica) => botica.botica_id == vue.usuario.botica_id
            ).criterio_id;
            vue.getCarrerasxGrupo();
        },
        getCarrerasxGrupo() {
            let vue = this;
            axios
                .get("/usuarios/getCarrerasxGrupo/" + vue.grupo_select + "/" + vue.modulo_select)
                .then((res) => {
                    vue.carreras = res.data.carreras;
                });
        },
        cargarCiclos() {
            let vue = this;
            axios
                .get("/usuarios/getCiclosxCarrera/" + vue.carrera_select + "/" + vue.grupo_select)
                .then((res) => {
                    vue.ciclos = res.data.ciclo_final;
                    // console.log(res.data);
                });
        },
        activar_desactivar(index, estado) {
            let vue = this;
            // console.log(index, estado);
            if (estado) {
                vue.ciclos.forEach((ciclo, key) => {
                    if (index > 0 && ciclo.secuencia == 0) {
                        ciclo.estado = false;
                        return;
                    }
                    if (key <= index) {
                        ciclo.estado = true;
                    }
                    if (index == 0 && key != 0) {
                        ciclo.estado = false;
                    }

                });
            } else {
                vue.ciclos.forEach((ciclo, key) => {
                    if (key > index) {
                        ciclo.estado = false;
                    }
                });
            }
        },
        volverAtras() {
            window.location.href = "/usuarios/index";
        },
        async guardarUsuario() {
            let vue = this;
            vue.alert.error = false;
            vue.alert.msg = '';
            vue.loading = true;
            vue.usuario.modulo = vue.modulo_select;
            vue.usuario.grupo_sistema = vue.grupo_sistema_select;
            let validar = vue.$refs.form.validate();
            if (validar) {
                vue.valid_form = true;
                await axios
                    .post("/usuarios/crear", {
                        accion: vue.crear > 0 ? 1 : 0,
                        usuario: vue.usuario,
                        ciclos: vue.ciclos,
                        carrera_id: vue.carrera_select,
                        grupo: vue.grupo_select
                    })
                    .then((res) => {
                        vue.loading = false;
                        console.log(res);
                        setTimeout(() => {
                            window.location.href = `/usuarios/index?success=1&msg=${res.data.msg}`;
                        }, 1000);
                    }).catch((error) => {
                        vue.loading = false;
                        if (error.response.data.error) {
                            vue.alert.error = true;
                            vue.alert.msg = error.response.data.msg;
                            vue.alert.dni_duplicado = vue.usuario.dni;
                            vue.$refs.form.validate();
                        }
                        console.log(error.response.data);
                    });
            } else {
                vue.loading = false;
                vue.valid_form = false;
            }
        },
        checkDuplicateDni() {
            return (this.alert.error && this.usuario.dni == this.alert.dni_duplicado) ? this.alert.msg : true;
        }
    }
};
</script>

<style>
.v-application--wrap {
    min-height: 0;
}

.color-white {
    color: white !important;
    font-weight: bold !important;
}

.vertical-align {
    display: flex !important;
    align-items: center !important;
}

.theme--light.v-application {
    background: transparent !important;
}

.texto-negrita {
    font-weight: bold !important;
}

.texto-descripcion {
    font-size: 1.3em !important;
    /* font-weight: bold; */
}
</style>
