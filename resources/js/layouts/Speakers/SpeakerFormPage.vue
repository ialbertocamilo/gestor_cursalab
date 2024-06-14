<template>
    <section class="section-list">
        <v-card flat elevation="0">
            <v-card-title class="justify-content-between align-items-center position-relative">
                <span>Facilitadores: {{ speaker_id ? 'Editar Facilitador' : 'Crear' }}</span>
            </v-card-title>
        </v-card>
        <br>
        <v-card flat elevation="0">
            <v-card-text>
                <v-form ref="BenefitForm">
                    <DefaultErrors :errors="errors"/>

                    <v-row>
                        <v-col cols="4">
                            <DefaultInput
                                dense
                                label="Nombre"
                                placeholder="Indicar nombre aquí."
                                v-model="resource.name"
                                :rules="rules.name"
                                show-required
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultInput
                                dense
                                label="Especialidad"
                                placeholder="Indicar especialidad aquí."
                                v-model="resource.specialty"
                                :rules="rules.specialty"
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultInput
                                dense
                                label="Correo"
                                placeholder="Indica el correo aquí."
                                v-model="resource.email"
                                :rules="rules.email"
                            />
                        </v-col>
                    </v-row>
                    <v-row justify="center">
                        <v-col cols="6">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputLogo"
                                v-model="resource.image"
                                label="Imagen (500x500px)"
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'image')"/>
                        </v-col>
                        <v-col cols="6">
                            <editor
                                api-key="dph7cfjyhfkb998no53zdbcbwxvxtge2o84f02zppo4eix1g"
                                v-model="resource.biography"
                                :init="{
                                content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                height: 300,
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

                    <!-- Experiencia -->
                    <v-row justify="space-around">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Experiencia del facilitador(a)"
                            >
                                <template slot="content">
                                    <div class="box_beneficio_links">

                                        <v-row>
                                            <v-col cols="12" md="12" lg="12">
                                                    <draggable v-model="lista_experiencias" @start="drag_encuestas=true" @end="drag_encuestas=false" class="custom-draggable" ghost-class="ghost">
                                                        <transition-group type="transition" name="flip-list" tag="div">
                                                            <div v-for="(encuesta, i) in lista_experiencias"
                                                                :key="encuesta.id">
                                                                <div class="item-draggable activities">
                                                                    <v-row>
                                                                        <v-col cols="1" class="d-flex align-center justify-content-center ">
                                                                            <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                                            </v-icon>
                                                                        </v-col>
                                                                        <v-col cols="6">
                                                                            <DefaultInput
                                                                                dense
                                                                                placeholder="Cargo o puesto"
                                                                                v-model="encuesta.occupation"
                                                                            />
                                                                        </v-col>
                                                                        <v-col cols="4">
                                                                            <DefaultInput
                                                                                dense
                                                                                placeholder="Empresa"
                                                                                v-model="encuesta.company"
                                                                            />
                                                                        </v-col>
                                                                        <v-col cols="1" class="d-flex align-center">
                                                                            <v-icon class="ml-0 mr-2 icon_size" color="black"
                                                                                    @click="eliminarExperiencia(encuesta, i)">
                                                                                mdi-delete
                                                                            </v-icon>
                                                                        </v-col>
                                                                    </v-row>
                                                                </div>
                                                            </div>
                                                        </transition-group>
                                                    </draggable>
                                            </v-col>
                                            <v-col cols="12" md="12" lg="12" class="d-flex justify-content-center">
                                                <v-btn color="primary" outlined @click="addExperiencia">
                                                    <v-icon class="icon_size">mdi-plus</v-icon>
                                                    Agregar experiencia
                                                </v-btn>
                                            </v-col>
                                        </v-row>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <!-- End Experiencia -->
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
    'name',
    'biography',
    'email',
    'specialty',
    'active',
    'image',
    'lista_experiencias',
];
const file_fields = ['image'];

import DialogConfirm from "../../components/basicos/DialogConfirm";
import Editor from "@tinymce/tinymce-vue";

export default {
    components: {DialogConfirm, Editor},
    props: [ 'speaker_id','mode_assign' ],
    data() {
        return {
            // otros
            drag_encuestas: false,
            lista_experiencias: [],

            url: window.location.search,
            errors: [],
            conf_focus: true,
            base_endpoint: '/speakers',
            resourceDefault: {
                name: null,
                biography: null,
                email: null,
                specialty: null,
                image: null,
                file_image: null,
                active: true,
            },
            resource: {},
            rules: {
                title: this.getRules(['required', 'max:200']),
            },
            loadingActionBtn: false,
        }
    },
    computed: {
    },
    async mounted() {
        this.showLoader()
        await this.loadData()
        this.hideLoader()
    },
    methods: {
        confirmAddLink( value ) {
            let vue = this;
            vue.modalAddLink.open = false
        },
        async openModalAddLink( link ) {
            let vue = this
            vue.modalAddLink.open = true
            vue.modalAddLink.data = link
        },
        addExperiencia() {
            let vue = this;
            const newID = `n-${Date.now()}`;
            const newExperiencia = {
                id: newID,
                company: "",
                occupation: "",
                active: 1,
                speaker_id: vue.resource.id,
                hasErrors: false,
                is_default:false
            };
            vue.lista_experiencias.push(newExperiencia);
        },
        eliminarExperiencia(link, index) {
            let vue = this;
            vue.lista_experiencias.splice(index, 1);
        },
        images_upload_handler(blobInfo, success, failure) {
            let formdata = new FormData();
            formdata.append("image", blobInfo.blob(), blobInfo.filename());
            formdata.append("model_id", null);

            axios
                .post("/upload-image/speakers", formdata)
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
            if(vue.mode_assign)
                window.close()
            else
                window.location.href = vue.base_endpoint;
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

            const edit = vue.speaker_id !== ''
            let url = `${vue.base_endpoint}/${edit ? `update/${vue.speaker_id}` : 'store'}`
            let method = edit ? 'PUT' : 'POST';

            const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
            formData.append('validateForm', validateForm ? "1" : "0");

            let lista_experiencias = JSON.stringify(vue.lista_experiencias)
            formData.append('lista_experiencias', lista_experiencias)

            vue.$http.post(url, formData)
                .then(async ({data}) => {
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
            let params = this.getAllUrlParams(window.location.search);

            if( params.type ) {
                vue.selectType = params.type
            }

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            if(vue.speaker_id != '') {
                let url = `${vue.base_endpoint}/search/${vue.speaker_id}`
                await vue.$http.get(url)
                    .then(({data}) => {
                        let response = data.data.data;

                        if(response.experiences != null && response.experiences.length > 0) {
                            response.experiences.forEach(element => {
                                const newExperiencia = {
                                    id: element.id,
                                    company: element.company,
                                    occupation: element.occupation,
                                    active: element.active,
                                    speaker_id: element.speaker_id,
                                    hasErrors: false,
                                    is_default:false
                                };
                                vue.lista_experiencias.push(newExperiencia);
                            });
                        }
                        vue.resource = Object.assign({}, response)
                    })
            }
            return 0;
        },
        isValid() {

            let valid = true;
            let errors = [];

            if (!valid) {
                this.errors = errors;
            }

            return valid;
        },
    }
}

</script>
<style lang="scss">
</style>
