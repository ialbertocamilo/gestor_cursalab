<template>
    <!--    <v-dialog-->
    <!--        :ref="referenceComponent"-->
    <!--        v-model="options.modal"-->
    <!--        :return-value.sync="date"-->
    <!--        persistent-->
    <!--        width="290px"-->
    <!--    >-->
    <v-menu
        :ref="referenceComponent"
        v-model="options.modal"
        :close-on-content-click="false"
        :return-value.sync="date"
        :transition="transition"
        :offset-y="offsetY"
        :offset-x="offsetX"
        :top="top"
        :left="left"
        min-width="auto"
        attach
    >
        <template v-slot:activator="{ on, attrs }">
            <v-text-field
                :placeholder="placeholder"
                attach outlined
                :dense="dense"
                hide-details="auto"
                :label="label"
                append-icon="mdi-calendar"
                readonly
                v-bind="attrs"
                v-on="on"
                v-model="dateText"
                clearable
                :rules="rules"
                @click:clear="clearDate"
                :disabled="disabled"
            >
                <template v-slot:label>
                    {{ label }}
                    <RequiredFieldSymbol  v-if="showRequired"/>
                </template>
                <template v-slot:append>
                    <v-btn class="no-background-hover bk_calendar" icon :ripple="false"
                           style="cursor: default"
                           :disabled="disabled" append-icon="mdi-magnify">

                        <img src="/img/calendar_black.png">
                    </v-btn>
                    <DefaultInfoTooltipForm v-if="tooltip != ''" :tooltip="tooltip" />
                </template>
            </v-text-field>
        </template>
        <v-date-picker
            color="primary"
            v-model="date"
            scrollable
            :range="range"
            locale="es-ES"
            no-title
            :min="min"
        >
            <v-spacer></v-spacer>
            <v-btn
                text
                color="primary"
                @click="options.modal = false"
            >
                Cancelar
            </v-btn>
            <v-btn
                text
                color="primary"
                @click="updateModel"
            >
                OK
            </v-btn>
        </v-date-picker>
    </v-menu>
    <!--    </v-dialog>-->
</template>

<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        value: {
            required: true
        },
        range: {
            type: Boolean,
            default: false
        },
        label: {
            type: String,
            default: 'Fecha'
        },
        placeholder: {
            type: String,
        },
        showRequired: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        dense: {
            type: Boolean,
            default: false
        },
        rules: {
            type: Object | Array,
        },
        referenceComponent: String,
        tooltip: {
            type: String,
            default: ''
        },
        min: String,
        offsetY: {
            type: Boolean,
            default: true,
        },
        offsetX: {
            type: Boolean,
            default: false,
        },
        top: {
            type: Boolean,
            default: false,
        },
        left: {
            type: Boolean,
            default: false,
        },
        transition: {
            type:String,
            default: 'scale-transition'// slide-x-transition
        }

    },
    computed: {
        dateText: {
            get() {
                let vue = this
                if (vue.range) {
                    if (vue.date.length === 0) return ''
                    let dates = []
                    if (vue.date[0]) {
                        let fecha1 = vue.date[0].replaceAll('-', '/')
                        // console.log('fecha1', fecha1)
                        dates.push(fecha1)
                    }
                    if (vue.date[1]) {
                        let fecha2 = vue.date[1].replaceAll('-', '/')
                        // console.log('fecha2', fecha2)
                        dates.push(fecha2)
                    }
                    // console.log(vue.date)
                    return dates.join(' - ')
                }
                else
                {
                    if (!vue.date) return vue.date

                    const [year, month, day] = vue.date.split('-')
                    return `${day}-${month}-${year}`
                }
            },
            set() {
                let vue = this
                return vue.date
            },
        },
    },
    data() {
        return {
            date: this.range ? [] : null,
            rand: Math.random()
        }
    },
    created() {
        if (this.value) {
            this.date = this.value // set initial value
        }
    },
    watch: {
        value(val) {
            this.date = val // watch change from parent component
        }
    },
    methods: {
        clearDate() {
            let vue = this
            if (vue.TypeOf(vue.date) === 'array')
                vue.date = []
            else
                vue.date = null

            vue.updateModel()
        },
        updateModel() {
            let vue = this
            vue.$refs[vue.referenceComponent].save(vue.date)
            vue.$emit('input', vue.date)
            vue.$emit('onChange')
        },
        parseDate(date) {
            if (!date) {
                return null
            }

            const [year, month, day] = date.split('-')
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
        }
    }
}
</script>
<style lang="scss">
button.bk_calendar span.v-btn__content img {
    max-width: 16px;
    height: auto;
}
i.v-icon.icon_tooltip {
    color: #000 !important;
}
</style>
