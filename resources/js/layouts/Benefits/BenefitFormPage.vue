<template>
    <section class="section-list">
        <v-card flat elevation="0">
            <v-card-title>
                Beneficios: {{ curso_id ? 'Editar' : 'Crear' }}
            </v-card-title>
        </v-card>
        <br>
        <v-card flat elevation="0">
            <v-card-text>
                <v-form ref="BenefitForm">
                    <DefaultErrors :errors="errors"/>

                    <v-row>
                        <v-col cols="7">
                            <DefaultInput
                                dense
                                label="Nombre"
                                placeholder="Ingrese un nombre"
                                v-model="resource.title"
                                :rules="rules.title"
                                show-required
                                counter="120"
                            />
                        </v-col>
                        <v-col cols="3">
                            <DefaultAutocomplete
                                show-required
                                :rules="rules.lista_escuelas"
                                dense
                                label="Tipo"
                                v-model="resource.lista_escuelas"
                                :items="selects.lista_escuelas"
                                item-text="name"
                                item-value="id"
                                multiple
                            />
                        </v-col>
                        <v-col cols="2">
                            <DefaultToggle v-model="resource.active"/>
                        </v-col>
                    </v-row>
                    <v-row justify="center">
                        <v-col cols="6">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputLogo"
                                v-model="resource.imagen"
                                label="Imagen (500x350px)"
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'imagen')"/>
                        </v-col>
                        <v-col cols="6">
                            <editor
                                api-key="6i5h0y3ol5ztpk0hvjegnzrbq0hytc360b405888q1tu0r85"
                                v-model="resource.description"
                                :init="{
                                content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                height: 175,
                                menubar: false,
                                language: 'es',
                                force_br_newlines : true,
                                force_p_newlines : false,
                                forced_root_block : '',
                                plugins: ['lists image preview anchor', 'code', 'paste','link'],
                                toolbar:
                                    'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code | link',
                                images_upload_handler: images_upload_handler,
                            }"/>

                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="4">
                            <DefaultInput
                                dense
                                label="Cupo de participantes"
                                placeholder="Indicar cupos"
                                v-model="resource.cupo"
                                :rules="rules.cupo"
                                show-required
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultInputDate
                                clearable
                                dense
                                :referenceComponent="'modalDateFilter1'"
                                :options="modalDateFilter1"
                                v-model="resource.inicio_inscripcion"
                                label="Fecha de inicio de inscripción"
                                placeholder="Indicar fecha"
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultInputDate
                                clearable
                                dense
                                :referenceComponent="'modalDateFilter2'"
                                :options="modalDateFilter2"
                                v-model="resource.fin_inscripcion"
                                label="Fecha de fin de inscripción"
                                placeholder="Indicar fecha"
                            />
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="4">
                            <DefaultInputDate
                                clearable
                                dense
                                :referenceComponent="'modalDateFilter3'"
                                :options="modalDateFilter3"
                                v-model="resource.inicio_liberacion"
                                label="Fecha de liberación"
                                placeholder="Indicar fecha"
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultInput
                                dense
                                label="Correo de contacto"
                                placeholder="Indicar el correo aquí"
                                v-model="resource.correo"
                                :rules="rules.correo"
                                show-required
                            />
                        </v-col>
                    </v-row>

                    <!-- Map -->
                    <!-- <v-row justify="space-around">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Ubicación"
                            >
                                <template slot="content">
                                    <v-row justify="center">
                                        <v-col cols="9">
                                            <DefaultInput
                                                label="Dirección"
                                                v-model="resource.direccion"
                                                dense
                                            />
                                            <input type="text" id="search-map">
                                        </v-col>
                                        <v-col cols="3" class="d-flex justify-content-center align-items-center">
                                            <DefaultToggle
                                                v-model="resource.discapacidad"
                                                active-label="Accesible para usuarios con discapacidad"
                                                inactive-label="Accesible para usuarios con discapacidad"
                                            />
                                        </v-col>
                                    </v-row>
                                    <div class="row">
                                        <div class="bx_maps_benefit" id="bx_maps_benefit">

                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row> -->
                    <!-- End Map -->

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
const fields = [
    'title', 'active', 'position', 'imagen',
    'type_id',
    'description'
];
const file_fields = ['imagen', 'plantilla_diploma'];

import DialogConfirm from "../../components/basicos/DialogConfirm";
import Editor from "@tinymce/tinymce-vue";
// import { GoogleMap, Marker } from "vue3-google-map";

export default {
    components: {DialogConfirm, Editor},
    props: ["modulo_id", 'categoria_id', 'curso_id'],
    data() {
        const route_school = (this.categoria_id !== '')
            ? `/escuelas/${this.categoria_id}`
            : ``;

        let base_endpoint_temp = `${route_school}/cursos`;



        return {
            modalDateFilter1: {
                open: false,
            },
            modalDateFilter2: {
                open: false,
            },
            modalDateFilter3: {
                open: false,
            },
            date: null,
            date2: null,
            date3: null,
            url: window.location.search,
            errors: [],
            conf_focus: true,
            // base_endpoint: base_endpoint_temp,
            base_endpoint: '/beneficios',
            resourceDefault: {
                title: null,
                description: null,
                // position: null,
                imagen: null,
                file_imagen: null,
                config_id: this.modulo_id,
                // categoria_id: this.categoria_id,
                active: true,
                type_id: null,
                inicio_inscripcion: null,
                fin_inscripcion: null,
                inicio_liberacion: null,
                correo: null,
                lista_escuelas: [],
            },
            resource: {},
            rules: {
                title: this.getRules(['required', 'max:120']),
                // lista_escuelas: this.getRules(['required']),
                // types: this.getRules(['required']),
                cupo: this.getRules(['number']),
                inicio_inscripcion: this.getRules(['required']),
                fin_inscripcion: this.getRules(['required']),
                inicio_liberacion: this.getRules(['required']),
            },
            selects: {
                requisito_id: [],
                lista_escuelas: [],
                types: [],
            },
            loadingActionBtn: false,
            courseValidationModal: {
                ref: 'CursoValidacionesModal',
                open: false,
                title_modal: 'El curso es prerrequisito',
                type_modal:'requirement',
                content_modal: {
                    requirement: {
                        title: '¡El curso que deseas desactivar es un prerrequisito!'
                    },
                }
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
            courseUpdateStatusModal: {
                ref: 'CourseUpdateStatusModal',
                title: 'Actualizar Curso',
                contentText: '¿Desea actualizar este registro?',
                open: false,
                endpoint: '',
                title_modal: 'Cambio de estado de un <b>curso</b>',
                type_modal: 'status',
                status_item_modal: null,
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un curso!',
                        details: [
                            'Los usuarios verán los cambios en su progreso en unos minutos.',
                            'Los usuarios no podrán acceder al curso.',
                            'El diploma del curso no aparecerá para descargar desde el app.',
                            'No podrás ver el curso como opción para la descarga de reportes.',
                            'El detalle del curso activos/inactivos aparecerá en “Notas de usuario”.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un curso!',
                        details: [
                            'Los usuarios verán los cambios en su progreso en unos minutos.',
                            'Los usuarios ahora podrán acceder al curso.',
                            'El diploma del curso ahora aparecerá para descargar desde el app.',
                            'Podrás ver el curso como opción para descargar reportes.'
                        ]
                    }
                },
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

        if (+this.$props.categoria_id) {
            let exists = this.resource
                .lista_escuelas
                .includes(+this.$props.categoria_id);
            if (!exists) {
                this.resource.lista_escuelas.push(+this.$props.categoria_id);
            }
        }
        // iniciarMapBenefit()
    },
    methods: {

        images_upload_handler(blobInfo, success, failure) {
            // console.log(blobInfo.blob());
            let formdata = new FormData();
            formdata.append("image", blobInfo.blob(), blobInfo.filename());
            formdata.append("model_id", null);

            axios
                .post("/upload-image/beneficios", formdata)
                .then((res) => {
                    success(res.data.location);
                })
                .catch((err) => {
                    console.log(err)
                    failure("upload failed!");
                });
        },
        closeModal() {
            let vue = this

            let params = this.getAllUrlParams(window.location.search);
            let temp = `${this.addParamsToURL(vue.base_endpoint, params)}`;
            temp = `${vue.base_endpoint}?${temp}`;

            // console.log(temp);
            // return;

            window.location.href = temp;
        },
        confirmModal(validateForm = true) {
            let vue = this
            vue.errors = []
            vue.loadingActionBtn = true
            vue.showLoader()
            const validForm = vue.validateForm('BenefitForm')

            if (!validForm || !vue.isValid()) {
                this.hideLoader()
                vue.loadingActionBtn = false
                return
            }

            const edit = vue.curso_id !== ''
            let url = `${vue.base_endpoint}/${edit ? `update/${vue.curso_id}` : 'store'}`
            let method = edit ? 'PUT' : 'POST';

            const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
            formData.append('validateForm', validateForm ? "1" : "0");

            vue.$http.post(url, formData)
                .then(async ({data}) => {
                    console.log(data);
                    this.hideLoader()
                    const has_info_messages = data.data.messages.list.length > 0
                    vue.showAlert(data.data.msg)
                    setTimeout(() => vue.closeModal(), 2000)
                })
                .catch(error => {
                    if (error && error.errors){
                        vue.errors = error.errors
                    }
                    console.log(error);
                    vue.loadingActionBtn = false
                })
        },
        async loadData() {
            let vue = this
            // vue.$nextTick(() => {
            //     vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            // })
            // let url = `${vue.base_endpoint}/${vue.curso_id === '' ? 'form-selects' : `search/${vue.curso_id}`}`
            // await vue.$http.get(url)
            //     .then(({data}) => {
            //         let response = data.data ? data.data : data;

            //         vue.selects.requisito_id = response.requisitos
            //         vue.selects.lista_escuelas = response.escuelas
            //         vue.selects.types = response.types
            //         if (vue.curso_id !== '') {
            //             response.curso.nota_aprobatoria = response.curso.mod_evaluaciones.nota_aprobatoria;
            //             response.curso.nro_intentos = response.curso.mod_evaluaciones.nro_intentos;

            //             vue.resource = Object.assign({}, response.curso)
            //         }
            //     })
            return 0;
        },
        isValid() {

            let valid = true;
            let errors = [];

            // if (this.resource.lista_escuelas.length === 0) {
            //     errors.push({
            //         message: 'Debe seleccionar una escuela'
            //     })
            //     valid = false;
            // }

            if (!valid) {
                this.errors = errors;
            }

            return valid;
        }
    }
}

// let  map;
// let marker;
// function iniciarMapBenefit(){
//         var coord = {lat:-12.0529046 ,lng: -77.0253457};
//         map = new google.maps.Map(document.getElementById('bx_maps_benefit'),{
//             zoom: 10,
//             center: coord
//         });
//         marker = new google.maps.Marker({
//             position: coord,
//             map: map
//         });
//         const searchMap = document.getElementById('search-map');
//         const autocomplete = new google.maps.places.Autocomplete(searchMap);
//         // autocomplete.bindTo('bounds',map);
//         autocomplete.addListener("place_changed",()=>{
//             const place = autocomplete.getPlace();
//             const {geometry:{viewport,location}} = place;
//             map.setCenter(location);
//             map.setZoom(16);
//             // addInfoMap(location);
//             console.log(place);
//         })
//         //Buscar código postal
// }
</script>
<style lang="scss">
.bx_maps_benefit {
    height: 300px;
    width: 100%;
}
</style>
