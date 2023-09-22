<template>
    <v-select
        :attach="attach"
        outlined
        :dense="dense"
        hide-details="auto"
        :disabled="disabled"
        :loading="loading"
        :clearable="clearable"
        :menu-props="computedMenuProps"
        v-model="localSelected"
        :items="items"
        :multiple="multiple"
        :show-select-all="showSelectAll"
        :placeholder="placeholder"
        :label="label"
        :item-text="itemText"
        :item-value="itemValue"
        :color="color"
        :return-object="returnObject"
        :rules="rules"
        @input="updateValue"
        :no-data-text="noDataText"
        :background-color="backgroundColor"
        @click:clear="clickClear"
    >
        <template v-slot:prepend-item v-if="multiple && showSelectAll">
            <v-list-item ripple dense @click="toggle">
                <v-list-item-action>
                    <v-icon :color="localSelected.length > 0 ? 'indigo darken-4' : ''" v-text="icon()"/>
                </v-list-item-action>
                <v-list-item-content>
                    <v-list-item-title v-text="'Seleccionar todos'"/>
                </v-list-item-content>
            </v-list-item>
            <DefaultDivider/>
        </template>
        <template v-slot:selection="{ item, index }" v-if="multiple">
            <v-chip small v-if="index < countShowValues">
                <span>{{ item.nombre || item.name || item[itemText] }}</span>
            </v-chip>
            <span
                v-if="index === countShowValues"
                class="grey--text text-caption"
            >
                (+{{ localSelected.length - countShowValues }})
            </span>
        </template>
        <template v-slot:label v-if="showRequired">
            {{ label }}
            <RequiredFieldSymbol/>
        </template>
    </v-select>
</template>


<script>
export default {
    props: {
        items: {
            type: Array,
            required: true,
        },
        value: {
            type: Object | Array,
            required: true
        },
        multiple: {
            type: Boolean,
            default: false
        },
        showRequired: {
            type: Boolean,
            default: false,
        },
        showSelectAll: {
            type: Boolean,
            default: true
        },
        clearable: {
            type: Boolean,
            default: false,
        },
        dense: {
            type: Boolean,
            default: false,
        },
        placeholder: {
            type: String
        },
        label: {
            type: String
        },
        color: {
            type: String,
            default: 'primary'
        },
        backgroundColor: {
            type: String,
            default: ''
        },
        itemText: {
            type: String,
            default: 'nombre'
        },
        itemValue: {
            type: String,
            default: 'id'
        },
        openUp: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        returnObject: {
            type: Boolean,
            default: false
        },
        rules: {
            type: Object | Array,
        },
        loading: {
            type: Boolean,
            default: false
        },
        noDataText: {
            type: String,
            default: 'No hay datos que mostrar'
        },
        countShowValues: {
            type: String | Number,
            default: 1
        },
        attach:{
            type:Boolean,
            default:true
        }
    },
    computed: {
        computedMenuProps() {
            return {
                offsetY: true,
                maxHeight: 150,
                top: this.openUp,
            }
        }
    },
    data() {
        return {
            localSelected: this.multiple ? [] : null,
        }
    },
    created() {
        if (this.value) {
            this.localSelected = this.value // set initial value
        }
    },
    watch: {
        value(val) {
            this.localSelected = val // watch change from parent component
        }
    },
    methods: {
        updateValue(value) {
            let vue = this
            vue.$emit('input', value || null)
            vue.$emit('onChange')
        },
        icon() {
            if (this.selectAll()) return "mdi-close-box";
            if (this.selectSome()) return "mdi-minus-box";
            return "mdi-checkbox-blank-outline";
        },
        toggle() {
            let vue = this;
            vue.$nextTick(() => {
                vue.localSelected = vue.selectAll() ? [] : vue.selectIdsOrObject()
                vue.updateValue(vue.localSelected)
            });
        },
        selectIdsOrObject() {
            let vue = this

            if (vue.returnObject) {
                return vue.items.slice()
            } else {
                return vue.items.map(el => el.id)
            }
        },
        selectAll() {
            return this.localSelected.length === this.items.length;
        },
        selectSome() {
            return this.localSelected.length > 0 && !this.selectAll;
        },
        clickClear() {
            let vue = this

            vue.$emit('onClickClear')
        }
    }
}
</script>
