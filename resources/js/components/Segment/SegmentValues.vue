<template>
    <div>
        <v-row style="padding: 10px 12px !important" v-if="criterion.field_type.code === 'date'">
        <!-- <v-row style="padding: 10px 12px !important" v-if="criterion.field_type.code === 'Fecha'"> -->
            <v-col cols="12" md="3" lg="3" class="p-0 vertical-align">
                <date-picker
                    confirm
                    confirm-text="Agregar rango"
                    attach
                    v-model="value1"
                    type="date"
                    range
                    :placeholder="criterion.name"
                    :lang="lang"
                    @confirm="agregarRango()"
                    style="width: 100% !important"
                    value-type="YYYY-MM-DD"
                ></date-picker>
            </v-col>

            <v-col cols="12" md="9" lg="9" class="p-0 vertical-align">
                <default-autocomplete
                    attach
                    dense
                    outlined
                    color="#796aee"
                    hide-details="auto"
                    :label="criterion.name"
                    :menu-props="{ top: true, offsetY: true }"

                    return-object
                    multiple
                    :show-select-all="false"
                    chips
                    :items="criterion.values_selected"
                    v-model="criterion.values_selected"
                    item-text="name"
                    item-value="segment_value_id"
                >
                    <template v-slot:selection="{ item, index }">
                        <v-chip
                            style="font-size: 0.9rem !important; color: white !important"
                            color="#796aee"
                            v-if="index < 3"
                            small
                        >
                            {{ item.name }}
                        </v-chip>
                        <span v-if="index === 3" class="grey--text caption">
							(+{{ date_range_selected.length - 3 }} seleccionado{{
                                date_range_selected.length - 3 > 1 ? "s" : ""
                            }})
						</span>
                    </template>
                </default-autocomplete>
            </v-col>

        </v-row>

        <v-row v-else>

            <v-col cols="12" md="12" lg="12" class="p-0 px-3 vertical-align">

                <DefaultAutocompleteOrder
                    return-object
                    dense
                    :label="criterion.name"
                    v-model="criterion.values_selected"
                    :items="criterion.values"
                    multiple
                    item-text="value_text"
                    item-id="id"
                    :count-show-values="Infinity"
                    />

            </v-col>
        </v-row>
    </div>
</template>

<script>

import DatePicker from "vue2-datepicker";
import "vue2-datepicker/index.css";
import lang from "./../../plugins/lang_datepicker";

export default {
    components: {DatePicker},
    props: ["criterion"],
    data() {
        return {
            search: null,
            debounce: null,
            value1: [new Date().toISOString().substr(0, 10), new Date().toISOString().substr(0, 10)],
            lang: lang,
            date_range_selected: []
        };
    },
    methods: {
        cleanSelectedDates(){
            let vue = this;
            vue.value1 = [null, null];
        },
        agregarRango() {
            let vue = this;

            const newDateRange = {
                id: `new-date-range-${Date.now()}`,
                name: `${vue.value1[0]} - ${vue.value1[1]}`,
                date_range: vue.value,
                start_date: vue.value1[0],
                end_date: vue.value1[1],
            };

            vue.date_range_selected.push(newDateRange);

            let data = {
                date_range_selected: vue.date_range_selected,
                new_date_range: newDateRange,
                criterion_code: vue.criterion.code,
            };

            vue.cleanSelectedDates();

            vue.$emit("addDateRange", data);
        },

    },
};
</script>

<style>
.mx-input {
    height: 40px !important;
    border-color: #dee2e6 !important;
    border-radius: 0px !important;
    color: black !important;
}

.mx-calendar-content .cell.active {
    color: #fff;
    background-color: #796aee !important;
}

.mx-calendar-content .cell.in-range,
.mx-calendar-content .cell.hover-in-range {
    color: #fff !important;
    background-color: #aba2f1 !important;
}

.label-fecha {
    height: 39px;
    border-radius: 0;
    border-color: #dee2e6;
    border-width: thin;
    width: 100%;
    border-style: solid;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    font-size: 16px;
    padding-right: 10px;
}

.label-tipo_criterio {
    height: 39px;
    border-radius: 0;
    border-color: #dee2e6;
    border-width: thin;
    width: 100%;
    border-style: solid;
    display: flex;
    justify-content: start;
    align-items: center;
    font-size: 16px;
    padding-left: 10px;
    text-transform: uppercase;
}
</style>
