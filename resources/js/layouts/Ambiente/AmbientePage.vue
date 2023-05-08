<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Ambiente
                <v-spacer/>
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4 px-4 py-3">

            <DefaultErrors :errors="errors"/>

            <v-form ref="ambienteForm">
                <v-row>
                    <v-col cols="12" class="pb-0">
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
                                <div class="d-flex flex-column">
                                    <span class="mb-1">Posición del formulario</span>
                                    <div class="d-flex justify-content-between">
                                        <!-- {{ resource.form_login_position }} -->
                                    <v-btn 
                                        small 
                                        color="primary" 
                                        :text="!(resource.form_login_position == 'left')" 
                                        @click="resource.form_login_position = 'left'">
                                            <v-icon>
                                                mdi-format-align-left
                                            </v-icon>
                                        </v-btn>
                                        
                                        <v-btn 
                                            small 
                                            color="primary" 
                                            :text="!(resource.form_login_position == 'middle')" 
                                            @click="resource.form_login_position = 'middle'">
                                            <v-icon>
                                                mdi-format-align-center
                                            </v-icon> 
                                        </v-btn>

                                        <v-btn 
                                            small 
                                            color="primary" 
                                            :text="!(resource.form_login_position == 'right')" 
                                            @click="resource.form_login_position = 'right'">
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
                            <v-col cols="6">
                                <DefaultInput 
                                    v-model="resource.color_primario_app"
                                    type="color" 
                                    clearable 
                                    label="Color primario" 
                                />
                            </v-col>
                            <v-col cols="6">
                                <DefaultInput 
                                    v-model="resource.color_secundario_app"
                                    type="color" 
                                    clearable 
                                    label="Color secundario" 
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
                            ref="inputLogoApp" 
                            v-model="resource.logo_app"
                            label="Imagen (560x224 px)"
                            label-button="Logo del login"
                            :file-types="['image']"
                            :rules="rules.logo_app"
                            @onSelect="setFile($event, resource,'logo_app')"
                        />
                    </v-col>
                </v-row>

                <v-row class="my-4">
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
                    <v-col cols="6" class="d-flex flex-column">
                        <div class="d-flex my-4">
                            <p class="mb-1 mr-2">Posición del logo Cursalab</p>
                            <div class="d-flex justify-content-between">
                                <!-- {{ resource.logo_cursalab_position }} -->
                                <v-btn 
                                    small 
                                    color="primary" 
                                    :text="!(resource.logo_cursalab_position == 'left')" 
                                    @click="resource.logo_cursalab_position = 'left'">
                                    <v-icon>
                                        mdi-format-align-left
                                    </v-icon>
                                </v-btn>
                                
                                <v-btn 
                                    small 
                                    color="primary"
                                    :text="!(resource.logo_cursalab_position == 'middle')" 
                                    @click="resource.logo_cursalab_position = 'middle'">
                                    <v-icon>
                                        mdi-format-align-center
                                    </v-icon> 
                                </v-btn>

                                <v-btn 
                                    small 
                                    color="primary" 
                                    :text="!(resource.logo_cursalab_position == 'right')" 
                                    @click="resource.logo_cursalab_position = 'right'">
                                    <v-icon>
                                        mdi-format-align-right
                                    </v-icon>
                                </v-btn>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="mr-3 mt-2">Mostrar botón de blog Cursalab</span>
                            <div>
                                <DefaultToggle class="mt-0" v-model="resource.show_blog_btn" no-label/>
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
    'titulo_login_app', 'subtitulo_login_app', 'form_login_transparency',  'form_login_position', 
    'color_primario_app', 'color_secundario_app', 'fondo_app', 'logo_app',
    'logo_cursalab_position','show_blog_btn','logo_cursalab',
    'completed_courses_logo', 'enrolled_courses_logo', 'diplomas_logo'
];
const file_fields = ['fondo_app', 'logo_app', 'logo_cursalab', 'completed_courses_logo', 'enrolled_courses_logo','diplomas_logo'];

    export default {
        name: 'AmbientePage',
        data() {
            return {
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
                    logo_app: this.getRules(['required']),
                }
            }
        },
        mounted() {
            const vue = this;
            vue.loadData();
        },
        methods:{
            resetForm() {
                let vue = this;

                vue.removeFileFromDropzone(vue.resource.fondo_app, 'inputFondoApp')
                vue.removeFileFromDropzone(vue.resource.logo_app, 'inputLogoApp')
                vue.removeFileFromDropzone(vue.resource.logo_cursalab,'inputLogoCursalab');

                vue.removeFileFromDropzone(vue.resource.completed_courses_logo,'inputCompletedCoursesLogo');
                vue.removeFileFromDropzone(vue.resource.enrolled_courses_logo,'inputEnrolledCoursesLogo');
                vue.removeFileFromDropzone(vue.resource.diplomas_logo,'inputDiplomasLogo');


                vue.is_superuser = false;
                vue.resource.form_login_position = null;
                vue.resource.logo_cursalab_position = null;

            },
            loadData() {
                const vue = this;
                const base_url = `${vue.base_endpoint}/edit`;

                vue.showLoader();
                vue.$http.get(base_url)
                         .then(({data}) => {
                            vue.hideLoader();

                            if (data.data) {
                                vue.is_superuser = data.data.is_superuser || false;
                                vue.resource = Object.assign({}, data.data);
                            }
                            // console.log('data loadData', data);
                         }, (err) => console.error(err));
            },
            storeForm() {

                const vue = this;
                vue.errors = [];
                this.showLoader();

                const isValid = vue.validateForm('ambienteForm');
                let base_url = `${vue.base_endpoint}/store`;
                if (isValid) {

                    // Prepare data
                    let formData = vue.getMultipartFormData(
                        'PUT', vue.resource, fields, file_fields
                    );

                    vue.$http.post(base_url, formData)
                        .then(({data}) => {

                            // console.log('data storeForm', data);
                            this.hideLoader();
                            vue.resetForm();
                            vue.showAlert(data.data.msg);
                            this.hideLoader();

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