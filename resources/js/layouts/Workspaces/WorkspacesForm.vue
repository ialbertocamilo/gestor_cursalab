<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>

            <DefaultErrors :errors="errors"/>

            <v-form ref="workspaceForm">

                <v-tabs fixed-tabs v-model="tabs">
                    <!-- <v-tabs-slider></v-tabs-slider> -->
                    <v-tab href="#tab-1" :key="1" class="primary--text">
                        <v-icon>mdi-text-box-outline</v-icon>
                        <span class="ml-3">Datos generales</span>
                    </v-tab>

                    <v-tab
                        href="#tab-2"
                        :key="2"
                        class="primary--text"
                    >
                        <v-icon>mdi-text-box-edit-outline</v-icon>
                        <span class="ml-3">Criterios</span>
                    </v-tab>

                    <v-tab
                        href="#tab-3"
                        :key="3"
                        class="primary--text"
                        v-if="is_superuser"
                    >
                        <v-icon>mdi-text-box-search-outline</v-icon>
                        <span class="ml-3">Configuración</span>
                    </v-tab>

                </v-tabs>

                <v-tabs-items v-model="tabs">
                    
                    <v-tab-item :key="1" :value="'tab-1'">
                        <v-row class="justify-content-center pt-4">
                            <v-col cols="6">
                                <DefaultInput
                                    clearable
                                    v-model="resource.name"
                                    label="Nombre del workspace"
                                    :rules="rules.name"
                                />
                            </v-col>
                            <v-col cols="6">
                                <DefaultInput
                                    clearable
                                    v-model="resource.url_powerbi"
                                    label="Link de learning analytics (PowerBI)"
                                />
                            </v-col>
                        </v-row>
                        <v-row class="justify-content-center">
                            <v-col cols="6">
                                <DefaultSelectOrUploadMultimedia
                                    ref="inputLogo"
                                    v-model="resource.logo"
                                    label="Logotipo (400x142px)"
                                    :file-types="['image']"
                                    :rules="rules.logo"
                                    @onSelect="setFile($event, resource,'logo')"/>
                            </v-col>
                            <v-col cols="6">
                                <DefaultSelectOrUploadMultimedia
                                    ref="inputLogoNegativo"
                                    v-model="resource.logo_negativo"
                                    label="Logotipo negativo (400x142px)"
                                    :file-types="['image']"
                                    @onSelect="setFile($event, resource,'logo_negativo')"/>
                            </v-col>
                        </v-row>

                        <DefaultSection title="Configuración de sistema de calificación">
                            <template v-slot:content>
                                <v-row justify="space-around">
                                    <v-col cols="6">
                                        <DefaultSelect
                                            clearable
                                            :items="selects.qualification_types"
                                            item-text="name"
                                            return-object
                                            v-model="resource.qualification_type"
                                            label="Sistema de calificación"
                                            :rules="rules.qualification_type_id"
                                        />
                                    </v-col>
                                    <v-col cols="6">
                                        <small>*Elija el sistema de calificación que se tendrá por defecto en la creación de cursos.</small>
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>
          
                        <DefaultSection title="Configuración de diplomas">
                            <template v-slot:content>
                                <v-row>
                                    <v-col cols="6">
                                        <DefaultSelectOrUploadMultimedia
                                            ref="inputLogoMarcaAgua"
                                            v-model="resource.logo_marca_agua"
                                            label="Imagen (500x350px)"
                                            :file-types="['image']"
                                            @onSelect="setFile($event, resource, 'logo_marca_agua')"
                                        />
                                    </v-col>
                                    <v-col cols="6" class="d-flex">
                                        <span class="mt-4 mr-2">¿Activar marca de agua en diploma?</span>
                                        <div>
                                            <DefaultToggle
                                                class="mt-0"
                                                v-model="resource.marca_agua_estado"
                                                no-label
                                                />
                                        </div>
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>

                    </v-tab-item>

                    <v-tab-item :key="2" :value="'tab-2'">

                        <v-row>
                            <v-col>
                                <v-alert
                                    colored-border
                                    elevation="2"
                                    class="mb-0"
                                >
                                    <!-- Selecciona los criterios que usa la empresa para segmentar el contenido -->
                                    <v-row>
                                        <v-col cols="12" class="py-0">
                                            <small class="mb-2 d-flex align-items-start" v-for="(mensaje,index) in mensajes"
                                               :key="index">
                                                <v-icon class="mx-2"
                                                        style="font-size: 0.60em; color: #22b573; margin-top: 7px;">fas fa-check
                                                </v-icon>
                                                <span>{{ mensaje }}</span>
                                            </small>
                                        </v-col>
                                    </v-row>
                                </v-alert>
                            </v-col>
                        </v-row>
                        <v-container
                                id="scroll-target"
                                class="overflow-y-auto py-0 px-1"
                                style="min-height: 380px; max-height: 400px"
                            >
                                   
                            <v-row class="mr-1">
                                <v-col cols="6">
                                    <v-subheader class="mb-3 px-0">
                                        <strong>Por defecto</strong>
                                    </v-subheader>
                                    <v-checkbox
                                        v-for="criterion in defaultCriteria"
                                        :key="criterion.id"
                                        v-model="resource.selected_criteria[criterion.id]"
                                        :label="generateCriterionTitle(criterion)"
                                        :disabled="false"
                                    >
                                        <!-- :disabled="criterion.code === 'module'" -->
                                    </v-checkbox>
                                </v-col>
                                <v-col cols="6">

                                    <v-subheader class="mb-3 px-0">
                                        <strong>Personalizados</strong>
                                    </v-subheader>

                                    <v-checkbox
                                        v-for="criterion in customCriteria"
                                        :key="criterion.id"
                                        v-model="resource.selected_criteria[criterion.id]"
                                        :label="`${criterion.name} ` + (criterion.required ? '(requerido)' : '(opcional)') "
                                        :disabled="criterion.its_used && resource.selected_criteria[criterion.id]"
                                    >
                                        <!-- :append-icon="criterion.its_used && resource.selected_criteria[criterion.id] ? 'fas fa-file-alt':''" -->

                                    </v-checkbox>
                                </v-col>
                            </v-row>

                        </v-container>

                    </v-tab-item>

                    <v-tab-item :key="3" :value="'tab-3'" v-if="is_superuser">

                        <DefaultSection
                            title="Configuración de límites"
                            v-if="is_superuser"
                        >
                            <template v-slot:content>

                                <v-row justify="space-around" >
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Límite de usuarios"
                                            v-model="limit_allowed_users"
                                            type="number"
                                            min="0"
                                            clearable
                                        />
                                    </v-col>
                                    <v-col cols="8">
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>

                        <v-row justify="space-around" v-if="is_superuser">
                            <v-col cols="12">
                                <DefaultSection
                                    title="Configuración de módulos del cliente"
                                >
                                    <template v-slot:content>

                                        <v-row justify="space-around">

                                            <v-col cols="4" v-for="functionality in functionalities" :key="functionality.id">
                                                <v-checkbox
                                                    v-model="resource.selected_functionality[functionality.id]"
                                                    :label="functionality.name"
                                                >
                                                </v-checkbox>
                                            </v-col>

                                        </v-row>

                                    </template>
                                </DefaultSection>
                            </v-col>
                        </v-row>

               
                        <DefaultSection title="Configuración de envío de notificaciones push" v-if="is_superuser">
                            <template v-slot:content>
                                <v-row>
                                    <v-col cols="4">
                                        <DefaultInput
                                            class="mb-4"
                                            label="Empezar envío luego de: (en minutos)"
                                            type="number"
                                            v-model="resource.notificaciones_push_envio_inicio" />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Número de usuarios por envío"
                                            type="number"
                                            v-model="resource.notificaciones_push_envio_intervalo"
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Frecuencia de envío por bloques (en minutos)"
                                            type="number"
                                            v-model="resource.notificaciones_push_chunk"
                                            />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>

                    </v-tab-item>

                </v-tabs-items>
                
            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>


const fields = [
    'name', 'url_powerbi', 'logo', 'logo_negativo', 'selected_criteria',
    'logo_marca_agua', 'marca_agua_estado', 'qualification_type_id',
    'notificaciones_push_envio_inicio', 'notificaciones_push_envio_intervalo', 'notificaciones_push_chunk', 'selected_functionality'
];
const file_fields = ['logo', 'logo_negativo', 'logo_marca_agua'];
const mensajes = [
    'Los criterios son atributos de los usuarios, que se utilizan para segmentar (asignar) el contenido (cursos).',
    'Los "criterios por defecto" son datos que se usan de forma obligatoria para todos workspaces.',
    'Los "criterios personalizados" son datos que se utilizan de forma opcional por cada workspace.',
    'Al habilitar un "criterio personalizado", es necesario actualizar la data de los usuarios mediante APIs o subida masiva. De esa forma se podrá utilizar el criterio en las segmentaciones.',
    'Los criterios que se activen, estarán disponibles en todas las secciones donde se realice "segmentación" dentro del workspace.',
    'Se recomienda utilizar los criterios predefinidos en la configuración inicial y no habilitar los criterios que no se van  a usar en segmentación o no se va a actualizar el dato por cada usuario.',
    'Es importante saber que si un criterio es activado y utilizado en alguna segmentación, ya no será posible desactivarlo a menos que se eliminen la segmentaciones donde está presente el criterio.'
];

export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    // data: () => ({
    data() {
        return {
            tabs: null,
            is_superuser: false,
            mensajes: mensajes,
            errors: []
            ,
            generateCriterionTitle(criterion) {

                let requiredLabel = criterion.required
                    ? '(requerido)'
                    : '(opcional)';

                return `${criterion.name} ${requiredLabel}`;
            }
            ,
            resourceDefault: {
                name: '',
                url_powerbi: '',
                logo: '',
                logo_negativo: '',
                qualification_type_id: '',
                selected_criteria: {},
                selected_functionality: {}
            },
            limit_allowed_users: null,
            resource: {
            },
            selects: {
                qualification_types: [],
            },
            defaultCriteria: [],
            functionalities: []
            ,
            customCriteria: []
            ,
            rules: {
                name: this.getRules(['required', 'max:255']),
                logo: this.getRules(['required']),
                qualification_type_id: this.getRules(['required']),
            }
        }
    }
    // })
    ,
    mounted() {

        // this.loadData();
    }
    ,
    methods: {
        resetValidation() {
            let vue = this
            //vue.resetFormValidation('workspaceForm')
        }
        ,
        resetForm() {
            let vue = this

            vue.selects.qualification_types = []
            vue.removeFileFromDropzone(vue.resource.logo, 'inputLogo')
            vue.removeFileFromDropzone(vue.resource.logo_negativo, 'inputLogoNegativo')
            vue.removeFileFromDropzone(vue.resource.logo_marca_agua,'inputLogoMarcaAgua');
        }
        ,
        closeModal() {
            let vue = this;
            vue.resetForm();
            vue.$emit('onCancel');
        }
        ,
        confirmModal() {

            let vue = this
            vue.errors = []
            this.showLoader()

            const isValid = vue.validateForm('workspaceForm');
            const edit = vue.options.action === 'edit';

            let base = `${vue.options.base_endpoint}`;
            let url = vue.resource.id
                ? `/${base}/${vue.resource.id}/update`
                : `/${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            if (isValid) {

                // Prepare data

                let formData = vue.getMultipartFormData(
                    method, vue.resource, fields, file_fields
                );
                formData.set(
                    'selected_criteria', JSON.stringify(vue.resource.selected_criteria)
                );
                formData.set(
                    'selected_functionality', JSON.stringify(vue.resource.selected_functionality)
                );

                vue.setLimitUsersAllowed(formData);


                // Submit data to be saved

                vue.$http
                    .post(url, formData)
                    .then(({data}) => {

                        vue.resetForm();
                        vue.closeModal();
                        vue.showAlert(data.data.msg);
                        this.hideLoader();
                        vue.$emit('onConfirm');

                    }).catch((error) => {
                    this.hideLoader();
                    if (error && error.errors)
                        vue.errors = error.errors
                })
            } else {
                this.hideLoader();
            }
        }
        ,

        setLimitUsersAllowed(formData) {
            let vue = this;
            if (vue.limit_allowed_users) {
                formData.append('limit_allowed_users_type', 'by_workspace');
                formData.append('limit_allowed_users_limit', vue.limit_allowed_users);
            }
        },
        /**
         * Load data from server
         */
        async loadData(workspace) {

            // if (!workspace) return;

            this.showLoader()

            let vue = this;
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let url = !workspace ? '/workspaces/create' : `/workspaces/${workspace.workspaceId}/edit`;

            await this.$http
                .get(url)
                .then(({data}) => {
                    // vue.hideLoader();

                    vue.selects.qualification_types = data.data.qualification_types

                    console.log('vue.selects')
                    console.log(vue.selects)

                    vue.is_superuser = data.data.is_superuser || false;

                    vue.resource = Object.assign({}, data.data);

                    // Filter criteria in two collections,
                    // according its "required" properties

                    vue.defaultCriteria = data.data.criteria.filter(c => c.is_default === 1);
                    vue.customCriteria = data.data.criteria.filter(c => c.is_default === 0);

                    // Update content of selected criteria

                    vue.resource.selected_criteria = {};
                    data.data.criteria.forEach(c => {
                        vue.resource.selected_criteria[c.id] = vue.criterionExistsInCriteriaValue(
                            c.id, data.data.criteria_workspace
                        );
                    });

                    vue.limit_allowed_users = data.data.limit_allowed_users;

                    vue.functionalities = data.data.functionalities;

                    vue.resource.selected_functionality = {};
                    data.data.functionalities_selected.forEach(c => {
                        vue.resource.selected_functionality[c.id] = vue.criterionExistsInCriteriaValue(
                            c.id, data.data.functionalities
                        );
                    });
                    this.hideLoader();
                })
                .catch((error) => {
                    this.hideLoader();
                })
        }
        ,
        loadSelects() {
        }
        ,
        criterionExistsInCriteriaValue(criterionId, criteria_workspace) {

            let exists = false;

            if (criteria_workspace) {

                criteria_workspace.forEach(v => {
                    if (v.id === criterionId)
                        exists = true;
                });
            }


            return exists;
        }
    }
}
</script>
