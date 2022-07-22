<template>
    <v-dialog class="default-dialog" max-width="600" scrollable v-model="modalData.open" @click:outside="onClose">
        <v-card>
            <v-card-title class="default-dialog-title">
                Categorías
                <v-spacer/>
                <v-btn icon @click="onClose" color="white">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>

            <v-card-text style="max-height: 600px">
                <v-row>
                    <v-col cols="8">
                        <v-text-field outlined dense hide-details
                                      label="Buscar por nombre"
                                      v-model="filter"
                                      @keyup.enter="getData"
                        >
                            <template v-slot:append>
                                <v-btn icon @click="getData">
                                    <v-icon>
                                        mdi-magnify
                                    </v-icon>

                                </v-btn>
                            </template>
                        </v-text-field>
                    </v-col>
                    <v-col cols="4">
                        <v-btn @click="crearCategoria = !crearCategoria">
                            <v-icon v-text="'mdi-plus'"/>
                            Categoria
                        </v-btn>
                    </v-col>
                </v-row>
                <v-expand-transition>
                    <v-row
                        v-show="crearCategoria"
                        style="border: 1px #f3f3f3 solid; border-radius: 15px"
                    >
                        <v-col cols="4">
                            <DefaultInput
                                :label="`Nombre`"
                                clearable
                                dense
                                v-model="newCategoria.name"
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultButton
                                :label="`Crear`"
                                @click="saveCategoria"
                            />
                        </v-col>
                    </v-row>
                </v-expand-transition>
                <v-row>
                    <v-col col="12">
                        <table class="table table-hover">
                            <thead class="bg-dark--">
                            <th class="text-left">Nombre</th>
<!--                            <th class="text-center">Acciones</th>-->
                            </thead>
                            <tbody>
                            <tr v-for="categoria in categorias" :key="categoria.id">
                                <td>
                                    <v-edit-dialog
                                        :return-value.sync="categoria.name"
                                        cancel-text="Cancelar"
                                        save-text="Guardar"
                                        large
                                        @save="editCategoria(categoria)"
                                    >
                                        {{ categoria.name }}
                                        <template v-slot:input>
                                            <v-text-field
                                                v-model="categoria.name"
                                                label="Edit"
                                                single-line
                                                counter
                                            />
                                        </template>
                                    </v-edit-dialog>
                                </td>
<!--                                <td class="text-center">-->
<!--                                    <v-btn icon color="primary" @click="openDelete(categoria)">-->
<!--                                        <v-icon>mdi-trash-can</v-icon>-->
<!--                                    </v-btn>-->
<!--                                </td>-->
                            </tr>
                            </tbody>
                        </table>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
        <v-dialog
            v-model="modalDelete.open"
            max-width="400"
        >
            <v-card>
                <v-card-title class="text-h5">
                    ¿Está seguro de eliminar este elemento?
                </v-card-title>
                <v-card-text v-text="'Esta acción no se puede revertir'"/>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="secondary"
                        @click="modalDelete.open = false"
                    >
                        Cancelar
                    </v-btn>
                    <v-btn
                        @click="deleteResource"
                    >
                        Eliminar
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-snackbar
            top
            centered
            v-model="snackBar.open"
            :timeout="3000"
            :color="snackBar.color"
        >
            {{ snackBar.text }}

            <template v-slot:action="{ attrs }">
                <v-btn
                    v-bind="attrs"
                    text
                    @click="snackBar.open = false"
                >
                    Cerrar
                </v-btn>
            </template>
        </v-snackbar>
    </v-dialog>
</template>


<script>
export default {
    props: {
        modalData: {
            type: Object,
            required: true,
        }
    },
    data() {
        return {
            crearCategoria: false,
            newCategoria: {
                name: null,
            },
            snackBar: {
                open: false,
                color: '',
                text: '',
            },
            categorias: [],
            filter: null,
            deleteItem: null,
            modalDelete: {
                open: false
            }
        }
    },
    mounted() {
        // let vue = this
        // vue.getData()
    },
    methods: {
        saveCategoria() {
            let vue = this
            vue.$http.post(`/videoteca/categorias`, vue.newCategoria)
                .then(({data}) => {
                    vue.showSnackbar(vue.snackBar, true, data.data.msg)
                    vue.getData()
                    vue.newCategoria.name = null
                    vue.crearCategoria = false
                })
        },
        editCategoria(tag) {
            let vue = this
            vue.$http.put(`/videoteca/categorias/${tag.id}/update`, tag)
                .then(({data}) => {
                    vue.showSnackbar(vue.snackBar, true, data.data.msg)
                })

        },
        onClose() {
            let vue = this
            vue.$emit(`onClose`)
        },
        getData() {
            let vue = this
            let url = `/videoteca/categorias/list`
            if (vue.filter) url += `?q=${vue.filter}`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.categorias = data.categorias_videoteca.data
                    // vue.tags.forEach(el => Object.assign(el, {edit: false}))
                })
        },
        openDelete(recurso) {
            let vue = this;
            vue.modalDelete.open = true
            vue.deleteItem = recurso
        },
        deleteResource() {
            let vue = this;
            let url = `/videoteca/categorias/${vue.deleteItem.id}/delete`

            vue.$http.delete(url)
                .then(() => {
                    vue.filter = null
                    vue.getData();
                })
            vue.modalDelete.open = false
            vue.deleteItem = null
        },
    }
}
</script>
