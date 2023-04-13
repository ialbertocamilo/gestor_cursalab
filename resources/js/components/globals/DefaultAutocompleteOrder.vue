<template>
    <div class="d-flex">
        <v-autocomplete
            attach
            outlined
            :dense="dense"
            hide-details="auto"
            :disabled="localDisabled"
            :clearable="clearable"
            :menu-props="computedMenuProps"
            v-model="localSelected"
            :items="localItems"
            :multiple="multiple"
            :show-select-all="showSelectAll"
            :placeholder="placeholder"
            :label="label"
            :item-text="itemText"
            :item-value="itemValue"
            :color="color"
            :return-object="returnObject"
            @input="updateValue"
            :no-data-text="noDataText"
            :rules="rules"
            :loading="computedLocalLoading"

            :class="localDisabled && 'grey lighten-4'"
            ref="vue_autocomplete"
        >
            <template v-slot:prepend-item v-if="multiple && showSelectAll">
                <v-list-item ripple dense @click="toggle">
                    <v-list-item-action>
                        <v-icon :color="localSelected.length > 0 ? 'indigo darken-4' : ''"
                                v-text="icon()"/>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title v-text="computedLocalTextToggle"/>
                    </v-list-item-content>
                </v-list-item>
                <DefaultDivider/>
            </template>

            <template v-slot:item={item} v-if="customItems">
                <slot name="customItems" :item="item"/>
            </template>

            <template v-slot:item="{ item }" v-if="!customItems">
                <v-list-item
                    ripple
                    dense
                    :class="readyOnlyCodes.includes(item.code) ? 'readonly-list-item' : ''"
                    @click="select(item)">
                    <v-list-item-icon>
                        <v-icon :color="itemSelect(item.id).color"
                                v-text="itemSelect(item.id).icon" />
                    </v-list-item-icon>

                    <v-list-item-content>
                        <v-list-item-title v-text="String(item[itemText])"></v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </template>


            <template v-slot:selection="{ item, index }" v-if="multiple">
                <v-chip
                    small
                    class="flex justify-content-between mt-1"
                    :class="[hideCodes.includes(item.code) ? 'hidden-chip' : '', readyOnlyCodes.includes(item.code) ? 'readonly-chip' : '']"
                    v-if="index < countShowValues">
                    <span> {{ String(item[itemText]) }}
                        <v-btn
                            v-if="!readyOnlyCodes.includes(item.code)"
                            icon
                            small
                            @click="removeItem(item.id)">
                            <v-icon color="white">mdi-minus-circle</v-icon>
                        </v-btn>

                        <v-btn
                            v-if="readyOnlyCodes.includes(item.code)"
                            icon
                            small>
                            <v-icon size="14" color="white">mdi-lock</v-icon>
                        </v-btn>
                    </span>
                </v-chip>
                <span
                    v-if="index === countShowValues"
                    class="grey--text text-caption"
                >
                    (+{{ localSelected.length - countShowValues }})
                </span>
            </template>

            <template v-slot:label v-if="showRequired">
                {{ label }} <RequiredFieldSymbol/>
            </template>
        </v-autocomplete>

        <!--div class="d-flex flex-column justify-content-center align-items-center">
            <v-btn
                text small color="primary" class="h-20 p-0"
                @click="canEditInput">
                <span v-text="localSelected.length > 0 ? 'Editar': 'Agregar' "></span>
            </v-btn>
            <v-btn v-show="localSelected.length > 0" class="h-20 p-0"
                text small color="secondary" @click="clearEditInput">
                <span>Limpiar</span>
            </v-btn>
        </div-->
    </div>
</template>


<script>
export default {
    props: {
        readyOnlyCodes: {
            type: Array,
            default: function() { return [] }
        },
        hideCodes: {
            type: Array,
            default: function() { return [] }
        },
        items: {
            type: Object | Array,
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
        customItems: {
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
        noDataText: {
            type: String,
            default: 'No hay datos que mostrar'
        },
        rules: {
            type: Object | Array,
        },
        countShowValues: {
            type: Number,
            default: 1
        },
        maxValuesSelected: {
            type: Number,
            default: null
        },
        showRequired: {
            type: Boolean,
            default: false,
        },
        loadingState:{
            type: Boolean,
            default: false
        },
        clearableState:{
            type: Boolean,
            default: false
        },
        // indicamos que este componente no esta ligado a otro componente
        onlyOne:{
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            localSelected: this.multiple ? [] : null,
            localItems: [],

            localDisabled: false
        }
    },
    computed: {
        computedMenuProps() {
            return {
                offsetY: true,
                maxHeight: 200,
                top: this.openUp,
            }
        },
        computedLocalLoading() {
            const vue = this;

            if(vue.loadingState) {
                // como esta cargando no va a pasar por el watch y aqui ordenamos.
                if(!vue.localItems.length) vue.localItems = vue.itemsOrderSelect(vue.localSelected);
            }
            return (vue.items.length === 0) && vue.loadingState;
        },
        computedLocalTextToggle() {
            const vue = this;
            let countItems = vue.localItems.length - vue.localSelected.length;

            if(vue.$refs.vue_autocomplete) {
                const countFiltered = vue.$refs.vue_autocomplete.filteredItems.length;

                if(vue.loadingState) { // si el loading esta activo - llega nueva data despues de 'x' ms
                    if(vue.localItems.length === countFiltered) return 'Seleccionar todos';
                    countItems = vue.localItems.length - vue.localSelected.length;
                }

                if(countItems !== countFiltered) return 'Seleccionar filtrados';
            }
            return 'Seleccionar todos';
        }
    },
    mounted() {
        const vue = this;

        const { $refs: { label } } = vue.$refs.vue_autocomplete;
        const VSelections = label.nextElementSibling;
              VSelections.classList.add('v-scroll-auto-complete');

        if (vue.value) {
            vue.localSelected = vue.value // set initial value
            vue.localItems = vue.itemsOrderSelect(vue.value) // set initial value
        }else{
            vue.localItems = [...vue.items] // set initial value
        }

        // console.log('mounted hook', vue.localItems, vue.items);
    },
    watch: {
        value: {
            handler(val) {
                const vue = this;

                const currentVal = val ? val : [];
                vue.localSelected = currentVal // watch change from parent component
                vue.localItems = vue.itemsOrderSelect(currentVal);
            }
        }
    },
    methods: {
        select(item) {
            const vue = this;
            const { id: itemId } = item;

            const currentState = vue.localSelected.some( ({ id }) => id === itemId );

            if(!currentState) vue.localSelected.push(item);
            else {
                const index = vue.localSelected.findIndex( ({ id }) => id === itemId );
                if(vue.clearableState) vue.checkCleareable(index);

                vue.localSelected.splice(index, 1);
            }

            vue.updateValue(vue.localSelected);// actualizar al agregar
        },
        itemSelect(itemId){
            const vue = this;

            const currentState = vue.localSelected.some( ({ id }) => id === itemId );
            return {
                color: currentState ? 'indigo darken-4' : '',
                icon: currentState ? 'mdi-checkbox-marked' : 'mdi-checkbox-blank-outline'
            };
        },
        focusInputSearch() {
            const vue = this;
            const currentNode = vue.$refs.vue_autocomplete;
            setTimeout(() => currentNode.focus(), 150); //focus input
        },
        canEditInput() {
            const vue = this;
            vue.focusInputSearch();
        },
        //clearable state all
        checkCleareable(index = null){
            const vue = this;

            if(vue.clearableState && index === null){
                const valuesCount = vue.localSelected.length;
                for (let i = 0; i < valuesCount; i++) {
                    vue.localSelected[i].values_selected = [];
                }
            } else vue.localSelected[index].values_selected = [];

        },
        //clearable state all
        clearEditInput(){
            const vue = this;
            if(vue.clearableState) vue.checkCleareable();

            vue.localSelected = [];
            vue.updateValue([]);
        },
        removeItem(s_id) {
            const vue = this;
            const index = vue.localSelected.findIndex( ({id}) => id === s_id)
            if(vue.clearableState) vue.checkCleareable(index);

            vue.localSelected.splice(index, 1);

            vue.updateValue(vue.localSelected); // actualizar al remover
        },
        itemsOrderSelect(val){
            const vue = this;
            const StaticItems = vue.items;
            const StaticValue = val;

            let localItems = [];

            for (let i = 0; i < StaticItems.length; i++) {
                const currentItem = StaticItems[i];
                const { id } = currentItem;

                const stateValue = StaticValue.some(( {id: s_id} ) => s_id === id);
                if(stateValue) {
                    localItems.push(currentItem);
                }
            }

            return [ ...localItems, ...StaticItems ];
        },
        updateValue(value) {
            let vue = this
            const prevData = vue.returnObject ? value.slice() : value.map(el => el.id);

            vue.$emit('input', prevData || null)
            vue.$emit('onChange')

            // es un solo componente y la data - no esta ligado a otro componente
            if(vue.onlyOne) {
                vue.localItems = vue.itemsOrderSelect(vue.localSelected);
            }
        },

        icon() {
            const vue = this;
            const filtered = vue.$refs.vue_autocomplete;

            if (vue.selectAll()) return "mdi-close-box";
            if (vue.selectSome()) return "mdi-minus-box";
            if (filtered) {
                if(vue.selectFilter()) return "mdi-close-box";
            }
            return "mdi-checkbox-blank-outline";
        },
        toggle() {
            let vue = this;
            vue.$nextTick(() => {
                vue.localSelected = vue.selectAll() ? [] : vue.selectIdsOrObject();
                vue.updateValue(vue.localSelected);
            });
        },
        selectIdsOrObject() {
            const vue = this;
            const filtered = vue.$refs.vue_autocomplete.filteredItems;

            let currentData =  [];

            if(vue.selectFilter()) currentData = [];
            else if(filtered.length) currentData = filtered;

            return currentData;
        },
        selectFilter() {
            const vue = this;
            const filtered = vue.$refs.vue_autocomplete.filteredItems;

            //verificar que los items filtrados sean iguales con los seleccionados en cantidad
            if(filtered.length === vue.localSelected.length) {
                // verficar que sus items sean iguales a traves del 'id'
                const currentSames = filtered.every((ele) => {
                    const existState = vue.localSelected.some((s_ele) => s_ele.id === ele.id );
                    return (existState === true);
                });

                return currentSames; //me indica que son similares y que pueden borrarse.
            }
            return false;// (filtered.length === vue.localSelected.length); no valida que sean iguales
        },
        selectAll() {
            const vue = this;
            return vue.localSelected.length === vue.items.length;
        },
        selectSome() {
            return this.localSelected.length > 0 && !this.selectAll;
        },
    },
    updated() {
        let vue = this;
        if (vue.multiple && vue.maxValuesSelected) {
            if (this.localSelected.length > vue.maxValuesSelected) {
                this.localSelected.splice(vue.maxValuesSelected, 1)
                vue.updateValue(this.localSelected)
            } else {
                return false;
            }
        }
    }
}
</script>

<style>
    .h-20{
        height: 20px !important;
    }

    .v-scroll-auto-complete{
        overflow-y: auto;
        overflow-x: hidden;
        max-height: 4.3rem;
    }

    .v-scroll-auto-complete::-webkit-scrollbar {
        width: 10px;
        border-radius: 3rem;
    }

    .v-scroll-auto-complete::-webkit-scrollbar-track {
        background: #cfcfcf;
        border-radius: 3rem;
    }

    .v-scroll-auto-complete::-webkit-scrollbar-thumb {
        background: #afafaf;
        border-radius: 3rem;
    }

    .readonly-chip {
        background: white !important;
        border: 2px solid #5458ea !important;
    }

    .readonly-chip .v-icon {
        background: silver;
        border-radius: 50%;
        margin-right: -7px;
    }

    .hidden-chip {
        display: none !important;
    }

    .readonly-list-item {
        display: none !important;
    }

</style>
