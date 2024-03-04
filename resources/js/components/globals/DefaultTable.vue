<template>
    <section class="p-0">
        <v-data-table
            transition="fade"
            class="elevation-0"
            auto-width
            no-data-text="No se encontraron resultados"
            :loading="loading"
            loading-text="Cargando datos..."
            v-model="selectedRows"
            @input="onSelectRow"
            :headers="dataTable.headers"
            :items="rows"
            :show-select="showSelect"
            :items-per-page="pagination.rows_per_page"
            @update:sort-by="sortBy"
            @update:sort-desc="sortDesc"
            @update:items-per-page="updateItemsPerPage"
            :footer-props="footerProps"
            hide-default-footer
            :item-class="row_classes"
            :server-items-length="pagination.total_rows"
        >
            <template v-for="(h,index) in dataTable.headers" v-slot:[`header.${h.value}`]="{  }">
                <div v-if="h.value == 'custom-select'" class="d-flex align-items-center" style="width: 20px;" :key="index">
                    <v-checkbox
                        color="primary"
                        hide-details="false"
                        v-model="all_check_select"
                        @click="checkOrUncheckAllItems()"
                    ></v-checkbox>
                    <v-menu
                        v-model="menu_massive_actions"
                        attach
                        offset-x
                        right
                        nudge-bottom="10"
                        min-width="auto"
                    >
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn
                                text
                                icon
                                v-bind="attrs"
                                v-on="on"
                            >
                                <v-icon>mdi mdi-chevron-down</v-icon>
                            </v-btn>
                        </template>
                        <v-list dense>
                            <v-list-item
                                v-for="(item, i) in dataTable.customSelectActions"
                                :key="i"
                            >
                                <v-list-item-title
                                    @click="doAction({type:'action',method_name: item.method_name},rows.filter(r => r.selected))"
                                    style="cursor: pointer;"
                                >
                                    {{ item.text }}
                                </v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                </div>
                <div v-else class="d-inline">
                    {{ h.text }}
                    <v-tooltip top attach v-if="h.tooltip">
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon
                                small
                                color="primary"
                                dark
                                v-bind="attrs"
                                v-on="on"
                                v-text="'mdi-information'"
                                class="icon_tooltip"
                            />
                        </template>
                        <span v-text="h.tooltip"/>
                    </v-tooltip>
                </div>
            </template>

            <!-- VOTACIONES -->
            <template v-slot:item.etapa="{item, header}">
                <DefaultButton
                    dense
                    text
                    :label="item.etapa"
                    @click="doAction({type:'action', method_name: 'stage'}, item)"
                />
            </template>

            <!-- VOTACIONES - DETALLE -->
            <template v-slot:item.actions_sustent="{item, header}">
                <DefaultButton
                    dense
                    small
                    :color="(item.state_sustent) ? 'success' : 'primary'"
                    :disabled="!item.total"
                    :class="(!item.total) ? '' : (item.state_sustent) ? 'px-6' : 'px-7'"
                    :label="(!item.total) ? 'Pendiente' : (item.state_sustent) ? 'Validado' : 'Validar'"
                    @click="doAction({type:'action', method_name: 'sustent'}, item)"
                />


                <v-chip
                    v-if="dataObject.availableStageVotation"
                    class="ma-2"
                    color="primary"
                    :disabled="!item.candidate_state"
                    outlined
                    >
                  <v-icon class="mb-1" left>
                    {{ item.candidate_state ? 'mdi-check' : 'mdi-close' }}
                  </v-icon>
                  Candidato
                </v-chip>

            </template>

              <!-- VOTACIONES - DETALLE -->
            <template v-slot:item.criterio_porcent="{item, header}">
                <v-chip
                    class="ma-2"
                    :color="item.porcent_state ? 'success' : 'primary' "
                    outlined
                    >

                    {{ item.criterio_porcent + '%' }}

                </v-chip>
            </template>

            <template v-slot:item.position="{item, header}">
                <div class="d-flex flex-column align-items-center">
                    <v-btn
                        text icon
                        color="primary"
                        @click="changeOrder(item, 'up', header.model, header.field)"
                        v-show="item.canChangePosition || (!dataTable.filters && item.position && (!sortParams.sortBy || sortParams.sortBy == 'position'))"
                        :disabled="pagination.actual_page == 1 && rows.indexOf(item) == 0"
                    >
                        <v-icon v-text="'mdi-chevron-up'"/>
                    </v-btn>
                    <span v-text="item.position"/>
                    <v-btn
                        text icon
                        color="primary"
                        @click="changeOrder(item, 'down', header.model, header.field)"
                        v-show="item.canChangePosition || (!dataTable.filters && item.position && (!sortParams.sortBy || sortParams.sortBy == 'position'))"
                        :disabled="pagination.actual_page == pagination.total_pages && rows.indexOf(item) + 1 == rows.length"
                    >
                        <v-icon v-text="'mdi-chevron-down'"/>
                    </v-btn>
                </div>
            </template>
            <template v-slot:item.images="{ item, header }">
                <div class="d-flex justify-center ssflex-row my-2 " style="gap: 5px;"
                     v-if="item.images">
                    <v-img
                        v-for="(row, index) in item.images.slice(0, 3)"
                        max-height="50"
                        max-width="50"
                        :key="index"
                        :src="row.image"
                    >
                        <template v-slot:placeholder>
                            <v-row
                                class="fill-height ma-0"
                                align="center"
                                justify="center"
                            >
                                <v-progress-circular
                                    indeterminate
                                    color="grey lighten-5"
                                ></v-progress-circular>
                            </v-row>
                        </template>
                    </v-img>

                    <v-tooltip :left="true">
                        <!-- Icon -->
                        <template
                            v-slot:activator="{ on, attrs }">
                            <div
                                v-bind="attrs"
                                v-on="on"
                                v-if="item.images.length > 3"
                                class="mt-3"
                                style="position: relative; font-size: 18px; font-weight: 700; color: #5458ea">
                                ...
                            </div>
                        </template>

                        <!-- Tooltip message -->
                        <div v-html="generateImagesNamesList(item.images)" />
                    </v-tooltip>
                </div>
            </template>
            <template v-slot:item.tags="{ item, header }">
                <div class="d-flex justify-center ssflex-row my-2 " style="gap: 5px;"
                     v-if="item.tags">

                    <v-chip color="primary" small class="mx-1"
                            v-for="(row, index) in item.tags"
                            :key="index"
                    >
                        #{{ row.nombre || row.name }}
                    </v-chip>
                </div>
            </template>
            <template v-slot:item.status="{ item, header }">
                <div class="d-flex justify-center flex-row my-2"
                     v-if="item.status">
                    <v-chip
                        class="ma-2 white--text"
                        :color="item.status.color"
                    >
                        {{ item.status.text || item.status }}
                    </v-chip>
                </div>
            </template>
            <template v-slot:item.image="{ item, header }">
                <div class="d-flex justify-center flex-row my-2"
                     v-if="item.image">
                    <v-img
                        max-height="70"
                        max-width="70"
                        :src="item.image"
                    >
                        <template v-slot:placeholder>
                            <v-row
                                class="fill-height ma-0"
                                align="center"
                                justify="center"
                            >
                                <v-progress-circular
                                    indeterminate
                                    color="grey lighten-5"
                                ></v-progress-circular>
                            </v-row>
                        </template>
                    </v-img>
                </div>
            </template>

            <template v-slot:item.new_image="{ item, header }">
                <div class="d-flex justify-center flex-row my-2"
                     v-if="item.image">
                    <v-img
                        height="65"
                        max-width="100"
                        :src="item.image"
                        style="border-radius: 5px"
                    >
                        <template v-slot:placeholder>
                            <v-row
                                class="fill-height ma-0"
                                align="center"
                                justify="center"
                            >
                                <v-progress-circular
                                    indeterminate
                                    color="grey lighten-5"
                                ></v-progress-circular>
                            </v-row>
                        </template>
                    </v-img>
                </div>
            </template>
            <template v-slot:item.new_image_2="{ item, header }">
                <div class="d-flex justify-center flex-row my-2"
                     v-if="item.image">
                    <v-img
                        height="50"
                        max-width="100"
                        :src="item.image"
                    >
                        <template v-slot:placeholder>
                            <v-row
                                class="fill-height ma-0"
                                align="center"
                                justify="center"
                            >
                                <v-progress-circular
                                    indeterminate
                                    color="grey lighten-5"
                                ></v-progress-circular>
                            </v-row>
                        </template>
                    </v-img>
                </div>
            </template>
            <template v-slot:item.medium_image="{ item, header }">
                <div class="d-flex justify-center flex-row my-2"
                     v-if="item.image">
                    <v-img
                        max-height="90"
                        max-width="90"
                        :src="item.image"
                    >
                        <template v-slot:placeholder>
                            <v-row
                                class="fill-height ma-0"
                                align="center"
                                justify="center"
                            >
                                <v-progress-circular
                                    indeterminate
                                    color="grey lighten-5"
                                ></v-progress-circular>
                            </v-row>
                        </template>
                    </v-img>
                </div>
            </template>
            <template v-slot:item.statusActions="{ item, header }">
                <div class="default-table-actions d-flex justify-center flex-row my-2 position-relative"
                     v-if="dataTable.statusActions">

                    <div v-if="getStatusIcon(item) === 'active'"
                         @click="doAction({type: 'action', method_name: 'status'}, item)"
                         style="cursor: pointer">
                        <i class="row-icon fa fa-circle"/>
                        <br> <span class="table-default-icon-title" v-text="'Activo'"/>
                    </div>

                    <div v-if="getStatusIcon(item) === 'inactive'"
                         @click="doAction({type: 'action', method_name: 'status'}, item)"
                         style="cursor: pointer">
                        <i class="row-icon far fa-circle"/>
                        <br> <span class="table-default-icon-title" v-text="'Activo'"/>
                    </div>

                    <v-tooltip :left="true" attach class="scheduled-tooltip">
                        <!-- Icon -->
                        <template
                            v-slot:activator="{ on, attrs }">
                            <div v-if="getStatusIcon(item) === 'scheduled'"
                                 class="text-center"
                                 v-bind="attrs"
                                 v-on="on">
                                <i class="row-icon fa fa-clock"/>
                                <br> <span class="table-default-icon-title" v-text="'Programado'"/>
                            </div>
                        </template>

                        <!-- Tooltip message -->
                        <div v-html="generateScheduleMessage(item)" />
                    </v-tooltip>



                    <div v-if="getStatusIcon(item) === 'invisible'"
                         class="text-center">
                        <div class="position-relative fancy-menu-wrapper">

                            <i class="fas fa-exclamation-triangle"
                               style="color: red !important; font-size: 18px"></i>
                            <br> <span class="table-default-icon-title"
                                       v-text="'No visible'"/>

                            <ul class="fancy-menu">
                                <li class="red-title">
                                    <i class="fas fa-exclamation-triangle"
                                       style="color: red !important; font-size: 12px"></i>
                                    Configuración faltante
                                </li>
                                <li @click="doAction({type:'route', route: 'temas_route'}, item)">
                                    Agregar o activar temas del curso
                                </li>
                                <li @click="doAction({type: 'action', method_name: 'segmentation'}, item)">
                                    Asignar colaboradores al curso
                                </li>
                            </ul>
                        </div>
                    </div>



                </div>
            </template>
            <template v-slot:item.actions="{ item, header }">
                <div class="default-table-actions d-flex justify-center flex-row my-2"
                     v-if="dataTable.actions && dataTable.actions.length > 0">
                    <!-- {{ item }} -->
                    <div v-for="action in dataTable.actions">
                        <button
                            type="button" class="btn btn-md position-relative"
                            :title="action.text"
                            @click="item.disabled_btns_actions ? null : doAction(action, item)"
                            v-if="!action.show_condition || (action.show_condition && item[action.show_condition])"
                            :class="{'default-table-disable-action-btn' : action.disable_btn || (action.disable_btn && item[action.disable_btn])}"
                        >
                            <v-badge
                                v-if="(action.count && item[action.count])"
                                :color="item.active ? 'primary': 'grey'"
                                :content="item[action.count]"
                            >
                                <i :class="action.method_name == 'status' ? (item.active ? action.icon : 'far fa-circle')  : action.icon"/>
                                <br> <span class="table-default-icon-title" v-text="action.text ? action.text : (item.active ? 'Activo' : 'Inactivo')"/>
                            </v-badge>

                            <!--
                                Badge counts and icons
                            ================================================= -->

                            <template v-else-if="(action.countBadgeConditions)">
                                <i :class="action.icon"/>

                                <v-tooltip :left="true" attach>

                                    <!-- Badge count -->
                                    <template
                                        v-slot:activator="{ on, attrs }">
                                        <span :class="'badge-count'"
                                              v-bind="attrs"
                                              v-on="on"
                                              :style="{backgroundColor: item.active ? getConditionalCountColor(item, action.countBadgeConditions) : 'gray'}">
                                            {{ getConditionalCount(item, action.countBadgeConditions) }}
                                        </span>
                                    </template>

                                    <!-- Tooltip message -->
                                    <div v-html="getConditionalCountMessage(item, action.countBadgeConditions)" />

                                </v-tooltip>

                                <br> <span class="table-default-icon-title" v-text="action.text"/>
                            </template>

                            <!--
                                Badge Icons
                            ================================================= -->

                            <template v-else-if="(action.conditionalBadgeIcon)">
                                <i :class="action.icon"/>

                                <v-tooltip :left="true" attach>
                                    <template v-slot:activator="{ on, attrs }">
                                        <i :class="[getConditionalBadgeIcon(item, action.conditionalBadgeIcon), {'color_icons': type_table=='process'}]"
                                           class="badge-icon"
                                           v-bind="attrs"
                                           v-on="on"
                                           :style="{
                                               fontSize:getConditionalBadgeIconSize(item, action.conditionalBadgeIcon),
                                               color: ((item.active || getConditionalBadgeIconColorActive(item, action.conditionalBadgeIcon)) ? getConditionalBadgeIconColor(item, action.conditionalBadgeIcon)  : 'grey') +'  !important'}"></i>
                                    </template>

                                    <!-- Tooltip message -->
                                    <div v-html="getConditionalBadgeIconMessage(item, action.conditionalBadgeIcon)" />

                                </v-tooltip>

                                <br> <span class="table-default-icon-title" v-text="action.text"/>
                            </template>

                            <template v-else>
                                <i :class="action.method_name == 'status' ? (item.active ? action.icon : 'far fa-circle')  : action.icon"/>
                                <br> <span class="table-default-icon-title" v-text="action.text ? action.text : (item.active ? 'Activo' : 'Inactivo')"/>
                            </template>
                        </button>
                        <!-- <span class="badge table_default_badge-notify"
                              v-if="action.count"
                              v-text="item[action.count]"/> -->
                    </div>
                    <div class="table-default-more-actions"
                         v-if="dataTable.more_actions && dataTable.more_actions.length > 0 && isAvailableMoreActions(item)">
                        <v-menu
                            attach
                            tile
                            bottom
                            left
                            close-on-content-click
                            close-on-click
                            class="elevation-0"
                        >
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                    color="primary"
                                    icon
                                    v-bind="attrs"
                                    v-on="on"
                                >
                                    <v-icon v-text="'mdi-dots-horizontal'"/>
                                </v-btn>
                            </template>

                            <v-list dense>
                                <!--                                <v-list-item-group-->
                                <!--                                    v-model="more_actions_selectedItem"-->
                                <!--                                    color="primary"-->
                                <!--                                >-->
                                <v-list-item
                                    style="cursor: pointer;"
                                    v-for="action in dataTable.more_actions"
                                    :key="action.text"
                                    v-if="!action.show_condition || (action.show_condition && item[action.show_condition])"
                                >
                                    <v-list-item-content @click="doAction(action, item)">

                                        <v-list-item-title class="d-flex justify-content-start">
                                            <v-icon color="primary" class="mx-1" small
                                                    v-text="action.method_name == 'status' ? (item.active ? action.icon : 'far fa-circle')  : action.icon"/>

                                            {{ action.text }}

                                            <template v-if="action.count && item[action.count]">
                                                &nbsp;<strong>[{{ item[action.count] }}]</strong>
                                            </template>

                                        </v-list-item-title>

                                    </v-list-item-content>

                                </v-list-item>
                                <!--                                </v-list-item-group>-->
                            </v-list>
                        </v-menu>
                    </div>
                </div>
            </template>
            <template v-slot:item.actions_extras="{ item, header }">
                <div class="default-table-actions d-flex justify-center flex-row my-2"
                     v-if="dataTable.actions_extras && dataTable.actions_extras.length > 0">
                    <div class="table-default-more-actions"
                         v-if="dataTable.actions_extras && dataTable.actions_extras.length > 0 && isAvailableMoreActions(item)">
                        <v-menu
                            attach
                            tile
                            bottom
                            left
                            close-on-content-click
                            close-on-click
                            class="elevation-0"
                        >
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                    color="primary"
                                    icon
                                    v-bind="attrs"
                                    v-on="on"
                                >
                                    <v-icon v-text="'mdi-dots-horizontal'"/>
                                </v-btn>
                            </template>

                            <v-list dense>
                                <v-list-item
                                    style="cursor: pointer;"
                                    v-for="action in dataTable.actions_extras"
                                    :key="action.text"
                                    v-if="!action.show_condition || (action.show_condition && item[action.show_condition])"
                                >
                                    <v-list-item-content @click="doAction(action, item)">

                                        <v-list-item-title class="d-flex justify-content-start">
                                            <v-icon color="primary" class="mx-1" small
                                                    v-text="action.method_name == 'status' ? (item.active ? action.icon : 'far fa-circle')  : action.icon"/>

                                            {{ action.text }}

                                            <template v-if="action.count && item[action.count]">
                                                &nbsp;<strong>[{{ item[action.count] }}]</strong>
                                            </template>

                                        </v-list-item-title>

                                    </v-list-item-content>

                                </v-list-item>
                            </v-list>
                        </v-menu>
                    </div>
                </div>
            </template>

            <template v-slot:item.custom-select="{item,header,index}">
                {{index}}
                <v-checkbox
                    color="primary"
                    hide-details="false"
                    v-model="item.selected"
                >
                </v-checkbox>
            </template>
            <!--   CUSTOM COLUMNS -->

            <template v-slot:item.status_meeting="{ item, header }">
                <div class="d-flex justify-center flex-row my-2"
                     v-if="item.status_meeting">
                     <span :style="{'color': `${item.status_meeting.color}`, 'font-weight':'700'}">
                        {{ item.status_meeting.text || item.status_meeting }}
                    </span>
                </div>
            </template>
            <template v-slot:item.custom_block="{item, header}">


                <v-expansion-panels flat class="custom-expansion-block">
                    <v-expansion-panel>
                      <v-expansion-panel-header v-slot="{ open }">
                        <v-row no-gutters>
                          <v-col cols="4">
                            {{ item.name }}
                          </v-col>
                          <v-col
                            cols="8"
                            class="text--secondary"
                          >
                            <v-fade-transition leave-absolute>
                              <span v-if="open">Creado el {{ item.created_at }}</span>
                              <v-row
                                v-else
                                no-gutters
                                style="width: 100%"

                              >
                                <v-col cols="6">

                                    <v-chip
                                        class="default-chip ml-2 my-1"
                                        x-small
                                        color="primary"
                                        active-class="default-chip"
                                        v-for="(criterion_value, ind) in item.criterion_values"
                                        :key="'block-value-' + ind"
                                    >
                                        {{ criterion_value.value_text }}
                                        <!-- <v-icon x-small class="ml-1">mdi-book</v-icon> -->
                                    </v-chip>

                                </v-col>
                                <v-col cols="6">
                                    <v-chip
                                        class="default-chip ml-2 my-1"
                                        x-small
                                        color="primary"
                                        active-class="default-chip"
                                    >
                                        {{ item.segments_count }} segmentos
                                        <v-icon x-small class="ml-1">mdi-star</v-icon>
                                    </v-chip>
                                    <v-chip
                                        class="default-chip ml-2 my-1"
                                        x-small
                                        color="primary"
                                        active-class="default-chip"
                                    >
                                        {{ item.children_count }} rutas
                                        <v-icon x-small class="ml-1">mdi-book</v-icon>
                                    </v-chip>

                          <!--           <button
                                        type="button" class="btn btn-md"
                                        :title="action.text"
                                        @click="doAction(action, item)"
                                    >
                                        <v-badge
                                            v-if="(action.count && item[action.count])"
                                            :color="item.active ? 'primary': 'grey'"
                                            :content="item[action.count]"
                                        >
                                            <i :class="action.method_name == 'status' ? (item.active ? action.icon : 'far fa-circle')  : action.icon"/>
                                            <br> <span class="table-default-icon-title" v-text="action.text"/>
                                        </v-badge>

                                        <template v-else>
                                            <i :class="action.method_name == 'status' ? (item.active ? action.icon : 'far fa-circle')  : action.icon"/>
                                            <br> <span class="table-default-icon-title" v-text="action.text"/>
                                        </template>
                                    </button>
 -->
                                </v-col>
                              </v-row>
                            </v-fade-transition>
                          </v-col>
                        </v-row>
                      </v-expansion-panel-header>
                      <v-expansion-panel-content v-if="item.children_count">
                        <v-row
                            justify="space-around"
                            no-gutters
                            v-for="(row, i) in item.block_children"
                            :key="'segment-' + i"
                            class="segments"
                        >
                          <v-col cols="6">
                            {{ row.child.name }}
                            <!-- #{{ segment.id }} -->
                          </v-col>

                          <v-col cols="3">

                                <!-- v-for="(segment, index) in row.child.segments" -->
                            <v-chip
                                class="default-chip ml-2 my-1"
                                x-small
                                color="primary"
                                active-class="default-chip"
                            >
                                <!-- {{ row.child.segments_count }} segmentos -->
                                {{ row.child.segments.length }} segmentos
                                <v-icon x-small class="ml-1">mdi-star</v-icon>
                            </v-chip>

                            <!-- {{ segment.id }} -->

                          </v-col>

                          <v-col cols="3">

                            <v-chip
                                class="default-chip ml-2 my-1"
                                x-small
                                color="primary"
                                active-class="default-chip"
                            >

                                {{ row.child.courses.length }} cursos
                                <!-- <v-icon x-small class="ml-1">mdi-book</v-icon> -->
                            </v-chip>

                            <!-- {{ segment.id }} -->

                          </v-col>
                        </v-row>
                      </v-expansion-panel-content>
                    </v-expansion-panel>
                </v-expansion-panels>

            </template>


            <template v-slot:item.custom_block_courses="{item, header}">

                <v-expansion-panels flat class="custom-expansion-block">
                    <v-expansion-panel>
                      <v-expansion-panel-header v-slot="{ open }">
                        <v-row no-gutters>
                          <v-col cols="3">
                            <v-img src="/img/we-connect-logo.png" class="" style="width: 85%;" />
                          </v-col>
                          <v-col
                            cols="9"
                            class="text--secondary"
                          >
                            <v-fade-transition leave-absolute>
                              <v-row
                                no-gutters
                                style="width: 100%"

                              >
                                <v-col cols="8">
                                    <h5>{{ item.name }}</h5>

                                    <span >Creado el {{ item.created_at }}</span>

                                    <br />

                                    <v-chip
                                        class="default-chip mt-3"
                                        x-small
                                        color="primary"
                                        active-class="default-chip"
                                    >
                                        {{ item.segments_count }} segmentos
                                        <v-icon x-small class="ml-1">mdi-star</v-icon>
                                    </v-chip>
                                    <v-chip
                                        class="default-chip ml-2 mt-3"
                                        x-small
                                        color="primary"
                                        active-class="default-chip"
                                    >
                                        {{ item.children_count }} rutas
                                        <v-icon x-small class="ml-1">mdi-book</v-icon>
                                    </v-chip>

                                </v-col>
                                <v-col cols="4">
                                    <v-chip
                                        class="default-chip ml-2 my-1"
                                        x-small
                                        color="primary"
                                        active-class="default-chip"
                                    >
                                        {{ item.segments_count }} segmentos
                                        <v-icon x-small class="ml-1">mdi-star</v-icon>
                                    </v-chip>
                                    <v-chip
                                        class="default-chip ml-2 my-1"
                                        x-small
                                        color="primary"
                                        active-class="default-chip"
                                    >
                                        {{ item.children_count }} rutas
                                        <v-icon x-small class="ml-1">mdi-book</v-icon>
                                    </v-chip>

                                </v-col>
                              </v-row>
                            </v-fade-transition>
                          </v-col>
                        </v-row>
                      </v-expansion-panel-header>
                      <v-expansion-panel-content v-if="item.children_count">
                        <v-row
                            justify="space-around"
                            no-gutters
                            v-for="(row, i) in item.block_children"
                            :key="'segment-' + i"
                            class="segments"
                        >
                          <v-col cols="6">
                            {{ row.child.name }}
                            <!-- #{{ segment.id }} -->
                          </v-col>

                          <v-col cols="3">

                                <!-- v-for="(segment, index) in row.child.segments" -->
                            <v-chip
                                class="default-chip ml-2 my-1"
                                x-small
                                color="primary"
                                active-class="default-chip"
                            >
                                <!-- {{ row.child.segments_count }} segmentos -->
                                {{ row.child.segments.length }} segmentos
                                <v-icon x-small class="ml-1">mdi-star</v-icon>
                            </v-chip>

                            <!-- {{ segment.id }} -->

                          </v-col>

                          <v-col cols="3">

                            <v-chip
                                class="default-chip ml-2 my-1"
                                x-small
                                color="primary"
                                active-class="default-chip"
                            >

                                {{ row.child.courses.length }} cursos
                                <!-- <v-icon x-small class="ml-1">mdi-book</v-icon> -->
                            </v-chip>

                            <!-- {{ segment.id }} -->

                          </v-col>
                        </v-row>
                      </v-expansion-panel-content>
                    </v-expansion-panel>
                </v-expansion-panels>

            </template>

            <template v-slot:item.nombre_and_requisito="{item, header}">
                <p class="my-0">{{ item.nombre_and_requisito.nombre }}</p>
                <p class="my-0" v-if="item.nombre_and_requisito.requisito"><small><strong>Requisito:</strong> {{ item.nombre_and_requisito.requisito }}</small></p>
            </template>

            <template v-slot:item.curso_estado="{item, header}">
                <p class="my-0">
                    {{ item.curso_estado.estado }}
                    <template v-if="item.curso_estado.icon">
                        <v-icon v-text="`${item.curso_estado.icon.name}`" :title="item.curso_estado.icon.title" small color="primary"/>
                    </template>
                </p>
                <p class="my-0 course-status-subtitles" v-if="item.curso_estado.subtitles">
                    <span v-for="(subtitle, index) in item.curso_estado.subtitles" :key="index">
                        <small :class="subtitle.class" >{{ subtitle.name }}</small>
                    </span>
                </p>
                <div v-if="item.actualizaciones.length > 0" class="customm-cursos-box-actualizaciones">
                    <small v-for="(act, index) in item.actualizaciones" :key="index" v-html="act"/>
                </div>
            </template>

            <template v-slot:item.curso_nombre_escuela="{item, header}">
                <div class="py-2">
                    <p class="my-0">
                        {{ item.curso_nombre_escuela.curso }}
                    </p>
                    <p class="my-0" v-if="item.curso_nombre_escuela.escuela"><small><strong>Escuela:</strong> {{ item.curso_nombre_escuela.escuela }}</small></p>
                    <div class="d-flex ---justify-center mt-2 " style="gap: 5px;" v-if="item.images">

                     <!--    <span v-for="(row, index) in item.images" :key="index" :title="row.name || 'Logo'"
                            :style="`
                                background-image: url(${row.image});
                                background-size: contain;
                                height: 15px;
                                width: 100%;
                                max-width: 50px;
                            `"
                        >
                        </span>
 -->
                        <v-img
                            v-for="(row, index) in item.images"
                            height="15"
                            max-width="50px"
                            :key="index"
                            :src="row.image"
                            :title="row.name || 'Logo'"
                            style="opacity: 0.65;"
                        >
                            <template v-slot:placeholder>
                                <v-row
                                    class="fill-height ma-0"
                                    align="center"
                                    justify="center"
                                >
                                    <v-progress-circular
                                        indeterminate
                                        color="grey lighten-5"
                                    ></v-progress-circular>
                                </v-row>
                            </template>
                        </v-img>
                    </div>

                </div>
            </template>

            <template v-slot:item.escuela_nombre="{item, header}">
                <div class="py-2">
                    <p class="my-0">
                        {{ item.escuela_nombre.name }}
                    </p>

                    <!-- <div class="d-flex ---justify-center mt-2 " style="gap: 5px;" v-if="item.images">

                        <v-img
                            v-for="(row, index) in item.images"
                            height="15"
                            max-width="50px"
                            :key="index"
                            :src="row.image"
                            :title="row.name || 'Logo'"
                            style="opacity: 0.65;"
                        >
                            <template v-slot:placeholder>
                                <v-row
                                    class="fill-height ma-0"
                                    align="center"
                                    justify="center"
                                >
                                    <v-progress-circular
                                        indeterminate
                                        color="grey lighten-5"
                                    ></v-progress-circular>
                                </v-row>
                            </template>
                        </v-img>
                    </div> -->

                </div>
            </template>

            <template v-slot:item.tema_evaluacion="{item, header}">
                <p class="my-0">{{ item.tema_evaluacion.title }}</p>
                <p class="my-0" v-if="item.tema_evaluacion.subtitle"><small>{{ item.tema_evaluacion.subtitle }}</small></p>
            </template>

            <template v-slot:item.custom_error="{item, header}">
                <p class="my-0"><strong>{{ item.custom_error.title }}</strong></p>
                <p class="my-0"><small>{{ item.custom_error.subtitle }}</small></p>
            </template>

            <!--   Aulas Virtuales -->
            <template v-slot:item.custom_aulas_virtuales_fecha="{item, header}">
                <p class="my-0">{{ item.date }}</p>
                <p class="my-0">{{ item.hora }}</p>
            </template>
            <!--   ===================================================  -->

            <!--   Notificaciones Push Firebase -->
            <template v-slot:item.custom_notification_push_efectividad="{item, header}">
                <DefaultStaticProgressLinear
                    :text="`${item.custom_notification_push_efectividad}%`"
                />
            </template>
            <!--   ===================================================  -->

            <!--   TEMA PREGUNTAS (EVALUACIONES) -->
            <template v-slot:item.custom_tema_preguntas_pregunta="{item, header}">
                <p class="my-0" v-html="item.custom_tema_preguntas_pregunta"/>
            </template>
            <!--   ===================================================  -->

            <!--   CURSOS  -->
            <template v-slot:item.custom_curso_nombre="{item, header}">
                <div class="py-2">

                    <p class="my-0" v-text="item.custom_curso_nombre.nombre"/>
                    <div class="d-flex justify-content-start modules-images pt-3">
                        <v-img
                            v-for="(row, index) in item.images.slice(0,3)"
                            max-height="50"
                            max-width="50"
                            :key="index"
                            :src="row.image"
                            class="mr-3"
                            :title="row.name"
                            style="border-radius: 15px"
                        >
                            <template v-slot:placeholder>
                                <v-row
                                    class="fill-height ma-0"
                                    align="center"
                                    justify="center"
                                >
                                    <v-progress-circular
                                        indeterminate
                                        color="grey lighten-5"
                                    ></v-progress-circular>
                                </v-row>
                            </template>
                        </v-img>
                        <v-tooltip :left="true">
                            <!-- Icon -->
                            <template
                                v-slot:activator="{ on, attrs }">
                                <div
                                    v-bind="attrs"
                                    v-on="on"
                                    v-if="item.images.length > 3"
                                    class="mt-3"
                                    style="position: relative; font-size: 18px; font-weight: 700; color: #5458ea">
                                    ...
                                </div>
                            </template>

                            <!-- Tooltip message -->
                            <div v-html="generateImagesNamesList(item.images)" />
                        </v-tooltip>
                    </div>


                 <!--    <p class="my-0 course-status-subtitles" v-if="item.custom_curso_nombre.subtitles">
                        <span v-for="(subtitle, index) in item.custom_curso_nombre.subtitles" :key="index">
                            <small :class="subtitle.class" :title="subtitle.title">{{ subtitle.name }}</small>
                        </span>
                    </p>

                    <div class="d-flex ---justify-center mt-2 " style="gap: 5px;" v-if="item.images">

                            <v-img
                                v-for="(row, index) in item.images"
                                height="15"
                                max-width="50px"
                                :key="index"
                                :src="row.image"
                                :title="row.name || 'Logo'"
                                style="opacity: 0.65;"
                            >
                                <template v-slot:placeholder>
                                    <v-row
                                        class="fill-height ma-0"
                                        align="center"
                                        justify="center"
                                    >
                                        <v-progress-circular
                                            indeterminate
                                            color="grey lighten-5"
                                        ></v-progress-circular>
                                    </v-row>
                                </template>
                            </v-img>
                        </div> -->

                    <!-- <p class="my-0">
                        <small ><strong>Creado: </strong>{{ item.created_at }}</small>
                    </p> -->

                    <div v-if="item.actualizaciones.length > 0" class="customm-cursos-box-actualizaciones">
                        <small v-for="(act, index) in item.actualizaciones" :key="index" v-html="act"/>
                    </div>
                </div>

            </template>
            <!--   ===================================================  -->

            <!-- BENEFICIOS -->
            <template v-slot:item.benefit_speaker="{ item, header }">
                <div v-if="item.type != null && item.type.code == 'descuento'">
                    <div class="text-center" style="color: #b8b8b8;">
                        No aplica
                    </div>
                </div>
                <div v-else>
                    <div class="text-center" v-if="item.benefit_speaker">
                        {{ item.benefit_speaker }}
                    </div>
                    <div class="text-center" v-else>
                        <span class="custom_link_add_speaker" @click="addSpeaker(item)">Agregar facilitador(a)</span>
                    </div>
                </div>
            </template>
            <template v-slot:item.benefit_type="{ item, header }">
                <div class="text-center" v-if="item.benefit_type">
                    {{ item.benefit_type }}
                </div>
                <div class="text-center" v-else>
                    <span class="custom_benefit_type">Pendiente</span>
                </div>
            </template>
            <template v-slot:item.benefit_stars="{ item, header }">
                <div class="text-center" v-if="item.benefit_stars">
                    {{ item.benefit_stars }}
                </div>
                <div class="text-center" v-else>
                    <span class="custom_benefit_stars">Pendiente</span>
                </div>
            </template>
            <template v-slot:item.perfil_speaker="{ item, header }">
                <div class="tbl_perfil_speaker">
                    <img :src="item.perfil_speaker" v-if="item.perfil_speaker">
                </div>
            </template>
            <template v-slot:item.title_process="{ item, header }">
                <div v-if="item.edit_inline == true" class="input_edit_process">
                    <input type="text" v-on:keyup.enter="saveNewProcessInline(item)" v-model="item.title" ref="input_edit_process" placeholder="Escribe el título de tu proceso de inducción">
                </div>
                <div v-else>
                    {{ item.title_process }}
                </div>
            </template>
            <template v-slot:item.progress_process="{ item, header }">
                <div v-if="item.progress_process == null">
                    <span class="custom_benefit_type">Pendiente de asignación</span>
                </div>
                <div class="bx_progress_linear" v-else>
                    <DefaultStaticProgressLinear
                        :text="`${item.progress_process}%`"
                        color="#25B374"
                        height="20"
                        background-color="#E6E6E6"
                    />
                </div>
            </template>

            <!-- CUSTOM -->
            <template v-slot:[`item.custom_slot`]="{item}">
                <slot name="custom_slot" v-bind="{ item }" />
            </template>
        </v-data-table>
        <!--   Custom Paginator -->

        <div class="ps-3 mt-4" v-if="type_table=='process'">
            <button @click="addNewProcessInline" class="btn_add_new_process_inline">+ Nuevo proceso de inducción</button>
        </div>
        <section>
            <v-row class="justify-content-end" no-gutters>
                <v-col cols="4" lg="2" sm="3" class="d-flex justify-content-end">
                    <v-select
                        v-if="!hideRowsPerPage"
                        dense
                        hide-details="auto"
                        attach
                        :items="[10,15,20,25,30]"
                        v-model="pagination.rows_per_page"
                        :menu-props="{ top: true, offsetY: true }"
                        class="table-default-items-per-page"
                    >
                        <template v-slot:prepend>
                            <div class="d-flex align-items-center">
                                <small v-text="'Filas por página'"/>
                            </div>
                        </template>
                        <!--                        <template v-slot:append-outer>-->
                        <!--                            <div class="d-flex align-items-center">-->
                        <!--                                <small-->
                        <!--                                    v-text="`${pagination.fromRow} - ${pagination.toRow} de ${pagination.total_rows}`"/>-->
                        <!--                            </div>-->
                        <!--                        </template>-->
                    </v-select>
                </v-col>
                <v-col cols="4" lg="1" sm="2" class="d-flex align-items-end justify-content-around">
                    <small
                        v-text="`${pagination.fromRow} - ${pagination.toRow} de ${pagination.total_rows}`"/>
                </v-col>
                <v-col cols="4" lg="1" sm="1" class="d-flex align-items-center justify-content-around">
                    <v-icon :disabled="pagination.actual_page === 1" v-text="'mdi-chevron-left'"
                            @click="changePage(false)"/>
                    <v-icon :disabled="pagination.actual_page === pagination.total_pages" v-text="'mdi-chevron-right'"
                            @click="changePage(true)"/>
                </v-col>
            </v-row>
        </section>
        <!--   ===================================================  -->

    </section>

</template>

<script>
import DefaultStaticProgressLinear from "./DefaultStaticProgressLinear";

export default {
    components: {DefaultStaticProgressLinear},
    props: {
        dataTable: {
            required: true
        },
        showSelect: {
            type: Boolean,
            default: false
        },
        filters: {
            type: Object,
        },
        hideRowsPerPage: {
            type: Boolean,
            default: false
        },
        rowsPerPage: {
            type: Number,
            default: 10
        },
        defaultSortBy: {
            type: String,
            default: null
        },
        defaultSortDesc: {
            type: Boolean,
            default: false
        },
        dataObject: {
            type: Object
        },
        avoid_first_data_load: {
            type: Boolean,
            default: false,
        },
        type_table: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            // data: () => ({
            last_sorted: Date.now(),
            loading: false,
            sortParams: {
                sortBy: this.defaultSortBy,
                sortDesc: this.defaultSortDesc,
            },
            more_actions_selectedItem: null,
            pagination: {
                total_pages: 1,
                actual_page: 1,
                rows_per_page: this.rowsPerPage,
                fromRow: 1,
                toRow: 1,
                total_rows: 0,
            },
            rows: [],
            selectedRows: [],
            footerProps: {
                'items-per-page-options': [10, 15, 20, 25, 30],
            },
            menu_massive_actions:false,
            all_check_select:false,
        }
        // ),
    },
    mounted() {
        let vue = this;
        if (!vue.avoid_first_data_load){
            let filters = this.addParamsToURL(this.dataTable.filters, this.filters)
            this.getData(filters)
        }
    },
    watch: {
        sortParams: {
            handler(val) {

                let vue = this;

                let new_date = Date.now()

                let time_passed = new_date - vue.last_sorted

                if (time_passed > 50) {
                    let filters = vue.addParamsToURL(vue.dataTable.filters, vue.filters)

                    vue.getData(filters)

                    vue.last_sorted = new_date
                } else {
                    console.log('time_passed not enough')
                }

            },
            deep: true
        }
    },
    methods: {
        isAvailableMoreActions(item) {
            return true
            let vue = this
            let show_more_actions = false
            let more_actions = vue.dataTable.more_actions
            more_actions.forEach(el => {
                let prop = el.show_condition
                console.log(`PROP => ${prop}`)
                if (!prop) {
                    show_more_actions = true
                    return
                }
                console.log(`VALUE ITEM[PROP] => ${item[prop]}`)
                if (item[prop]) {
                    show_more_actions = true
                    return
                }
            })
            return show_more_actions
        },
        row_classes(item) {
            if (item.active == false) {
                return "row-inactive"; //can also return multiple classes e.g ["orange","disabled"]
            }
        },
        getData(filters = '', page = null) {
            let vue = this
            vue.loading = true
            if (page)
                vue.pagination.actual_page = page

            let url = `${vue.dataTable.endpoint}?` +
                `page=${page || vue.pagination.actual_page}` +
                `&paginate=${vue.pagination.rows_per_page}`


            // vue.$nextTick()

            // vue.$nextTick(() => {
            //     console.log('vue.sortParams')
            //     console.log(vue.sortParams)
            // });
            // if (vue.sortParams.sortBy&&vue.sortParams.sortDesc) // Add param to sort result
            //     url += `&sortBy=${vue.sortParams.sortBy}&sortDesc=${vue.sortParams.sortDesc}`

            if (vue.sortParams.sortBy) // Add param to sort result
                url += `&sortBy=${vue.sortParams.sortBy}`

            if (vue.sortParams.sortDesc) // Add param to sort orientation
                url += `&sortDesc=${vue.sortParams.sortDesc}`

            url += filters
            this.$http.get(url)
                .then(({data}) => {
                    data.data.data.forEach(e => e.selected=false);
                    vue.rows = data.data.data

                    // if (vue.filters !== "")
                    if (vue.pagination.actual_page > data.data.total_pages)
                        vue.pagination.actual_page = data.data.total_pages

                    vue.pagination.total_pages = data.data.last_page;
                    vue.pagination.fromRow = data.data.from || 0;
                    vue.pagination.toRow = data.data.to || 0;
                    vue.pagination.total_rows = data.data.total;
                    vue.loading = false;
                    vue.$emit('data-loaded', data);
                })
        },
        changePage(sum) {
            let vue = this
            if (sum) {
                if (vue.pagination.actual_page < vue.pagination.total_pages)
                    vue.pagination.actual_page++
            } else {
                if (vue.pagination.actual_page > 1)
                    vue.pagination.actual_page--
                else
                    vue.pagination.actual_page = 1
            }
            let filters = vue.addParamsToURL(vue.dataTable.filters, vue.filters)
            vue.getData(filters)
        },
        updateItemsPerPage(rowsPerPage) {
            let vue = this
            vue.pagination.rows_per_page = rowsPerPage
            let filters = vue.addParamsToURL(vue.dataTable.filters, vue.filters)
            vue.getData(filters)
        },
        doAction(action, rowData) {
            let vue = this
            if (action.type === 'action') { // Emit parent method
                // console.log("METHOD EMIT :: ", action.method_name)
                vue.$emit(action.method_name, rowData)
            } else { // Navigate
                // console.log('navegar')
                if (action.route) {
                    if (action.route_type == 'external') {
                        window.open(rowData[action.route]);
                    } else {
                        window.location.href = rowData[action.route]
                    }
                }
            }
        },
        sortBy(sortProperty) {
            // console.log('sortBy')
            // console.log(sortProperty)
            let vue = this
            let value = sortProperty.length > 0 ? sortProperty[0] : null

            if (vue.sortParams.sortBy != value) {
                // console.log('sortBy changed')
                vue.sortParams.sortBy = value
            }

            // let filters = vue.addParamsToURL(vue.dataTable.filters, vue.filters)

            // vue.getData(filters)
        },
        sortDesc(sortDesc) {
            // console.log('sortDesc')
            // console.log(sortDesc)
            let vue = this
            let value = sortDesc.length > 0 && vue.sortParams.sortBy !== null ? sortDesc[0] : false

            if (vue.sortParams.sortDesc != value) {
                // console.log('sortDesc changed')
                vue.sortParams.sortDesc = value

                // let vue = this;

            }
        },
        onSelectRow() {
            let vue = this
            vue.$emit('onSelectRow', vue.selectedRows)
        },
        // ORDER METHODS
        statusUp() {

        },
        statusDown() {

        },
        // changeOrder(model_name, item, subir, prop_name = 'orden') {
        async changeOrder(item, action, model, field = 'position') {

            let vue = this

            this.showLoader()

            let new_action = action == 'down' ? 'up' : 'down';

            action = vue.sortParams.sortDesc ? action : new_action;

            let url = `/common/change_order`
            let data = {
                id: item.id,
                model,
                action,
                field,
                pivot_id_selected:item.pivot_id_selected || null
            }

            await vue.$http.put(url, data)
                .then(({data}) => {
                    vue.showAlert(data.data.msg)
                    let filters = vue.addParamsToURL(vue.dataTable.filters, vue.filters)
                    vue.getData(filters)
                    this.hideLoader()
                })

            this.hideLoader()
        },
        addSpeaker( item ) {
            let vue = this
            vue.$emit('addSpeaker', item)
        },
        checkOrUncheckAllItems(){
            let vue = this;
            vue.rows.forEach(item => (item.selected = vue.all_check_select));
        },
        getStatusIcon(item) {

            let iconType = ''
            let isScheduled = (item.activate_at && !item.active) ||
                (item.deactivate_at && item.active);

            if (isScheduled) {
                iconType = 'scheduled'
            } else {
                if (item.active) {

                    if (item.active_topics_count) {
                        iconType = 'active'
                    } else {
                        iconType = 'invisible' // active without topics
                    }

                } else {
                    iconType = 'inactive'
                }
            }

            return iconType
        },
        getConditionalCount(item, conditions) {
            let condition = this.getMatchedCondition(item, conditions)
            return item[condition['propertyShow']]

        },
        getConditionalCountColor(item, conditions) {
            let condition = this.getMatchedCondition(item, conditions)
            return condition['backgroundColor']
        },

        getConditionalCountMessage(item, conditions) {
            let condition = this.getMatchedCondition(item, conditions)
            return condition['message']
        },

        getConditionalCountMessageIcon(item, conditions) {
            let condition = this.getMatchedCondition(item, conditions)
            return condition['icon']
        },

        getConditionalBadgeIconColor(item, conditions) {
            let condition = this.getMatchedCondition(item, conditions)
            return condition['color']
        },

        getConditionalBadgeIcon(item, conditions) {
            let condition = this.getMatchedCondition(item, conditions)
            return condition['icon']
        },
        getConditionalBadgeIconSize(item, conditions) {
            let condition = this.getMatchedCondition(item, conditions)
            return condition['iconSize']
        },
        getConditionalBadgeIconMessage(item, conditions) {
            let condition = this.getMatchedCondition(item, conditions)
            return condition['message']
        },
        getConditionalBadgeIconColorActive(item, conditions) {
            let condition = this.getMatchedCondition(item, conditions)
            return condition['colorActive']
        },
        getMatchedCondition(item, conditions) {

            let higherIndex = -1;
            conditions.forEach((condition, index) => {
                const key = condition['propertyCond'];
                const value = item[key];
                if (value >= condition['minValue']) {
                    higherIndex = index;
                }
            })

            return conditions[higherIndex];
        },
        /**
         * Generate tooltip message for course schedule
         * @param item
         * @returns {string}
         */
        generateScheduleMessage(item) {

            let messages = [];
            if (item.activate_at && !item.active) {

                messages.push(`Se activará: ${item.activate_at}`);
            }

            if (item.deactivate_at) {
                messages.push(`Se inactivará: ${item.deactivate_at}`);
            }

            return messages.join('<br>');
        },
        addNewProcessInline() {
            let vue = this;
            let new_item = {
                title: '',
                edit_inline: true,
                assigned_users: 0,
                config_completed: false,
                stages_count: 0,
                certificate_template_id: null,
                active: false,
                disabled_btns_actions: true
            };
            vue.rows.push(new_item);
            this.$nextTick(() => {
                const editButtonRef = this.$refs.input_edit_process;
                editButtonRef.focus();
            });
        },
        saveNewProcessInline( item ) {
            let vue = this
            item.edit_inline = false
            vue.$emit('saveNewProcessInline', item)
        },
        generateImagesNamesList(images) {
            let names = [];
            images.forEach(i => names.push(`<li style="font-size: 12px">${i.name}</li>`))

            return '<ul style="padding-left: 5px; margin: 0">' + names.join('') + '</ul>'
        }
    }
}
</script>

<style lang="scss">
thead.v-data-table-header .v-tooltip__content {
    max-width: 300px;
    top: -70px !important;
}
i.v-icon.icon_tooltip {
    color: #000 !important;
}
span.custom_link_add_speaker {
    font-size: 14px;
    color: #5458EA;
    font-family: "Nunito", sans-serif;
    text-decoration: underline;
    cursor: pointer;
}
span.custom_benefit_stars,
span.custom_benefit_type {
    color: #C9CED6;
    font-size: 14px;
    font-family: "Nunito", sans-serif;
}

.tbl_perfil_speaker {
    width: 40px;
    height: 40px;
    overflow: hidden;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    background: #D9D9D9;
    img {
        max-width: 100%;
    }
}

.row-icon {
    font-size: 16px;
}

.row-icon.red {
    color: #FF4242
}

.badge-small .v-badge__badge {

    border-radius: 3px;
    padding: 2px 4px 0 4px;
    font-size: 10px !important;
    height: 12px !important;
    letter-spacing: 2px !important;
}

.badge-icon {
    position: absolute;
    top: 0;
}

.badge-count {
    position: absolute;
    top: 0;
    left: 38px;
    border-radius: 3px;
    padding: 2px 4px 2px 4px;
    font-size: 10px !important;
    height: 12px !important;
    letter-spacing: 2px !important;
    color: white;
}

.fancy-menu {
    opacity: 0;
    position: absolute;
    top: 0 !important;
    right: 0;
    min-width: 220px;
    background: white;
    box-shadow: 0px 4px 10px rgba(200,200,200,0.5);
    border-radius: 10px;
    padding: 0 !important;
    margin: 0;
    list-style: none;
    overflow: hidden;
    z-index: 2;
    transition: all 700ms;
}

.fancy-menu-wrapper:hover .fancy-menu {
    opacity: 1;
}

.fancy-menu li {
    padding: 11px 10px 11px 10px;
    margin: 0;
    font-size: 12px !important;
}

.fancy-menu li:not(.red-title) {
    cursor: pointer
}

.fancy-menu li.red-title {
    background: #fce6e5 !important;
}

/* Override tooltip styles */

table .v-tooltip__content.menuable__content__active {
    border-width: 1px !important;
    /* Fix position to show tooltip from right position */
    right: 0 !important;
    left: unset !important;
}

.scheduled-tooltip .v-tooltip__content.menuable__content__active {
    width: 300px;

}

.input_edit_process input {
    border: 1px solid #D9D9D9;
    width: 100%;
    max-width: 400px;
    padding: 6px 10px;
    border-radius: 5px;
}
.input_edit_process input:focus {
    border: 1px solid #5458ea;
}
.btn_add_new_process_inline {
    font-weight: 700 !important;
    font-family: "Nunito", sans-serif;
}
</style>
