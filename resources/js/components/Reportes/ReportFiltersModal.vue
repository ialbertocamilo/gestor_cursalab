<template>
    <DefaultDialog
        :options="{
            title: 'Filtro&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Detalle / descripción',
            open: isOpen,
            showFloatingCloseButton: true
         }"
        @onCancel="close"
        :width="'667px'"
        :showCardActions="false"
    >
        <template v-slot:content>
            <v-row>
                <v-col cols="12">
                    <div class="search-input-wrapper">
                        <v-icon
                            class="mr-2 ml-2"
                            :color="'#000'">
                            mdi-magnify
                        </v-icon>
                        <input
                            type="text"
                            v-model="searchValue"
                            placeholder="Buscador">
                    </div>
                </v-col>
            </v-row>
            <div class="filters-wrapper">
                <v-row
                    v-for="(item, index) in filterDescriptions"
                    v-if="!isEmpty(item.values)"
                    :key="generateKey(item.label)">

                    <!-- Titlebar -->

                    <v-col cols="12 pt-0 pb-0">
                        <div class="title-bar" @click="item.isVisible = !item.isVisible">
                            <span>{{ item.label }}</span>
                            <v-icon
                                v-if="item.isVisible"
                                class="down mr-2 ml-2"
                                :color="'#fff'">
                                mdi-chevron-left
                            </v-icon>

                            <v-icon
                                v-if="!item.isVisible"
                                class="down mr-2 ml-2"
                                :color="'#fff'">
                                mdi-chevron-right
                            </v-icon>
                        </div>
                    </v-col>

                    <!-- Values -->

                    <v-col cols="12">
                        <!-- Single values -->
                        <div
                            v-if="!isArray(item.values)"
                            :class="['values', item.isVisible ? '' : 'hidden', matchesSearch(item.values) ? 'matches' : '']">
                            {{ item.values }}
                        </div>
                        <!-- List of values -->
                        <div
                            v-if="isArray(item.values)"
                            :class="['values', item.isVisible ? '' : 'hidden']">
                            <div v-for="name of item.values"
                                 :class="['value', matchesSearch(name) ? 'matches' : '']">
                                {{ name }}
                            </div>
                        </div>

                    </v-col>
                </v-row>
            </div>

        </template>
    </DefaultDialog>
</template>

<script>
export default {
    props: {
        isOpen : {
            type: Boolean,
            required: true
        },
        filters : {
            type: Object,
            required: true,
            default: {}
        },
        filtersVisibilty : {}
    }
    ,
    data () {
        return {
            searchValue: '',
            filterDescriptions: []
        }
    }
    ,
    watch: {
        filters: function (filters) {
            this.filterDescriptions = []
            let isTheFirstElement = true
            for (let [property, values] of Object.entries(filters)) {

                this.filterDescriptions.push({
                    label: property,
                    key: this.generateKey(property),
                    values: Array.isArray(values) ? values.concat().sort() : values,
                    isVisible: isTheFirstElement
                })
                isTheFirstElement = false
            }
        }
    },
    methods: {
        matchesSearch(value) {
            if (!this.searchValue) return false
            if (!value) return false
            if (this.searchValue.length === 1) return false

            let pattern = new RegExp(`${this.searchValue.toLowerCase()}`, 'g')
            return value
                ? String(value).toLowerCase().match(pattern) != null
                : false;
        },
        close() {
            this.$emit('cancel')
        },
        isArray(arr) {
            return Array.isArray(arr)
        },
        isEmpty(value) {
            if (Array.isArray(value)) {
                return value.length === 0
            } else {
                return !value
            }
        },
        generateKey(str) {
            return str
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w-]+/g, '')
                .replace(/--+/g, '-')
        },
        sort(Arr) {

        }
    }
}
</script>

<style scoped>

    .search-input-wrapper {
        display: flex;
        align-items: center;
        background: #FFFFFF;
        border: 1px solid #CED4DA;
        border-radius: 10px;
        height: 40px;
    }

    .search-input-wrapper input {
        flex-grow: 1;
    }

    .title-bar {
        display: flex;
        align-items: center;
        height: 34px;
        padding-left: 10px;
        font-weight: 500;
        font-size: 18px;
        color: #fff;
        background: #9B98FE;
        border-radius: 10px;
        cursor: pointer;
    }

    .title-bar span {
        flex-grow: 1;
    }

    .values {
        background: #FFFFFF;
        border: 0.2px solid #9E9E9E;
        border-radius: 10px;
        padding: 4px;
        min-height: 28px;
        max-height: 254px;
        overflow-y: auto;
        opacity: 1;
        transition: all 500ms;
        display: flex;
        flex-direction: column;
    }

    .values.hidden {
        /*height: 0;*/
        /*opacity: 0;*/
        display: none;
    }

    .value {
        height: 30px;
        padding: 4px 8px;
        margin-top: 4px;
        margin-bottom: 4px;
        border-radius: 8px;
    }

    .value:nth-child(odd) {
        background-color: white;
    }

    .value:nth-child(even) {
        background-color: #F6F6F6;
    }

    .value.matches {
        background: rgba(37, 150, 190, 0.2);
        /* Required for order to work */
        display: inline-flex;
        order: -1;
    }


    /*.value {*/
    /*    max-width: 45%;*/
    /*    background-color: #eaeefe;*/
    /*    padding: 10px;*/
    /*    display: inline-block;*/
    /*    border-radius: 20px;*/
    /*    margin: 5px;*/
    /*}*/


</style>
