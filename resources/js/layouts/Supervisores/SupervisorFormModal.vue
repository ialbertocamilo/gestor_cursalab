<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="SupervisorForm">
                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable dense
                            :items="selects.modules"
                            v-model="filters.module"
                            label="Módulos"
                            @onChange="getAreas"
                        />
                    </v-col>
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable dense
                            :items="selects.areas"
                            v-model="filters.area"
                            label="Áreas"
                            :disabled="!filters.module"
                        />
                    </v-col>
                    <v-col cols="12" class="d-flex justify-content-center">
                        <v-autocomplete
                            attach
                            clearable
                            :search-input.sync="get_usuarios"
                            hide-details="auto"
                            :loading="isLoading"
                            dense
                            outlined
                            label="DNI o nombre del supervisor"
                            :items="selects.usuarios"
                            return-object
                            no-data-text="No hay usuarios encontrados"
                            :disabled="!filters.area"
                            v-model="dialog_supervisor"
                        >
                            <template v-slot:selection="{ item}">
                                <v-chip
                                    style="font-size: 0.9rem !important; color: white !important"
                                    color="primary"
                                    small
                                >
                                    {{ item.text }}
                                </v-chip>
                            </template>
                            <template v-slot:item="data">
                                <v-list-item-content @click="addUsuario(data)">
                                    <v-container
                                        class="px-0 align d-flex justify-content-start align-center"
                                        fluid
                                        style="min-height: 10px; max-height: 40px"
                                    >
                                        <v-btn
                                            icon
                                            @click="addUsuario(data)"
                                        >
                                            <v-icon>mdi-plus</v-icon>
                                        </v-btn>
                                        <div>
                                            <v-list-item-title
                                                v-html="data.item.text"/>
                                            <v-list-item-subtitle
                                                class="list-cursos-carreras"
                                                v-html="data.item.cargo"/>
                                        </div>
                                    </v-container>
                                </v-list-item-content>
                            </template>
                        </v-autocomplete>
                    </v-col>
                </v-row>
                <v-row>
                    <v-card-title>
                        Resultado de la selección
                    </v-card-title>
                    <v-col cols="12">
                        <v-simple-table
                            fixed-header
                            :height="filters.usuarios.length === 0 ? '100px' :
                                                filters.usuarios.length > 10 ? '480px' :
                                                (filters.usuarios.length*48 +48)">
                            <template v-slot:default>
                                <thead>
                                <tr>

                                    <th class="text-left" v-text="'Módulo'"/>
                                    <th class="text-left" v-text="'Área'"/>
                                    <th class="text-left" v-text="'Supervisor'"/>
                                    <th class="text-left" v-text="'Opción'"/>
                                    <!--                                            <th class="text-center" v-text="'Seleccionar'"/> -->
                                </tr>
                                </thead>
                                <tbody>
                                <tr
                                    v-if="filters.usuarios.length === 0"
                                >
                                    <td colspan="4" class="text-center">No hay datos para mostrar</td>
                                </tr>
                                <tr
                                    v-else
                                    v-for="usuario in filters.usuarios"
                                    :key="usuario.id"
                                >
                                    <td>{{ usuario.config }}</td>
                                    <td>{{ usuario.grupo || 'sin grupo' }}</td>
                                    <td>
                                        {{ usuario.text }} <br>
                                        ({{ usuario.cargo }})
                                    </td>
                                    <td class="text-center">
                                        <v-btn icon @click="deleteRelation(usuario.id)">
                                            <v-icon color="red">mdi-trash-can</v-icon>
                                        </v-btn>
                                    </td>
                                </tr>
                                </tbody>
                            </template>
                        </v-simple-table>
                    </v-col>
                </v-row>
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
        width: String
    },
    data() {
        return {
            resourceDefault: {},
            resource: {},
            selects: {
                modules: [],
                areas: [],
                usuarios: []
            },
            filters: {
                usuarios: [],
                list_usuarios: [],
                module: null,
                area: null
            },
            get_usuarios: null,
            isLoading: false,
            dialog_supervisor: null,
            rules: {}
        }
    },
    watch: {
        get_usuarios(val) {
            let vue = this;
            if (this.isLoading) return
            if (val === '' || val === ' ' || !val) return
            // console.log(val);
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                this.isLoading = true
                vue.usuarios = []
                let url = `${vue.options.base_endpoint}/get-usuarios`
                axios.post(url, {
                    confid_id: vue.filters.module,
                    q: val
                }).then(({data}) => {
                    // console.log(data)
                    vue.selects.usuarios = data.data.usuarios
                    // vue.filters.usuarios = data.usuarios_seleccionados
                }).catch(err => {
                    console.log(err)
                }).finally(() => (this.isLoading = false))

            }, 1200)
        },
    },
    methods: {
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.resetFormValidation('SupervisorForm')
        },
        async confirmModal() {
            let vue = this
            vue.showLoader()
            let url = `${vue.options.base_endpoint}/store-supervisor`
            const data = {
                usuarios: vue.filters.usuarios,
                criterio_id: vue.filters.area,
            }

            vue.$http.post(url, data)
                .then(({data}) => {
                    vue.closeModal()
                    vue.showAlert(data.data.msg)
                    vue.hideLoader()
                }).catch(err => {
                    vue.hideLoader()
            })
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.modules = []
            // Selects dependientes
            vue.selects.areas = []
            vue.selects.usuarios = []
            vue.filters.usuarios = []
            vue.filters.list_usuarios = []
            vue.filters.module = null
            vue.filters.area = null
        },
        async loadData(resource) {
            let vue = this

            return 0;
        },
        loadSelects() {
            let vue = this
            vue.loadModulos()
        },
        loadModulos() {
            let vue = this
            vue.listModulos()
                .then(data => {
                    vue.selects.modules = data
                })
        },
        getAreas() {
            let vue = this
            vue.filters.areas = []

            if (!vue.filters.module) {
                vue.selects.areas = []
                return
            }
            let url = `${vue.options.base_endpoint}/get-areas/${vue.filters.module}`

            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.areas = data.data
                })

        },
        addUsuario(data) {
            let idx = this.filters.usuarios.findIndex((e) => e.value === data.item.value);
            // console.log(data);
            if (idx > -1) {
                return false;
            }
            let insert = {
                value: data.item.value,
                config: data.item.config.etapa,
                grupo: data.item.criterio.valor,
                text: data.item.text,
                cargo: data.item.cargo
            };
            // console.log(insert);
            this.filters.usuarios.push(insert);
            this.filters.list_usuarios.push(insert);
        },
        deleteRelation(usuario_id) {
            let idx = this.filters.usuarios.findIndex((e) => e.value === usuario_id);
            (idx != null) && (this.filters.usuarios.splice(idx, 1));
            let idx2 = this.filters.list_usuarios.findIndex((e) => e.value === usuario_id);
            (idx2 != null) && (this.filters.list_usuarios.splice(idx2, 1));
        },
    }
}
</script>
