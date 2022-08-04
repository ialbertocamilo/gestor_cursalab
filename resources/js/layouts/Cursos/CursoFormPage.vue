<template>
    <section class="section-list">
        <v-card flat elevation="0">
            <v-card-title>
                Cursos: {{ curso_id ? 'Editar' : 'Crear' }}
            </v-card-title>
        </v-card>
        <!--        <DefaultDivider/>-->
        <br>
        <v-card flat elevation="0">
            <v-card-text>
                <v-form ref="CursoForm">
                    <v-row justify="center">
                        <v-col cols="6">
                            <DefaultInput
                                dense
                                label="Nombre"
                                placeholder="Ingrese un nombre"
                                v-model="resource.name"
                                :rules="rules.name"
                                show-required
                            />
                        </v-col>
                        <v-col cols="6">
                            <DefaultInput
                                dense
                                label="Orden"
                                placeholder="Orden"
                                v-model="resource.position"
                                :rules="rules.position"
                                show-required
                            />
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="12">
                            <DefaultInput
                                dense
                                label="Descripción"
                                placeholder="Ingrese una descripción"
                                v-model="resource.description"
                            />
                        </v-col>
                        <v-col cols="12">
                            <DefaultAutocomplete
                                dense
                                label="Requisito"
                                v-model="resource.requisito_id"
                                :items="selects.requisito_id"
                                custom-items
                                clearable
                            >
                                <template v-slot:customItems="{item}">
                                    <v-list-item-content>
                                        <v-list-item-title v-html="item.nombre"/>
                                        <v-list-item-subtitle class="list-cursos-carreras" v-html="item.carreras"/>
                                    </v-list-item-content>
                                </template>
                            </DefaultAutocomplete>
                        </v-col>
                    </v-row>
                    <v-row justify="center">
                        <v-col cols="6">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputLogo"
                                v-model="resource.imagen"
                                label="Imagen"
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'imagen')"/>
                        </v-col>
                        <v-col cols="6">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputDiploma"
                                v-model="resource.plantilla_diploma"
                                label="Plantilla de Diploma (Medida: 1743x1553 píxeles)  "
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'plantilla_diploma')"/>
                        </v-col>
                    </v-row>
                    <v-row justify="space-around">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Programación de reinicios"
                            >
                                <template slot="content">
                                    <v-row justify="center">
                                        <v-col cols="3" class="d-flex justify-content-center align-items-center">
                                            <DefaultToggle
                                                active-label="Automático"
                                                inactive-label="Manual"
                                                v-model="resource.reinicio_automatico"
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                            <DefaultInput
                                                label="Días"
                                                v-model="resource.reinicio_automatico_dias"
                                                :disabled="!resource.reinicio_automatico"
                                                type="number"
                                                dense
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                            <DefaultInput
                                                label="Horas"
                                                v-model="resource.reinicio_automatico_horas"
                                                :disabled="!resource.reinicio_automatico"
                                                type="number"
                                                dense
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                            <DefaultInput
                                                label="Minutos"
                                                v-model="resource.reinicio_automatico_minutos"
                                                :disabled="!resource.reinicio_automatico"
                                                type="number"
                                                dense
                                            />
                                        </v-col>
                                    </v-row>
                                    <div class="d-flex justify-content-center mt-1" v-if="showErrorReinicios">
                                        <div style="color: #FF5252" class="v-messages__wrapper">
                                            <div class="v-messages__message">Validar hora de reinicio</div>
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="2">
                            <DefaultToggle v-model="resource.active"/>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)">
                <DefaultModalActionButton
                    @cancel="closeModal"
                    @confirm="confirmModal"
                    :loading="loadingActionBtn"
                />
            </v-card-actions>
            <CursoValidacionesModal
                width="50vw"
                :ref="modalCursoValidaciones.ref"
                :options="modalCursoValidaciones"
                @onCancel="closeFormModal(modalCursoValidaciones)"
                @onConfirm="confirmModalValidaciones"
            />
        </v-card>
    </section>
</template>
<script>
const fields = ['name', 'reinicios_programado', 'active', 'position', 'imagen', 'plantilla_diploma', 'config_id', 'categoria_id',
    'description', 'requisito_id'];
const file_fields = ['imagen', 'plantilla_diploma'];
import CursoValidacionesModal from "./CursoValidacionesModal";

export default {
    components: {CursoValidacionesModal},
    props: ["modulo_id", 'categoria_id', 'curso_id'],
    data() {
        return {
            base_endpoint: `/escuelas/${this.categoria_id}/cursos`,
            resourceDefault: {
                name: null,
                description: null,
                position: null,
                imagen: null,
                plantilla_diploma: null,
                file_imagen: null,
                file_plantilla_diploma: null,
                config_id: this.modulo_id,
                categoria_id: this.categoria_id,
                active: false,
                requisito_id: null,
                reinicio_automatico: false,
                reinicio_automatico_dias: null,
                reinicio_automatico_horas: null,
                reinicio_automatico_minutos: 1,
            },
            resource: {},
            rules: {
                name: this.getRules(['required']),
                position: this.getRules(['required', 'number']),
            },
            selects: {
                requisito_id: []
            },
            loadingActionBtn: false,
            modalCursoValidaciones: {
                ref: 'CursoValidacionesModal',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'TemasValidaciones',
                persistent: false,
                showCloseIcon: true

            },
            modalCursoValidacionesDefault: {
                ref: 'CursoValidacionesModal',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'TemasValidaciones',
                persistent: false,
                showCloseIcon: true
            },
        }
    },
    computed: {
        showErrorReinicios() {
            let vue = this
            const reinicio = vue.resource.reinicio_automatico
            const dias = vue.resource.reinicio_automatico_dias
            const horas = vue.resource.reinicio_automatico_horas
            const minutos = vue.resource.reinicio_automatico_minutos
            if (!reinicio) {
                return false
            }
            if (dias > 0 || horas > 0 || minutos > 0) {
                return false
            }
            return true
        }
    },
    async mounted() {
        this.showLoader()
        await this.loadData()
        this.hideLoader()
    },
    methods: {
        closeModal() {
            let vue = this
            // window.history.back()
            window.location.href = vue.base_endpoint;
        },
        confirmModal() {
            let vue = this
            vue.loadingActionBtn = true
            this.showLoader()
            const validateForm = vue.validateForm('CursoForm')
            if (!validateForm) {
                this.hideLoader()
                vue.loadingActionBtn = false
                return
            }
            const edit = vue.curso_id !== ''
            let url = `${vue.base_endpoint}/${edit ? `update/${vue.curso_id}` : 'store'}`
            let method = edit ? 'PUT' : 'POST';

            const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
            vue.getJSONReinicioProgramado(formData)

            vue.$http.post(url, formData)
                .then(async ({data}) => {
                    const messages = (data.data.messages) ? data.data.messages : null;
                    this.hideLoader()
                    if (messages && messages.data.length > 0) {
                        // console.log(messages.data)
                        await vue.cleanModalCursoValidaciones()
                        vue.modalCursoValidaciones.hideCancelBtn = true
                        vue.modalCursoValidaciones.confirmLabel = 'Entendido'
                        vue.modalCursoValidaciones.persistent = true
                        vue.modalCursoValidaciones.showCloseIcon = false

                        await vue.openFormModal(vue.modalCursoValidaciones, messages, 'messagesActions', 'Aviso')
                    } else {
                        vue.showAlert(data.data.msg)
                        setTimeout(() => vue.closeModal(), 2000)
                    }
                })
                .catch(async ({data}) => {
                    // console.log('PAGE ERROR DATA ::', data)
                    await vue.cleanModalCursoValidaciones()
                    vue.loadingActionBtn = false
                    vue.modalCursoValidaciones.hideConfirmBtn = true
                    vue.modalCursoValidaciones.cancelLabel = 'Entendido'
                    await vue.openFormModal(vue.modalCursoValidaciones, data.validate, data.validate.type, data.validate.title)
                })
        },
        confirmModalValidaciones(data){
            let vue = this
            if (data.confirmMethod === 'messagesActions')
                vue.closeModal()
        },
        getJSONReinicioProgramado(formData) {
            let vue = this
            const minutes = parseInt(vue.resource.reinicio_automatico_minutos) +
                (parseInt(vue.resource.reinicio_automatico_horas) * 60) +
                (parseInt(vue.resource.reinicio_automatico_dias) * 1440)
            const data = {
                activado: vue.resource.reinicio_automatico,
                tiempo_en_minutos: minutes,
                reinicio_dias: vue.resource.reinicio_automatico_dias,
                reinicio_horas: vue.resource.reinicio_automatico_horas,
                reinicio_minutos: vue.resource.reinicio_automatico_minutos,
            }
            let json = JSON.stringify(data)
            formData.append('reinicios_programado', json)
        },
        async loadData() {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.base_endpoint}/${vue.curso_id === '' ? 'form-selects' : `search/${vue.curso_id}`}`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.requisito_id = data.data.requisitos
                    if (vue.curso_id !== '') {
                        vue.resource = Object.assign({}, data.data.curso)
                    }
                })
            return 0;
        },
        async cleanModalCursoValidaciones() {
            let vue = this
            await vue.$nextTick(() => {
                vue.modalCursoValidaciones = Object.assign({}, vue.modalCursoValidaciones, vue.modalCursoValidacionesDefault)
            })
        },
    }
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
