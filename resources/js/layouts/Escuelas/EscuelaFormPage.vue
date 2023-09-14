<template>
    <section class="section-list">
        <v-card flat elevation="0">
            <v-card-title>
                Escuelas: {{ categoria_id ? 'Editar' : 'Crear' }}
            </v-card-title>
        </v-card>
        <!--        <DefaultDivider/>-->
        <br>
        <v-card flat elevation="0">
            <v-card-text>
                <v-form ref="EscuelaForm">
                    <v-row align="start">
                        <v-col cols="6">
                            <DefaultInput
                                dense
                                label="Nombre"
                                placeholder="Ingrese un nombre"
                                show-required
                                v-model="resource.name"
                                :rules="rules.name"
                                counter="120"
                            />
                        </v-col>
                        <!-- <v-col cols="6">
                            <DefaultInput
                                dense
                                label="Orden"
                                placeholder="Orden"
                                v-model="resource.position"
                                :rules="rules.position"
                                show-required
                            />
                        </v-col> -->
                        <v-col cols="6">
                            <DefaultAutocomplete
                                show-required
                                :rules="rules.subworkspaces"
                                dense
                                label="Módulos"
                                v-model="resource.subworkspaces"
                                :items="selects.subworkspaces"
                                item-text="name"
                                item-value="id"
                                multiple
                                :count-show-values="4"
                            />
                        </v-col>
                    </v-row>
                    <v-row justify="center">
                        <v-col cols="12">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputLogo"
                                v-model="resource.imagen"
                                label="Logo (500x350px)"
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'imagen')"/>
                        </v-col>
                        <v-col cols="6" class="d-none">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputDiploma"
                                v-model="resource.plantilla_diploma"
                                label="Plantilla de Diploma (Medida: 1743x1553 píxeles)  "
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'plantilla_diploma')"/>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="12">

                            <DefaultModalSection
                                title="Intentos automáticos"
                            >
                                <template slot="content">
                                    <v-row justify="center">
                                        <v-col cols="3" class="d-flex justify-content-center align-items-center">
                                            <DefaultToggle
                                                active-label="Activo"
                                                inactive-label="Inactivo"
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
                                        <!--                                        <small class="ml-2 date_reinicios_error_message"-->
                                        <!--                                               v-if="showErrorReinicios"-->
                                        <!--                                               v-text="'Validar hora de reinicio'"/>-->
                                        <div style="color: #FF5252" class="v-messages__wrapper">
                                            <div class="v-messages__message">Validar hora de reinicio</div>
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>

                    </v-row>
                    <v-row justify="space-around" class="menuable">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Configuración de diploma"
                            >
                                <!-- tooltip="Tooltip" -->
                                <template slot="content">

                                    <DiplomaSelector
                                        v-model="resource.certificate_template_id"/>

                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="2">
<!--                            <DefaultFormLabel-->
<!--                                label="Estado"-->
<!--                            />-->
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
        </v-card>
    </section>
</template>
<script>
import DiplomaSelector from "../../components/Diplomas/DiplomaSelector.vue";

const fields = ['name', 'nombre_ciclo_0', 'active', 'position', 'config_id', 'subworkspaces',
    'imagen', 'scheduled_restarts', 'modalidad', 'certificate_template_id'];
const file_fields = ['imagen','plantilla_diploma'];
export default {
    components: {DiplomaSelector},
    props: ["modulo_id", 'categoria_id'],
    data() {
        return {
            base_endpoint: `/escuelas`,
            resourceDefault: {
                modalidad: null,
                config_id: this.modulo_id,
                name: null,
                position: null,
                nombre_ciclo_0: null,
                imagen: null,
                plantilla_diploma:null,
                file_imagen: null,
                file_plantilla_diploma:null,
                position: null,
                active: true,
                subworkspaces: [],
                reinicio_automatico: false,
                reinicio_automatico_dias: null,
                reinicio_automatico_horas: null,
                reinicio_automatico_minutos: 1,
                certificate_template_id: null
            },
            resource: {},
            rules: {
                name: this.getRules(['required', 'max:120']),
                modalidad: this.getRules(['required']),
                subworkspaces: this.getRules(['required']),
                position: this.getRules(['number']),
            },
            selects: {
                subworkspaces: [],
                modalidad: [
                    {id: 'regular', nombre: 'REGULAR (dentro de malla)'},
                    {id: 'extra', nombre: 'EXTRACURRICULAR (fuera de malla)'},
                    {id: 'libre', nombre: 'LIBRE (no forma parte del progreso)'}
                ]
            },
            loadingActionBtn: false
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
            const validateForm = vue.validateForm('EscuelaForm')
            if (!validateForm) {
                this.hideLoader()
                vue.loadingActionBtn = false
                return
            }
            const edit = vue.categoria_id !== ''
            let url = `${vue.base_endpoint}/${edit ? `update/${vue.categoria_id}` : 'store'}`
            let method = edit ? 'PUT' : 'POST';

            const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
            vue.getJSONReinicioProgramado(formData)

            let data = {}
            vue.$http.post(url, formData)
                .then(({data}) => {
                    this.hideLoader()
                    vue.showAlert(data.data.msg)
                    setTimeout(() => {
                        window.location.href = `${vue.base_endpoint}`
                    }, 2000)
                })
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
            formData.append('scheduled_restarts', json)
        },
        resetValidation() {
            let vue = this
            vue.resetFormValidation('EscuelaForm')
        },
        async loadData() {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.base_endpoint}/${vue.categoria_id === '' ? 'form-selects' : `search/${vue.categoria_id}`}`
            await vue.$http.get(url)
                .then(({data}) => {

                    let response = data.data ? data.data : data;

                    vue.selects.subworkspaces = response.modules

                    if (vue.categoria_id !== '') {
                        vue.resource = Object.assign({}, data.data.escuela)
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
