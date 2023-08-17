<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="RoleForm">
                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.title"
                            :rules="rules.title"
                            label="Nombre"
                            dense
                            show-required
                            placeholder="Ingrese un nombre"
                        />
                    </v-col>
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            label="Código"
                            dense
                            show-required
                            placeholder="Ingrese un código"
                            :rules="rules.name"
                        />
                    </v-col>
                </v-row>

                <!-- <v-row justify="space-around">
                    <template v-if="!selection.length">
                          No nodes selected.
                        </template>
                        <template v-else>
                          <div
                            v-for="node in selection"
                            :key="'node-' + node"
                          >
                            {{ node }}
                          </div>
                        </template>
                </v-row> -->
                <v-row justify="space-around">
                    <v-col cols="12">

                        <v-container
                            id="scroll-target"
                            class="overflow-y-auto py-0 px-1"
                            style="max-height: 500px"
                        >

                                    <!--  open ? 'mdi-folder-open' : 'mdi-folder' -->
                            <v-treeview
                              v-model="selection"
                              :items="items"
                              :selection-type="selectionType"
                              selectable
                              selected-color="primary"
                            >
                                <template v-slot:prepend="{ item, open }">
                                  <v-icon>
                                    {{ item.icon }}
                                  </v-icon>
                                </template>
                            </v-treeview>
                              <!-- open-all -->

                        </v-container>


                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="2">
                        <DefaultToggle v-model="resource.active"/>
                    </v-col>
                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>

const fields = ['name', 'title', 'active', 'permissions'];
// const file_fields = ['logo', 'plantilla_diploma'];
export default {
    // components: {DefaultRichText, draggable},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            resourceDefault: {
                id: null,
                name: null,
                title: null,
                active: false,

            },
            rules: {
                title: this.getRules(['required']),
                name: this.getRules(['required']),
            },
            resource: {},

            selectionType: 'leaf',
            selection: [],
            items: [],
            // items: [
            //     {
            //       id: 1,
            //       name: 'Usuarios',
            //       children: [
            //         { id: 10, name: 'Listar y filtrar usuarios' },
            //         { id: 2, name: 'Crear usuarios' },
            //         { id: 3, name: 'Editar usuarios' },
            //         { id: 4, name: 'Eliminar usuarios' },
            //         { id: 11, name: 'Activar usuarios' },
            //         { id: 7, name: 'Ver log de usuarios' },
            //         {
            //           id: 5,
            //           name: 'Otras opciones',
            //           children: [
            //             { id: 6, name: 'Ver cursos de usuarios' },
            //             { id: 8, name: 'Restaurar contraseña de usuarios' },
            //             { id: 9, name: 'Personificar usuarios' },
            //           ],
            //         },
            //       ],
            //     },
            //     {
            //       id: 20,
            //       name: 'Módulos',
            //       children: [
            //         { id: 21, name: 'Listar y filtrar módulos' },
            //         { id: 22, name: 'Crear módulos' },
            //         { id: 23, name: 'Editar módulos' },
            //         { id: 24, name: 'Eliminar módulos' },
            //         { id: 28, name: 'Ver log de módulos' },
            //         // { id: 29, name: 'Activar módulos' },
            //       ],
            //     },
            //     {
            //       id: 30,
            //       name: 'Criterios',
            //       children: [
            //         { id: 31, name: 'Listar y filtrar criterios' },
            //         { id: 32, name: 'Crear criterios' },
            //         { id: 33, name: 'Editar criterios' },
            //         { id: 34, name: 'Eliminar criterios' },
            //         { id: 35, name: 'Ver log de criterios' },
            //         {
            //           id: 36,
            //           name: 'Valores de criterio',
            //           children: [
            //             { id: 39, name: 'Listar y filtrar valores' },
            //             { id: 40, name: 'Crear valores' },
            //             { id: 41, name: 'Editar valores' },
            //             { id: 42, name: 'Eliminar valores' },
            //             { id: 43, name: 'Ver log de valores' },
            //           ],
            //         },
                    
            //       ],
            //     },
            //     {
            //       id: 50,
            //       name: 'Diplomas',
            //       children: [
            //         { id: 51, name: 'Listar y filtrar diplomas' },
            //         { id: 52, name: 'Crear diplomas' },
            //         { id: 53, name: 'Editar diplomas' },
            //         { id: 54, name: 'Eliminar diplomas' },
            //         { id: 58, name: 'Ver log de diplomas' },
            //       ],
            //     },
            //     {
            //       id: 60,
            //       name: 'Anuncios',
            //       children: [
            //         { id: 61, name: 'Listar y filtrar anuncios' },
            //         { id: 62, name: 'Crear anuncios' },
            //         { id: 63, name: 'Editar anuncios' },
            //         { id: 69, name: 'Activar anuncios' },
            //         { id: 64, name: 'Eliminar anuncios' },
            //         { id: 68, name: 'Ver log de anuncios' },
            //       ],
            //     },
            //     {
            //       id: 70,
            //       name: 'Escuelas',
            //       children: [
            //         { id: 71, name: 'Listar y filtrar escuelas' },
            //         { id: 72, name: 'Crear escuelas' },
            //         { id: 73, name: 'Editar escuelas' },
            //         { id: 79, name: 'Activar escuelas' },
            //         { id: 74, name: 'Eliminar escuelas' },
            //         { id: 78, name: 'Ver log de escuelas' },
            //       ],
            //     },
            //     {
            //       id: 90,
            //       name: 'Cursos',
            //       children: [
            //         { id: 91, name: 'Listar y filtrar cursos' },
            //         { id: 92, name: 'Crear cursos' },
            //         { id: 93, name: 'Editar cursos' },
            //         { id: 99, name: 'Activar cursos' },
            //         { id: 94, name: 'Eliminar cursos' },
            //         { id: 98, name: 'Ver log de cursos' },
            //         {
            //           id: 995,
            //           name: 'Otras opciones',
            //           children: [
            //             { id: 989, name: 'Duplicar cursos' },
            //             { id: 999, name: 'Asignar encuestas' },
            //             { id: 996, name: 'Segmentar cursos' },
            //             { id: 998, name: 'Establecer compatibilidades' },
            //           ],
            //         },
            //         {
            //           id: 80,
            //           name: 'Temas',
            //           children: [
            //             { id: 81, name: 'Listar y filtrar temas' },
            //             { id: 82, name: 'Crear temas' },
            //             { id: 83, name: 'Editar temas' },
            //             { id: 89, name: 'Activar temas' },
            //             { id: 84, name: 'Eliminar temas' },
            //             { id: 88, name: 'Ver log de temas' },

            //             {
            //               id: 880,
            //               name: 'Evaluaciones',
            //               children: [
            //                 { id: 881, name: 'Listar y filtrar preguntas' },
            //                 { id: 882, name: 'Crear preguntas' },
            //                 { id: 883, name: 'Editar preguntas' },
            //                 // { id: 889, name: 'Activar preguntas' },
            //                 { id: 884, name: 'Eliminar preguntas' },
            //                 { id: 888, name: 'Ver log de preguntas' },
            //               ],
            //             },
            //           ],
            //         },
            //       ],
            //     },
            // ],

        }
    },

    methods: {
        closeModal() {
            let vue = this
            vue.resetSelects()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.resetFormValidation('RoleForm')
        },
        resetSelects() {
            let vue = this

            vue.resource = Object.assign({}, {})
        },
        confirmModal() {
            let vue = this
            this.showLoader()
            const validateForm = vue.validateForm('RoleForm')

            if (validateForm) {
                const edit = vue.options.action === 'edit'
                let url = `${vue.options.base_endpoint}/${edit ? `${vue.resource.id}/update` : 'store'}`
                let method = edit ? 'PUT' : 'POST';

                vue.resource.permissions = vue.selection

                const formData = vue.getMultipartFormData(method, vue.resource, fields);
       
                vue.$http.post(url, formData)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()
                    })
            } else {
                this.hideLoader()
            }
        },
      

        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            vue.selection = []
            let url = `${vue.options.base_endpoint}/${resource ? `${resource.id}/edit` : `form-selects`}`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.items = data.data.permissions

                    if (data.data.permissions_ticked) {
                        vue.selection = data.data.permissions_ticked
                    }
                    // vue.selects.side_menu = data.data.side_menu
                    if (resource) {
                        vue.resource = Object.assign({}, data.data.role)
                    }
                })
            return 0;
        },
        loadSelects() {
            let vue = this

        },

    },
}
</script>
<style lang="scss">
@import "resources/sass/variables";

.date_reinicios_disabled {
    pointer-events: none;
    padding: 10px 0;
    border-radius: 9px;
    opacity: 0.3;
    background: #CCC;
}

.date_reinicios_error {
    padding: 10px 0;
    border: #FF5252 2px solid;
    border-radius: 5px;
}

.date_reinicios_error_message {
    line-height: 12px;
    word-break: break-word;
    overflow-wrap: break-word;
    word-wrap: break-word;
    -webkit-hyphens: auto;
    -ms-hyphens: auto;
    hyphens: auto;
    font-weight: 400;
    color: #FF5252;
    caret-color: #FF5252;
}

.box_date_reinicios {
    background: $primary-default-color;
    padding: 2px 9px 9px 9px;
    border-radius: 5px;

    label {
        color: white;
    }

    .input_date_reinicios {
        appearance: textfield;
        -moz-appearance: textfield;
        text-align: center;
        background: white;
        width: 50px;
        height: 30px;
    }
}


</style>
