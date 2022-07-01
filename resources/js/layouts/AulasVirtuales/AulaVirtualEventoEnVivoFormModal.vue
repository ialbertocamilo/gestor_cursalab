<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="EventoEnVivoForm">
                <v-row justify="space-around" align="start">
                    <v-col cols="6">
                        <DefaultInputDate
                            :options="modalDateOptions"
                            label="Fecha del evento"
                            v-model="resource.fecha_inicio"
                            :rules="rules.fecha_inicio"
                        />
                    </v-col>
                    <v-col cols="6">
                        <DefaultInput
                            clearable
                            label="Hora del evento"
                            v-model="resource.hora_inicio"
                            :rules="rules.hora_inicio"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around" align="start">
                    <v-col cols="6">
                        <DefaultInput
                            clearable
                            label="Duración en min."
                            v-model="resource.duracion"
                            :rules="rules.duracion"
                        />
                    </v-col>
                    <v-col cols="6">
                        <DefaultInput
                            label="Código del evento"
                            v-model="resource.link_vimeo"
                            :rules="rules.link_vimeo"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around" align="start">
                    <v-col cols="12">
                        <DefaultInput
                            label="Título"
                            clearable
                            placeholder="Ingrese un título"
                            v-model="resource.titulo"
                            :rules="rules.titulo"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultTextArea
                            label="Descripción"
                            placeholder="Ingrese una descripción"
                            v-model="resource.descripcion"
                            :rules="rules.descripcion"
                        />
                    </v-col>
                </v-row>

                <v-row justify="start" align="start">
                    <v-col cols="12">
                        <DefaultModalSection
                            title="Segmentación"
                            tooltip="Tooltip"
                        >
                            <template slot="content">
                                <DefaultSimpleTable>
                                    <template slot="content">
                                        <thead>
                                        <tr>
                                            <th>Módulos</th>
                                            <th>Carreras</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr
                                            v-for="(item, index) in modules"
                                            :key="item.id"
                                        >
                                            <td class="w-25">{{ item.nombre }}</td>
                                            <td class="w-75">
                                                <DefaultAutocomplete
                                                    label="Carreras"
                                                    dense multiple
                                                    :items="item.carreras"
                                                    v-model="modules[index].selected"
                                                    :count-show-values="3"
                                                />
                                            </td>
                                        </tr>
                                        </tbody>
                                    </template>
                                </DefaultSimpleTable>
                            </template>
                        </DefaultModalSection>
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
            resourceDefault: {
                titulo: null,
                descripcion: null,
                link_vimeo: null,
                fecha_inicio: null,
                hora_inicio: null,
                duracion: null,
                tipo_evento_id: 2,
                estado_id: 2,
                modules: []
            },
            resource: {},
            modalDateOptions: {
                ref: 'DateEvent',
                open: false,
            },
            rules: {
                titulo: this.getRules(['required']),
                descripcion: this.getRules(['required']),
                link_vimeo: this.getRules(['required']),
                link_google_form: this.getRules(['required']),
                fecha_inicio: this.getRules(['required']),
                hora_inicio: this.getRules(['required', '24hour']),
                fecha_fin: this.getRules(['required']),
                duracion: this.getRules(['required', 'number']),
            },
            modules: []
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            this.showLoader()
            const validateForm = vue.validateForm('EventoEnVivoForm')

            if (validateForm) {
                let url = `${vue.options.base_endpoint}/evento_en_vivo/store`
                let data = {
                    titulo: vue.resource.titulo,
                    descripcion: vue.resource.descripcion,
                    link_vimeo: vue.resource.link_vimeo,
                    fecha_inicio: vue.resource.fecha_inicio,
                    hora_inicio: vue.resource.hora_inicio,
                    duracion: vue.resource.duracion,
                    tipo_evento_id: vue.resource.tipo_evento_id,
                    estado_id: vue.resource.estado_id,
                    modules: vue.modules,
                    usuario_id: vue.options.usuario_id
                }
                vue.$http.post(url, data)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()
                    })
                this.hideLoader()
            } else {
                this.hideLoader()
            }
        },
        resetValidation() {
            let vue = this
            vue.resetFormValidation('EventoEnVivoForm')
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.options.base_endpoint}/evento_en_vivo/form-selects`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.modules = data.data.modules
                    vue.resource.modules = data.data.modules
                    vue.resource.modules.forEach(el => {
                        Object.assign(el, {selected: []})
                    })

                })
            return 0;
        },
        loadSelects() {

        },
    }
}
</script>

<style lang="scss">
.aulas-virtuales-invitados-table {
    .v-data-table__wrapper {
        max-height: 35vh;
    }
}
</style>
