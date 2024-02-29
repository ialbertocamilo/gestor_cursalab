<template>
    <div class="modalAsignacionXDni">
        <v-row>
            <v-col cols="12">
                <p class="text-h7 mb-0 text_default" v-text="description"></p>
            </v-col>
            <v-col class="d-flex justify-content-between" cols="12" md="6" lg="6">
                <DefaultInput
                    clearable dense
                    v-model="search"
                    placeholder="Buscar"
                    append-icon="mdi-magnify"
                    :loading="autocomplete_loading"
                    class="col-11"
                />
                <v-file-input
                    v-show="showSubidaMasiva"
                    show-size
                    label="Suba el archivo"
                    v-model="file"
                    color="#796aee"
                    hide-details="auto"
                    dense
                    outlined
                    hide-input
                    prepend-icon="mdi-file-upload"
                    @change="subirExcel()"
                    class="justify-end"
                >
                    <template v-slot:append-outer>
                    </template>
                </v-file-input
                >
            </v-col>
            <v-col class="pr-0 col-md-6 d-flex justify-end">
                <a v-show="showSubidaMasiva" class="pt-2" href="/templates/Plantilla-Segmentacion.xlsx"
                >Descargar plantilla</a
                >
            </v-col>
        </v-row>
        <v-row style="border:1px solid #DADAED">
            <v-col cols="12" md="6" lg="6">
                <div class="title-list">
                    Resultados de búsquedas ({{ filtro_result.length }})
                </div>
                <div class="box-result">
                    <ul class="ul_result-users">
                        <li
                            class="li-users_ok_error vertical-align"
                            v-for="(result, i) in filtro_result"
                            :key="i"
                            style="justify-content: space-between"
                        >
                            <div>
                                <span v-text="result.fullname" class="col-md-10"></span>
                                <v-btn color="#796aee" icon class="mr-1 col-md-2" @click="agregarUsuario(result, i)">
                                    <v-icon v-text="'mdi-plus'" color="#5458EA"></v-icon>
                                </v-btn>
                            </div>
                        </li>
                    </ul>
                </div>
            </v-col>
            <v-col cols="12" md="6" lg="6">
                <div class="title-list">
                    <DefaultInput
                        clearable dense
                        v-model="input_filtro_usuarios_ok"
                        placeholder="Usuarios"
                        append-icon="mdi-magnify"
                        :loading="loading_filtros_usuarios_ok"
                    />
                </div>
                <div class="box-usuarios_ok">
                    <ul class="ul-users">
                        <li
                            class="li-users_ok_error vertical-align"
                            v-for="usuario in search_usuarios_ok"
                            :key="usuario.dni"
                            style="justify-content: space-between"
                        >
                            <div>
                                <span v-text="usuario.fullname" class="col-md-10"></span>
                                <v-btn icon class="mr-1" @click="eliminarUsuario(usuario.dni)">
                                    <v-icon v-text="'mdi-minus'" color="#5458EA"></v-icon>
                                </v-btn>
                            </div>
                        </li>
                    </ul>
                </div>
            </v-col>

            <v-col cols="12" md="1" lg="1"></v-col>
        </v-row>
        <v-row>
            <v-col cols="12" md="6" lg="6" v-if="Object.entries(usuarios_error).length > 0">
                <div style="display: flex; align-items: center" class="my-1">
                    <p class="text-h7">Usuarios no encontrados dentro del módulo</p>
                </div>
                <v-expand-transition>
                    <div class="box-result">
                        <ul class="ul-users-error">
                            <li
                                class="li-users_ok_error vertical-align"
                                v-for="(usuario, index) in usuarios_error"
                                :key="`us_error-${index}`"
                            >
                                {{ usuario }}
                            </li>
                        </ul>
                    </div>
                </v-expand-transition>
            </v-col>
        </v-row>
    </div>
</template>
<script>
export default {
    props: {
        description: {
            type: String,
            required: true
        },
        apiSearchUser: {
            type: String,
            required: true
        },
        apiUploadPlantilla: {
            type: String,
            required: true
        },
        showSubidaMasiva: {
            type: Boolean,
            required: true
        },
        load_data_default: {
            type: Boolean,
            default: false
        },
        list_users_selected: {
            type: Array,
            default: []
        }
    },
    data() {
        return {
            autocomplete_loading: false,
            file: null,
            input_filtro_usuarios_ok: "",
            loading_filtros_usuarios_ok: false,
            usuarios_ok: [],
            usuarios_error: [],
            dialog_guardar: false,
            search: null,
            debounce: null,
            filtro_result: [],
        }
    },
    computed: {
        search_usuarios_ok() {
            let vue = this;
            if (vue.input_filtro_usuarios_ok === null) {
                return vue.usuarios_ok;
            }
            return vue.usuarios_ok.filter((usuario) => {
                return (
                    usuario.fullname.toLowerCase().indexOf(vue.input_filtro_usuarios_ok.toLowerCase()) !== -1
                );
            });
        },
    },
    watch: {
        search(filtro) {
            let vue = this;
            if (filtro === null && !vue.load_data_default) {
                return;
            }
            vue.autocomplete_loading = true;
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                let data = {
                    filtro,
                };
                vue.$http
                    .post(vue.apiSearchUser, data)
                    .then(({data}) => {
                        vue.filtro_result = data.data.users;
                        vue.autocomplete_loading = false;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            }, 1600);
        },
    },
    mounted: function () {
        this.$nextTick(function () {
            let vue = this;
            vue.usuarios_ok = vue.list_users_selected
        })
    },
    methods: {
        agregarUsuario(usuario, index) {
            let vue = this;
            let validar = vue.usuarios_ok.filter((usu) => usu.document === usuario.document);

            if (validar.length === 0) vue.usuarios_ok.push(usuario);

            vue.filtro_result.splice(index, 1);
        },
        eliminarUsuario(dni) {
            let vue = this;
            if (vue.search_usuarios_ok.length == 1) {
                vue.input_filtro_usuarios_ok = "";
            }
            let index = vue.usuarios_ok.findIndex((us) => us.dni == dni);
            vue.usuarios_ok.splice(index, 1);
        },
        subirExcel() {
            let vue = this;
            let data = new FormData();
            data.append("archivo", vue.file);
            vue.showLoader();
            axios.post(vue.apiUploadPlantilla, data)
                .then((res) => {
                    for (const document in res.data.info.ok) {
                        if (Object.hasOwnProperty.call(res.data.info.ok, document)) {
                            const validar = vue.usuarios_ok.filter((usuario) => usuario.document == document);
                            if (validar.length === 0) {
                                vue.usuarios_ok.push({document, fullname: res.data.info.ok[document]});
                            }
                        }
                    }
                    for (const document in res.data.info.error) {
                        if (Object.hasOwnProperty.call(res.data.info.ok, document)) {
                            const validar = vue.usuarios_error.filter((usuario_document) => usuario_document == document);
                            if (validar.length === 0) {
                                vue.usuarios_error.push(document);
                            }
                        }
                    }

                    vue.usuarios_error = res.data.info.error;
                    vue.file = null;
                    vue.search_usuarios_ok();
                    vue.hideLoader();
                })
                .catch((err) => {
                    vue.hideLoader();
                    console.log(err);
                });
        },
    }
}
</script>
<style lang="scss">

.modalAsignacionXDni {

    .custom-default-input {
        border-radius: 6px !important;
        border: 1px solid #DADAED !important;
    }

    .v-input__control {
        box-shadow: 0px 4px 4px rgba(165, 166, 246, 0.25);
    }

    .v-input__slot {
        background: white !important;
    }

    .v-text-field__slot > label {
        color: rgba(51, 61, 93, 0.5) !important;
    }

    .v-icon.v-icon.v-icon--link {
        color: #796aee !important;
    }

    .li-users_ok_error {
        list-style-type: none;
    }

    .ul-users {
        list-style: none;
        height: 300px;
        overflow-x: hidden;
        overflow-y: scroll;
    }

    .ul-users-error {
        list-style: none;
        max-height: 100px;
        overflow-x: hidden;
        overflow-y: scroll;
    }

    .ul_result-users {
        list-style: none;
        height: 300px;
        overflow-x: hidden;
        overflow-y: scroll;
        width: 100%;
        padding: 0 !important;
    }

    .box-result {
        padding: 10px 5px 3px 0;
        border: 1.8px solid #EDF1F4;
    }

    .box-usuarios_ok {
        border-style: solid;
        padding: 10px 5px 3px 0;
        border-color: #5458EA;
        border-width: 0px 1.8px 1.8px 1.8px;
    }

    .title-list {
        padding: 0px 20px;
        min-height: 60px !important;
        display: flex;
        align-items: center;
        background: #EDF1F4;
        background-repeat: repeat;
        border-radius: 3px 3px 0px 0px;
        color: #333D5D;
    }
}
</style>
