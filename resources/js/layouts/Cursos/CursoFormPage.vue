<template>
    <section class="section-list">
        <v-card flat elevation="0">
            <v-card-title>
                Cursos: {{ curso_id ? 'Editar' : 'Crear' }}
            </v-card-title>
        </v-card>
        <br>
        <v-card flat elevation="0">
            <v-card-text>
                <v-form ref="CursoForm">
                    <DefaultErrors :errors="errors"/>

                    <v-row>
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
                            <DefaultAutocomplete
                                show-required
                                :rules="rules.lista_escuelas"
                                dense
                                label="Escuelas"
                                v-model="resource.lista_escuelas"
                                :items="selects.lista_escuelas"
                                item-text="name"
                                item-value="id"
                                multiple
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
                                item-text="name"
                                item-value="id"
                                clearable
                            >
                                <template v-slot:customItems="{item}">
                                    <v-list-item-content>
                                        <v-list-item-title v-html="item.name"/>
                                        <v-list-item-subtitle class="list-cursos-carreras" v-html="item.escuelas"/>
                                    </v-list-item-content>
                                </template>
                            </DefaultAutocomplete>
                        </v-col>
                    </v-row>
                    <v-row justify="center">
                        <v-col cols="6">
                            <DefaultInput
                                numbersOnly
                                dense
                                label="Duración (en horas)"
                                placeholder="Ingrese la duración del curso"
                                v-model="resource.duration"
                            />
                        </v-col>
                        <v-col cols="6">
                            <DefaultInput
                                numbersOnly
                                dense
                                label="Inversión (en soles)"
                                placeholder="Ingrese la inversión"
                                v-model="resource.investment"
                            />
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
                                                v-model="resource.scheduled_restarts_activado"
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                            <DefaultInput
                                                label="Días"
                                                v-model="resource.scheduled_restarts_dias"
                                                :disabled="!resource.scheduled_restarts_activado"
                                                type="number"
                                                dense
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                            <DefaultInput
                                                label="Horas"
                                                v-model="resource.scheduled_restarts_horas"
                                                :disabled="!resource.scheduled_restarts_activado"
                                                type="number"
                                                dense
                                            />
                                        </v-col>
                                        <v-col cols="3">
                                            <DefaultInput
                                                label="Minutos"
                                                v-model="resource.scheduled_restarts_minutos"
                                                :disabled="!resource.scheduled_restarts_activado"
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
                :ref="courseValidationModal.ref"
                :options="courseValidationModal"
                :resource="resource"
                @onCancel="closeFormModal(courseValidationModal)"
                @onConfirm="confirmValidationModal(courseValidationModal, base_endpoint, confirmModal(false))"
            />
        </v-card>
    </section>
</template>
<script>
const fields = [
    'name', 'reinicios_programado', 'active', 'position', 'imagen',
    'plantilla_diploma', 'config_id', 'categoria_id',
    'description', 'requisito_id', 'lista_escuelas',
    'duration', 'investment'
];
const file_fields = ['imagen', 'plantilla_diploma'];
import CursoValidacionesModal from "./CursoValidacionesModal";

export default {
    components: {CursoValidacionesModal},
    props: ["modulo_id", 'categoria_id', 'curso_id'],
    data() {
        let route_school = (this.categoria_id !== '')
                            ? `/escuelas/${this.categoria_id}`
                            : ``;

        return {
            errors: [],
            base_endpoint: `${route_school}/cursos`,
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
                active: true,
                requisito_id: null,
                duration: null,
                investment: null,
                scheduled_restarts_activado: false,
                scheduled_restarts_dias: null,
                scheduled_restarts_horas: null,
                scheduled_restarts_minutos: 1,
                lista_escuelas: [],
            },
            resource: {},
            rules: {
                name: this.getRules(['required']),
                lista_escuelas: this.getRules(['required']),
                position: this.getRules(['required', 'number']),
            },
            selects: {
                requisito_id: [],
                lista_escuelas: [],
            },
            loadingActionBtn: false,
            courseValidationModal: {
                ref: 'CursoValidacionesModal',
                open: false,
            },
            courseValidationModalDefault: {
                ref: 'CursoValidacionesModal',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'CursosValidaciones',
                persistent: false,
                showCloseIcon: true,
                type: null
            },
        }
    },
    computed: {
        showErrorReinicios() {
            let vue = this
            const reinicio = vue.resource.scheduled_restarts
            const dias = vue.resource.scheduled_restarts_dias
            const horas = vue.resource.scheduled_restarts_horas
            const minutos = vue.resource.scheduled_restarts_minutos
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
            window.location.href = vue.base_endpoint;
        },
        confirmModal(validateForm = true) {
            let vue = this
            vue.errors = []
            vue.loadingActionBtn = true
            vue.showLoader()
            const validForm = vue.validateForm('CursoForm')
            if (!validForm) {
                this.hideLoader()
                vue.loadingActionBtn = false
                return
            }

            if (vue.courseValidationModal.action === 'validations-after-update') {
                vue.hideLoader();
                vue.courseValidationModal.open = false;
                setTimeout(() => vue.closeModal(), 10000);
                return;
            }

            const edit = vue.curso_id !== ''
            let url = `${vue.base_endpoint}/${edit ? `update/${vue.curso_id}` : 'store'}`
            let method = edit ? 'PUT' : 'POST';

            const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
            formData.append('validateForm', validateForm ? "1": "0");
            vue.setJSONReinicioProgramado(formData)

            vue.$http.post(url, formData)
                .then(async ({data}) => {
                    this.hideLoader()
                    const has_info_messages = data.data.messages.list.length > 0
                    if (has_info_messages)
                        await vue.handleValidationsAfterUpdate(data.data, vue.courseValidationModal, vue.courseValidationModalDefault);
                    else {
                        vue.showAlert(data.data.msg)
                        setTimeout(() => vue.closeModal(), 2000)
                    }
                })
                .catch(error => {
                    if (error && error.errors)
                        vue.errors = error.errors

                    vue.handleValidationsBeforeUpdate(error, vue.courseValidationModal, vue.courseValidationModalDefault);
                    vue.loadingActionBtn = false
                })
        },
        setJSONReinicioProgramado(formData) {
            let vue = this
            const minutes = parseInt(vue.resource.scheduled_restarts_minutos) +
                (parseInt(vue.resource.scheduled_restarts_horas) * 60) +
                (parseInt(vue.resource.scheduled_restarts_dias) * 1440)
            const data = {
                activado: vue.resource.scheduled_restarts_activado,
                tiempo_en_minutos: minutes,
                reinicio_dias: vue.resource.scheduled_restarts_dias,
                reinicio_horas: vue.resource.scheduled_restarts_horas,
                reinicio_minutos: vue.resource.scheduled_restarts_minutos,
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
                    let response = data.data ? data.data : data;

                    vue.selects.requisito_id = response.requisitos
                    vue.selects.lista_escuelas = response.escuelas
                    if (vue.curso_id !== '') {
                        vue.resource = Object.assign({}, response.curso)
                    }
                })
            return 0;
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
