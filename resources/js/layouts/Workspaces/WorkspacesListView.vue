<template>
    <section class="p-0">
        <v-data-table
            transition="fade"
            class="elevation-0"
            auto-width
            no-data-text="No se encontraron resultados"
            :loading="loading"
            loading-text="Cargando datos..."
            :headers="dataTable.headers"
            :items="data"
            :items-per-page="12"
            @update:sort-by="sortBy"
            @update:sort-desc="sortDesc"
            hide-default-footer
            :item-class="row_classes"
        >
            <template v-slot:item.image="{ item, header }">
                <div class="d-flex justify-center flex-row my-2"
                     v-if="item.image">
                    <v-img
                        max-height="80"
                        max-width="80"
                        :src="item.image"
                    ></v-img>
                </div>
            </template>
            <template v-slot:item.actions="{ item, header }">
                <div class="default-table-actions d-flex justify-center flex-row my-2"
                     v-if="dataTable.actions && dataTable.actions.length > 0">
                    <div v-for="action in dataTable.actions">
                        <button type="button" class="btn btn-md"
                                :title="action.text"
                                @click="doAction(action, item)">
                            <v-badge
                                v-if="(action.count && item[action.count])"
                                color="primary"
                                :content="item[action.count]"
                            >
                                <i :class="action.icon"/>
                                <br> <span class="table-default-icon-title" v-text="action.text"/>
                            </v-badge>

                            <template v-else>
                                <i :class="action.icon"/>
                                <br> <span class="table-default-icon-title" v-text="action.text"/>
                            </template>
                        </button>
                        <!-- <span class="badge table_default_badge-notify"
                              v-if="action.count"
                              v-text="item[action.count]"/> -->
                    </div>
                    <div class="table-default-more-actions"
                         v-if="dataTable.more_actions && dataTable.more_actions.length > 0">
                        <v-menu
                            tile
                            bottom
                            left
                            attach
                            close-on-content-click
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
                                <v-list-item-group
                                    v-model="more_actions_selectedItem"
                                    color="primary"
                                >
                                    <v-list-item
                                        v-for="action in dataTable.more_actions"
                                        :key="action.text"
                                        @click="doAction(action, item)"
                                    >
                                        <v-list-item-content>
                                            <v-list-item-title class="d-flex justify-content-start">
                                                <v-icon color="primary" class="mx-1" small v-text="action.icon"/>
                                                {{ action.text }}
                                            </v-list-item-title>
                                        </v-list-item-content>
                                    </v-list-item>
                                </v-list-item-group>
                            </v-list>
                        </v-menu>
                    </div>
                </div>
            </template>
        </v-data-table>
    </section>
</template>

<script>
export default {
    props: {
        data: {
            required: true
        },
        loading: {
            required: true
        },
        dataTable: {
            type: Object,
            required: true
        },
        rowsPerPage: {
            type: Number,
            default: 10
        }
    },
    methods: {
        row_classes(item) {
            if (item.active === false) {
                return "row-inactive"; //can also return multiple classes e.g ["orange","disabled"]
            }
        },
        doAction(action, rowData) {
            let vue = this
            if (action.type === 'action') { // Emit parent method
                console.log("METHOD EMIT :: ", action.method_name)
                vue.$emit(action.method_name, rowData)
            } else { // Navigate
                console.log('IR A :: ', action.route )
                window.location.href = rowData[action.route]
            }
        },
        sortBy(sortProperty) {
            let vue = this
            // vue.sortParams.sortBy = sortProperty.length > 0
            //     ? sortProperty[0] : null
        },
        sortDesc(sortDesc) {
            let vue = this
            // vue.sortParams.sortDesc = sortDesc.length > 0 && vue.sortParams.sortBy !== null
            //     ? sortDesc[0] : false
            // vue.getData()
        },
    }
}
</script>
