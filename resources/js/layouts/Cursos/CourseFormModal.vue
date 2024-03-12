<template>
    <DefaultDialog :options="options" :width="width" @onCancel="closeModal" @onConfirm="confirmModal">
        <template v-slot:content>

            <v-form ref="CursoForm">
                <DefaultErrors :errors="errors"/>
                <v-row v-if="$root.isSuperUser">
                    <v-col cols="12">
                        <v-chip>
                            ID: {{ resource.id }}
                        </v-chip>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="6" class="pb-0">
                        <DefaultInput label="Nombre del curso" placeholder="Ingrese un nombre" v-model="resource.name"
                            :rules="rules.name" show-required emojiable dense />
                    </v-col>
                    <v-col cols="6" class="pb-0">
                        <DefaultAutocomplete show-required :rules="rules.lista_escuelas" label="Escuelas a las que pertenece"
                            v-model="resource.lista_escuelas" :items="selects.lista_escuelas" item-text="name"
                            item-value="id" multiple dense />
                    </v-col>
                    <v-col cols="6">
                        <DefaultTextArea dense label="Descripción u objetivo"
                            placeholder="Agrega una descripción u objetivo del curso" v-model="resource.description"
                            @eventGenerateIA="generateIaDescription" :limits="limits_descriptions_generate_ia"
                            :loading="loading_description" :disabled="loading_description" :rows="10"
                            :showButtonIaGenerate="showButtonIaGenerate" />
                    </v-col>
                    <v-col cols="6">
                        <DefaultSelectOrUploadMultimedia ref="inputLogo" v-model="resource.imagen"
                            label="Imagen (500x350px)" :file-types="['image']"
                            @onSelect="setFile($event, resource, 'imagen')" select-width="60vw" select-height="100%" />
                    </v-col>
                    <v-col cols="6">
                        <DefaultAutocomplete show-required :rules="rules.types" dense label="Modalidad del curso"
                            v-model="resource.modality_id" :items="selects.modalities" item-text="name" item-value="id"
                            disabled />
                    </v-col>
                    <v-col cols="6">
                        <DefaultAutocomplete show-required :rules="rules.types" dense label="Tipo de curso"
                            v-model="resource.type_id" :items="selects.types" item-text="name" item-value="id" />
                    </v-col>
                    <v-row v-if="current_modality.code == 'in-person'" class="mx-1" style="width: 100%;">
                        <v-col cols="6">
                            <DefaultSimpleSection title="Toma de asistencia" marginy="my-1" marginx="mx-0">
                                <template slot="content">
                                    <v-radio-group v-model="resource.modality_in_person_properties.assistance_type" row
                                        class="ml-2">
                                        <v-radio value="assistance-by-session">
                                            <template v-slot:label>
                                                <v-tooltip top>
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <div v-bind="attrs" v-on="on" class="mt-2">
                                                            Asistencia por sesión
                                                        </div>
                                                    </template>
                                                    <span>Se tomará asistencia en cada sesión del curso</span>
                                                </v-tooltip>
                                            </template>
                                        </v-radio>
                                        <v-radio value="assistance-by-day">
                                            <template v-slot:label>
                                                <v-tooltip top>
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <div v-bind="attrs" v-on="on" class="mt-2">
                                                            Asitencia por día
                                                        </div>
                                                    </template>
                                                    <span>Si se tiene 2 o más sesiones en el día se tomará solo una
                                                        asistencia</span>
                                                </v-tooltip>
                                            </template>
                                        </v-radio>
                                    </v-radio-group>
                                </template>
                            </DefaultSimpleSection>
                        </v-col>
                        <v-col cols="6">
                            <DefaultSimpleSection title="Quienes pueden ver el contenido" marginy="my-1" marginx="mx-0">
                                <template slot="content">
                                    <v-radio-group v-model="resource.modality_in_person_properties.visualization_type" row
                                        class="ml-2">
                                        <v-radio value="scheduled-users">
                                            <template v-slot:label>
                                                <v-tooltip top>
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <div v-bind="attrs" v-on="on" class="mt-2">
                                                            Todos los agendados
                                                        </div>
                                                    </template>
                                                    <span>Todos los agendados al curso podrán ver al contenido luego de
                                                        culminado</span>
                                                </v-tooltip>
                                            </template>
                                        </v-radio>
                                        <v-radio value="all-users">
                                            <template v-slot:label>
                                                <v-tooltip top>
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <div v-bind="attrs" v-on="on" class="mt-2">
                                                            Solo los asistentes
                                                        </div>
                                                    </template>
                                                    <span>Todos los participantes podrán ver los datos del curso</span>
                                                </v-tooltip>
                                            </template>
                                        </v-radio>
                                    </v-radio-group>
                                </template>
                            </DefaultSimpleSection>
                        </v-col>
                        <v-col cols="6">
                            <DefaultSimpleSection title="Firmas" marginy="my-1" marginx="mx-0">
                                <template slot="content">
                                    <DefaultToggle class="ml-4 mb-2"
                                        v-model="resource.modality_in_person_properties.required_signature" dense
                                        :active-label="'Solicitar firma del colaborador'"
                                        :inactive-label="'Solicitar firma del colaborador'" />
                                </template>
                            </DefaultSimpleSection>
                        </v-col>
                        <v-col cols="6">
                        </v-col>
                    </v-row>
                    <v-col cols="6" v-if="current_modality.code == 'asynchronous'">
                        <DefaultAutocomplete dense label="Requisito" v-model="resource.requisito_id"
                            :items="selects.requisito_id" custom-items item-text="name" item-value="id" clearable>
                            <template v-slot:customItems="{ item }">
                                <v-list-item-content>
                                    <v-list-item-title v-html="item.name" />
                                    <v-list-item-subtitle class="list-cursos-carreras" v-html="item.escuelas" />
                                </v-list-item-content>
                            </template>
                        </DefaultAutocomplete>
                    </v-col>
                    <v-col cols="3"
                        v-if="!resource.can_create_certificate_dc3_dc4 && current_modality.code == 'asynchronous'">
                        <DefaultAutocomplete dense label="Duración (hrs.)" v-model="resource.duration"
                            :items="selects.duration" item-text="name" item-value="id" placeholder="Ej. 2:00" />
                    </v-col>
                    <v-col :cols="resource.can_create_certificate_dc3_dc4 ? '6' : '3'"
                        v-if="current_modality.code == 'asynchronous'">
                        <DefaultInput numbersOnly dense label="Inversión" placeholder="Ej. 2000"
                            v-model="resource.investment" />
                    </v-col>

                </v-row>
                <v-row justify="space-around" class="menuable">
                    <v-col cols="12">
                        <DefaultModalSectionExpand title="Configuración avanzada"
                            :expand="sections.shosSectionAdvancedconfiguration" :simple="true">
                            <template slot="content">
                                <DefaultSimpleSection v-if="has_DC3_functionality" title="DC3-DC4 (México)">
                                    <template slot="content">
                                        <v-row justify="center">
                                            <v-col cols="12">
                                                <DefaultToggle active-label="Creación de formulario DC3-DC4"
                                                    inactive-label="Creación de formulario DC3-DC4"
                                                    v-model="resource.can_create_certificate_dc3_dc4" dense />
                                                <div>
                                                    Anexa la elaboración de los formularios DC3 (colaborador) y DC4(gestor)
                                                </div>
                                            </v-col>
                                            <v-row v-if="resource.can_create_certificate_dc3_dc4">
                                                <v-col cols="4">
                                                    <DefaultAutocomplete placeholder="" dense label="Catálogo de área"
                                                        v-model="resource.dc3_configuration.catalog_denomination_dc3_id"
                                                        :items="catalog_denominations" item-text="name" clearable
                                                        :rules="rules.dc3" />
                                                </v-col>
                                                <v-col cols="4">
                                                    <DefaultInputDate clearable dense range
                                                        :referenceComponent="'modalDateFilter3'" :options="modalDateFilter3"
                                                        v-model="resource.dc3_configuration.date_range"
                                                        label="Periodo de ejecución" :rules="rules.dc3" />
                                                </v-col>
                                                <v-col cols="4">
                                                    <DefaultAutocomplete dense label="Duración (hrs.)"
                                                        v-model="resource.duration" :items="selects.duration"
                                                        item-text="name" item-value="id" placeholder="Ej. 2:00"
                                                        :rules="rules.dc3" />
                                                </v-col>
                                                <v-col cols="9">
                                                    <DefaultAutocomplete placeholder="" dense label="Instructor"
                                                        v-model="resource.dc3_configuration.instructor"
                                                        :items="people.instructors" item-text="person_attributes.name"
                                                        clearable :rules="rules.dc3" />
                                                </v-col>
                                                <v-col cols="3" class="d-flex align-items-center">
                                                    <DefaultModalButton label="Agregar" outlined
                                                        @click="openFormModal(modalDC3PersonOptions, { type: 'dc3-instructor' }, 'create', 'Agregar Instructor')" />
                                                </v-col>
                                                <v-col cols="9">
                                                    <DefaultAutocomplete placeholder="" dense label="Representante Legal"
                                                        v-model="resource.dc3_configuration.legal_representative"
                                                        :items="people.legal_representatives"
                                                        item-text="person_attributes.name" clearable :rules="rules.dc3" />
                                                </v-col>
                                                <v-col cols="3" class="d-flex align-items-center">
                                                    <DefaultModalButton label="Agregar" outlined
                                                        @click="openFormModal(modalDC3PersonOptions, { type: 'dc3-legal-representative' }, 'create', 'Representante Legal')" />
                                                </v-col>
                                            </v-row>
                                        </v-row>
                                    </template>
                                </DefaultSimpleSection>
                                <DefaultSimpleSection v-if="has_registro_capacitacion_functionality"
                                    title="Registro de capacitación (Perú)">
                                    <template slot="content">
                                        <div>
                                            <v-row>
                                                <v-col cols="12">
                                                    <DefaultToggle v-model="resource.registro_capacitacion.active"
                                                        :activeLabel="'Creación de registro de capacitación'"
                                                        :inactiveLabel="'Creación de registro de capacitación'" dense />
                                                </v-col>
                                                <v-col cols="12">
                                                    Anexa la elaboración del registro de capacitación para tus reportes.
                                                </v-col>
                                            </v-row>
                                            <v-row v-if="resource.registro_capacitacion.active">

                                                <v-col cols="12" class="pb-1">
                                                    <label style="font-weight: 500; font-size: 16px">
                                                        Datos para registro
                                                    </label>
                                                </v-col>

                                                <v-col cols="6">
                                                    <DefaultAutocomplete placeholder="" dense label="Instructor"
                                                        v-model="resource.registro_capacitacion.trainerAndRegistrar"
                                                        :items="registro_capacitacion_trainers" item-text="name" clearable
                                                        :rules="rules.dc3" />
                                                </v-col>
                                                <v-col v-if="resource.registro_capacitacion.trainerAndRegistrar" cols="2">
                                                    <v-btn elevation="0"
                                                        @click="trainerDeleteConfirmationDialog.open = true">
                                                        <v-icon v-text="'mdi-trash-can'" />
                                                    </v-btn>
                                                </v-col>
                                                <v-col cols="2">

                                                    <DefaultModalButton label="Agregar" outlined
                                                        @click="openFormModal(modalRegistroTrainerOptions, { type: 'registro-trainer' }, 'create', 'Agregar Instructor')" />
                                                </v-col>

                                                <v-col cols="2"></v-col>
                                                <v-col cols="12">
                                                    <DefaultInput clearable
                                                        v-model="resource.registro_capacitacion.certificateCode"
                                                        label="Encabezado del registro de capacitación"
                                                        :rules="rules.certificateCode" dense />
                                                </v-col>

                                                <v-col cols="12">
                                                    <DefaultRichText clearable
                                                        v-model="resource.registro_capacitacion.syllabus"
                                                        label="Temario para el registro" :rules="rules.syllabus"
                                                        :ignoreHTMLinLengthCalculation="true" :height="195"
                                                        :key="`temario-editor`" :loading="loading_description"
                                                        :maxLength="3000" ref="descriptionRichText" />
                                                </v-col>

                                                <v-col cols="12">
                                                    <DefaultTextArea dense label="Observaciones del curso"
                                                        placeholder="Ingrese una descripción del curso"
                                                        v-model="resource.registro_capacitacion.comment" />
                                                </v-col>

                                            </v-row>

                                        </div>
                                    </template>
                                </DefaultSimpleSection>
                                <DefaultSimpleSection title="Configuración de evaluaciones">
                                    <template slot="content">
                                        <v-row justify="center">
                                            <v-col cols="6">

                                                <DefaultSelect dense :items="selects.qualification_types" item-text="name"
                                                    return-object show-required v-model="resource.qualification_type"
                                                    label="Sistema de calificación" :rules="rules.qualification_type_id" />
                                            </v-col>
                                            <v-col cols="3">
                                                <DefaultInput label="Nota mínima aprobatoria"
                                                    v-model="resource.nota_aprobatoria" :rules="rules.nota_aprobatoria"
                                                    type="number" :min="0"
                                                    :max="resource.qualification_type ? resource.qualification_type.position : 0"
                                                    show-required dense
                                                    @onFocus="resource.id && conf_focus ? alertNotaMinima() : null" />
                                            </v-col>
                                            <v-col cols="3">
                                                <DefaultInput label="Cantidad de intentos" v-model="resource.nro_intentos" dense
                                                    :rules="rules.nro_intentos" type="number" show-required></DefaultInput>
                                            </v-col>
                                            <v-col cols="12" class="py-1">
                                                <p class="mb-0 p-small-instruction">** Utilizado para mostrar el resultado
                                                    del curso y que se tendrá por defecto en la creación de temas.</p>
                                            </v-col>
                                        </v-row>
                                    </template>
                                </DefaultSimpleSection>
                                <DefaultSimpleSection title="Configuración de diplomas">
                                    <template slot="content">
                                        <v-row class="px-8">
                                            <DiplomaSelector v-model="resource.certificate_template_id"
                                                :old-preview="resource.plantilla_diploma" />
                                            <v-col cols="12">
                                                <DefaultDivider class="my-1" />
                                            </v-col>
                                            <div class="col-6">
                                                <DefaultToggle dense :active-label="'Mostrar diploma al usuario'"
                                                    :inactive-label="'Mostrar diploma al usuario'"
                                                    v-model="resource.show_certification_to_user" />
                                            </div>
                                            <div class="col-6">
                                                <DefaultToggle dense
                                                    :active-label="'Habilitar aceptación de diploma al usuario'"
                                                    :inactive-label="'Habilitar aceptación de diploma al usuario'"
                                                    v-model="resource.user_confirms_certificate" />
                                            </div>
                                        </v-row>
                                    </template>
                                </DefaultSimpleSection>
                                <DefaultSimpleSection title="Programación de reinicios de evaluaciones"
                                    v-if="current_modality.code == 'asynchronous'">
                                    <template slot="content">
                                        <v-row justify="center">
                                            <v-col cols="3" class="d-flex justify-content-center align-items-center">
                                                <DefaultToggle active-label="Automático" inactive-label="Manual"
                                                    v-model="resource.scheduled_restarts_activado" dense />
                                            </v-col>
                                            <v-col cols="3">
                                                <DefaultInput label="Días" v-model="resource.scheduled_restarts_dias"
                                                    :disabled="!resource.scheduled_restarts_activado" type="number" dense />
                                            </v-col>
                                            <v-col cols="3">
                                                <DefaultInput label="Horas" v-model="resource.scheduled_restarts_horas"
                                                    :disabled="!resource.scheduled_restarts_activado" type="number" dense />
                                            </v-col>
                                            <v-col cols="3">
                                                <DefaultInput label="Minutos" v-model="resource.scheduled_restarts_minutos"
                                                    :disabled="!resource.scheduled_restarts_activado" type="number" dense />
                                            </v-col>
                                        </v-row>
                                        <div class="d-flex justify-content-center mt-1" v-if="showErrorReinicios">
                                            <div style="color: #FF5252" class="v-messages__wrapper">
                                                <div class="v-messages__message">Validar hora de reinicio</div>
                                            </div>
                                        </div>
                                    </template>
                                </DefaultSimpleSection>

                                <DefaultSimpleSection title="Programación de curso">
                                    <template slot="content">
                                        <v-row justify="center">
                                            <v-col cols="4" class="d-flex justify-content-center align-items-center">
                                                <DefaultInputDate clearable :referenceComponent="'modalDateFilter1'"
                                                    :options="modalDateFilter1" v-model="resource.publish_date_1"
                                                    label="Fecha de inicio" dense />
                                            </v-col>
                                            <v-col cols="2">
                                                <DefaultInput class="time-input" type="time" label="Hora"
                                                    v-model="resource.publish_time_1" :disabled="!resource.publish_date_1"
                                                    :rules="rules.time" step="60" />
                                            </v-col>

                                            <v-col cols="4" class="d-flex justify-content-center align-items-center">
                                                <DefaultInputDate clearable :referenceComponent="'modalDateFilter1'"
                                                    :options="modalDateFilter2" v-model="resource.publish_date_2"
                                                    label="Fecha de fin" dense />
                                            </v-col>

                                            <v-col cols="2">
                                                <DefaultInput class="time-input" type="time" label="Hora"
                                                    v-model="resource.publish_time_2" :disabled="!resource.publish_date_2"
                                                    :rules="rules.time" step="60" />
                                            </v-col>

                                            <v-col cols="12" class="py-1">
                                                <p class="mb-0 p-small-instruction">** El curso pasará a estar activo de
                                                    acuerdo a la fecha configurada.</p>
                                                <p class="mb-0 p-small-instruction">** Recuerda que el curso debe estar
                                                    segmentado, pertenecer a una escuela activa y contener al menos un tema
                                                    activo para que este sea visible por tus usuarios cuando este se active.
                                                </p>
                                            </v-col>
                                        </v-row>
                                    </template>
                                </DefaultSimpleSection>
                            </template>
                        </DefaultModalSectionExpand>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="2">
                        <DefaultToggle v-model="resource.active" @onChange="modalStatusEdit" dense />
                    </v-col>
                </v-row>
            </v-form>
            <CursoValidacionesModal width="408px" :ref="courseValidationModal.ref" :options="courseValidationModal"
                :resource="resource" @onCancel="closeFormModal(courseValidationModal)" @onConfirm="confirmValidationModal(
                    courseValidationModal,
                    `${base_endpoint}?${addParamsToURL(base_endpoint, getAllUrlParams(url))}`,
                    confirmModal(false))" />

            <DialogConfirm v-model="alertConfirmationDialog.open" :options="alertConfirmationDialog" width="408px"
                title="Cambiar de estado del curso" subtitle="¡Estás a punto de cambiar la configuración de un curso!"
                @onConfirm="confirmValidationModal" @onCancel="alertConfirmationDialog.open = false" />

            <DialogConfirm :ref="courseUpdateStatusModal.ref" v-model="courseUpdateStatusModal.open"
                :options="courseUpdateStatusModal" width="408px" title="Cambiar de estado al curso"
                subtitle="¿Estás seguro de cambiar de estado al curso?" @onConfirm="courseUpdateStatusModal.open = false"
                @onCancel="closeModalStatusEdit" />

            <DialogConfirm :ref="trainerDeleteConfirmationDialog.ref" v-model="trainerDeleteConfirmationDialog.open"
                width="408px" title="Eliminar entrenador" subtitle="¿Está seguro de eliminar el entrenador?"
                @onConfirm="confirmTrainerDelete" @onCancel="trainerDeleteConfirmationDialog.open = false" />

            <DC3PersonModal :ref="modalDC3PersonOptions.ref" v-model="modalDC3PersonOptions.open"
                :options="modalDC3PersonOptions" width="30vw" @onConfirm="setPersonDC3"
                @onCancel="modalDC3PersonOptions.open = false" />
            <RegistroTrainerModal :ref="modalRegistroTrainerOptions.ref" v-model="modalRegistroTrainerOptions.open"
                :options="modalRegistroTrainerOptions" width="30vw" @onConfirm="setTrainer"
                @onCancel="modalRegistroTrainerOptions.open = false" />
        </template>
    </DefaultDialog>
</template>
<script>
import editor from "@tinymce/tinymce-vue";

const fields = [
    'name', 'reinicios_programado', 'active', 'position', 'imagen',
    'plantilla_diploma', 'config_id', 'categoria_id', 'type_id', 'qualification_type',
    'description', 'requisito_id', 'lista_escuelas',
    'duration', 'investment', 'show_certification_date', 'certificate_template_id',
    'activate_at', 'deactivate_at', 'show_certification_to_user', 'user_confirms_certificate', 'can_create_certificate_dc3_dc4',
    'dc3_configuration', 'registro_capacitacion', 'modality_id'
];
const file_fields = ['imagen', 'plantilla_diploma'];
import CursoValidacionesModal from "./CursoValidacionesModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import DiplomaSelector from "../../components/Diplomas/DiplomaSelector";
import DC3PersonModal from './DC3PersonModal';
import RegistroTrainerModal from './RegistroTrainerModal';
import DefaultRichText from "../../components/globals/DefaultRichText.vue";

export default {
    components: {
        DefaultRichText,
        editor, CursoValidacionesModal, DialogConfirm, DiplomaSelector, DC3PersonModal, RegistroTrainerModal
    },
    // props: ["modulo_id", 'categoria_id', 'curso_id'],
    props: {
        options: {
            type: Object,
            required: true
        },
        width: {
            type: String,
            required: false
        },
        school_id: null,
    },

    data() {
        // const route_school = (this.categoria_id !== '') ? `/escuelas/${this.categoria_id}` : ``;

        // let base_endpoint_temp = `${route_school}/cursos`;
        let base_endpoint_temp = `/cursos`;

        return {
            has_registro_capacitacion_functionality: false,
            url: window.location.search,
            errors: [],
            conf_focus: true,
            sections: {
                showSectionQualification: { status: true },
                showSectionCertification: { status: true },
                showSectionRestarts: { status: false },
                showSectionSchedule: { status: false },
                showSectionDC3DC4: { status: false },
                showSectionPosition: { status: false },
                showSectionAssistance: { status: false },
                showSectionVisualization: { status: false },
                showSectionRegistroCapacitacion: { status: false },
                shosSectionAdvancedconfiguration: { status: false }
            },
            // base_endpoint: base_endpoint_temp,
            base_endpoint: base_endpoint_temp,
            resourceDefault: {
                id: null,
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
                type_id: null,
                duration: null,
                investment: null,
                nota_aprobatoria: null,
                nro_intentos: null,
                scheduled_restarts_activado: false,
                scheduled_restarts_dias: null,
                scheduled_restarts_horas: null,
                certificate_template_id: null,
                scheduled_restarts_minutos: 1,
                lista_escuelas: [],
                show_certification_date: false,
                qualification_type: { position: 0 },
                show_certification_to_user: null,
                user_confirms_certificate: 1,

                activate_at: null,
                deactivate_at: null,
                publish_date_1: null,
                publish_time_1: null,
                publish_date_2: null,
                publish_time_2: null,
                dc3_configuration: {},
                can_create_certificate_dc3_dc4: false,
                modality_id: null,
                modality_in_person_properties: {
                    assistance_type: 'assistance-by-session',
                    required_signature: false,
                    visualization_type: 'scheduled-users'
                },
                registro_capacitacion: {}
            },
            resource: {
                qualification_type: { position: 0 },
                dc3_configuration: {},
                modality_in_person_properties: {
                    assistance_type: 'assistance-by-session',
                    required_signature: false,
                    visualization_type: 'scheduled-users'
                },
                registro_capacitacion: {}
            },
            rules: {
                name: this.getRules(['required', 'max:120']),
                lista_escuelas: this.getRules(['required']),
                types: this.getRules(['required']),
                position: this.getRules(['required', 'number']),
                nota_aprobatoria: this.getRules(['required']),
                nro_intentos: this.getRules(['required', 'number', 'min_value:1']),
                qualification_type_id: this.getRules(['required']),
                dc3: this.getRules(['required']),

                trainerAndRegistrar: this.getRules(['required']),
                certificateCode: this.getRules(['required']),
                syllabus: this.getRules(['required']),
                comment: this.getRules(['required']),
            },
            selects: {
                requisito_id: [],
                lista_escuelas: [],
                types: [],
                qualification_types: [],
                duration: [
                    { 'id': '0.50', 'name': '0:30' },
                    { 'id': '1.00', 'name': '1:00' },
                    { 'id': '1.50', 'name': '1:30' },
                    { 'id': '2.00', 'name': '2:00' },
                    { 'id': '3.00', 'name': '3:00' },
                    { 'id': '4.00', 'name': '4:00' },
                    { 'id': '5.00', 'name': '5:00' },
                    { 'id': '6.00', 'name': '6:00' },
                ],
                modalities: [],
                hosts: []
            },
            loadingActionBtn: false,
            courseValidationModal: {
                ref: 'CursoValidacionesModal',
                open: false,
                title_modal: 'El curso es pre-requisito',
                type_modal: 'requirement',
                content_modal: {
                    requirement: {
                        title: '¡El curso que deseas desactivar es un pre-requisito!'
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
            trainerDeleteConfirmationDialog: {
                ref: 'TrainerDeleteModal',
                title: 'Eliminar entrenador',
                contentText: '¿Desea eliminar este registro?',
                open: false,
                endpoint: ''
            },
            alertConfirmationDialog: {
                open: false,
                title_modal: 'Cambiar de estado del <b>curso</b>',
                type_modal: 'confirm',
                content_modal: {
                    confirm: {
                        title: '¡Estás a punto cambiar la configuración de un curso!',
                        details: [
                            'Los usuarios con histórico se mantendrán con la información y no se recalculará su estado.'
                        ],
                    }
                },
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

            modalDateFilter1: {
                open: false
            },

            modalDateFilter2: {
                open: false
            },
            modalDateFilter3: {
                open: false,
            },
            modalDC3PersonOptions: {
                open: false,
                ref: 'PersonFormModal',
                open: false,
                base_endpoint: '/person',
                confirmLabel: 'Guardar',
                resource: 'person',
                title: '',
                action: null,
                persistent: true,
            },
            modalRegistroTrainerOptions: {
                open: false,
                ref: 'TrainerFormModal',
                base_endpoint: '/person',
                confirmLabel: 'Guardar',
                resource: 'person',
                title: '',
                action: null,
                persistent: true,
            },
            new_value: 0,
            //Jarvis
            loading_description: false,
            limits_descriptions_generate_ia: {
                ia_descriptions_generated: 0,
                limit_descriptions_jarvis: 0
            },
            people: {
                legal_representatives: [],
                instructors: []
            },
            //Permissions
            showButtonIaGenerate: false,
            has_DC3_functionality: false,
            current_modality: {},
            catalog_denominations: [],
            //Courses in person
            //maps
            center: { lat: -12.0529046, lng: -77.0253457 },
            zoom: 16,
            currentPlace: null,
            markers: [{
                position: { lat: -12.0529046, lng: -77.0253457 }
            }],
            ubicacion_mapa: null,
            registro_capacitacion_trainers: [],
            catalog_denominations: []
        }
    },
    async mounted() {
        this.loadLimitsGenerateIaDescriptions();
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
        },
    },
    watch: {
        'resource.qualification_type': function (newValue, oldValue) {
            let vue = this
            let value = vue.resource.nota_aprobatoria

            if (newValue) {
                if (value && newValue.position && oldValue.position) {

                    vue.new_value = value * newValue.position / oldValue.position
                    vue.resource.nota_aprobatoria = parseFloat(vue.new_value.toFixed(2))
                }
            }

        },
    },
    methods: {

        resetValidation() {
            let vue = this
            vue.resetFormValidation('CursoForm')
        },
        resetSelects() {
            let vue = this
            // Limpiar inputs file
            vue.removeFileFromDropzone(vue.resource.imagen, 'inputLogo')
            // Selects independientes
            // Selects dependientes
            // vue.resource = Object.assign({}, {})
        },

        alertNotaMinima() {
            let vue = this
            vue.alertConfirmationDialog.open = true
        },
        confirmValidationModal(validateForm = true) {
            let vue = this
            vue.alertConfirmationDialog.open = false
            vue.conf_focus = false
        },
        closeModal() {
            let vue = this
            vue.resetSelects()
            vue.$emit('onCancel')
        },
        closeModalStatusEdit() {
            let vue = this
            vue.courseUpdateStatusModal.open = false
            vue.resource.active = !vue.resource.active
        },
        modalStatusEdit() {
            let vue = this
            const edit = (vue.resource && vue.resource.id)
            if (edit) {
                vue.courseUpdateStatusModal.open = true
                vue.courseUpdateStatusModal.status_item_modal = !vue.resource.active
            }
        },
        confirmModal(validateForm = true) {

            this.showLoader()

            // Get datetimes values
            if (this.resource.publish_date_1) {
                let time1 = this.resource.publish_time_1 || '00:01';
                this.resource.activate_at = `${this.resource.publish_date_1} ${time1}`
            } else {
                this.resource.activate_at = null
            }

            if (this.resource.publish_date_2) {
                let time2 = this.resource.publish_time_2 || '00:01';
                this.resource.deactivate_at = `${this.resource.publish_date_2} ${time2}`
            } else {
                this.resource.deactivate_at = null
            }

            let vue = this
            vue.errors = []
            vue.loadingActionBtn = true
            // vue.showLoader()
            const validForm = vue.validateForm('CursoForm')

            if (!validForm || !vue.isValid()) {
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

            const edit = (vue.resource && vue.resource.id)
            let url = `${vue.base_endpoint}/${edit ? `update/${vue.resource.id}` : 'store'}`
            let method = edit ? 'PUT' : 'POST';

            const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
            formData.set(
                'dc3_configuration', JSON.stringify(vue.resource.dc3_configuration)
            );
            formData.set(
                'modality_in_person_properties', JSON.stringify(vue.resource.modality_in_person_properties)
            );
            formData.set(
                'registro_capacitacion', JSON.stringify(vue.resource.registro_capacitacion)
            );
            formData.append('validateForm', validateForm ? "1" : "0");
            vue.setJSONReinicioProgramado(formData)
            vue.getJSONEvaluaciones(formData)

            vue.$http.post(url, formData)
                .then(async ({ data }) => {
                    this.hideLoader()
                    const has_info_messages = data.data.messages.list.length > 0

                    // Additional validation is required when course
                    // in being disabled

                    if (has_info_messages && !this.resource.active)
                        await vue.handleValidationsAfterUpdate(data.data, vue.courseValidationModal, vue.courseValidationModalDefault);
                    else {
                        vue.queryStatus("curso", "crear_curso");
                        vue.showAlert(data.data.msg)
                        // setTimeout(() => vue.closeModal(), 2000)
                        vue.closeModal()
                        vue.$emit('onConfirm')
                    }
                })
                .catch(error => {
                    if (error && error.errors) {
                        vue.errors = error.errors

                        if (error.data.validations.list) {
                            error.data.validations.list.forEach(element => {
                                if (element.type == "has_active_topics" && error.data.validations.list.length == 1) {
                                    vue.courseValidationModal.title_modal = 'Cambio de estado de un <b>curso</b>';
                                    vue.courseValidationModal.content_modal.requirement.title = '¡Estás por desactivar un curso!';
                                }
                            });
                        }
                    }
                    vue.handleValidationsBeforeUpdate(error, vue.courseValidationModal, vue.courseValidationModalDefault);
                    vue.loadingActionBtn = false

                    if (e.response.data.msg) {
                        vue.showAlert(e.response.data.msg, 'warning')
                    }
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
        getJSONEvaluaciones(formData) {
            let vue = this

            const data = {
                // preg_x_ev: vue.resource.preg_x_ev,
                nota_aprobatoria: vue.resource.nota_aprobatoria,
                nro_intentos: vue.resource.nro_intentos,
            }
            let json = JSON.stringify(data)
            formData.append('mod_evaluaciones', json)
        },
        async generateIaDescription() {
            const vue = this;
            let url = `/jarvis/generate-description-jarvis`;
            if (vue.loading_description || !vue.resource.name) {
                const message = vue.loading_description ? 'Se está generando la descripción, espere un momento' : 'Es necesario colocar un nombre al curso para poder generar la descripción';
                vue.showAlert(message, 'warning', '')
                return ''
            }
            if (vue.limits_descriptions_generate_ia.ia_descriptions_generated >= vue.limits_descriptions_generate_ia.limit_descriptions_jarvis) {
                vue.showAlert('Ha sobrepasado el limite para poder generar descripciones con IA', 'warning', '')
                return ''
            }
            vue.loading_description = true;
            await axios.post(url, {
                name: vue.resource.name,
                type: 'course'
            }).then(({ data }) => {
                vue.limits_descriptions_generate_ia.ia_descriptions_generated += 1;
                let characters = data.data.description.split('');
                vue.resource.description = ''; // Limpiar el contenido anterior
                function updateDescription(index) {
                    if (index < characters.length) {
                        vue.resource.description += characters[index];
                        setTimeout(() => {
                            updateDescription(index + 1);
                        }, 10);
                    } else {
                        vue.loading_description = false;
                    }
                }
                updateDescription(0);
            }).catch(() => {
                vue.loading_description = false;
            })
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            // let url = `${vue.base_endpoint}/${vue.resource.id === '' ? 'form-selects' : `search/${vue.resource.id}`}`
            // let url = `${vue.base_endpoint}/${!resource ? 'form-selects' : `search/${resource.id}`}`
            let url = vue.base_endpoint;
            url += (resource ? `/search/${resource.id}` : '/form-selects');

            await vue.$http.get(url)
                .then(({ data }) => {
                    let response = data.data ? data.data : data;

                    vue.selects.requisito_id = response.requisitos

                    vue.selects.qualification_types = response.qualification_types
                    vue.selects.lista_escuelas = response.escuelas
                    vue.selects.modalities = response.modalities;
                    vue.selects.types = response.types
                    vue.showButtonIaGenerate = response.show_buttom_ia_description_generate;

                    vue.people.instructors = response.instructors;
                    vue.people.legal_representatives = response.legal_representatives;
                    vue.catalog_denominations = response.catalog_denominations;
                    vue.has_DC3_functionality = response.has_DC3_functionality;
                    vue.registro_capacitacion_trainers = response.registro_capacitacion_trainers;
                    vue.has_registro_capacitacion_functionality = response.has_registro_capacitacion_functionality;

                    if (resource && resource.id) {
                        response.curso.nota_aprobatoria = response.curso.mod_evaluaciones.nota_aprobatoria;
                        response.curso.nro_intentos = response.curso.mod_evaluaciones.nro_intentos;

                        vue.resource = Object.assign({}, response.curso)

                        // Set schedule datetime

                        if (response.curso.activate_at) {
                            vue.resource.publish_date_1 = response.curso.activate_at.substring(0, 10);
                            vue.resource.publish_time_1 = response.curso.activate_at.substring(11, 16);
                        }

                        if (response.curso.deactivate_at) {
                            vue.resource.publish_date_2 = response.curso.deactivate_at.substring(0, 10);
                            vue.resource.publish_time_2 = response.curso.deactivate_at.substring(11, 16);
                        }

                    } else {
                        vue.resource.qualification_type = response.qualification_type
                        vue.resource.modality_id = vue.options.modality.id;
                        if (vue.school_id) {

                            const found = vue.selects.lista_escuelas.find((element) => element.id == vue.school_id);

                            if (found) {
                                vue.resource.lista_escuelas.push(found);
                            }
                        }
                    }
                    vue.current_modality = vue.selects.modalities.find(m => m.id == vue.resource.modality_id);
                })
            return 0;
        },
        isValid() {

            let valid = true;
            let errors = [];

            // Validation: At least one school should be selected

            if (this.resource.lista_escuelas.length === 0) {
                errors.push({
                    message: 'Debe seleccionar una escuela'
                })
                valid = false;
            }

            if (!valid) {
                this.errors = errors;
            }

            return valid;
        },
        loadSelects() {
            let vue = this
        },
        async loadLimitsGenerateIaDescriptions() {
            await axios.get('/jarvis/limits?type=descriptions').then(({ data }) => {
                this.limits_descriptions_generate_ia = data.data;
            })
        },
        setPersonDC3(person) {
            this.modalDC3PersonOptions.open = false;
            if (person.type == 'dc3-instructor') {
                this.people.instructors.push(person);
                this.resource.dc3_configuration.instructor = person.id;
                return 0;
            }
            this.people.legal_representatives.push(person);
            this.resource.dc3_configuration.legal_representative = person.id;
        },
        setTrainer(item) {
            this.modalRegistroTrainerOptions.open = false;
            this.registro_capacitacion_trainers.push(item);
            this.resource.registro_capacitacion.trainerAndRegistrar = item.id;
        },
        confirmTrainerDelete() {

            const vue = this;
            this.trainerDeleteConfirmationDialog.open = false
            const trainerId = this.resource.registro_capacitacion.trainerAndRegistrar;

            let url = `/registrotrainer/${trainerId}/destroy`;
            vue.$http.delete(url)
                .then(async ({ data }) => {

                    vue.registro_capacitacion_trainers = vue.registro_capacitacion_trainers.filter(t => {
                        return t.id != trainerId
                    })
                })
        }
    }
}
</script>
<style lang="scss">
@import "resources/sass/variables";

.time-input .v-input__slot {
    min-height: 40px !important;
}

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

.sep-left {
    position: relative;
}

.sep-left:before {
    border-left: 1px solid #D9D9D9;
    position: absolute;
    left: 0;
    content: '';
    top: 25px;
    bottom: 25px;
}
</style>
