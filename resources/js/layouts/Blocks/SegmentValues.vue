<template>
    <div>
        <v-row  v-if="criterion.field_type.code === 'date'"

                style="padding: 10px 12px !important" >
        <!-- <v-row style="padding: 10px 12px !important" v-if="criterion.field_type.code === 'Fecha'"> -->
            <v-col cols="12" md="3" lg="3"
                   class="p-0 vertical-align">
                <!--
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
                -->
                <b-button
                    :style="{marginTop: popoverIsShown ? '60px' : '2px'}"
                    variant="outline-secondary"
                    id="popover-target-1">
                    {{ criterion.name }}
                </b-button>
                <b-popover
                    id="relative-range-popover"
                    target="popover-target-1"
                    triggers="click"
                    container="#popover-container"
                    placement="topright"
                    @show="popoverShown()"
                    @hide="popoverHidden()">

                    <!--
                    Tabs
                    ========================================-->

                    <div>
                        <button
                            @click="calendarIsActive = true"
                            type="button"
                            :class="['tab', calendarIsActive ? '' : 'outline']">
                            Calendario
                        </button>
                        <button
                            @click="calendarIsActive = false"
                            type="button"
                            :class="['tab', calendarIsActive ? 'outline' : '']">
                            Vinculación de tiempo
                        </button>
                    </div>

                    <!--
                    Tab content: calendar
                    ========================================-->
                    <date-picker
                        v-if="calendarIsActive"
                        confirm
                        confirm-text="Agregar rango"
                        attach
                        inline
                        v-model="value1"
                        type="date"
                        range
                        :placeholder="criterion.name"
                        :lang="lang"
                        @confirm="agregarRango()"
                        style="width: 100% !important"
                        value-type="YYYY-MM-DD"
                    ></date-picker>

                    <!--
                    Tab content: relative date range selector
                    ========================================-->

                    <div v-if="!calendarIsActive"
                        class="relative-range p-3">
                        <div class="row">
                            <div class="col-9 pr-0 mr-0">
                                <div class="label-wrapper">
                                    <input type="radio"
                                           v-model="relativeDateType"
                                           value="greater-than"
                                           name="relative-range">
                                    Se vincula a usuarios con un tiempo mayor a
                                </div>
                                <div class="label-wrapper mt-2 optional">
                                    Opcional: Duración de la segmentación
                                </div>
                            </div>
                            <div class="col-3 pl-0 ml-0">
                                <div class="input-wrapper">
                                    <input
                                        :disabled="relativeDateType === 'less-than'"
                                        type="text"
                                        @keydown="restrictInput"
                                        v-model="greaterThan"/>
                                    <span>Días</span>
                                </div>
                                <div class="input-wrapper mt-2">
                                    <input
                                        :disabled="relativeDateType === 'less-than'"
                                        type="text"
                                        @keydown="restrictInput"
                                        v-model="duration"/>
                                    <span>Días</span>
                                </div>
                            </div>
                        </div>

                        <div class="row line">
                            <div class="col-9 pr-0 mr-0">
                                <div class="label-wrapper">
                                    <input type="radio"
                                           v-model="relativeDateType"
                                           value="less-than"
                                           name="relative-range">
                                    Se vincula a usuarios con un tiempo menor a
                                </div>
                            </div>
                            <div class="col-3 pl-0 ml-0">
                                <div class="input-wrapper">
                                    <input
                                        :disabled="relativeDateType === 'greater-than'"
                                        type="text"
                                        @keydown="restrictInput"
                                        v-model="lessThan"/>
                                    <span>Días</span>
                                </div>
                            </div>
                        </div>

                        <div class="row line">
                            <div class="col-12 d-flex justify-content-center p-0">
                                <DefaultButton
                                    label=""
                                    icon="mdi-plus-circle"
                                    isIconButton
                                    @click="addRelativeRange()"
                                />
                            </div>
                        </div>

                    </div>
                </b-popover>
            </v-col>

            <v-col cols="12" md="9" lg="9" class="p-0 vertical-align">
                <default-autocomplete
                    attach
                    dense
                    outlined
                    color="#796aee"
                    hide-details="auto"
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

        <v-row
            v-else
            :class="criterion.code === 'module' ? 'module' : ''"
            style="padding: 10px 0px 10px 0px !important">

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
            popoverIsShown: false,
            relativeDateType: 'greater-than',
            greaterThan: 0,
            lessThan: 0,
            duration: 0,
            calendarIsActive: true,
            search: null,
            debounce: null,
            value1: [new Date().toISOString().substr(0, 10), new Date().toISOString().substr(0, 10)],
            lang: lang,
            date_range_selected: []
        };
    },
    watch: {
        relativeDateType(newValue, oldValue) {

            if (newValue === 'greater-than') {
                this.lessThan = 0;
            } else {
                this.greaterThan = 0;
                this.duration = 0;
            }
        }
    },
    methods: {
        popoverShown() {
            this.popoverIsShown = true;
        },
        popoverHidden() {
            this.popoverIsShown = false;
        },
        cleanSelectedDates(){
            let vue = this;
            vue.value1 = [null, null];
        },
        restrictInput(event) {

            // Get the pressed key
            const key = event.key;

            const isNumeric = /^\d+$/.test(key);
            const isSpecialKey =
                event.keyCode === 8 || // Backspace
                event.keyCode === 46 || // Delete
                event.keyCode === 37 || // Left arrow
                event.keyCode === 39; // Right arrow

            // If the key is not a number and not a special key, prevent default behavior

            if (!isNumeric && !isSpecialKey) {
                event.preventDefault();
            }
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

            this.$root.$emit('bv::hide::popover')
        },
        addRelativeRange() {

            // Generate range values

            let durationDescription = ''
            if (this.duration) {
                durationDescription = `(Durante ${this.duration} días)`;
            }

            const name = this.relativeDateType === 'greater-than'
                ? `Mayor a ${this.greaterThan} días. ${durationDescription}`
                : `Menor a ${this.lessThan} días`;

            const newDateRange = {
                id: `new-relative-range-${Date.now()}`,
                name: name,
                greaterThan: this.greaterThan,
                lessThan: this.lessThan,
                duration: this.duration
            };

            // Add value to list

            this.date_range_selected.push(newDateRange);

            let data = {
                date_range_selected: this.date_range_selected,
                new_date_range: newDateRange,
                criterion_code: this.criterion.code,
            };

            this.$emit("addDateRange", data);

            // Reset values and close popover

            this.greaterThan = 0;
            this.lessThan = 0;
            this.duration = 0;

            this.$root.$emit('bv::hide::popover')
        }
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

.module .v-text-field--outlined fieldset {
    border: 2px solid #d7d6d8 !important;
}


.popover {
    background: white !important;
    border: none !important;
    box-shadow: 0 5px 10px rgba(200,200,200,0.5);
    max-width: 475px !important;
}

.popover-body {
    padding: 0 !important;
    width: 475px !important;
}

.popover-body .mx-datepicker-main {
    border: none !important;
}

button.tab {
    height: 28px;
    background: #796aee;
    color: white;
    border: 1px solid #796aee;
    border-radius: 4px;
    padding-left: 12px;
    padding-right: 12px;
    margin: 10px 0 10px 10px;
}

button.tab.outline {
    background: white;
    color: #796aee;
}

#relative-range-popover {
    left: -40px !important;
    //top: 50px !important;
}

.relative-range input[type=radio] {
    width: 12px;
    height: 12px;
    margin-right: 5px;
}

.relative-range .optional {
    margin-left: 20px;
}

.relative-range .input-wrapper {
    border: 1px solid #E0E0E0;
    border-radius: 4px;
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 0 3px 0 3px;
}

.relative-range .label-wrapper {
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: start;
}
.relative-range .input-wrapper span {
    font-size: 10px;
}

.relative-range input[type=text] {
    height: 40px;
    width: 50px;
}

.line {
    border-top: 1px solid #eaeaea;
}

</style>
