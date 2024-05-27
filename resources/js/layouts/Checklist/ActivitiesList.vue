<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/>
                <v-spacer/>
                <!-- <DefaultModalButton 
                    label="Tarea"
                    icon_name="mdi-plus"
                    @click="openFormModal(modalOptions)"
                /> -->

            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start" class="align-items-center">
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por temática o actividad..."
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex">
                        <div class="chips-container">
                            <v-chip class="text-white mx-1" color="#9A98F7" v-for="(chip,i) in chips" :key="i">{{ chip }}</v-chip>
                        </div>
                    </v-col>
                    <v-col cols="1">
                        <DefaultButton 
                            is-icon-button
                            icon="mdi mdi-pencil"
                            @click="openFormModal(modalChecklist, checklist, null,`Editar Checklist - ${checklist.name}`)"
                        />
                    </v-col>
                    <v-col cols="2">
                        <DefaultToggle 
                            v-model="gruped_by_areas_and_tematicas"  
                            active-label="Agrupar actividades por áreas y temáticas" 
                            inactive-label="Agrupar actividades por áreas y temáticas" 
                            dense
                            @onChange="chengeAgrupationChecklist"
                        />
                    </v-col>
                </v-row>
                <v-row v-if="gruped_by_areas_and_tematicas">
                    <v-col cols="12">
                        <v-row>
                            <v-col cols="1" class="d-flex align-center">
                            </v-col>
                            <v-col cols="5" class="d-flex align-center">
                                <span class="text_default fw-bold" style="color: #6C757D;">Nombre</span>
                            </v-col>
                            <v-col cols="3" class="d-flex align-center justify-content-center">
                                <span class="text_default fw-bold" style="color: #6C757D;">Contenido</span>
                            </v-col>
                            <v-col cols="3" class="d-flex align-center justify-content-center">
                                <span class="text_default fw-bold" style="color: #6C757D;">Opciones</span>
                            </v-col>
                        </v-row>
                        
                        <draggable v-model="areas" @start="drag=true"
                                    @end="drag=false" class="custom-draggable" ghost-class="ghost" @change="changePositionArea(areas, $event)">
                                <transition-group type="transition" name="flip-list" tag="div" key="transition-area">
                                    <v-expansion-panels key="expansion-area" v-model="panel" multiple flat>
                                        <v-expansion-panel
                                            v-for="(area,index_area) in areas"
                                            :key="area.id"
                                          
                                        >
                                            <div class="item-draggable areas">
                                                <v-expansion-panel-header>
                                                    <v-row>
                                                        <v-col cols="6" class="d-flex align-center justify-content-center">
                                                            <div style="margin-right: 15px;">
                                                                <v-icon class="ml-0 mr-2 icon_size icon_size_drag">fas fa-grip-vertical
                                                                </v-icon>
                                                            </div>
                                                            <div class="d-flex justify-content-between" style="column-gap: 20px; flex: 1;">
                                                                <div style="flex: 1;">
                                                                    <div>
                                                                        <span class="text_default fw-bold">{{ area.name }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </v-col>
                                                        <v-col cols="3" class="d-flex justify-content-around text-center align-items-center" style="color:#A9B2B9">
                                                            <span>Temáticas: {{area.tematicas.length}}</span>
                                                            <span>Actividades: {{ getTotalActivities(area.tematicas) }}</span>
                                                        </v-col>
                                                        <v-col cols="3" class="bx_actions_area">
                                                            <div>
                                                                <div class="btn_action" :class="{'disabled': !area.active}" @click.stop="openFormModal(modalAreaEditOptions,area,null,'Edición de áreas')">
                                                                    <v-icon class="ml-0 icon_size">
                                                                        mdi mdi-pencil
                                                                    </v-icon>
                                                                    <span class="text_default">Editar</span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="btn_action"  :class="{'disabled': !area.active}" @click.stop="verifyStep">
                                                                    <v-icon class="ml-0 icon_size">
                                                                        {{area.active ? 'fas fa-circle' : 'far fa-circle'}}
                                                                    </v-icon>
                                                                    <span class="text_default">{{area.active ? 'Activo' : 'Inactivo'}}</span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="btn_action" :class="{'disabled': !area.active}" @click.stop="verifyStep">
                                                                    <v-icon class="ml-0 icon_size">
                                                                        mdi mdi-trash-can
                                                                    </v-icon>
                                                                    <span class="text_default">Eliminar</span>
                                                                </div>
                                                            </div>
                                                        </v-col>
                                                    </v-row>
                                                </v-expansion-panel-header>
                                                <v-expansion-panel-content>
                                                    <v-row>
                                                        <v-col cols="12">
                                                            <draggable v-model="areas.tematicas" @start="drag=true"
                                                                    @end="drag=false" class="custom-draggable" ghost-class="ghost" @change="changePositionActivity(area, $event)">
                                                                <transition-group type="transition" name="flip-list" tag="div">
                                                                    <v-expansion-panels key="expansion-area" v-model="panel_tematica" multiple flat>
                                                                        <v-expansion-panel
                                                                            v-for="(tematica,index_tematica) in area.tematicas"
                                                                            :key="'act_'+tematica.id"
                                                                            >
                                                                                <div class="item-draggable areas areas_tematicas">
                                                                                    <v-expansion-panel-header>
                                                                                        <v-row>
                                                                                            <v-col cols="6" class="d-flex align-center">
                                                                                                <div style="margin-left: 35px; margin-right: 10px;">
                                                                                                    <v-icon class="ml-0 mr-2 icon_size icon_size_drag">fas fa-grip-vertical
                                                                                                    </v-icon>
                                                                                                </div>
                                                                                                <div>
                                                                                                    <div class="d-flex align-items-center">
                                                                                                        <span class="text_default fw-bold">{{ tematica.name }}</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </v-col>
                                                                                            <v-col cols="3" class="d-flex justify-content-center text-center" style="color:#A9B2B9">
                                                                                                <span>Cantidad de actividades: {{ tematica.activities.length }}</span>
                                                                                            </v-col>
                                                                                            <v-col cols="3" class="bx_actions_area">
                                                                                                <div>
                                                                                                    <div class="btn_action" :class="{'disabled': !tematica.active}" @click.stop="openFormModal(modalTematicaOptions,tematica,null,'Editar temática')">
                                                                                                        <v-icon class="ml-0 icon_size">
                                                                                                            mdi mdi-pencil
                                                                                                        </v-icon>
                                                                                                        <span class="text_default">Editar</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div>
                                                                                                    <div class="btn_action"  :class="{'disabled': !tematica.active}">
                                                                                                        <v-icon class="ml-0 icon_size">
                                                                                                            {{tematica.active ? 'fas fa-circle' : 'far fa-circle'}}
                                                                                                        </v-icon>
                                                                                                        <span class="text_default">{{tematica.active ? 'Activo' : 'Inactivo'}}</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div>
                                                                                                    <div class="btn_action" :class="{'disabled': !tematica.active}">
                                                                                                        <v-icon class="ml-0 icon_size">
                                                                                                            mdi mdi-trash-can
                                                                                                        </v-icon>
                                                                                                        <span class="text_default">Eliminar</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </v-col>
                                                                                        </v-row>
                                                                                    </v-expansion-panel-header>
                                                                                    <v-expansion-panel-content>
                                                                                        <v-row class="ml-12">
                                                                                            <v-col cols="12">
                                                                                                <draggable v-model="tematica.activities" @start="drag=true"
                                                                                                        @end="drag=false" class="custom-draggable" ghost-class="ghost" @change="changePositionActivity(area, $event)">
                                                                                                    <transition-group type="transition" name="flip-list" tag="div">
                                                                                                        <div v-for="(activity,index_activity) in tematica.activities"
                                                                                                            :key="'act_'+activity.id">
                                                                                                            <div class="item-draggable areas areas_tematicas">
                                                                                                                <v-row class="elevation-2 my-2">
                                                                                                                    <v-col cols="1" class="d-flex align-center justify-content-center " style="max-width: 3rem;">
                                                                                                                        <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                                                                                        </v-icon>
                                                                                                                    </v-col>
                                                                                                                    <v-row class="col-11 px-0 mx-0" >
                                                                                                                        <v-col cols="12" class="px-0">
                                                                                                                            <DefaultRichText
                                                                                                                                clearable
                                                                                                                                :height="150"
                                                                                                                                v-model="activity.activity"
                                                                                                                                label="Actividad de checklist"
                                                                                                                                :ignoreHTMLinLengthCalculation="true"
                                                                                                                                :key="`${activity.id}-editor`"
                                                                                                                                ref="descriptionRichText1"
                                                                                                                                customSelectorImage
                                                                                                                            />
                                                                                                                        </v-col>
                                                                                                                        <v-expansion-panels flat class="custom-expansion-block" v-model="activity.panel">
                                                                                                                            <v-expansion-panel >
                                                                                                                                <v-expansion-panel-header flat>
                                                                                                                                    <span style="color:#5458EA" class="d-flex">
                                                                                                                                        <i class="pr-1 mdi mdi-cog"></i>
                                                                                                                                        Configuración avanzada
                                                                                                                                    </span>
                                                                                                                                    <div class="d-flex">
                                                                                                                                        <v-chip small v-if="activity.checklist_response" color="#9A98F7" class="mx-1" style="max-width: min-content;color: white;">
                                                                                                                                            <i class="pr-1 mdi mdi-file-document-check"></i>
                                                                                                                                            Tipo de repuesta: {{ activity.checklist_response.name }}
                                                                                                                                        </v-chip>
                                                                                                                                        <v-chip small v-if="activity.extra_attributes.is_evaluable" color="#E57A9B" class="mx-1" style="max-width: min-content;color: white;">
                                                                                                                                            <i class="pr-1 mdi mdi-file-chart"></i>
                                                                                                                                            Será evaluable
                                                                                                                                        </v-chip>
                                                                                                                                        <v-chip small v-if="activity.extra_attributes.photo_response" color="#67CB91" class="mx-1" style="max-width: min-content;color: white;">
                                                                                                                                            <i class="pr-1 mdi mdi-image"></i>
                                                                                                                                            Se agregará foto
                                                                                                                                        </v-chip>
                                                                                                                                        <v-chip small v-if="activity.extra_attributes.comment_activity" color="#67CB91" class="mx-1" style="max-width: min-content;color: white;">
                                                                                                                                            <!-- <v-icon>{{ mdi-message-image  }}</v-icon>  -->
                                                                                                                                            <i class="pr-1 mdi mdi-comment-outline"></i>
                                                                                                                                            Se agregará comentario
                                                                                                                                        </v-chip>
                                                                                                                                    </div>
                                                                                                                                    <v-spacer></v-spacer>
                                                                                                                                    <div @click.stop="openDeleteModal(activity,'activity')">
                                                                                                                                        <DefaultButton
                                                                                                                                            icon="mdi-delete"
                                                                                                                                            isIconButton
                                                                                                                                        />
                                                                                                                                    </div>
                                                                                                                                    <div @click.stop="saveActivity(activity)" :class="{'disabled': !activity.activity}">
                                                                                                                                        <DefaultButton
                                                                                                                                            icon="mdi-content-save"
                                                                                                                                            isIconButton
                                                                                                                                            :disabled="!activity.activity"
                                                                                                                                        />
                                                                                                                                    </div>
                                                                                                                                </v-expansion-panel-header>
                                                                                                                                <v-expansion-panel-content class="row">
                                                                                                                                    <v-row>
                                                                                                                                        <v-col cols="12" class="d-flex align-items-center">
                                                                                                                                            <span>
                                                                                                                                                General / tipo de respuesta
                                                                                                                                            </span>
                                                                                                                                            <v-divider></v-divider>
                                                                                                                                        </v-col>
                                                                                                                                        <v-col cols="4">
                                                                                                                                            <DefaultSelect 
                                                                                                                                                :items="checklist_type_response" 
                                                                                                                                                dense 
                                                                                                                                                item-text="name"
                                                                                                                                                show-required 
                                                                                                                                                v-model="activity.checklist_response"
                                                                                                                                                return-object
                                                                                                                                                label="Tipo de respuesta"
                                                                                                                                            />
                                                                                                                                        </v-col>
                                                                                                                                        <v-col cols="2" class="d-flex align-items-center">
                                                                                                                                            <v-checkbox
                                                                                                                                                class="my-0 mr-2 checkbox-label"
                                                                                                                                                label="Evaluable"
                                                                                                                                                color="primary"
                                                                                                                                                v-model="activity.extra_attributes.is_evaluable"
                                                                                                                                                hide-details="false"
                                                                                                                                            />
                                                                                                                                        </v-col>
                                                                                                                                        <v-col cols="3" class="d-flex align-items-center">
                                                                                                                                            <v-checkbox
                                                                                                                                                class="my-0 mr-2 checkbox-label"
                                                                                                                                                label="Se usará foto como respuesta"
                                                                                                                                                color="primary"
                                                                                                                                                v-model="activity.extra_attributes.photo_response"
                                                                                                                                                hide-details="false"
                                                                                                                                            />
                                                                                                                                        </v-col>
                                                                                                                                        <v-col cols="3" class="d-flex align-items-center">
                                                                                                                                            <v-checkbox
                                                                                                                                                class="my-0 mr-2 checkbox-label"
                                                                                                                                                label="Actividad acepta comentario"
                                                                                                                                                color="primary"
                                                                                                                                                v-model="activity.extra_attributes.comment_activity"
                                                                                                                                                hide-details="false"
                                                                                                                                            />
                                                                                                                                        </v-col>
                                                                                                                                        <v-col cols="12" v-if="activity.checklist_response.code == 'custom_option'">
                                                                                                                                            Respuestas personalizadas:
                                                                                                                                            <v-divider></v-divider>
                                                                                                                                            <div v-for="(option,index_option) in activity.custom_options" class="col col-12 d-flex" :key="index_option">
                                                                                                                                                <DefaultInput
                                                                                                                                                    clearable
                                                                                                                                                    v-model="option.name"
                                                                                                                                                    :label="`Opción ${index_option+1}`"
                                                                                                                                                    dense
                                                                                                                                                />
                                                                                                                                                <DefaultButton
                                                                                                                                                    icon="mdi-delete"
                                                                                                                                                    isIconButton
                                                                                                                                                    @click="deleteCustomOption(index_area,index_tematica,index_activity,index_option)"
                                                                                                                                                />
                                                                                                                                            </div>
                                                                                                                                            <span style="color: #5757EA;cursor: pointer;" @click="addCustomOption(index_area,index_tematica,index_activity)">Agregar una respuesta personalizada +</span>
                                                                                                                                        </v-col>
                                                                                                                                        <v-col cols="12" class="d-flex align-items-center">
                                                                                                                                            <span>
                                                                                                                                                Inteligencia artificial
                                                                                                                                            </span>
                                                                                                                                            <v-divider></v-divider>
                                                                                                                                        </v-col>
                                                                                                                                        <v-col cols="4" class="d-flex align-items-center">
                                                                                                                                            <v-checkbox
                                                                                                                                                class="my-0 mr-2 checkbox-label"
                                                                                                                                                label="Aplicar visión computacional"
                                                                                                                                                color="primary"
                                                                                                                                                v-model="activity.extra_attributes.computational_vision"
                                                                                                                                                :disabled="!is_checklist_premium"
                                                                                                                                                hide-details="false"
                                                                                                                                            />
                                                                                                                                            <div v-if="!is_checklist_premium" class="ml-1 tag_beta_upgrade d-flex align-items-center">
                                                                                                                                                    <span class="d-flex beta_upgrade">
                                                                                                                                                        <img src="/img/premiun.svg"> Upgrade
                                                                                                                                                    </span>
                                                                                                                                                </div>
                                                                                                                                        </v-col>
                                                                                                                                        <v-col cols="4" v-if="activity.extra_attributes.computational_vision ">
                                                                                                                                            <DefaultSelect 
                                                                                                                                                v-model="activity.extra_attributes.type_computational_vision"
                                                                                                                                                :items="types_computational_vision" 
                                                                                                                                                dense 
                                                                                                                                                item-text="name"
                                                                                                                                                item-value="id"
                                                                                                                                                show-required 
                                                                                                                                                label="Selecciona una opción"
                                                                                                                                                :openUp="true"
                                                                                                                                            />  
                                                                                                                                        </v-col>
                                                                                                                                        <v-col cols="4"
                                                                                                                                            v-if="activity.extra_attributes.type_computational_vision && activity.extra_attributes.type_computational_vision != 'simil'"
                                                                                                                                        >
                                                                                                                                            <DefaultInput
                                                                                                                                                clearable
                                                                                                                                                v-model="activity.extra_attributes.type_computational_vision_value"
                                                                                                                                                :label="`${activity.extra_attributes.type_computational_vision == 'counter' ? 'Indica la cantidad a verificar' : 'Indicar el texto a verificar'}`"
                                                                                                                                                dense
                                                                                                                                            />
                                                                                                                                        </v-col>
                                                                                                                                    </v-row>
                                                                                                                                </v-expansion-panel-content>
                                                                                                                            </v-expansion-panel>
                                                                                                                        </v-expansion-panels>
                                                                                                                    </v-row>
                                                                                                                </v-row>
                                                                                                                
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </transition-group>
                                                                                                </draggable>
                                                                                            </v-col>
                                                                                            <v-col cols="12">
                                                                                                <v-row class="elevation-2 my-2">
                                                                                                    <v-col cols="1" class="d-flex align-center justify-content-center " style="max-width: 3rem;">
                                                                                                        <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                                                                        </v-icon>
                                                                                                    </v-col>
                                                                                                    <v-col cols="11">
                                                                                                        <div class="btn_add_activity">
                                                                                                            <span class="text_default c-default" @click="addActivity(area.id,tematica.id,index_area,index_tematica)">+ Añadir actividad</span>
                                                                                                        </div>
                                                                                                    </v-col>
                                                                                                </v-row>
                                                                                            </v-col>
                                                                                        </v-row>
                                                                                    </v-expansion-panel-content>
                                                                                </div>
                                                                        </v-expansion-panel>
                                                                    </v-expansion-panels>
                                                                </transition-group>
                                                            </draggable>
                                                        </v-col>
                                                        <v-col cols="12" class="ml-2">
                                                            <v-row class="elevation-2 my-2">
                                                                <v-col cols="1" class="d-flex align-center justify-content-center " style="max-width: 3rem;">
                                                                    <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                                    </v-icon>
                                                                </v-col>
                                                                <v-col cols="11">
                                                                    <div class="btn_add_activity">
                                                                        <span class="text_default c-default" @click="addTematica(area.id)">+ Añadir temática</span>
                                                                    </div>
                                                                </v-col>
                                                            </v-row>
                                                        </v-col>
                                                    </v-row>
                                                </v-expansion-panel-content>
                                            </div>
                                        </v-expansion-panel>
                                    </v-expansion-panels>
                                </transition-group>
                        </draggable>
                    </v-col>
                </v-row>
                <v-row v-else>
                    <v-col cols="12">
                        <draggable v-model="activities" @start="drag=true"
                                @end="drag=false" class="custom-draggable" ghost-class="ghost" @change="changePositionActivity(area, $event)">
                            <transition-group type="transition" name="flip-list" tag="div">
                                <div v-for="(activity,index_activity) in activities"
                                    :key="'act_'+activity.id">
                                    <div class="item-draggable areas areas_tematicas">
                                        <v-row class="elevation-2 my-2">
                                            <v-col cols="1" class="d-flex align-center justify-content-center " style="max-width: 3rem;">
                                                <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                </v-icon>
                                            </v-col>
                                            <v-row class="col-11 px-0 mx-0" >
                                                <v-col cols="12" class="px-0">
                                                    <DefaultRichText
                                                        clearable
                                                        :height="150"
                                                        v-model="activity.activity"
                                                        label="Actividad de checklist"
                                                        :ignoreHTMLinLengthCalculation="true"
                                                        :key="`${activity.id}-editor`"
                                                        ref="descriptionRichText1"
                                                        customSelectorImage
                                                    />
                                                </v-col>
                                                <v-expansion-panels flat class="custom-expansion-block" v-model="activity.panel">
                                                    <v-expansion-panel >
                                                        <v-expansion-panel-header flat>
                                                            <span style="color:#5458EA" class="d-flex">
                                                                <i class="pr-1 mdi mdi-cog"></i>
                                                                Configuración avanzada
                                                            </span>
                                                            <div class="d-flex">
                                                                <v-chip small v-if="activity.checklist_response" color="#9A98F7" class="mx-1" style="max-width: min-content;color: white;">
                                                                    <i class="pr-1 mdi mdi-file-document-check"></i>
                                                                    Tipo de repuesta: {{ activity.checklist_response.name }}
                                                                </v-chip>
                                                                <v-chip small v-if="activity.extra_attributes.is_evaluable" color="#E57A9B" class="mx-1" style="max-width: min-content;color: white;">
                                                                    <i class="pr-1 mdi mdi-file-chart"></i>
                                                                    Será evaluable
                                                                </v-chip>
                                                                <v-chip small v-if="activity.extra_attributes.photo_response" color="#67CB91" class="mx-1" style="max-width: min-content;color: white;">
                                                                    <i class="pr-1 mdi mdi-image"></i>
                                                                    Se agregará foto
                                                                </v-chip>
                                                                <v-chip small v-if="activity.extra_attributes.comment_activity" color="#67CB91" class="mx-1" style="max-width: min-content;color: white;">
                                                                    <!-- <v-icon>{{ mdi-message-image  }}</v-icon>  -->
                                                                    <i class="pr-1 mdi mdi-comment-outline"></i>
                                                                    Se agregará comentario
                                                                </v-chip>
                                                            </div>
                                                            <v-spacer></v-spacer>
                                                            <div @click.stop="openDeleteModal(activity,'activity')">
                                                                <DefaultButton
                                                                    icon="mdi-delete"
                                                                    isIconButton
                                                                />
                                                            </div>
                                                            <div @click.stop="saveActivity(activity)" :class="{'disabled': !activity.activity}">
                                                                <DefaultButton
                                                                    icon="mdi-content-save"
                                                                    isIconButton
                                                                    :disabled="!activity.activity"
                                                                />
                                                            </div>
                                                        </v-expansion-panel-header>
                                                        <v-expansion-panel-content class="row">
                                                            <v-row>
                                                                <v-col cols="12" class="d-flex align-items-center">
                                                                    <span>
                                                                        General / tipo de respuesta
                                                                    </span>
                                                                    <v-divider></v-divider>
                                                                </v-col>
                                                                <v-col cols="4">
                                                                    <DefaultSelect 
                                                                        :items="checklist_type_response" 
                                                                        dense 
                                                                        item-text="name"
                                                                        show-required 
                                                                        v-model="activity.checklist_response"
                                                                        return-object
                                                                        label="Tipo de respuesta"
                                                                    />
                                                                </v-col>
                                                                <v-col cols="2" class="d-flex align-items-center">
                                                                    <v-checkbox
                                                                        class="my-0 mr-2 checkbox-label"
                                                                        label="Evaluable"
                                                                        color="primary"
                                                                        v-model="activity.extra_attributes.is_evaluable"
                                                                        hide-details="false"
                                                                    />
                                                                </v-col>
                                                                <v-col cols="3" class="d-flex align-items-center">
                                                                    <v-checkbox
                                                                        class="my-0 mr-2 checkbox-label"
                                                                        label="Se usará foto como respuesta"
                                                                        color="primary"
                                                                        v-model="activity.extra_attributes.photo_response"
                                                                        hide-details="false"
                                                                    />
                                                                </v-col>
                                                                <v-col cols="3" class="d-flex align-items-center">
                                                                    <v-checkbox
                                                                        class="my-0 mr-2 checkbox-label"
                                                                        label="Actividad acepta comentario"
                                                                        color="primary"
                                                                        v-model="activity.extra_attributes.comment_activity"
                                                                        hide-details="false"
                                                                    />
                                                                </v-col>
                                                                <v-col cols="12" v-if="activity.checklist_response.code == 'custom_option'">
                                                                    Respuestas personalizadas:
                                                                    <v-divider></v-divider>
                                                                    <div v-for="(option,index_option) in activity.custom_options" class="col col-12 d-flex" :key="index_option">
                                                                        <DefaultInput
                                                                            clearable
                                                                            v-model="option.name"
                                                                            :label="`Opción ${index_option+1}`"
                                                                            dense
                                                                        />
                                                                        <DefaultButton
                                                                            icon="mdi-delete"
                                                                            isIconButton
                                                                            @click="deleteCustomOption(null,null,index_activity,index_option)"
                                                                        />
                                                                    </div>
                                                                    <span style="color: #5757EA;cursor: pointer;" @click="addCustomOption(null,null,index_activity)">Agregar una respuesta personalizada +</span>
                                                                </v-col>
                                                                <v-col cols="12" class="d-flex align-items-center">
                                                                    <span>
                                                                        Inteligencia artificial
                                                                    </span>
                                                                    <v-divider></v-divider>
                                                                </v-col>
                                                                <v-col cols="4" class="d-flex align-items-center">
                                                                    <v-checkbox
                                                                        class="my-0 mr-2 checkbox-label"
                                                                        label="Aplicar visión computacional"
                                                                        color="primary"
                                                                        v-model="activity.extra_attributes.computational_vision"
                                                                        :disabled="!is_checklist_premium"
                                                                        hide-details="false"
                                                                    />
                                                                    <div v-if="!is_checklist_premium" class="ml-1 tag_beta_upgrade d-flex align-items-center">
                                                                            <span class="d-flex beta_upgrade">
                                                                                <img src="/img/premiun.svg"> Upgrade
                                                                            </span>
                                                                        </div>
                                                                </v-col>
                                                                <v-col cols="4" v-if="activity.extra_attributes.computational_vision ">
                                                                    <DefaultSelect 
                                                                        v-model="activity.extra_attributes.type_computational_vision"
                                                                        :items="types_computational_vision" 
                                                                        dense 
                                                                        item-text="name"
                                                                        item-value="id"
                                                                        show-required 
                                                                        label="Selecciona una opción"
                                                                        :openUp="true"
                                                                    />  
                                                                </v-col>
                                                                <v-col cols="4"
                                                                    v-if="activity.extra_attributes.type_computational_vision && activity.extra_attributes.type_computational_vision != 'simil'"
                                                                >
                                                                    <DefaultInput
                                                                        clearable
                                                                        v-model="activity.extra_attributes.type_computational_vision_value"
                                                                        :label="`${activity.extra_attributes.type_computational_vision == 'counter' ? 'Indica la cantidad a verificar' : 'Indicar el texto a verificar'}`"
                                                                        dense
                                                                    />
                                                                </v-col>
                                                            </v-row>
                                                        </v-expansion-panel-content>
                                                    </v-expansion-panel>
                                                </v-expansion-panels>
                                            </v-row>
                                        </v-row>
                                        
                                    </div>
                                </div>
                            </transition-group>
                        </draggable>
                    </v-col>
                    <v-col cols="12">
                        <v-row class="elevation-2 my-2">
                            <v-col cols="1" class="d-flex align-center justify-content-center " style="max-width: 3rem;">
                                <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                </v-icon>
                            </v-col>
                            <v-col cols="11">
                                <div class="btn_add_activity">
                                    <span class="text_default c-default" @click="addActivity(null,null,null,null)">+ Añadir actividad</span>
                                </div>
                            </v-col>
                        </v-row>
                    </v-col>
                </v-row>
                <v-row v-if="gruped_by_areas_and_tematicas">
                    <v-col cols="12">
                        <div class="d-flex align-items-center justify-content-center">
                            <div id="tooltip-target-1" class="btn_tooltip d-inline-flex" 
                                @click="openFormModal(modalAreaOptions,null,null,'Gestión de áreas')"
                            >
                                <v-icon class="icon_size" small :color="!(areas.length < max_areas) ? '#BDBEC0' : '#5458EA'" style="font-size: 1.25rem !important;">
                                    mdi-plus-circle
                                </v-icon>
                            </div>
                            <b-tooltip target="tooltip-target-1" triggers="hover" placement="bottom">
                                Agregar una nueva área.
                            </b-tooltip>
                        </div>
                    </v-col>
                </v-row>
            </v-card-text>
            <DefaultDeleteModal
                :options="modalDeleteOptions"
                :ref="modalDeleteOptions.ref"
                @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters);loadData()"
                @onCancel="closeFormModal(modalDeleteOptions)"
            />
            <ModalAddActivity
                :options="modalActivityOptions"
                :ref="modalActivityOptions.ref"
                width="500px"
                @onConfirm="closeFormModal(modalActivityOptions);loadData()"
                @onCancel="closeFormModal(modalActivityOptions)"
            />
            <ModalFormArea 
                :options="modalAreaOptions"
                :ref="modalAreaOptions.ref"
                width="500px"
                @onConfirm="closeFormModal(modalAreaOptions);loadData()"
                @onCancel="closeFormModal(modalAreaOptions)"
            />
            <ModalFormTematica 
                :options="modalTematicaOptions"
                :ref="modalTematicaOptions.ref"
                width="500px"
                @onConfirm="closeFormModal(modalTematicaOptions);loadData()"
                @onCancel="closeFormModal(modalTematicaOptions)"
            />
            <ModalEditArea 
                :options="modalAreaEditOptions"
                :ref="modalAreaEditOptions.ref"
                width="500px"
                @onConfirm="closeFormModal(modalAreaEditOptions);loadData()"
                @onCancel="closeFormModal(modalAreaEditOptions)"
            />
            <ModalCreateChecklist
                :ref="modalChecklist.ref"
                :options="modalChecklist"
                width="60vw"
                @onConfirm="
                    closeSimpleModal(modalChecklist);
                    refreshDefaultTable(dataTable, filters, 1);
                    loadSelects();
                "
                @onClose="closeSimpleModal(modalChecklist)"
            />
        </v-card>
    </section>
</template>
<script>
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import ModalFormArea from "./Activities/ModalFormArea";
import ModalFormTematica from "./Activities/ModalFormTematica";
import ModalEditArea from './Activities/ModalEditArea';
import ModalAddActivity from "./Activities/ModalAddActivity";

import DefaultRichText from "../../components/globals/DefaultRichText";
import ModalCreateChecklist from "../../components/Entrenamiento/Checklist/ModalCreateChecklist";

export default {
    components: {DefaultDeleteModal,ModalFormArea,DefaultRichText,ModalCreateChecklist,ModalAddActivity,ModalFormTematica,ModalEditArea},
    data() {
        return {
            panel: [],
            gruped_by_areas_and_tematicas : false,
            panel_tematica:[],
            breadcrumbs: [
                {title: null, text: 'Checklist', disabled: true, href: ``},
                {
                    title: null,
                    text: null,
                    disabled: false,
                    href: '/entrenamiento/checklists'
                },
                {title: null, text: 'Configuración de estructura', disabled: true, href: ''},
            ],
            chips:[],
            max_areas: 4,
            checklist:{
                id:0,
                name:''
            },
            checklist_id:null,
            areas:[],
            activities:[],
            dataTable: {
                avoid_first_data_load: false,
                endpoint: '/projects/search',
                ref: 'ProjectTable',
                headers: [
                    {text: "Módulo", value: "subworkspaces",sortable: false},
                    {text: "Escuela", value: "school",sortable: false},
                    {text: "Curso", value: "course",sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Usuarios",
                        icon: 'mdi mdi-account',
                        type: 'route',
                        count: 'usuarios_count',
                        route: 'usuarios_route',
                    },
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit'
                    },
                    {
                        text: "Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                ],
                more_actions: [
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                    {
                        text: "Logs",
                        icon: "mdi mdi-database",
                        type: "action",
                        show_condition: "is_super_user",
                        method_name: "logs"
                    }
                ]
            },
            selects: {
                sub_workspaces: [],
                schools:[],
                courses:[],
                statuses: [
                    {id: 3, name: 'Todos'},
                    {id: 1, name: 'Activos'},
                    {id: 2, name: 'Inactivos'},
                ]
            },
            filters: {
                q: '',
                active: 3,
                subworkspace_id:null,
                school_id:null,
                course_id:null
            },
            modalChecklist:{
                open:false,
                ref: 'ChecklistModal',
                base_endpoint: '/entrenamiento/checklist/v2',
                confirmLabel: 'Guardar',
                resource: 'checklist',
                action: null,
                persistent: true,
            },
            modalDeleteOptions: {
                ref: 'ResourceDeleteModal',
                open: false,
                base_endpoint: '/entrenamiento/checklist/v2',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar este registro!',
                        details: [
                            'Esto no podrá ser visto por los usuarios.',
                            'La información eliminada no podra recuperarse'
                        ],
                    }
                },
                width: '408px'
            },
            modalAreaOptions: {
                ref: 'modalAreaOptions',
                open: false,
                showCloseIcon: true,
                title:'Gestión de Áreas',
                base_endpoint: '/entrenamiento/checklist/v2/',
                confirmLabel:'Guardar',
                persistent: true
            },
            modalAreaEditOptions:{
                ref: 'modalAreaEditOptions',
                open: false,
                showCloseIcon: true,
                title:'Edición de áreas',
                base_endpoint: '/entrenamiento/checklist/v2/',
                confirmLabel:'Guardar',
                persistent: true
            },
            modalTematicaOptions:{
                ref: 'modalTemaitcaOptions',
                open: false,
                showCloseIcon: true,
                title:'Editar temática',
                base_endpoint: '/entrenamiento/checklist/v2/',
                confirmLabel:'Guardar',
                persistent: true
            },
            checklist_type_response:[],
            is_checklist_premium:false,
            modalActivityOptions: {
                ref: 'modalActivityOptions',
                open: false,
                showCloseIcon: true,
                title:'Nueva actividad',
                base_endpoint: '/entrenamiento/checklist/v2/',
                confirmLabel:'Guardar',
                persistent: true
            },
            types_computational_vision:[
                {id:'simil',name:'Porcentaje de similitud'},
                {id:'text',name:'Verificar texto'},
                {id:'counter',name:'Contador de objetos'},
            ]
        }
    },
    mounted() {
        let vue = this;
        vue.setChecklistSinceUrl();
        vue.loadSelects();
        vue.loadData();
    },
    methods: {
        async loadSelects(){
            let vue = this;
            const url = `/entrenamiento/checklist/v2/${vue.checklist_id}/activity/form-selects`;
            await vue.$http.get(url).then(({data})=>{
                vue.checklist_type_response = data.data.checklist_type_response;
                vue.breadcrumbs[1].text = data.data.checklist_name;
                vue.is_checklist_premium = data.data.is_checklist_premium;
                vue.gruped_by_areas_and_tematicas = data.data.gruped_by_areas_and_tematicas;
                vue.chips = data.data.chips;
                vue.checklist = {
                    id:vue.checklist_id,
                    name:data.data.checklist_name
                }
            })
        },
        setChecklistSinceUrl(){
            let vue = this;
            const checklist_id = window.location.pathname.split('/')[4];
            vue.checklist_id = checklist_id;
        },
        loadData(){
            let vue = this;
            vue.$http.get(`/entrenamiento/checklist/v2/${vue.checklist_id}/activities-by-areas`).then(({data})=>{
                if(vue.gruped_by_areas_and_tematicas){
                    vue.areas = data.data;
                }else{
                    vue.activities = data.data.activities;
                }
            })
        },
        getTotalActivities(tematicas) {
           return tematicas.reduce((sum, tematica) => sum + tematica.activities.length, 0);
        },
        verifyStep(){
            console.log('no abre');
        },
        removeActivity(index_activity){
            let vue = this;
            vue.activities.splice(index_activity,1);
        },
        saveActivity(activity){
            let vue = this;
            if(activity.activity){
                vue.showLoader();
                vue.$http.post(`/entrenamiento/checklist/v2/${vue.checklist_id}/activity/save`,{
                    activity:activity
                }).then(({data})=>{
                    vue.hideLoader();
                    vue.loadData();
                    vue.showAlert('Se guardó la actividad correctamente.','success');
                })
            }
        },
        addTematica(area_id){
            let vue = this;
            vue.$http.post(`/entrenamiento/checklist/v2/${vue.checklist_id}/tematica/save`,{
                area_id:area_id
            }).then(({data})=>{
                vue.showAlert('Se añadió la temática correctamente.','success');
                vue.loadData();
            })
        },
        addActivity(area_id,tematica_id,index_area,index_tematica){
            let vue = this;
            const checklist_response = vue.checklist_type_response.find(ctr => ctr.code == 'scale_evaluation');
            const _id = area_id 
                    ? 'insert'+vue.areas[index_area].tematicas[index_tematica].activities.length+vue.generateRandomString(4)
                    : 'insert'+vue.activities.length+vue.generateRandomString(4);
            const _position = area_id 
                            ? vue.areas[index_area].tematicas[index_tematica].activities.length+2
                            : vue.activities.length+2;
            const _activity = {
                    id:_id,
                    position:_position,
                    activity:'',
                    area_id:area_id,
                    tematica_id:tematica_id,
                    checklist_response:checklist_response,
                    custom_options:[],
                    extra_attributes:{
                        is_evaluable:false,
                        comment_activity:false,
                        photo_response:false,
                        computational_vision:false,
                        type_computational_vision:'',
                        type_computational_value:'',
                    },
                }
            if(area_id){
                vue.areas[index_area].tematicas[index_tematica].activities.push(_activity);
            }else{
                vue.activities.push(_activity);
            }
        },
        addCustomOption(index_area, index_tematica, index) {
            let vue = this;
            if(vue.gruped_by_areas_and_tematicas){
                if (!vue.areas[index_area].tematicas[index_tematica].activities[index].custom_options) {
                    vue.$set(vue.areas[index_area].tematicas[index_tematica].activities[index], 'custom_options', []);
                }
                const _id = 'insert' + vue.areas[index_area].tematicas[index_tematica].activities[index].custom_options.length + vue.generateRandomString(4);
                vue.areas[index_area].tematicas[index_tematica].activities[index].custom_options.push({
                    id: _id,
                    name: ''
                });
            }else{
                if (!vue.activities[index].custom_options) {
                    vue.$set(vue.activities[index], 'custom_options', []);
                }
                const _id = 'insert' + vue.activities[index].custom_options.length + vue.generateRandomString(4);
                vue.activities[index].custom_options.push({
                    id: _id,
                    name: ''
                });
            }
        },
        deleteCustomOption(index_area,index_tematica,index_activity,index_option){
            let vue = this;
            vue.areas[index_area].tematicas[index_tematica].activities[index_activity].custom_options.splice(index_option,1);
        },
        openDeleteModal(resource,type){
            let vue = this;
            switch (type) {
                case 'activity':
                    vue.modalDeleteOptions.base_endpoint = `/entrenamiento/checklist/v2/${vue.checklist_id}/activity`;
                    vue.openFormModal(vue.modalDeleteOptions,resource,null,'Eliminar Actividad');
                break;
                default:
                    break;
            }
        },
        async chengeAgrupationChecklist(){
            let vue = this;
            await vue.$http.get(`/entrenamiento/checklist/v2/${vue.checklist_id}/change-agrupation`,{
                tematica:vue.resource
            }).then(()=>{
                vue.hideLoader();
                vue.loadSelects();
                vue.loadData();
                vue.$emit('onConfirm')
            })
        }
    }
}
</script>
<style lang="scss">
.number_order {
    font-size: 20px;
    font-weight: 700;
    color: #434D56;
    font-family: "Nunito", sans-serif;
    position: relative;
    &:after {
        content: '°';
    }
}
.btn_icon_action {
    display: flex;
    align-items: center;
    .text_default {
        font-size: 13px;
        color: #434D56;
        line-height: 1;
    }
    button.v-icon.v-icon {
        color: #5458EA !important;
    }
    .v-input.v-input--switch label.v-label {
        font-size: 13px;
        margin: 0;
        line-height: 1;
        min-width: 70px;
    }
}
.tooltip.b-tooltip {
    .tooltip-inner {
        padding: 10px 20px;
        color: #5458ea;
        background-color: #fff;
        border-radius: 8px;
        border: 2px solid #5458ea;
    }
    .arrow::before,
    .arrow::before {
        border-bottom-color: #5458ea !important;
        border-top-color: #5458ea !important;
    }
}
.add_cert {
    line-height: 1;
    text-align: center;
    display: flex;
    flex-direction: column;
    .v-icon {
        font-size: 16px;
        color: #5458ea !important;
        margin-right: 0 !important;
    }
    span.text_default {
        font-size: 12px !important;
        margin-top: 3px;
    }
}
.btn_add_activity {
    cursor: pointer;
    span.text_default {
        color: #5458EA !important;
    }
    &.disabled {
        span.text_default {
            color: #BDBEC0 !important;
        }
    }
}
.txt_duration {
    position: relative;
    .v-input input {
        padding-right: 32px;
        width: 70px;
    }
    span.txt_after {
        padding: 0 5px;
        color: #434D56;
        font-weight: 400;
        font-family: "Nunito", sans-serif;
        font-size: 13px;
        position: absolute;
        top: 50%;
        right: 4px;
        transform: translateY(-50%);
        align-items: center;
        display: flex;
    }
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] {
  -moz-appearance: textfield;
}
.bx_actions_area, .bx_actions_activities {
    display: flex;
    align-items: center;
    justify-content: space-around;
    .btn_action {
        line-height: 1;
        text-align: center;
        display: flex;
        flex-direction: column;
        cursor: pointer;
        i {
            font-size: 16px !important;
            color: #5458ea !important;
            margin-right: 0 !important;
        }
        .text_default {
            font-size: 11px;
        }
        &.disabled {
            i, span.text_default {
                color: #BDBEC0 !important;
            }
        }
    }
}
.btn_tooltip {
    cursor: pointer;
}
i.icon_size_drag {
    color: #D9D9D9 !important;
    font-size: 17px !important;
}
.bx_item_area {
    padding: 0 15px;
    border-right: 1px solid #D9D9D9;
    display: flex;
    align-items: center;
    span.text_default {
        color: #5458ea !important;
        font-size: 14px;
    }
    &:last-child {
        border: none;
    }
}
.item-draggable.areas.areas_tematicas:hover {
    background: #5458ea0a;
}
.input_edit_percentage {
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px solid #D9D9D9;
    border-radius: 4px;
    input[type="number"] {
        width: 28px;
        text-align: right;
        height: 30px;
    }
    span.text_default {
        line-height: 1;
        padding-top: 3px;
        padding-right: 10px;
    }
}
.chips-container {
  display: flex;
  overflow-x: auto;
  scrollbar-width: thin; /* Para navegadores que soportan esta propiedad */
}
</style>