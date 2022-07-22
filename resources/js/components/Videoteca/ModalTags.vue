<template>
    <v-dialog class="default-dialog" max-width="600" scrollable v-model="modalData.open" @click:outside="onClose">
        <v-card>
            <v-card-title class="default-dialog-title">
                Tags
                <v-spacer/>
                <v-btn icon @click="onClose" color="white">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <!-- <v-divider/> -->

            <v-card-text style="max-height: 600px">
                <v-row>
                    <v-col cols="12">
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
                </v-row>
                <v-row>
                    <v-col col="12">
                        <table class="table table-hover">
                            <thead class="bg-dark--">
                                <th class="text-left">Nombre</th>
                                <th class="text-center">Acciones</th>
                            </thead>
                            <tbody>
                            <tr v-for="tag in tags" :key="tag.id">
                                <td>
                                    <v-edit-dialog
                                        :return-value.sync="tag.name"
                                        cancel-text="Cancelar"
                                        save-text="Guardar"
                                        large
                                        @save="editTag(tag)"
                                    >
                                        {{ tag.name }}
                                        <template v-slot:input>
                                            <v-text-field
                                                v-model="tag.name"
                                                label="Edit"
                                                single-line
                                                counter
                                            />
                                        </template>
                                    </v-edit-dialog>
                                </td>
                                <td class="text-center">
                                    <v-btn icon color="primary" @click="openDelete(tag)">
                                        <v-icon>mdi-trash-can</v-icon>
                                    </v-btn>
                                </td>
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
            ref="snackbarTag"
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
            creatTag: false,
            newTag: {
              name: null,
            },
            snackBar: {
                open: false,
                color: '',
                text: '',
            },
            tags: [],
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
        // saveTag(){
        //     let vue = this
        //     vue.$http.post(`/videoteca/tags`, vue.newTag)
        //         .then(({data}) => {
        //             vue.showSnackbar(vue.snackBar, true, data.data.msg)
        //         })
        // },
        editTag(tag) {
            let vue = this
            vue.$http.put(`/videoteca/tags/${tag.id}/update`, tag)
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
            let url = `/videoteca/tags/list`
            if (vue.filter) url += `?q=${vue.filter}`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.tags = data.tags.data
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
            let url = `/videoteca/tags/${vue.deleteItem.id}/delete`

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
