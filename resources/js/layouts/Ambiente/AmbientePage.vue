<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Espacio
                <v-spacer/>
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4 px-4 py-3">

            <v-tabs v-model="tab">
                <v-tab href="#tab-1" class="tab-primary text-capitalize mt-2" v-if="type == 'general'">
                    Gestor
                </v-tab>
                <v-tab href="#tab-2" class="tab-primary text-capitalize mt-2" >
                    Aplicación
                </v-tab>
            </v-tabs>

            <v-tabs-items v-model="tab">
                <v-tab-item value="tab-1" :reverse-transition="false" :transition="false">
                    <v-card flat>
                         <v-form ref="gestorForm">

                            <v-row class="mt-4">
                                <v-col cols="12">
                                    <DefaultInput
                                        v-model="resource.link_genially"
                                        clearable
                                        label="Link Genially"
                                    />
                                </v-col>
                                <!-- <v-col cols="6">
                                    <DefaultInput
                                        v-model="resource.color_primario"
                                        type="color"
                                        clearable
                                        label="Color primario"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <DefaultInput
                                        v-model="resource.color_secundario"
                                        type="color"
                                        clearable
                                        label="Color secundario"
                                    />
                                </v-col> -->

                               <!--  <v-col cols="3">
                                    <DefaultInput
                                        v-model="resource.color_primario_texto"
                                        type="color"
                                        clearable
                                        label="Color primario texto"
                                    />
                                </v-col>
                                <v-col cols="3">
                                    <DefaultInput
                                        v-model="resource.color_secundario_texto"
                                        type="color"
                                        clearable
                                        label="Color secundario texto"
                                    />
                                </v-col> -->


                                <v-col cols="6">
                                    <DefaultInput
                                        v-model="resource.titulo"
                                        clearable
                                        label="Título de la página"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <DefaultInput
                                        v-model="resource.titulo_login"
                                        clearable
                                        label="Título del login"
                                    />
                                </v-col>
                               <!--  <v-col cols="6">
                                    <DefaultInput
                                        v-model="resource.link_pbi"
                                        clearable
                                        label="Link - Power BI"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <DefaultInput
                                        v-model="resource.url_app_web"
                                        clearable
                                        label="URL App Web"
                                    />
                                </v-col> -->

                                <v-col cols="6">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputFondo"
                                        v-model="resource.fondo"
                                        label="Tamaño máximo (1920x1280 px)"
                                        label-button="Insertar fondo"
                                        :file-types="['image']"
                                        @onSelect="setFile($event, resource, 'fondo')"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputLogo"
                                        v-model="resource.logo"
                                        label="Imagen (560x224 px)"
                                        label-button="Insertar logo"
                                        :file-types="['image']"
                                        @onSelect="setFile($event, resource, 'logo')"
                                    />
                                </v-col>

                                <v-col cols="6">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputIcono"
                                        v-model="resource.icono"
                                        label="Tamaño máximo (32x32 px)"
                                        label-button="Insertar icono"
                                        :file-types="['image']"
                                        @onSelect="setFile($event, resource, 'icono')"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputLogoEmpresa"
                                        v-model="resource.logo_empresa"
                                        label="Imagen (400x400 px)"
                                        label-button="Insertar isotipo empresa"
                                        :file-types="['image']"
                                        @onSelect="setFile($event, resource, 'logo_empresa')"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <DefaultSimpleSection title="Configuración para cursos sin conexión" marginy="my-6 card_border card pb-4">
                                        <template slot="content">
                                            <DefaultInput
                                                v-model="resource.size_limit_offline"
                                                clearable
                                                label="Tamaño en MB de los limites por curso descargado."
                                            />
                                        </template>
                                    </DefaultSimpleSection>
                                </v-col>
                            </v-row>
                        </v-form>
                    </v-card>
                </v-tab-item>
                <v-tab-item value="tab-2" :reverse-transition="false" :transition="false">
                    <v-card flat>
                        <v-form ref="applicationForm">

                            <DefaultErrors :errors="errors"/>

                            <v-row v-if="type == 'general'">
                                <v-col cols="12" class="pb-0 mt-4">
                                    <h5 class="text-primary-sub">
                                        <i class="fas fa-sign-in-alt"></i> Login
                                    </h5>
                                </v-col>
                                <v-col cols="6">
                                    <DefaultInput
                                        v-model="resource.titulo_login_app"
                                        clearable
                                        class="mt-3"
                                        label="Título del login de la aplicación"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <v-row>
                                        <v-col cols="6">
                                            <DefaultInput
                                                v-model="resource.form_login_transparency"
                                                type="number"
                                                label="Opacidad del formulario"
                                            />
                                        </v-col>
                                        <v-col cols="6">
                                            <div class="d-flex flex-column text-center">
                                                <span class="mb-1">Posición del formulario</span>
                                                <div class="d-flex justify-content-between">
                                                    <!-- {{ resource.form_login_position }} -->
                                                <v-btn
                                                    small
                                                    color="primary"
                                                    :text="!(resource.form_login_position == 'start')"
                                                    @click="resource.form_login_position = 'start'">
                                                        <v-icon>
                                                            mdi-format-align-left
                                                        </v-icon>
                                                    </v-btn>

                                                    <v-btn
                                                        small
                                                        color="primary"
                                                        :text="!(resource.form_login_position == 'center')"
                                                        @click="resource.form_login_position = 'center'">
                                                        <v-icon>
                                                            mdi-format-align-center
                                                        </v-icon>
                                                    </v-btn>

                                                    <v-btn
                                                        small
                                                        color="primary"
                                                        :text="!(resource.form_login_position == 'end')"
                                                        @click="resource.form_login_position = 'end'">
                                                        <v-icon>
                                                            mdi-format-align-right
                                                        </v-icon>
                                                    </v-btn>
                                                </div>
                                            </div>
                                        </v-col>
                                    </v-row>
                                </v-col>
                                <v-col cols="6">
                                    <DefaultInput
                                        v-model="resource.subtitulo_login_app"
                                        clearable
                                        label="Subtítulo del login de la aplicación"
                                        />
                                </v-col>
                                <v-col cols="6" class="pt-0">
                                    <v-row>
                                        <v-col cols="4">
                                            <DefaultInput
                                                v-model="resource.color_primario_app"
                                                type="color"
                                                clearable
                                                label="Color primario"
                                            />
                                        </v-col>
                                        <v-col cols="4">
                                            <DefaultInput
                                                v-model="resource.color_secundario_app"
                                                type="color"
                                                clearable
                                                label="Color secundario"
                                            />
                                        </v-col>
                                        <v-col cols="4">
                                            <DefaultInput
                                                v-model="resource.color_terciario_app"
                                                type="color"
                                                clearable
                                                label="Color terciario"
                                            />
                                        </v-col>
                                    </v-row>
                                </v-col>

                                <v-col cols="6">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputFondoApp"
                                        v-model="resource.fondo_app"
                                        label="Tamaño máximo (1920x1280 px)"
                                        label-button="Insertar fondo de aplicación"
                                        :file-types="['image']"
                                        :rules="rules.fondo_app"
                                        @onSelect="setFile($event, resource,'fondo_app')"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputFondoApp"
                                        v-model="resource.fondo_invitados_app"
                                        label="Tamaño máximo (1920x1280 px)"
                                        label-button="Insertar fondo de sección de invitados"
                                        :file-types="['image']"
                                        :rules="rules.fondo_invitados_app"
                                        @onSelect="setFile($event, resource,'fondo_invitados_app')"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputLogoApp"
                                        v-model="resource.logo_app"
                                        label="Imagen (560x224 px)"
                                        label-button="Logo del login"
                                        :file-types="['image']"
                                        :rules="rules.logo_app"
                                        @onSelect="setFile($event, resource,'logo_app')"
                                    />
                                </v-col>

                                <!-- <v-col cols="6">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputMaleLogo"
                                        v-model="resource.male_logo"
                                        label="Tamaño máximo (120x120 px)"
                                        label-button="Insertar icono colaborador masculino"
                                        :file-types="['image']"
                                        @onSelect="setFile($event, resource,'male_logo')"
                                    />
                                </v-col>
                                <v-col cols="6">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputFemaleLogo"
                                        v-model="resource.female_logo"
                                        label="Imagen (120x120 px)"
                                        label-button="Insertar icono colaborador femenino"
                                        :file-types="['image']"
                                        @onSelect="setFile($event, resource,'female_logo')"
                                    />
                                </v-col> -->
                            </v-row>
                            <v-row v-else>
                                <v-col cols="12" class="pb-0 mt-4">
                                    <h5 class="text-primary-sub">
                                        <i class="mdi mdi-format-color-fill"></i> Colores
                                    </h5>
                                </v-col>
                                <v-col cols="12" class="pt-0">
                                    <v-row>
                                        <v-col cols="4">
                                            <DefaultInput
                                                v-model="resource.color_primario_app"
                                                type="color"
                                                clearable
                                                label="Color primario"
                                            />
                                        </v-col>
                                        <v-col cols="4">
                                            <DefaultInput
                                                v-model="resource.color_secundario_app"
                                                type="color"
                                                clearable
                                                label="Color secundario"
                                            />
                                        </v-col>
                                        <v-col cols="4">
                                            <DefaultInput
                                                v-model="resource.color_terciario_app"
                                                type="color"
                                                clearable
                                                label="Color terciario"
                                            />
                                        </v-col>
                                    </v-row>
                                </v-col>
                            </v-row>
                            <v-row class="my-4" v-if="type == 'general'">
                                <v-col cols="12">
                                    <h5 class="text-primary-sub">
                                        <i class="fas fa-bookmark"></i> Logo Cursalab
                                    </h5>
                                </v-col>
                                <v-col cols="6">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputLogoCursalab"
                                        v-model="resource.logo_cursalab"
                                        label="Imagen (560x224 px)"
                                        label-button="Logo cursalab"
                                        :file-types="['image']"
                                        :rules="rules.logo_cursalab"
                                        @onSelect="setFile($event, resource,'logo_cursalab')"
                                    />
                                </v-col>
                                <v-col cols="6" class="d-flex flex-column" >
                                    <div class="d-flex my-4">
                                        <p class="mb-1 mr-2">Posición del logo Cursalab</p>
                                        <div class="d-flex justify-content-between">
                                            <!-- {{ resource.logo_cursalab_position }} -->
                                            <v-btn
                                                small
                                                color="primary"
                                                :text="!(resource.logo_cursalab_position == 'top-left')"
                                                @click="resource.logo_cursalab_position = 'top-left'">
                                                <v-icon>
                                                    mdi-arrow-top-left-bold-box-outline
                                                </v-icon>
                                            </v-btn>

                                            <v-btn
                                                small
                                                color="primary"
                                                :text="!(resource.logo_cursalab_position == 'top-right')"
                                                @click="resource.logo_cursalab_position = 'top-right'">
                                                <v-icon>
                                                    mdi-arrow-top-right-bold-box-outline
                                                </v-icon>
                                            </v-btn>

                                            <v-btn
                                                small
                                                color="primary"
                                                :text="!(resource.logo_cursalab_position == 'bottom-left')"
                                                @click="resource.logo_cursalab_position = 'bottom-left'">
                                                <v-icon>
                                                    mdi-arrow-bottom-left-bold-box-outline
                                                </v-icon>
                                            </v-btn>

                                            <v-btn
                                                small
                                                color="primary"
                                                :text="!(resource.logo_cursalab_position == 'bottom-right')"
                                                @click="resource.logo_cursalab_position = 'bottom-right'">
                                                <v-icon>
                                                    mdi-arrow-bottom-right-bold-box-outline
                                                </v-icon>
                                            </v-btn>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="mr-3">Mostrar botón de blog Cursalab</span>
                                        <div>
                                            <DefaultToggle class="mt-0" v-model="resource.show_blog_btn" no-label dense/>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="d-flex align-items-center">
                                         <DefaultInput
                                            v-model="resource.template"
                                            clearable
                                            class="mt-3"
                                            placeholder="template-default"
                                            label="Código de plantilla"
                                        />
                                    </div>
                                    <hr>
                                    <div class="d-flex align-items-center">
                                        <span class="mr-3">Habilita el cambio de contraseña después de ingresar el documento de identidad o identificador.</span>
                                        <div>
                                            <DefaultToggle class="mt-0" v-model="resource.identity_validation_enabled" no-label dense/>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="d-flex align-items-center">
                                        <span class="mr-3">Habilitar expiración de contraseña de usuarios ({{ resource.app_password_expiration_days }} días)</span>
                                        <div>
                                            <DefaultToggle class="mt-0" v-model="resource.password_expiration_enabled" no-label dense/>
                                        </div>
                                    </div>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12">
                                    <h5 class="text-primary-sub">
                                        <i class="fas fa-shapes"></i> Iconos en progreso
                                    </h5>
                                </v-col>
                                <v-col cols="4">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputCompletedCoursesLogo"
                                        v-model="resource.completed_courses_logo"
                                        label="Tamaño máximo (120x120 px)"
                                        label-button="Icono para cursos completados"
                                        :file-types="['image']"
                                        @onSelect="setFile($event, resource,'completed_courses_logo')"
                                    />
                                </v-col>
                                <v-col cols="4">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputEnrolledCoursesLogo"
                                        v-model="resource.enrolled_courses_logo"
                                        label="Tamaño máximo (120x120 px)"
                                        label-button="Icono para cursos inscritos"
                                        :file-types="['image']"
                                        @onSelect="setFile($event, resource,'enrolled_courses_logo')"
                                    />
                                </v-col>
                                <v-col cols="4">
                                    <DefaultSelectOrUploadMultimedia
                                        ref="inputDiplomasLogo"
                                        v-model="resource.diplomas_logo"
                                        label="Tamaño máximo (120x120 px)"
                                        label-button="Icono para diplomas"
                                        :file-types="['image']"
                                        @onSelect="setFile($event, resource,'diplomas_logo')"
                                    />
                                </v-col>
                            </v-row>
                        </v-form>
                    </v-card>
                </v-tab-item>
            </v-tabs-items>



            <v-row class="justify-content-center mt-4">
                <div>
                    <!-- <DefaultButton @click="resetForm" text label="Cancelar" class="default-modal-action-button mr-2"/> -->
                    <DefaultButton @click="storeForm" label="Guardar" class="default-modal-action-button" dense/>
                </div>
            </v-row>
        </v-card>
    </section>
</template>

<script>
const fields = [
    // gestor
    'link_genially', 'color_primario', 'color_secundario',
    'titulo', 'titulo_login',
    'fondo', 'logo', 'icono', 'logo_empresa', 'template','size_limit_offline',
    //app
    'titulo_login_app', 'subtitulo_login_app', 'form_login_transparency',  'form_login_position',
    'color_primario_app', 'color_secundario_app', 'color_terciario_app', 'fondo_app','fondo_invitados_app' ,'logo_app',
    'male_logo', 'female_logo',
    'logo_cursalab_position','show_blog_btn','logo_cursalab',
    'completed_courses_logo', 'enrolled_courses_logo', 'diplomas_logo',
    'identity_validation_enabled', 'password_expiration_enabled',
    //relation
    'type'
];

const file_fields = [
    //gestor
    'fondo', 'logo', 'icono', 'logo_empresa',
    //app
    'fondo_app', 'logo_app','fondo_invitados_app','logo_cursalab', 'completed_courses_logo', 'enrolled_courses_logo','diplomas_logo',
    // 'male_logo', 'female_logo'
];

    export default {
        name: 'AmbientePage',
        props:['type'],
        data() {
            return {
                tab: null,
                is_superuser: false,
                resource: {
                    form_login_position: null,
                    logo_cursalab_position: null
                },
                base_endpoint: '/ambiente',
                errors: [],
                rules: {
                    titulo_login_app: this.getRules(['required', 'max:255']),
                    subtitulo_login_app: this.getRules(['required', 'max:255']),
                    fondo_app: this.getRules(['required']),
                    fondo_invitados_app: this.getRules(['required']),
                    logo_app: this.getRules(['required']),
                }
            }
        },
        watch:{
            tab(){
                const vue = this;
                vue.loadData();
            }
        },
        methods:{
            resetForm() {
                let vue = this;

                //gestor
                vue.removeFileFromDropzone(vue.resource.fondo,'inputFondo');
                vue.removeFileFromDropzone(vue.resource.logo,'inputLogo');
                vue.removeFileFromDropzone(vue.resource.icono,'inputIcono');
                vue.removeFileFromDropzone(vue.resource.logo_empresa,'inputLogoEmpresa');

                //app
                vue.removeFileFromDropzone(vue.resource.fondo_app, 'inputFondoApp')
                vue.removeFileFromDropzone(vue.resource.fondo_invitados_app, 'inputFondoInvitadosApp')

                this.getRules(['required'])
                vue.removeFileFromDropzone(vue.resource.logo_app, 'inputLogoApp')
                vue.removeFileFromDropzone(vue.resource.logo_cursalab,'inputLogoCursalab');
                vue.removeFileFromDropzone(vue.resource.completed_courses_logo,'inputCompletedCoursesLogo');
                vue.removeFileFromDropzone(vue.resource.enrolled_courses_logo,'inputEnrolledCoursesLogo');
                vue.removeFileFromDropzone(vue.resource.diplomas_logo,'inputDiplomasLogo');
                // vue.removeFileFromDropzone(vue.resource.male_logo,'inputMaleLogo');
                // vue.removeFileFromDropzone(vue.resource.female_logo,'inputFemaleLogo');

                vue.is_superuser = false;
                vue.resource.form_login_position = null;
                vue.resource.logo_cursalab_position = null;

            },
            loadData() {
                const vue = this;
                const base_url = `${vue.base_endpoint}/edit/${vue.type}`;
                vue.resource = {
                    form_login_position: null,
                    logo_cursalab_position: null
                };

                vue.showLoader();
                vue.$http.get(base_url)
                         .then(({data}) => {
                            if (data.data) {
                                vue.is_superuser = data.data.is_superuser || false;
                                vue.resource = Object.assign({}, data.data);
                            }
                            vue.hideLoader();
                         }, (err) => console.error(err));
                return (vue.type == 'general') ? 0 : 1;
            },
            storeForm() {

                const vue = this;
                vue.errors = [];
                this.showLoader();

                const isValid_app = vue.validateForm('applicationForm');
                const isValid_gestor = vue.validateForm('gestorForm');
                let base_url = `${vue.base_endpoint}/store`;
                vue.resource.type = vue.type;
                if (isValid_app || isValid_gestor) {

                    // Prepare data
                    let formData = vue.getMultipartFormData(
                        'PUT', vue.resource, fields, file_fields
                    );

                    vue.$http.post(base_url, formData)
                        .then(({data}) => {
                            this.hideLoader();
                            vue.showAlert(data.data.msg);
                            this.hideLoader();
                            vue.loadData();

                        }).catch((error) => {

                        this.hideLoader();
                        if (error && error.errors)
                            vue.errors = error.errors
                    })
                } else {
                    this.hideLoader();
                }
            },
        }
    }
</script>
