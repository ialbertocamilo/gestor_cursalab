

<template>
    <v-dialog :max-width="width" v-model="value" scrollable @click:outside="closeModal">
        <v-card>
            <v-card-title class="default-dialog-title">
                {{ checklist.id == 0 ? "Crear Checklist" : "Editar Checklist" }}
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-0">
                <v-stepper non-linear class="stepper_box" v-model="stepper_box">
                    <v-stepper-items>
                        <v-stepper-content step="1" class="p-0">
                                <v-card style="height: 100%;overflow: auto;" class="bx_steps bx_step1">
                                    <v-card-text>
                                        <v-row>
                                            <v-col cols="7">
                                                <DefaultInput 
                                                    dense
                                                    clearable
                                                    v-model="resource.title"
                                                    label="Título de checklist"
                                                    placeholder="Escribe el titulo del checklist aquí"
                                                />
                                            </v-col>
                                            <v-col cols="5">
                                                <DefaultAutocomplete
                                                    dense
                                                    label="Seleccione el tipo de checklist"
                                                    placeholder="Tipo de checklist"
                                                    v-model="resource.type_checklist"
                                                    :items="selects.type_checklist"
                                                    item-text="name"
                                                    item-value="id"
                                                    @onChange="changeTypeChecklist"
                                                />
                                            </v-col>
                                            <v-col cols="7">
                                                <DefaultRichText
                                                    clearable
                                                    :height="300"
                                                    v-model="resource.description"
                                                    label="Descripción u objetivo"
                                                    :rules="rules.content"
                                                    :ignoreHTMLinLengthCalculation="true"
                                                    :showGenerateIaDescription="showButtonIaGenerate"
                                                    :key="`${showButtonIaGenerate}-editor`"
                                                    :limits_descriptions_generate_ia:="limits_descriptions_generate_ia"
                                                    :loading="loading_description"
                                                    ref="descriptionRichText"
                                                    @generateIaDescription="generateIaDescription"
                                                />
                                            </v-col>
                                            <v-col cols="5">
                                                <DefaultSelectOrUploadMultimedia ref="inputLogo" v-model="resource.image"
                                                    label="Imagen (500x350px)" :file-types="['image']"
                                                    @onSelect="setFile($event, resource, 'imagen')" select-width="60vw" select-height="100%" />
                                            </v-col>
                                            <v-col cols="6">
                                                <DefaultSimpleSection title="Comentarios" marginy="my-1" marginx="mx-0">
                                                    <template slot="content">
                                                        <div class="d-flex">
                                                            <DefaultToggle class="ml-4 mb-2"
                                                                v-model="resource.extra_attributes.required_comments" dense
                                                                :active-label="'Activar comentarios dentro de las actividades del checklist'"
                                                                :inactive-label="'Activar comentarios dentro de las actividades del checklist'" />
                                                            <DefaultInfoTooltip
                                                                text="Se agregarán comentarios a las actividades del checklist que puede brindar al supervisor"
                                                                top
                                                            />
                                                        </div>
                                                    </template>
                                                </DefaultSimpleSection>
                                            </v-col>
                                            <v-col cols="6">
                                                <DefaultSimpleSection title="Geolocalización" marginy="my-1" marginx="mx-0">
                                                    <template slot="content">
                                                        <div class="d-flex">
                                                            <DefaultToggle class="ml-4 mb-2"
                                                                v-model="resource.extra_attributes.required_geolocation" dense
                                                                :active-label="'Activar geolocalización'"
                                                                :inactive-label="'Activar geolocalización'" />
                                                            <DefaultInfoTooltip
                                                                text="Solo se podrá realizar actividades si se encuentra ubicado en su centro laboral asignado"
                                                                top
                                                            />    
                                                        </div>
                                                    </template>
                                                </DefaultSimpleSection>
                                            </v-col>
                                        </v-row>
                                        <v-row justify="space-around" class="menuable">
                                            <v-col cols="12">
                                                <DefaultModalSectionExpand title="Configuración avanzada"
                                                    :expand="sections.showSectionAdvancedconfiguration" :simple="true">
                                                    <template slot="content">
                                                        <v-row justify="center">
                                                            <v-col cols="6">
                                                                <DefaultSimpleSection title="Escalas de evaluación" marginy="my-1 px-2 pb-4" marginx="mx-0">
                                                                    <template slot="content">
                                                                        <div class="d-flex justify-space-between" style="color:#5458EA">
                                                                            <p>Define las escalas de evaluación</p>
                                                                            <div>
                                                                                <i class="mdi mdi-account-multiple-check"></i>
                                                                                <span>Max: {{resource.evaluation_types.length}}/5</span>
                                                                            </div>
                                                                        </div>
                                                                        <draggable v-model="resource.evaluation_types" @start="drag_evaluation_type=true"
                                                                                @end="drag_evaluation_type=false" class="custom-draggable" ghost-class="ghost">
                                                                            <transition-group type="transition" name="flip-list" tag="div">
                                                                                <div v-for="(evaluation_type) in resource.evaluation_types"
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
                                                                                                    v-model="evaluation_type.percent"
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
                                                                                :disabled="resource.evaluation_types.length >= 5"
                                                                                @click="addScaleEvaluation()"
                                                                            />
                                                                        </div>
                                                                    </template>
                                                                </DefaultSimpleSection>
                                                            </v-col>
                                                            <v-col cols="6">
                                                                <v-col cols="12" class="pt-1">
                                                                    <DefaultSimpleSection title="Escalas de evaluación" marginy="my-1 px-2 py-4" marginx="mx-0">
                                                                        <template slot="content">
                                                                            <DefaultSelect dense :items="selects.qualification_types" item-text="name"
                                                                                show-required v-model="resource.extra_attributes.qualification_type"
                                                                                label="Sistema de calificación" :rules="rules.qualification_type_id" />
                                                                        </template>
                                                                    </DefaultSimpleSection>
                                                                </v-col>
                                                                <v-col cols="12">
                                                                    <DefaultSimpleSection title="Visualización" marginy="my-1" marginx="mx-0">
                                                                        <template slot="content">
                                                                            <div class="d-flex">
                                                                                <DefaultToggle class="ml-4 mb-2"
                                                                                    v-model="resource.extra_attributes.visualiazation_results" dense
                                                                                    :active-label="'Permite que al finalizar el checklist la entidad evaluada visualice sus resultados.'"
                                                                                    :inactive-label="'Permite que al finalizar el checklist la entidad evaluada visualice sus resultados.'" />
                                                                                <DefaultInfoTooltip
                                                                                    text="Solo se podrá realizar actividades si se encuentra ubicado en su centro laboral asignado"
                                                                                    top
                                                                                />    
                                                                            </div>
                                                                        </template>
                                                                    </DefaultSimpleSection>
                                                                </v-col>
                                                                <v-col cols="12">
                                                                    <DefaultSimpleSection title="Modo Recurrente" marginy="my-1" marginx="mx-0">
                                                                        <template slot="content">
                                                                            <div class="d-flex">
                                                                                <DefaultToggle class="ml-4 mb-2"
                                                                                    v-model="resource.extra_attributes.period_checklist" dense
                                                                                    :active-label="'Este checklist se replicará.'"
                                                                                    :inactive-label="'Este checklist se replicará.'" />
                                                                                <DefaultInfoTooltip
                                                                                    text="Solo se podrá realizar actividades si se encuentra ubicado en su centro laboral asignado"
                                                                                    top
                                                                                />    
                                                                            </div>
                                                                        </template>
                                                                    </DefaultSimpleSection>
                                                                </v-col>
                                                            </v-col>
                                                        </v-row>
                                                    </template>
                                                </DefaultModalSectionExpand>
                                            </v-col>
                                        </v-row>
                                    </v-card-text>
                                </v-card>
                        </v-stepper-content>
                        <v-stepper-content step="2" class="p-0">
                                <v-card style="height: 100%;overflow: auto;" class="bx_steps bx_step2">
                                    <v-card-text>
                                        <v-row class="px-8">
                                            <v-col cols="12">
                                                <b> <h5>Crea el listado de actividades para tu checklist</h5> </b>
                                                <p>
                                                    Tener en cuenta que al agregar o quitar actividades a un checklist completado por un usuario no tendrá efectos en su avance. 
                                                    Si un usuario ya completó un checklist se mantiene su estado y porcentaje, pero sí se actualiza para usuarios que aún no completan el checklist cuando el entrenador lo califique.
                                                </p>
                                            </v-col>
                                        </v-row>
                                        <v-carousel
                                            :continuous="false"
                                            :cycle="false"
                                            :show-arrows="false"
                                            hide-delimiter-background
                                            delimiter-icon="custom-control-buttom"
                                            height="390px"
                                        >
                                            <v-carousel-item>
                                                <v-row class="px-12">
                                                    <h5 style="color: #2A3649;" class="col col-12 py-0 my-0">¿Cómo deseas calificar tu checklist?</h5>
                                                    <p style="color: #2A3649;" class="col col-12 py-0 my-0">Escoge una opción</p>
                                                    <v-col cols="4" v-for="card in checklist_actions" :key="card.id">
                                                        <DefaultCardAction :card_properties="card" />
                                                    </v-col>
                                                </v-row>
                                            </v-carousel-item>
                                            <v-carousel-item>
                                                <v-row class="px-12">
                                                    <h5 style="color: #2A3649;" class="col col-12 py-0 my-0">¿Cómo deseas empezar con tu checklist?</h5>
                                                    <p style="color: #2A3649;" class="col col-12 py-0 my-0">Escoge una opción</p>
                                                    <v-col cols="4" v-for="card in checklist_actions_to_create" :key="card.id">
                                                        <DefaultCardAction :card_properties="card" />
                                                    </v-col>
                                                </v-row>
                                            </v-carousel-item>
                                        </v-carousel>
                                        
                                        <!-- checklist libre (segmentacion) -->
                                        <!-- <div v-if="checklist.type_checklist === 'libre'">
                                            <v-row>
                                                <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                                    <span class="text_default lbl_tit">Relaciona el checklist a los usuarios según criterio o doc. de identidad.</span>
                                                </v-col>
                                                <v-col cols="12" class="pb-0 pt-0">
                                                    <SegmentFormModal
                                                        :options="modalFormSegmentationOptions"
                                                        :list_segments="checklist.segments"
                                                        :list_segments_document="checklist.segmentation_by_document"
                                                        width="55vw"
                                                        model_type="App\Models\Checklist"
                                                        :model_id="null"
                                                        ref="modalFormSegmentationOptions"
                                                        @onCancel="closeSimpleModal(modalFormSegmentationOptions)"
                                                        @onConfirm="closeFormModal(modalFormSegmentationOptions, dataTable, filters)"
                                                        @disabledBtnModal="disabledBtnModal"
                                                    />
                                                </v-col>
                                            </v-row>
                                        </div> -->
                                        <!-- checklist libre (segmentacion) -->
                                        <!-- checklist curso -->
                                        <!-- <div v-else-if="checklist.type_checklist === 'curso'">
                                            <v-row>
                                                <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                                    <span class="text_default lbl_tit">Relaciona el checklist a los usuarios según el o los cursos.</span>
                                                </v-col>
                                            </v-row>
                                            <v-row>
                                                <v-col cols="12" md="12" lg="12" class="pb-0 pt-2">
                                                    <span class="title_sub">Selección de cursos</span>
                                                </v-col>
                                            </v-row>
                                            <v-row class="d-flex justify-content-between pb-0">
                                                <v-col cols="8" md="8" lg="8" class="pb-0 pt-2">
                                                    <v-text-field
                                                        outlined
                                                        dense
                                                        hide-details
                                                        label="Nombre de curso o escuela"
                                                        v-model="search_text"
                                                        @keyup="search"
                                                        clearable
                                                        append-icon="mdi-magnify"
                                                        autocomplete="off"
                                                        :loading="isLoading"
                                                    ></v-text-field>
                                                </v-col>
                                                <v-col cols="4" md="4" lg="4" class="d-flex justify-content-end bx_options_select pb-0 pt-2">
                                                    <span class="text_default">{{checklist.courses.length || 0}} seleccionados</span>
                                                </v-col>
                                            </v-row>
                                            <v-row class="pb-0">
                                                <v-col cols="12" md="12" lg="12" class="pb-0 pt-2">
                                                    <div class="box_resultados">
                                                        <div class="bx_message" v-if="results_search.length == 0">
                                                            <span class="text_default">{{ isLoading ? 'Espere...' : 'Resultados de búsqueda'}}</span>
                                                        </div>
                                                        <ul v-else>
                                                            <li v-for="(curso, index) in results_search" :key="curso.id" class="d-flex align-center justify-content-between">
                                                                <span class="text_default">{{ curso.escuela }} - {{ curso.curso }}</span>
                                                                <v-btn small icon :ripple="false" @click="seleccionarCurso(curso, index)" :disabled="disabled_add_courses">
                                                                    <v-icon class="icon_size" small color="black"
                                                                            style="font-size: 1.25rem !important;">
                                                                        mdi-plus-circle
                                                                    </v-icon>
                                                                </v-btn>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </v-col>
                                            </v-row>
                                            <v-row class="pb-0">
                                                <v-col cols="12" md="12" lg="12" class="pb-0 pt-2">
                                                    <div class="box_seleccionados">
                                                        <div class="bx_message" v-if="checklist.courses.length == 0">
                                                            <span class="text_default">Seleccionados</span>
                                                        </div>
                                                        <ul v-else>
                                                            <li v-for="(curso, index) in checklist.courses" :key="curso.id" class="d-flex align-center justify-content-between">
                                                                <span class="text_default">{{ curso.escuela }} - {{ curso.curso }}</span>
                                                                <v-btn small icon :ripple="false" @click="removeCurso(curso,index)">
                                                                    <v-icon class="icon_size" small color="black"
                                                                            style="font-size: 1.25rem !important;">
                                                                        mdi-minus-circle
                                                                    </v-icon>
                                                                </v-btn>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </v-col>
                                            </v-row>

                                        </div> -->
                                        <!-- checklist curso -->

                                    </v-card-text>
                                </v-card>
                        </v-stepper-content>
                        <v-stepper-content step="3" class="p-0">
                                <v-card style="height: 100%;overflow: auto;" class="bx_steps bx_step3">
                                    <v-card-text>
                                        <v-row>
                                            <v-col cols="8">
                                                <span>Crea el listado de actividades para tu checklist</span>
                                                <br>
                                                <span>
                                                    Tener en cuenta que al agregar o quitar actividades a un checklist completado por un usuario no tendrá efectos en su avance. 
                                                    Si un usuario ya completó un checklist se mantiene su estado y porcentaje, 
                                                    pero sí se actualiza para usuarios que aún no completan el checklist cuando el entrenador lo califique.
                                                </span>
                                            </v-col>
                                            <v-col cols="4">
                                                <DefaultSelect 
                                                    :items="checklist_actions" 
                                                    dense 
                                                    item-text="name"
                                                    return-object show-required v-model="resource.qualification_type"
                                                    label="Calificar usuario"
                                                    disabled
                                                />
                                                <br>
                                                <DefaultButton
                                                    rounded
                                                    outlined
                                                    label="Subida masiva de actividades"
                                                    icon="mdi mdi-plus"
                                                />
                                            </v-col>
                                        </v-row>
                                        <v-row>
                                            <v-col cols="1" class="d-flex align-center justify-content-center ">
                                                <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                </v-icon>
                                            </v-col>
                                            <v-row class="col-11">
                                                <v-col cols="12">
                                                    <DefaultRichText
                                                        clearable
                                                        :height="150"
                                                        v-model="resource.description"
                                                        label="Actividad de checklist"
                                                        :ignoreHTMLinLengthCalculation="true"
                                                        :key="`${showButtonIaGenerate}-editor`"
                                                        ref="descriptionRichText1"
                                                    />
                                                </v-col>
                                                <v-col cols="4">
                                                    <DefaultSelect 
                                                        :items="checklist_type_response" 
                                                        dense 
                                                        item-text="name"
                                                        show-required v-model="resource.checklist_response"
                                                        label="Tipo de respuesta"
                                                    />
                                                </v-col>
                                                <v-col cols="4" class="d-flex align-items-center">
                                                    <v-checkbox
                                                        class="my-0 mr-2 checkbox-label"
                                                        label="Se usará foto como respuesta"
                                                        color="primary"
                                                        v-model="resource.photo_response"
                                                        hide-details="false"
                                                    />
                                                </v-col>
                                                <v-col cols="4" class="d-flex align-items-center">
                                                    <v-checkbox
                                                        class="my-0 mr-2 checkbox-label"
                                                        label="Aplicar visión computacional"
                                                        color="primary"
                                                        v-model="resource.computational_vision"
                                                        hide-details="false"
                                                    />
                                                </v-col>
                                            </v-row>
                                        </v-row>
                                        <!-- <v-row>
                                            <v-col cols="12" md="12" lg="12" class="pb-0">
                                                <span class="text_default">Actividades</span>
                                            </v-col>
                                            <v-col cols="12" class="py-0 separated">
                                                <DefaultDivider class="divider_light"/>
                                            </v-col>
                                            <v-col cols="12" md="12" lg="12" class="text-center pt-0">
                                                <div class="text_default box_info_checklist_1 mt-3">
                                                    Tener en cuenta que al agregar o quitar actividades a un checklist completado por un usuario no tendrá efectos en su avance. Si un usuario ya completó un checklist se mantiene su estado y porcentaje, pero sí se actualiza para usuarios que aún no completan el checklist.
                                                </div>
                                            </v-col>
                                            <v-col cols="12" md="12" lg="12" class="d-flex justify-content-center">
                                                <v-btn color="primary" outlined @click="addActividad">
                                                    <v-icon class="icon_size">mdi-plus</v-icon>
                                                    Agregar Actividad
                                                </v-btn>
                                            </v-col>
                                        </v-row>

                                        <v-row>
                                            <v-col cols="12" md="12" lg="12">
                                                    <draggable v-model="checklist.checklist_actividades" @start="drag=true"
                                                            @end="drag=false" class="custom-draggable" ghost-class="ghost">
                                                        <transition-group type="transition" name="flip-list" tag="div">
                                                            <div v-for="(actividad, i) in checklist.checklist_actividades"
                                                                :key="actividad.id">
                                                                <div class="item-draggable activities">
                                                                    <v-row>
                                                                        <v-col cols="1" class="d-flex align-center justify-content-center ">
                                                                            <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                                            </v-icon>
                                                                        </v-col>
                                                                        <v-col cols="12" md="5" lg="5">
                                                                            <v-textarea
                                                                                rows="1"
                                                                                outlined
                                                                                dense
                                                                                auto-grow
                                                                                hide-details="auto"
                                                                                v-model="actividad.activity"
                                                                                :class="{'border-error': actividad.hasErrors}"
                                                                            ></v-textarea>
                                                                        </v-col>
                                                                        <v-col cols="12" md="3" lg="3" class="d-flex align-center no-white-space">
                                                                            <v-select
                                                                                outlined
                                                                                dense
                                                                                hide-details="auto"
                                                                                attach
                                                                                :items="tipo_actividades"
                                                                                v-model="actividad.type_name"
                                                                                item-text="text"
                                                                                item-value="value"
                                                                            >
                                                                            </v-select>
                                                                        </v-col>
                                                                        <v-col cols="12" md="2" lg="2" class="d-flex align-center toggle_text_default">
                                                                            <DefaultToggle v-model="actividad.active"/>
                                                                        </v-col>
                                                                        <v-col cols="1" class="d-flex align-center">
                                                                            <v-icon class="ml-0 mr-2 icon_size" color="black"
                                                                                    @click="eliminarActividad(actividad, i)">
                                                                                mdi-delete
                                                                            </v-icon>
                                                                        </v-col>
                                                                    </v-row>
                                                                </div>
                                                            </div>
                                                        </transition-group>
                                                    </draggable>
                                            </v-col>
                                        </v-row> -->
                                    </v-card-text>
                                </v-card>
                        </v-stepper-content>
                    </v-stepper-items>
                    <v-stepper-header class="stepper_dots">
                        <v-stepper-step step="1" :complete="stepper_box > 1">
                            <v-divider></v-divider>
                        </v-stepper-step>
                        <v-stepper-step step="2" :complete="stepper_box > 2">
                            <v-divider></v-divider>
                        </v-stepper-step>
                        <v-stepper-step step="3">
                        </v-stepper-step>
                    </v-stepper-header>
                </v-stepper>

            </v-card-text>

            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)" class="actions_btn_modal">
                <ButtonsModal
                    @cancel="prevStep"
                    @confirm="nextStep"
                    :cancelLabel="cancelLabel"
                    confirmLabel="Continuar"
                />
                    <!-- :disabled_next="disabled_btn_next" -->
            </v-card-actions>
        </v-card>
        <DefaultAlertDialog
            :ref="modalAlert.ref"
            :options="modalAlert"
            :confirmLabel="modalAlert.confirmLabel"
            :hideCancelBtn="modalAlert.hideCancelBtn"
            @onConfirm ="modalAlert.open=false"
            @onCancel ="modalAlert.open=false"
        >
            <template v-slot:content> {{ modalAlert.contentText }}</template>
        </DefaultAlertDialog>
    </v-dialog>
</template>


<script>
import draggable from 'vuedraggable'
import SegmentFormModal from "./Blocks/SegmentFormModal";
import ButtonsModal from './Blocks/ButtonsModal';
import DefaultRichText from "../../globals/DefaultRichText";
import DefaultCardAction from "../../globals/DefaultCardAction"
export default {
    components: {
    draggable,
    SegmentFormModal,
    ButtonsModal,
    DefaultRichText,
    DefaultCardAction
},
    props: {
        value: Boolean,
        width: String,
        checklist: Object,
        limitOne: {
            type:Boolean,
            default:false
        },
        tabs_title:{
            type: String,
            default: 'Segmentación'
        },
    },
    data() {
        return {
            sections:{
                showSectionAdvancedconfiguration:{ status: true },
            },
            checklist_actions :[
                {id:1,icon:'mdi mdi-home-city',code:'calificate_entity',name:'Calificar entidad',description:'Con este tipo de checklist se revisará a la entidad (tienda, oficina,etc)',color:'#57BFE3'},
                {id:2,icon:'mdi mdi-clipboard-account',code:'calificate_user',name:'Calificar al usuario',description:'El supervisor podrá evaluar a personalmente a cada uno de los usuarios asignados',color:'#CE98FE'},
                {id:3,icon:'mdi mdi-account-multiple-check',code:'autocalificate',name:'Autoevaluación',description:'Sube actividades para guiar a tus usuarios en sus primeros pasos.',color:'#547AE3'},
            ],
            checklist_actions_to_create:[
                {id:1,icon:'mdi mdi-file-edit',code:'create_activities',name:'Crear actividades',description:'Crea las actividades de tu checklist desde cero tu mismo',color:'#F5539B'},
                {id:2,icon:'mdi mdi-file-upload',code:'import_activities',name:'Importar actividades',description:'Sube tus actividades con una plantilla de excel',color:'#5357E0'},
                {id:3,image:'/img/robot_jarvis.png',code:'ia_activities',name:'Ayuda con IA',description:'Sube actividades para guiar a tus usuarios en sus primeros pasos.',color:'#9B98FE'},
            ],
            checklist_type_response:[
                {id:1,name:'Por escala de ev.'},
                {id:2,name:'Selecciona'},
                {id:3,name:'Desplegable'},
            ],
            disabled_btn_next: true,
            stepper_box_btn1: true,
            stepper_box_btn2: true,
            stepper_box_btn3: false,
            stepper_box: 1,
            cancelLabel: "Cancelar",
            list_segments:[],
            modalDateStart: {
                open: false,
            },
            modalDateEnd:{
                open: false,
            },
            drag: false,
            drag_evaluation_type: false,
            expand_cursos: true,
            actividades_expanded: [],
            tipo_actividades: [
                {
                    text: "Se califica al alumno",
                    value: "trainer_user"
                },
                {
                    text: "Se califica al entrenador",
                    value: "user_trainer"
                }
            ],
            dialog: false,
            file: null,
            search_text: null,
            results_search: [],
            disabled_add_courses: false,
            isLoading: false,
            messageActividadesEmpty: false,
            formRules: {
                titulo_descripcion: [
                    v => !!v || 'Campo requerido',
                    v => (v && v.length <= 280) || 'Máximo 280 caracteres.',
                ],
                actividad: [
                    v => !!v || 'Campo requerido',
                ],
            },
            modalAlert: {
                ref: 'modalAlert',
                title: 'Alerta',
                contentText: 'Este checklist debe de tener por lo menos una (1) actividad "Se califica al alumno"',
                open: false,
                endpoint: '',
                confirmLabel:"Entendido",
                hideCancelBtn:true,
            },
            selects: {
                type_checklist: [
                    {"id": "libre", "name": "Libre"},
                    {"id": "curso", "name": "Por curso"},
                ],
                qualification_types:[

                ]
            },
            type_checklist: "libre",
            // data segmentacion
            modalFormSegmentationOptions: {
                ref: 'SegmentFormModal',
                open: false,
                persistent: true,
                base_endpoint: '/segments',
                confirmLabel: 'Guardar',
                resource: 'segmentación',
            },
            // data segmentacion
            tabs: null,
            steps: 0,
            // total: 0,
            total: [],

            errors: [],
            showConfigTokens: false,
            resourceDefault: {
                id: null,
                name: null,
                extra_attributes :{},
                type_checklist: null,
                evaluation_types:[
                    {id:1,name:'Cumple',color:'#00E396',percent:100},
                    {id:3,name:'En proceso',color:'#FFB700',percent:50},
                    {id:2,name:'No cumple',color:'#FF4560',percent:0},
                ],
                photo_response:false,
                computational_vision:false
            },
            resource: {
                extra_attributes:{},
                evaluation_types:[
                    {id:1,name:'Cumple',color:'#00E396',percent:'100'},
                    {id:3,name:'En proceso',color:'#FFB700',percent:'50'},
                    {id:2,name:'No cumple',color:'#FF4560',percent:'0'},
                ],
                photo_response:false,
                computational_vision:false
            },
            segments: [{
                id: `new-segment-${Date.now()}`,
                type_code: '',
                criteria_selected: []
            }],
            segment_by_document: {
                id: `new-segment-${Date.now()}`,
                type_code: '',
                criteria_selected: []
            },
            criteria: [],
            courseModules: [],
            modulesSchools: [],

            modalInfoOptions: {
                ref: 'SegmentAlertModal',
                open: false,
                title: null,
                resource:'data',
                hideConfirmBtn: true,
                persistent: true,
                cancelLabel: 'Entendido'
            },
            stackModals: { continues: [],
                backers: [] },
            criteriaIndexModal: 0,

            segment_by_document_clean: false,
            rules: {
                // name: this.getRules(['required', 'max:255']),
            },
           //Jarvis
            loading_description: false,
            limits_descriptions_generate_ia: {
                ia_descriptions_generated: 0,
                limit_descriptions_jarvis: 0
            },
            showButtonIaGenerate: true,
            // data segmenteacion
        };
    },
    computed: {
        actividadesEmpty() {
            return this.checklist.checklist_actividades && this.checklist.checklist_actividades.length === 0
        }
    },
    async mounted() {
        let vue = this;
        // this.addActividad()
        await vue.loadLimitsGenerateIaDescriptions();
        await vue.loadFormSelects();
    },
    watch: {
        checklist: {
            handler(n, o) {
                let vue = this;

                if(vue.stepper_box == 1) {
                    vue.stepper_box_btn1 = !(vue.validateRequired(vue.checklist.type_checklist) && vue.validateRequired(vue.checklist.title) && vue.validateRequired(vue.checklist.description));
                    vue.disabled_btn_next = vue.stepper_box_btn1;
                }
                else if(vue.stepper_box == 2){
                    vue.disabledBtnModal()
                    vue.disabled_btn_next = vue.stepper_box_btn2;
                }
                else if(vue.stepper_box == 3){
                    let errors = vue.showValidateActividades()
                    vue.stepper_box_btn3 = vue.checklist.checklist_actividades.length == 0 || errors > 0
                    vue.disabled_btn_next = vue.stepper_box_btn3;
                }
            },
            deep: true
        },
        stepper_box: {
            handler(n, o) {
                let vue = this;

                if(vue.stepper_box == 1) {
                    if(vue.validateRequired(vue.checklist.type_checklist) && vue.validateRequired(vue.checklist.title) && vue.validateRequired(vue.checklist.description)) {
                        vue.stepper_box_btn1 = false;
                    }
                    vue.disabled_btn_next = vue.stepper_box_btn1;
                }
                else if(vue.stepper_box == 2){
                    vue.disabledBtnModal()
                    vue.disabled_btn_next = vue.stepper_box_btn2;
                }
                else if(vue.stepper_box == 3){
                    let errors = vue.showValidateActividades()
                    vue.stepper_box_btn3 = vue.checklist.checklist_actividades.length == 0 || errors > 0
                    vue.disabled_btn_next = vue.stepper_box_btn3;
                }
            },
            deep: true
        }
    },
    methods: {
        rep(){
            let vue = this
        },
        async loadFormSelects(){
            let vue = this;
            await axios.get('/entrenamiento/checklists/form-selects').then(({ data }) => {
                vue.resource.evaluation_types = data.data.checklist_default_configuration.evaluation_types;
                vue.resource.extra_attributes.qualification_type = data.data.checklist_default_configuration.qualification_type;
                vue.selects.extra_attributes = data.data.qualification_types;
            })
        },
        validateRequired(input) {
            return input != undefined && input != null && input != "";
        },
        nextStep(){
            let vue = this;
            vue.cancelLabel = "Cancelar";

            if(vue.checklist.segments != null && vue.checklist.segments.length > 0) {}
            else {
                vue.checklist.segments = [{
                    id: `new-segment-${Date.now()}`,
                    type_code: 'direct-segmentation',
                    criteria_selected: [],
                    direct_segmentation: [null]
                }];
            }

            if(vue.stepper_box == 1){
                vue.cancelLabel = "Retroceder";
                vue.stepper_box = 2;

                if(vue.checklist.type_checklist == "libre") {
                    vue.checklist.courses = [];
                }
                if(vue.checklist.type_checklist == "curso") {
                    vue.checklist.segments[0].direct_segmentation = [null];
                    vue.checklist.segmentation_by_document.segmentation_by_document = [];
                }
            }
            else if(vue.stepper_box == 2){
                vue.cancelLabel = "Retroceder";
                vue.stepper_box = 3;
            }
            else if(vue.stepper_box == 3){
                vue.cancelLabel = "Retroceder";
                vue.confirm();
            }
        },
        prevStep(){
            let vue = this;
            if(vue.stepper_box == 1){
                vue.closeModal();
                // vue.stepper_box = 2;
            }
            else if(vue.stepper_box == 2){
                vue.cancelLabel = "Cancelar";
                vue.stepper_box = 1;
            }
            else if(vue.stepper_box == 3){
                vue.cancelLabel = "Retroceder";
                vue.stepper_box = 2;
            }
        },
        disabledBtnModal() {
            let vue = this;
            vue.stepper_box_btn2 = false;

            let direct_segmentation = (vue.checklist.segments != null && vue.checklist.segments.length > 0) ? vue.checklist.segments[0].direct_segmentation : [];
            let segmentation_by_document = vue.checklist.segmentation_by_document != null && vue.checklist.segmentation_by_document.segmentation_by_document.length > 0;

            if(vue.checklist.courses.length == 0 && (direct_segmentation.length > 0 && direct_segmentation[0] == null) && !segmentation_by_document) {
                vue.stepper_box_btn2 = true;
            } else {

                if (vue.checklist.type_checklist == 'curso') {
                    if (vue.checklist.courses.length == 0) {
                        vue.stepper_box_btn2 = true
                    }
                } else {
                    if (direct_segmentation.length > 0)  {
                        if( direct_segmentation[0] == null ) {}
                        else {
                            direct_segmentation.forEach(element => {
                                if (direct_segmentation.length < 3 || element == null || element.values_selected == undefined || element.values_selected == null){
                                    vue.stepper_box_btn2 = true;
                                }
                            });
                        }
                    }
                }
            }
        },
        setActividadesHasErrorProp() {
            let vue = this
            if (vue.checklist.checklist_actividades) {
                vue.checklist.checklist_actividades.forEach(el => {
                    el = Object.assign({}, el, {hasErrors: false})
                })
            }
        },
        closeModal() {
            let vue = this;
            vue.expand_cursos = true;
            vue.actividades_expanded = [];
            vue.search_text = null;
            vue.resetValidation()
            vue.$emit("onClose");
        },
        resetValidation() {
            let vue = this;
            console.log('resetValidation')
            vue.search_text = null
            vue.results_search = []
            vue.stepper_box = 1

            if (vue.$refs.modalFormSegmentationOptions)
                vue.$refs.modalFormSegmentationOptions.closeModal()
        },
        confirm() {
            let vue = this;
            vue.checklist.list_segments = {
                'segments' : vue.checklist.segments,
                'model_type': "App\\Models\\Checklist",
                'model_id': null,
                'code': "direct-segmentation"
            };
            vue.checklist.list_segments_document = {
                'segment_by_document' : vue.checklist.segmentation_by_document,
                'model_type': "App\\Models\\Checklist",
                'model_id': null,
                'code': "segmentation-by-document"
            };
            const allIsValid = vue.moreValidaciones()

            if (allIsValid == 0)
                vue.$emit("onConfirm");
        },
        showValidateActividades() {
            let vue = this
            let errors = 0;

            if( vue.checklist.checklist_actividades.length > 0 ) {
                vue.checklist.checklist_actividades.forEach((el, index) => {
                    el.hasErrors = !el.activity || el.activity === ''
                    if(el.hasErrors) errors++;
                })
            }
            return errors;
        },
        moreValidaciones() {
            let vue = this
            let errors = 0

            let hasActividadEntrenadorUsuario = false;
            vue.checklist.checklist_actividades.map(actividad=>{
               if( actividad.type_name=='trainer_user') hasActividadEntrenadorUsuario=true;
            });
            if(!hasActividadEntrenadorUsuario){
                this.modalAlert.open= true;
               errors++
            }
            return errors > 0
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        eliminarActividad(actividad, index) {
            let vue = this;
            if (String(actividad.id).charAt(0).includes('n')) {
                vue.actividades_expanded = []
                vue.checklist.checklist_actividades.splice(index, 1);
                return
            }
            axios
                .post(`/entrenamiento/checklists/delete_actividad_by_id`, actividad)
                .then((res) => {
                    if (res.data.error) {
                        vue.$notification.warning(`${res.data.msg}`, {
                            timer: 6,
                            showLeftIcn: false,
                            showCloseIcn: true
                        });
                    } else {
                        vue.actividades_expanded = []
                        vue.checklist.checklist_actividades.splice(index, 1);
                        vue.$notification.success(`${res.data.msg}`, {
                            timer: 6,
                            showLeftIcn: false,
                            showCloseIcn: true
                        });
                    }
                })
                .catch((err) => {
                    console.err(err);
                });
        },
        addActividad() {
            let vue = this;
            const newID = `n-${vue.checklist.checklist_actividades.length + 1}`;
            const newActividad = {
                id: newID,
                activity: "",
                active: 1,
                type_name: "trainer_user",
                checklist_id: vue.checklist.id,
                hasErrors: false,
                is_default:false
            };
            vue.checklist.checklist_actividades.unshift(newActividad);
        },
        search() {
            clearTimeout(this.timeout);
            let vue = this;
            if (vue.search_text == null || vue.search_text === "") return;
            if (vue.isLoading) return;
            this.timeout = setTimeout(function () {
                vue.isLoading = true;
                const data = {
                    filtro: vue.search_text,
                };
                axios
                    .post(`/entrenamiento/checklists/buscar_curso`, data)
                    .then((res) => {
                        console.log(vue.results_search);
                        vue.results_search = res.data.data;
                        // vue.$nextTick(() => {
                        //     vue.$refs.resultSearch.focus()
                        //     vue.$refs.resultSearch.isMenuActive = true
                        //     vue.$refs.resultSearch.isMenuActive = true;
                        // })
                        setTimeout(() => {
                            vue.isLoading = false;
                        }, 1500);
                    })
                    .catch((err) => {
                        console.log(err);
                        vue.isLoading = false;
                    });
            }, 1000);
        },
        removeCurso(curso, index) {
            let vue = this;
            vue.results_search.push(curso)
            vue.checklist.courses.splice(index, 1)
            if(vue.checklist.courses.length >= 2) {
                vue.disabled_add_courses = true;
            } else {
                vue.disabled_add_courses = false;
            }

        },
        seleccionarCurso(curso, index) {
            let vue = this;
            vue.checklist.courses.push(curso)
            vue.results_search.splice(index, 1)
            if(vue.checklist.courses.length >= 2) {
                vue.disabled_add_courses = true;
            } else {
                vue.disabled_add_courses = false;
            }
        },
        getNewSegment(type_code) {
            return {
                id: `new-segment-${Date.now()}`,
                type_code,
                criteria_selected: []
            };
        },
        async addSegmentation(type_code) {
            let vue = this;
            vue.segments.push(this.getNewSegment(type_code));

            vue.steps = vue.segments.length - 1;
        },
        borrarBloque(segment) {
            let vue = this;
            // const isNewSegment = segment.id.search("new") !== -1;
            // if (vue.segments.length === 1 && !isNewSegment) return;

            vue.segments = vue.segments.filter((obj, idx) => {
                return obj.id != segment.id;
            });
        },
        isCourseSegmentation() {
            return this.model_type === 'App\\Models\\Course'
        },
        async changeTypeChecklist() {

            let vue = this;

            console.log(vue.resource.type_checklist);
            console.log(vue.type_checklist);
            vue.type_checklist = vue.resource.type_checklist;
        },
        async loadLimitsGenerateIaDescriptions() {
            await axios.get('/jarvis/limits?type=descriptions').then(({ data }) => {
                this.limits_descriptions_generate_ia = data.data;
            })
        },
        async generateIaDescription() {
            const vue = this;
            let url = `/jarvis/generate-description-jarvis`;
            if (vue.loading_description || !vue.resource.title) {
                const message = vue.loading_description ? 'Se está generando la descripción, espere un momento' : 'Es necesario colocar un nombre al checklist para poder generar la descripción';
                vue.showAlert(message, 'warning', '')
                return ''
            }
            if (vue.limits_descriptions_generate_ia.ia_descriptions_generated >= vue.limits_descriptions_generate_ia.limit_descriptions_jarvis) {
                vue.showAlert('Ha sobrepasado el limite para poder generar descripciones con IA', 'warning', '')
                return ''
            }
            vue.loading_description = true;
            await axios.post(url, {
                name: vue.resource.title,
                type: 'checklist'
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
        addScaleEvaluation(){
            let vue = this;
            if( vue.resource.evaluation_types.length < 5){
                vue.resource.evaluation_types.push(
                    {id:vue.resource.evaluation_types.length+1,name:'',color:'#FF4560',percent:'0'},
                )
            }
        }
    }
};
</script>
<style>
.list-cursos-carreras {
    width: 500px;
    white-space: unset;
}

.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}

.flip-list-move {
    transition: transform 0.5s;
}

.no-move {
    transition: transform 0s;
}

.txt-white-bold {
    color: white !important;
    font-weight: bold !important;
}

.v-input__icon {
    padding-bottom: 12px;
}

.v-icon.v-icon.v-icon--link {
    color: #1976d2;
}

.icon_size .v-icon.v-icon {
    font-size: 31px !important;
}

.lista-boticas {
    list-style-type: disc;
    -webkit-columns: 3;
    -moz-columns: 3;
    columns: 3;
    list-style-position: inside;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity .5s;
}

.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */
{
    opacity: 0;
}

.custom-draggable, .custom-draggable span {
    width: 100%;
}

.v-expansion-panel-header {
    padding: 0.7rem !important;
}
.bx_steps .v-text-field__slot label.v-label,
.bx_steps .v-select__slot label.v-label,
.text_default {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 13px;
}
.box_info_checklist_1 {
    text-align: justify;
}
.v-stepper__header.stepper_dots {
    justify-content: center;
    height: initial;
}
.v-stepper__header.stepper_dots .v-divider {
    margin: 0;
    flex: auto;
    border-color: #5458ea !important;
    border-width: 2px;
    width: 30px;
    min-width: 30px;
    max-width: 30px;
}
.v-stepper__header.stepper_dots .v-stepper__step {
    padding: 10px 0;
    margin: 0;
}
.v-stepper__header.stepper_dots .v-stepper__step span.v-stepper__step__step {
    margin: 0;
}
.v-stepper__header.stepper_dots .v-stepper__step:hover {
    background: none;
}
.v-stepper.stepper_box {
    box-shadow: none;
}
.txt_desc textarea {
    min-height: 280px;
}
.v-tab span.title_sub {
    font-size: 16px;
    color: #B9E0E9;
    display: flex;
    justify-content: center;
    margin: 12px 0;
    font-family: "Nunito", sans-serif;
    font-weight: 400;
    position: relative;
    text-transform: initial;
    letter-spacing: 0.1px;
}
.v-tab.v-tab--active span.title_sub,
span.title_sub {
    font-size: 16px;
    color: #5458EA;
    display: flex;
    justify-content: center;
    margin: 12px 0;
    font-family: "Nunito", sans-serif;
    font-weight: 700;
    position: relative;
    text-transform: initial;
    letter-spacing: 0.1px;
}
.v-tab.v-tab--active span.title_sub:after,
span.title_sub:after {
    content: '';
    border-bottom: 2px solid #5458EA;
    width: 112px;
    position: absolute;
    bottom: -2px;
}
.v-tab span.title_sub:after  {
    content: none;
}
button.btn_secondary {
    background: none !important;
    border: none;
    box-shadow: none;
}
button.btn_secondary span.v-btn__content {
    color: #5458EA;
    font-weight: 700;
    font-size: 12px;
    font-family: "Nunito", sans-serif;
}
button.btn_secondary span.v-btn__content i{
    font-size: 14px;
    line-height: 1;
}
.divider_light{
    border-color: #94DDDB !important;
}
.item-draggable.activities {
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
    margin: 10px 0;
}
.item-draggable.activities textarea,
.no-white-space .v-select__selection--comma,
.item-draggable.activities .toggle_text_default label.v-label {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 12px;
}
.no-white-space .v-select__selection--comma {
    white-space: initial;
    line-height: 13px;
    font-size: 13px;
}
.item-draggable.activities textarea {
    font-size: 13px;
}
.no-white-space .v-input__append-inner .v-input__icon {
    padding: 0;
}
.item-draggable.activities .default-toggle.default-toggle.v-input--selection-controls {
    margin-top: initial !important;
}
.box_resultados,
.box_seleccionados {
    height: 130px;
    overflow-y: auto;
    border-radius: 8px;
    border: 1px solid #D9D9D9;
    padding: 10px 0;
}
.box_resultados .v-btn:not(.v-btn--text):not(.v-btn--outlined):hover:before,
.box_seleccionados .v-btn:not(.v-btn--text):not(.v-btn--outlined):hover:before {
    opacity: 0;
}
.bx_message {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}
span.v-stepper__step__step {
    color: #5458ea !important;
    background-color: #5458ea !important;
    border-color: #5458ea !important;
    position: relative;
}
span.v-stepper__step__step:before {
    content: '';
    background: #fff;
    height: 17px;
    width: 17px;
    left: 50%;
    top: 50%;
    position: absolute;
    transform: translate(-50%, -50%);
    border-radius: 50%;
}
span.v-stepper__step__step:after {
    content: '';
    background-color: #5458ea;
    height: 6px;
    width: 6px;
    left: 50%;
    top: 50%;
    position: absolute;
    transform: translate(-50%, -50%);
    border-radius: 50%;
}
.v-stepper__step.v-stepper__step--inactive span.v-stepper__step__step,
.v-stepper__step.v-stepper__step--inactive span.v-stepper__step__step:after,
.v-stepper__step.v-stepper__step--inactive .v-divider {
    color: #E4E4E4 !important;
    background-color: #E4E4E4 !important;
    border-color: #E4E4E4 !important;
}
.bx_type_checklist .v-input__icon.v-input__icon--append,
.bx_steps .v-input__icon.v-input__icon--append {
    margin: 0;
    padding: 0;
}
.bx_steps .v-input__slot {
    min-height: 40px !important;
}
.bx_steps .v-text-field--outlined .v-label {
    top: 10px;
}
.bx_steps .v-btn--icon.v-size--default {
    height: 22px;
    width: 22px;
}
.bx_steps .v-select__slot,
.v-dialog.v-dialog--active .bx_steps .v-select--is-multi.v-autocomplete .v-select__slot {
    padding: 0 !important;
}
.bx_steps .v-text-field__details {
    display: none;
}
.bx_step1 .default-toggle {
    margin-top: 3px !important;
}
.bx_step1 .default-toggle .v-input__slot label.v-label {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 13px;
}
.v-stepper__step.v-stepper__step--complete span.v-stepper__step__step:before,
.v-stepper__step.v-stepper__step--complete span.v-stepper__step__step:after {
    content: initial;
}
.bx_steps .v-text-field--enclosed.v-input--dense:not(.v-text-field--solo).v-text-field--outlined .v-input__append-inner {
    margin-top: 10px !important;
}
.v-card__actions.actions_btn_modal button.default-modal-action-button.btn_back.v-btn.v-btn--flat span.v-btn__content {
    color: #5458ea;
}
.v-menu.bx_calendar_top .v-menu__content {
    bottom: 0px;
    top: initial !important;
    right: calc(100% - 10px);
    left: initial !important;
    transform-origin: right bottom !important;
}
.bx_seg .v-select__slot .v-input__append-inner,
.bx_steps .bx_seg .v-text-field--enclosed.v-input--dense:not(.v-text-field--solo).v-text-field--outlined .v-input__append-inner {
    margin-top: 6px !important;
}
.border-error .v-input__slot fieldset {
    border-color: #FF5252 !important;
}
.container-evaluation-type{
    width: 20px;
    height: 20px;
    border-radius: 50%;
}
.v-carousel__controls__item{
    font-size: 6px !important;
    background: #D9D9D9 !important;
    border-radius: 50% !important;
    padding: 0 !important;
    height: 18px !important;
    width: 18px !important;
}
.custom-control-buttom{
    font-size: 6px !important;
    background: #D9D9D9 !important;
    border-radius: 50% !important;
    height: 18px !important;
    width: 18px !important;
}
.carousel__controls__item .v-item--active{
    background:#5458EA !important; 
    background-color: #5458EA !important; /* Color de fondo del botón activo */
}
.checkbox-label .v-label{
    margin:auto !important;
}
</style>
