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
            <template v-for="h in dataTable.headers" v-slot:[`header.${h.value}`]="{ header }">
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
            </template>

            <template v-slot:item.position="{item, header}">
                <div class="d-flex flex-column align-items-center">
                    <v-btn
                        text icon
                        color="primary"
                        @click="changeOrder(item, 'up', header.model, header.field)"
                        v-show="!dataTable.filters && item.position && (!sortParams.sortBy || sortParams.sortBy == 'position')"
                        :disabled="pagination.actual_page == 1 && rows.indexOf(item) == 0"
                    >
                        <v-icon v-text="'mdi-chevron-up'"/>
                    </v-btn>
                    <span v-text="item.position"/>
                    <v-btn
                        text icon
                        color="primary"
                        @click="changeOrder(item, 'down', header.model, header.field)"
                        v-show="!dataTable.filters && item.position && (!sortParams.sortBy || sortParams.sortBy == 'position')"
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
                        v-for="(row, index) in item.images"
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
            <template v-slot:item.medium_image="{ item, header }">
                <div class="d-flex justify-center flex-row my-2"
                     v-if="item.image">
                    <v-img
                        max-height="120"
                        max-width="120"
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
            <template v-slot:item.actions="{ item, header }">
                <div class="default-table-actions d-flex justify-center flex-row my-2"
                     v-if="dataTable.actions && dataTable.actions.length > 0">
                    <!-- {{ item }} -->
                    <div v-for="action in dataTable.actions">
                        <button
                            type="button" class="btn btn-md"
                            :title="action.text"
                            @click="doAction(action, item)"
                            v-if="!action.show_condition || (action.show_condition && item[action.show_condition])"
                            :class="{'default-table-disable-action-btn' : action.disable_btn || (action.disable_btn && item[action.disable_btn])}"
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
                <p class="my-0" v-text="item.nombre"/>

                <div v-if="item.actualizaciones.length > 0" class="customm-cursos-box-actualizaciones">
                    <small v-for="(act, index) in item.actualizaciones" :key="index" v-html="act"/>
                </div>

            </template>
            <!--   ===================================================  -->


        </v-data-table>
        <!--   Custom Paginator -->

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
        avoid_first_data_load: {
            type: Boolean,
            default: false,
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
            }
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
                    vue.rows = data.data.data

                    // if (vue.filters !== "")
                    if (vue.pagination.actual_page > data.data.total_pages)
                        vue.pagination.actual_page = data.data.total_pages

                    vue.pagination.total_pages = data.data.last_page;
                    vue.pagination.fromRow = data.data.from || 0;
                    vue.pagination.toRow = data.data.to || 0;
                    vue.pagination.total_rows = data.data.total;
                    vue.loading = false
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
        changeOrder(item, action, model, field = 'position') {

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
            }

            vue.$http.put(url, data)
                .then(({data}) => {
                    vue.showAlert(data.data.msg)
                    let filters = vue.addParamsToURL(vue.dataTable.filters, vue.filters)
                    vue.getData(filters)
                    this.hideLoader()
                })

            this.hideLoader()
        },

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
</style>
