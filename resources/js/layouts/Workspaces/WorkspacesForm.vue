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

                    <v-tab href="#tab-1" :key="1" class="primary--text">
                        <v-icon>mdi-text-box-outline</v-icon>
                        <span class="ml-3">Datos generales</span>
                    </v-tab>

                    <v-tab
                        href="#tab-2"
                        :key="2"
                        class="primary--text"
                        v-if="is_superuser"
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
                    <!--  &&  -->
                    <v-tab
                        href="#tab-4"
                        :key="4"
                        class="primary--text"
                        v-if="is_superuser && showDc3Section"
                    >
                        <v-icon>mdi-text-box-search-outline</v-icon>
                        <span class="ml-3">Configuración (DC3-DC4)</span>
                    </v-tab>
                </v-tabs>

                <v-tabs-items v-model="tabs">

                    <v-tab-item :key="1" :value="'tab-1'">
                        <v-row class="--justify-content-center pt-4">
                            <v-col cols="6">
                                <DefaultInput
                                    clearable
                                    v-model="resource.name"
                                    label="Nombre del workspace"
                                    :rules="rules.name"
                                    dense
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

                    </v-tab-item>

                    <v-tab-item :key="2" :value="'tab-2'" v-if="is_superuser">

                        <DefaultSection title="Criterios disponibles y secciones de uso" v-if="is_superuser">
                            <template v-slot:content>

                                <v-container
                                    id="scroll-target"
                                    class="overflow-y-auto py-0 px-1"
                                    style="min-height: 360px; max-height: 450px;"
                                >
                                    <v-row class="mr-1 custom-row-checkbox">
                                        <v-col cols="12" v-if="resource.criteria_workspace">
                                            <v-row v-for="criterion in resource.criteria_workspace" :key="criterion ? criterion.id : null" class="mb-5" style="border-bottom: 1px solid #dddddd; padding-bottom: 10px;">

                                                <v-col cols="3" v-if="criterion" class="d-flex align-items-center custom-row-switch">
                                                    <DefaultToggle
                                                        class="--mt-5"
                                                        dense
                                                        :title="criterion.name"
                                                        v-model="criterion.available"
                                                        :disabled="criterion.disabled"
                                                        :active-label="criterion.name"
                                                        :inactive-label="criterion.name"
                                                    />
                                                </v-col>

                                                <v-col cols="9" v-if="criterion">
                                                    <v-row justify="start">
                                                        <v-col :cols="field.type == 'boolean' ? 4 : 12" v-for="field in criterion.fields" :key="field.code" class="py-0 pr-0">
                                                            <v-checkbox
                                                                v-if="field.type == 'boolean'"
                                                                hide-details
                                                                v-model="field.available"
                                                                :label="field.name"
                                                                :disabled="criterion.disabled ? criterion.disabled : (!criterion.available ? true : false ) "
                                                            />

                                                            <DefaultInput
                                                                v-if="field.type == 'text'"
                                                                clearable
                                                                v-model="field.text"
                                                                :label="field.name"
                                                                :disabled="criterion.disabled ? criterion.disabled : (!criterion.available ? true : false ) "
                                                                dense
                                                                class="mb-3 mx-1"
                                                            />
                                                        </v-col>
                                                    </v-row>
                                                </v-col>
                                            </v-row>
                                        </v-col>
                                    </v-row>
                                </v-container>

                            </template>
                        </DefaultSection>

                    </v-tab-item>

                    <v-tab-item :key="3" :value="'tab-3'" v-if="is_superuser">

                        <DefaultSection title="Configuración de sistema de calificación" v-if="is_superuser">
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
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="6" class="d-flex">
                                        <DefaultInfoTooltip
                                            class=""
                                            top
                                            text="Seleccione el sistema de calificación que se tendrá por defecto en la creación de cursos." />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>
                        <DefaultSection 
                            v-if="is_superuser"
                            title="Configuración de checklist" 
                            tooltipInfoText="Seleccione la configuración que se tendrá por defecto en la creación de checklists."
                        >
                            <template v-slot:content>
                                <v-row justify="space-around">
                                    <v-col cols="6">
                                        <DefaultSimpleSection title="Escalas de evaluación" marginy="my-1 px-2 pb-4" marginx="mx-0">
                                            <template slot="content">
                                                <div class="d-flex justify-space-between" style="color:#5458EA">
                                                    <p>Define las escalas de evaluación</p>
                                                </div>
                                                <draggable v-model="resource.checklist_configuration.evaluation_types" @start="drag_evaluation_type=true"
                                                        @end="drag_evaluation_type=false" class="custom-draggable" ghost-class="ghost">
                                                    <transition-group type="transition" name="flip-list" tag="div">
                                                        <div v-for="(evaluation_type) in resource.checklist_configuration.evaluation_types"
                                                            :key="evaluation_type.id">
                                                            <div class="activities">
                                                                <v-row class="align-items-center px-2">
                                                                    <v-col cols="1" class="d-flex align-center justify-content-center ">
                                                                        <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                                        </v-icon>
                                                                    </v-col>
                                                                    <!-- COLOR EDITABLE -->
                                                                    <v-col cols="1">
                                                                        <v-menu v-model="evaluation_type.menu_picker"
                                                                                bottom
                                                                                :close-on-content-click="false"
                                                                                offset-y
                                                                                right
                                                                                nudge-bottom="10"
                                                                                min-width="auto">
                                                                                <template v-slot:activator="{ on, attrs }">
                                                                                    <div class="container-evaluation-type"  v-bind="attrs" v-on="on" :style="`background:${evaluation_type.color};`">
                                                                                    </div>
                                                                                </template>
                                                                            <v-card>
                                                                                <v-card-text class="pa-0">
                                                                                    <v-color-picker v-model="evaluation_type.color" mode="hexa" flat />
                                                                                </v-card-text>
                                                                            </v-card>
                                                                        </v-menu>
                                                                    </v-col>
                                                                    <v-col cols="6">
                                                                        <DefaultInput 
                                                                            dense
                                                                            v-model="evaluation_type.name"
                                                                            appendIcon="mdi mdi-pencil"
                                                                        />
                                                                    </v-col>
                                                                    <v-col>
                                                                        <DefaultInput 
                                                                            suffix="%"
                                                                            dense
                                                                            v-model="evaluation_type.extra_attributes.percent"
                                                                        />
                                                                    </v-col>
                                                                </v-row>
                                                            </div>
                                                        </div>
                                                    </transition-group>
                                                </draggable>
                                                <div class="my-2">
                                                    <DefaultButton
                                                        label="Agregar escala"
                                                        icon="mdi-plus"
                                                        :outlined="true"
                                                        @click="addScaleEvaluation()"
                                                    />
                                                </div>
                                            </template>
                                        </DefaultSimpleSection>
                                    </v-col>
                                    <v-row class="col col-6 py-1">
                                        <v-col cols="12">
                                            <DefaultInput 
                                                label="Limite de escalas de evaluación"
                                                dense
                                                v-model="resource.checklist_configuration.max_limit_create_evaluation_types"
                                            />
                                        </v-col>
                                        <v-col cols="12">
                                            <DefaultSelect
                                                clearable
                                                :items="selects.qualification_types"
                                                item-text="name"
                                                return-object
                                                v-model="resource.checklist_configuration.qualification_type"
                                                label="Sistema de calificación"
                                                :rules="rules.qualification_type_id"
                                                dense
                                            />
                                        </v-col>
                                    </v-row>
                                </v-row>
                            </template>
                        </DefaultSection>
                        <DefaultSection
                            title="Configuración de límites"
                            v-if="is_superuser"
                        >
                            <template v-slot:content>

                                <v-row >
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Límite de usuarios"
                                            v-model="limit_allowed_users"
                                            type="number"
                                            min="0"
                                            clearable
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Límite de almacenamiento (Gb)"
                                            v-model="resource.limit_allowed_storage"
                                            type="number"
                                            min="0"
                                            clearable
                                            dense
                                        />
                                    </v-col>
                                    <v-col
                                        v-if="resource.limits"
                                        cols="4">
                                        <DefaultInput
                                            label="Límite de multimedias convertidos"
                                            v-model="resource.limits.limit_allowed_media_convert"
                                            type="number"
                                            min="0"
                                            clearable
                                            dense
                                        />
                                    </v-col>
                                    <v-col
                                        v-if="resource.limits"
                                        cols="4">
                                        <DefaultInput
                                            label="Límite de evaluaciones generadas"
                                            v-model="resource.limits.limit_allowed_ia_evaluations"
                                            type="number"
                                            min="0"
                                            clearable
                                            dense
                                        />
                                    </v-col>
                                    <v-col
                                        v-if="resource.limits"                   cols="4">
                                        <DefaultInput
                                            label="Límite de descripciones con IA"
                                            v-model="resource.limits.limit_descriptions_jarvis"
                                            type="number"
                                            min="0"
                                            clearable
                                            dense
                                        />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>

                        <DefaultSection
                            title="Configuración de Jarvis"
                            v-if="is_superuser"
                        >
                            <template v-slot:content>

                                <v-row v-if="resource.jarvis_configuration">
                                    <v-col cols="8">
                                        <DefaultInput
                                            label="Token"
                                            v-model="resource.jarvis_configuration.openia_token"
                                            type="text"
                                            min="0"
                                            clearable
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Modelo"
                                            v-model="resource.jarvis_configuration.openia_model"
                                            type="text"
                                            min="0"
                                            value="gpt-3.5-turbo"
                                            clearable
                                            dense
                                        />
                                    </v-col>
                                    <v-col cols="12">
                                        <DefaultTextArea
                                            clearable
                                            v-model="resource.jarvis_configuration.context_jarvis"
                                            label="Contexto para generar descripciones"
                                            rows="5"
                                        />
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

                                        <v-row justify="start" class="custom-row-checkbox">

                                            <v-col cols="4" v-for="functionality in functionalities" :key="functionality.id">
                                                <v-checkbox
                                                    hide-details
                                                    v-model="resource.selected_functionality[functionality.id]"
                                                    :label="functionality.name"
                                                    @change="verifyFunactionality"
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
                                            dense
                                            v-model="resource.notificaciones_push_envio_inicio" />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Número de usuarios por envío"
                                            type="number"
                                            dense
                                            v-model="resource.notificaciones_push_envio_intervalo"
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Frecuencia de envío por bloques (en minutos)"
                                            type="number"
                                            dense
                                            v-model="resource.notificaciones_push_chunk"
                                            />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>
                        <DefaultSection title="Configuración de envío de reminders" v-if="is_superuser && showReminderSection">
                            <template v-slot:content>
                                <v-row>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Número de usuarios por envío"
                                            type="number"
                                            dense
                                            v-model="resource.reminders_configuration.interval"
                                            :rules="rules.reminder"
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Frecuencia de envío por bloques (en minutos)"
                                            type="number"
                                            dense
                                            v-model="resource.reminders_configuration.chunk"
                                            :rules="rules.reminder"
                                        />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>
                        <DefaultSection title="Configuración de diplomas" v-if="is_superuser">
                            <template v-slot:content>
                                <v-row>
                                    <v-col cols="6">
                                        <DefaultToggle
                                            class="--"
                                            dense
                                            v-model="resource.marca_agua_estado"
                                            active-label="Mostrar marca de agua en diploma"
                                            inactive-label="No mostrar marca de agua en diploma"
                                        />
                                    </v-col>
                                    <v-col cols="6">
                                        <DefaultToggle
                                            class="--"
                                            dense
                                            v-model="resource.share_diplomas_social_media"
                                            active-label="Permitir compartir diploma en redes sociales"
                                            inactive-label="No permitir compartir diploma en redes sociales"
                                        />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>

                        <DefaultSection
                            title="Configuración adicional"
                            v-if="is_superuser">
                            <template v-slot:content>

                                <v-row>
                                    <v-col cols="4">
                                        <DefaultAutocomplete
                                            dense
                                            clearable
                                            return-object
                                            item-text="name"
                                            label="Criterio de fecha en Reconocimiento"
                                            v-model="resource.criterio_id_fecha_inicio_reconocimiento"
                                            :items="itemsCriterionDates"
                                        />
                                    </v-col>
                                    <v-col cols="8">
                                        <DefaultInput
                                            clearable
                                            v-model="resource.url_powerbi"
                                            label="Link de learning analytics (PowerBI)"
                                            dense
                                        />
                                    </v-col>
                                </v-row>

                                <v-row>
                                    <v-col cols="12">
                                        <DefaultToggle
                                            class="mt-5"
                                            dense
                                            v-model="resource.show_logo_in_app"
                                            active-label="Mostrar logo de workspace en la aplicación"
                                            inactive-label="No mostrar logo del workspace en la aplicación"
                                            />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>
                    </v-tab-item>
                    <!-- v-if="resource.selected_functionality.find(sf == taxonomy_id_dc3)" -->
                    <v-tab-item :key="4" :value="'tab-4'" v-if="showDc3Section">
                        <DefaultSection title="Datos del trabajador (DC3)" v-if="is_superuser">
                            <template v-slot:content>
                                <v-row justify="space-around">
                                    <!-- <v-col cols="9">
                                        <DefaultInput
                                            label="Clave Única de Registro de Población"
                                            v-model="resource.dc3_configuration.value_unique_population_registry_code"
                                            dense
                                            class="mb-3"
                                        />
                                    </v-col>
                                    <v-col cols="3">
                                        <DefaultAutocomplete
                                            clearable
                                            :items="resource.criteria_workspace"
                                            v-model="resource.dc3_configuration.criterion_unique_population_registry_code"
                                            item-text="name"
                                            item-value="criterion_id"
                                            label="Relación criterio"
                                            dense
                                        />
                                    </v-col> -->
                                    <!-- <v-col cols="9">
                                        <DefaultInput
                                            label="Ocupación específica (Catálogo Nacional Ocupaciones)"
                                            v-model="resource.dc3_configuration.value_specific_occupation"
                                            dense
                                            class="mb-3"
                                        />
                                    </v-col>
                                    <v-col cols="3">
                                        <DefaultAutocomplete
                                            clearable
                                            :items="resource.criteria_workspace"
                                            v-model="resource.dc3_configuration.criterion_specific_occupation"
                                            item-text="name"
                                            item-value="criterion_id"
                                            label="Relación criterio"
                                            dense
                                        />
                                    </v-col> -->
                                    <v-col cols="9">
                                        <DefaultInput
                                            label="Puesto"
                                            value="Puesto"
                                            v-model="resource.dc3_configuration.value_position"
                                            dense
                                            class="mb-3"
                                            :rules="rules.dc3"
                                        />
                                    </v-col>
                                    <v-col cols="3">
                                        <DefaultAutocomplete
                                            :items="resource.criteria_workspace"
                                            v-model="resource.dc3_configuration.criterion_position"
                                            item-text="name"
                                            item-value="criterion_id"
                                            label="Relación criterio"
                                            dense
                                            :rules="rules.dc3"
                                        />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>
                        <DefaultSection title="Datos de la empresa (DC3)" v-if="is_superuser">
                            <template v-slot:content>
                                <v-row v-for="(subwokspace_data,index) in resource.dc3_configuration.subwokspace_data" :key="subwokspace_data.subworkspace_id">
                                    <v-col cols="4">
                                        <DefaultAutocomplete
                                            :items="subworkspaces"
                                            v-model="resource.dc3_configuration.subwokspace_data[index].subworkspace_id"
                                            item-text="name"
                                            item-value="id"
                                            label="Módulo"
                                            dense
                                            disabled
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Nombre o razón social"
                                            dense
                                            class="mb-3 mx-1"
                                            v-model="resource.dc3_configuration.subwokspace_data[index].name_or_social_reason"
                                            :rules="rules.dc3"
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultInput
                                            label="Registro Federal de Contribuyentes con homoclave (SHCP)"
                                            dense
                                            class="mb-3 mx-1"
                                            v-model="resource.dc3_configuration.subwokspace_data[index].shcp"
                                            :rules="rules.dc3"
                                        />
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection>
                        <!-- <DefaultSection title="Logos y firmas (DC3)" v-if="is_superuser">
                            <template v-slot:content>
                                <v-row>
                                    <v-col cols="6">
                                        <DefaultSelectOrUploadMultimedia
                                            ref="inputLogoDC3"
                                            v-model="resource.dc3_logo"
                                            label="Logo Empresa DC3"
                                            :file-types="['image']"
                                            @onSelect="setFile($event, resource,'dc3_logo')"/>
                                    </v-col>
                                    <v-col cols="6">
                                        <DefaultSelectOrUploadMultimedia
                                            ref="inputInstructorSignature"
                                            v-model="resource.dc3_instructor_signature"
                                            label="Firma (Instructor o tutor)"
                                            :file-types="['image']"
                                            @onSelect="setFile($event, resource,'dc3_instructor_signature')"/>
                                    </v-col>
                                    <v-col cols="6">
                                        <DefaultSelectOrUploadMultimedia
                                            ref="inputBossSignature"
                                            v-model="resource.dc3_boss_signature"
                                            label="Firma (Patrón o representante legal)"
                                            :file-types="['image']"
                                            @onSelect="setFile($event, resource,'dc3_boss_signature')"/>
                                    </v-col>
                                </v-row>
                            </template>
                        </DefaultSection> -->
                    </v-tab-item>
                </v-tabs-items>

            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>


const fields = [
    'name', 'url_powerbi', 'logo', 'logo_negativo',
    'logo_marca_agua', 'marca_agua_estado', 'qualification_type',
    'notificaciones_push_envio_inicio', 'notificaciones_push_envio_intervalo', 'notificaciones_push_chunk', 'selected_functionality', 'criterio_id_fecha_inicio_reconocimiento','limit_allowed_storage', 'show_logo_in_app', 'share_diplomas_social_media',
    'dc3_configuration','show_logo_in_app','limits','reminders_configuration'
];
const file_fields = ['logo', 'logo_negativo', 'logo_marca_agua'];
const mensajes = [
    'Los criterios "por defecto" son obligatorios e inalterables, mientras que los "personalizados" son propios del wokspace.',
    'Para desactivar un criterio, debes retirarlo de las segmentaciones donde fue usado.',
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
            errors: [],
            sections: [
                {name: 'Perfil', code: 'profile'},
                {name: 'Filtros', code: 'filters'},
                {name: 'Ranking', code: 'ranking'},
                {name: 'Reportes', code: 'reports'},
            ],
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
                qualification_type: '',
                criteria_workspace: [],
                selected_functionality: {},
                jarvis_configuration: {},
                limits:{},
                // selected_section_criteria: {
                //     profile: false,
                //     filters: false,
                //     ranking: false,
                //     reports: false,
                // }
                dc3_configuration:{
                    // value_unique_population_registry_code:'Clave Única de Registro de Población',
                    // criterion_unique_population_registry_code:null,
                    // value_specific_occupation:'Ocupación específica (Catálogo Nacional Ocupaciones)',
                    // criterion_specific_occupation:null,
                    value_position:'Puesto',
                    criterion_position:null,
                    name_or_social_reason:'',
                    shcp:'',
                    client_name:[]
                },
                reminders_configuration:{
                    interval:0,
                    chunk:0 
                }
            },
            limit_allowed_users: null,
            resource: {
                selected_functionality: {},
            },
            selects: {
                qualification_types: [],
            },
            defaultCriteria: [],
            functionalities: [],
            customCriteria: [],
            itemsCriterionDates: [],
            subworkspaces:[],
            rules: {
                name: this.getRules(['required', 'max:255']),
                logo: this.getRules(['required']),
                qualification_type_id: this.getRules(['required']),
                dc3: this.getRules(['required']),
                reminder: this.getRules(['required']),
            },
            taxonomy_id_dc3:0,
            showDc3Section:false,
            showReminderSection:false,
            taxonomy_id_reminder:0
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
            vue.showDc3Section=false;
            vue.showReminderSection=false;
            vue.resource.limit_allowed_storage = null;
            vue.limit_allowed_users = null;
        }
        ,
        closeModal() {
            let vue = this;
            vue.resetForm();
            vue.$emit('onCancel');
        }
        ,
        async confirmModal() {

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
                    'criteria_workspace', JSON.stringify(vue.resource.criteria_workspace)
                );
                formData.set(
                    'selected_functionality',
                    vue.resource.selected_functionality
                    ? JSON.stringify(vue.resource.selected_functionality)
                    : '[]'
                );
                formData.set(
                    'dc3_configuration', JSON.stringify(vue.resource.dc3_configuration)
                );
                
                formData.set(
                    'reminders_configuration', JSON.stringify(vue.resource.reminders_configuration)
                );
                
                vue.setLimitUsersAllowed(formData);
                vue.setJarvisConfiguration(formData);

                // Submit data to be saved

                await vue.$http
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
        setJarvisConfiguration(formData){
            let vue = this;
            formData.append('limit_allowed_media_convert', vue.resource.limits.limit_allowed_media_convert);
            formData.append('limit_allowed_ia_evaluations', vue.resource.limits.limit_allowed_ia_evaluations);
            formData.append('limit_descriptions_jarvis', vue.resource.limits.limit_descriptions_jarvis);
            formData.append('context_jarvis', vue.resource.jarvis_configuration.context_jarvis);
            formData.append('openia_token', vue.resource.jarvis_configuration.openia_token);
            formData.append('openia_model', vue.resource.jarvis_configuration.openia_model);

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
            vue.resource.selected_functionality = {};
            await this.$http
                .get(url)
                .then(({data}) => {
                    // vue.hideLoader();
                    vue.selects.qualification_types = data.data.qualification_types
                    // criterion dates
                    vue.itemsCriterionDates = data.data.criteria_workspace_dates;
                    vue.is_superuser = data.data.is_superuser || false;
                    vue.resource = Object.assign({}, data.data);

                    // Filter criteria in two collections,
                    // according its "required" properties

                    // vue.defaultCriteria = data.data.criteria.filter(c => c.is_default === 1);
                    // vue.customCriteria = data.data.criteria.filter(c => c.is_default === 0);

                    // Update content of selected criteria

                    // vue.resource.criteria_workspace = {};
                    // data.data.criteria.forEach(c => {
                    //     vue.resource.criteria_workspace[c.id] = vue.criterionExistsInCriteriaValue(
                    //         c.id, data.data.criteria_workspace
                    //     );
                    // });

                    vue.limit_allowed_users = data.data.limit_allowed_users;
                    const functionalities = data.data.functionalities;
                    const taxonomy_id_dc3 = functionalities.find(f => f.code == 'dc3-dc4');
                    const taxonomy_id_reminder = functionalities.find(f => f.code == 'reminder-course');
                    vue.taxonomy_id_dc3 = taxonomy_id_dc3.id;
                    vue.taxonomy_id_reminder = taxonomy_id_reminder.id;
                    vue.subworkspaces = data.data.subworkspaces;
                    let selected_functionality ={};
                    for (const c of data.data.functionalities_selected) {
                        if(c.code == 'dc3-dc4') {
                            vue.showDc3Section = true;
                        }
                        if(c.code == 'reminder-course'){
                            vue.showReminderSection=true;
                        }
                        selected_functionality[c.id] = vue.criterionExistsInCriteriaValue(
                            c.id, data.data.functionalities
                        );
                    }
                    vue.resource.selected_functionality = selected_functionality;
                    vue.functionalities = functionalities;
                    this.hideLoader();
                })
                .catch((error) => {
                    this.hideLoader();
                })
        }
        ,
        loadSelects() {

            // Initialize objects

            if (!this.resource.jarvis_configuration) {
                this.resource.jarvis_configuration = {}
            }
            if (!this.resource.limits) {
                this.resource.limits = {}
            }
            if (!this.resource.dc3_configuration) {
                this.resource.dc3_configuration = {}
            }
        },
        criterionExistsInCriteriaValue(criterionId, criteria_workspace) {
            let exists = false;
            if (criteria_workspace) {
                criteria_workspace.forEach(v => {
                    if (v.id === criterionId){
                        exists = true;
                    }
                });
            }
            return exists;
        },
        verifyFunactionality(){
            let vue = this;
            vue.showDc3Section = vue.resource.selected_functionality[vue.taxonomy_id_dc3];
            vue.showReminderSection = vue.resource.selected_functionality[vue.taxonomy_id_reminder];
        }
    }
}
</script>
<style>
.container-evaluation-type{
    width: 20px;
    height: 20px;
    border-radius: 50%;
}
</style>