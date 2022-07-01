<template>
    <section class="section-list">
        <v-card flat elevation="0">
            <v-card-title>
                Crear Evento en vivo
            </v-card-title>
        </v-card>
        <!--        <DefaultDivider/>-->
        <br>

        <div class="d-block d-lg-flex justify-space-between">
            <v-card flat rounded class="side_form pa-4 mb-2" :class="{active: side_info}">
                <v-form ref="EventoEnVivoForm">
                    <v-row justify="center" align="start">
                        <v-col cols="6">
                            <DefaultInputDate
                                placeholder="Seleccione una fecha"
                                reference-component="EventoEnVivoInputDate1"
                                dense
                                show-required
                                :options="modalDateOptions"
                                label="Fecha del evento"
                                v-model="resource.fecha_inicio"
                                :rules="rules.fecha_inicio"
                            />
                        </v-col>
                        <v-col cols="6">
                            <DefaultInput
                                type="time"
                                dense
                                show-required
                                clearable
                                label="Hora del evento (24 hrs.)"
                                v-model="resource.hora_inicio"
                                :rules="rules.hora_inicio"
                            />
                        </v-col>
                    </v-row>
                    <v-row justify="center" align="start">
                        <v-col cols="6">
                            <DefaultInput
                                dense
                                clearable
                                show-required
                                label="Duración en min."
                                v-model="resource.duracion"
                                :rules="rules.duracion"
                                suffix="minutos"
                                placeholder="Ejemplo: 120"
                            />
<!--                            <DefaultSelect-->
<!--                                dense-->
<!--                                :items="[]"-->
<!--                                label="Duración en min."-->
<!--                                v-model="resource.duracion"-->
<!--                                :rules="rules.duracion"-->
<!--                            />-->
                        </v-col>
                        <v-col cols="6">
                            <DefaultInput
                                dense
                                show-required
                                label="Código del evento"
                                placeholder="Código de Vimeo"
                                v-model="resource.link_vimeo"
                                :rules="rules.link_vimeo"
                            />
                        </v-col>
                    </v-row>
                    <v-row justify="center" align="start">
                        <v-col cols="12">
                            <DefaultInput
                                dense
                                label="Título"
                                clearable
                                show-required
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
                                show-required
                            />
                        </v-col>
                    </v-row>

                    <v-row justify="center" align="start">
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

                <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)">
                    <DefaultModalActionButton
                        @cancel="closeModal"
                        @confirm="confirmModal"/>
                </v-card-actions>
            </v-card>

            <v-card flat rounded class="side_info" :class="{active: side_info}">

                <div class="btn_toggle_info pa-2 d-none d-lg-flex" @click="side_info = !side_info">
                    <v-icon color="white" class="icon_left" :hidden="side_info">mdi-menu-left</v-icon>
                    <v-icon color="white" class="icon_right" :hidden="!side_info">mdi-menu-right</v-icon>
                </div>

                <div class="px-2 py-6 ml-2">
                    <v-expansion-panels active-class="expansion_active" :value="0" dense :hidden="!side_info">
                        <v-expansion-panel
                        v-for="(item,i) in 1"
                        :key="i"
                        >
                            <div class="pa-2 primary d-block">
                                <v-icon color="white">mdi-information-outline</v-icon>
                            </div>
                            <v-expansion-panel-header>

                                <span class="primary--text font-weight-bold pa-1"> Importante:</span>
                                <template v-slot:actions>
                                    <v-icon color="primary">$expand</v-icon>
                                </template>
                            </v-expansion-panel-header>
                            <v-expansion-panel-content class="main-text--text">
                                Los Eventos en vivo se transmiten a través de la plataforma de Vimeo, por eso es importante crear y configurar el evento usando los mismos datos, primero en Vimeo y luego en este formulario.
                                <ul class="py-2">
                                    <li>La fecha y hora deben ser exactamente iguales</li>
                                    <li>El código del evento se inserta una vez creado el evento en Vimeo</li>
                                    <li>Se puede segmentar a nivel de módulos y carreras</li>
                                </ul>
                            </v-expansion-panel-content>
                        </v-expansion-panel>
                    </v-expansion-panels>
                </div>

            </v-card>
        </div>
    </section>
</template>
<script>
export default {
    props: {
        usuario: {
            required: true,
        }
    },
    data() {
        return {
            base_endpoint: '/aulas_virtuales',
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
                hora_inicio: this.getRules(['required', '24hr']),
                fecha_fin: this.getRules(['required']),
                duracion: this.getRules(['required', 'number']),
            },
            modules: [],
            side_info: true
        }
    },
    async mounted() {
        let vue = this

        // console.log(this.usuario.id)
        // this.showLoader()
        vue.showLoader()
        await this.loadData()
        // this.hideLoader()
        vue.hideLoader()
    },
    methods: {
        closeModal() {
            let vue = this
            // vue.$emit('onCancel')
            vue.openCRUDPage('/aulas_virtuales');
        },
        confirmModal() {
            let vue = this
            // this.showLoader()
            vue.showLoader()
            const validateForm = vue.validateForm('EventoEnVivoForm')
            const validateSelectedModules = vue.validateSelectedModules()
            if (validateForm && validateSelectedModules) {
                let url = `${vue.base_endpoint}/evento_en_vivo/store`
                let data = {
                    titulo: vue.resource.titulo,
                    descripcion: vue.resource.descripcion,
                    link_vimeo: vue.resource.link_vimeo,
                    fecha_inicio: vue.resource.fecha_inicio,
                    hora_inicio: vue.resource.hora_inicio,
                    duracion: vue.resource.duracion,
                    tipo_evento_id: vue.resource.tipo_evento_id,
                    estado_id: vue.resource.estado_id,
                    modules: vue.getSelectedModules(),
                    usuario_id: vue.usuario.id
                }
                vue.$http.post(url, data)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        // vue.$emit('onConfirm')
                        // this.hideLoader()
                        vue.hideLoader()
                        setTimeout(() => {
                            window.location.href = `${vue.base_endpoint}`
                        })
                    })
                // this.hideLoader()
                vue.hideLoader()
            } else {
                vue.hideLoader()
                // this.hideLoader()
            }
        },
        validateSelectedModules() {
            let vue = this
            return vue.modules.some(function (el) {
                return el.selected.length > 0;
            })
        },
        getSelectedModules() {
            let vue = this
            let temp = []
            vue.modules.forEach(function (el) {
                if (el.selected.length > 0)
                    temp.push({
                        id: el.id,
                        selected: el.selected
                    })
            })
            return temp
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
            let url = `${vue.base_endpoint}/evento_en_vivo/form-selects`
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

        }
    }
}
</script>
<style scoped>
.btn_toggle_info{
    background: #5458ea;
    position: absolute;
    height: 140px;
    left: -42px;
    top: 50%;
    transform: translateY(-50%);
    border-radius: 0!important;
}
.btn_toggle_info::before{
    content: "";
    position: absolute;
    top: -40px;
    border-bottom: 40px solid #5458ea;
    border-left: 40px solid transparent;
    left: 0;
}
.btn_toggle_info::after{
    content: "";
    position: absolute;
    bottom: -40px;
    border-top: 40px solid #5458ea;
    border-left: 40px solid transparent;
    left: 0;
}
.side_info.active .btn_toggle_info{
    background:#ABB5BE;
}
.side_info.active .btn_toggle_info::before{
    border-bottom: 40px solid #ABB5BE;
}
.side_info.active .btn_toggle_info::after{
    border-top: 40px solid #ABB5BE;
}

.expansion_active{
    border-bottom:1px solid #d6d6d6;
}

.side_form{
    width: 100%;
    transition: all 0.3s ease;
}

.side_info{
    width: 100%;
    transition: all 0.3s ease;
}

@media (min-width: 1264px) {
    .side_form{
        width: calc(100% - 80px);
    }
    .side_info{
        width: 0;
    }
    .side_form.active{
        width: 70%;
    }
    .side_info.active{
        width: calc(30% - 80px);
    }
}

</style>
