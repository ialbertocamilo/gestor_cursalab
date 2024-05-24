<template>
    <v-combobox
        attach
        hide-details="auto"
        outlined 
        :item-text="itemText"
        item-value="id"
        :rules="rules"
        v-model="localSelected"
        :multiple="multiple"
        :show-select-all="showSelectAll"
        :items="items"
        :search-input.sync="searchTag"
        @input="updateValue"
        :label="label"
    >
        <template v-slot:no-data>
            <v-list-item three-line>
                <v-list-item-content>
                    <v-list-item-title>
                        <span class="subheading pr-1"
                              v-text="'Se agregará el siguiente elemento:'"/>
                        <v-chip
                            label
                            small
                        >
                            {{ searchTag }}
                        </v-chip>
                    </v-list-item-title>
                    <v-list-item-subtitle>
                        {{ messageToCreate }}
                    </v-list-item-subtitle>
                </v-list-item-content>
            </v-list-item>
        </template>
        <template v-slot:selection="{ attrs, item, parent, selected, index }"
                  >
            <v-chip
                v-bind="attrs"
                :input-value="selected"
                label
                small
                v-if="index < countShowValues"
            >
                <span class="pr-2" v-text="item.nombre || item.name || item"/>
                <v-icon
                    small
                    @click="parent.selectItem(item)"
                >
                    $delete
                </v-icon>
            </v-chip>
            <span
                v-if="index === countShowValues"
                class="grey--text text-caption"
            >
                (+{{ localSelected.length - countShowValues }})
            </span>
        </template>
        <template v-slot:label v-if="showRequired">
            {{ label }}<RequiredFieldSymbol/>
        </template>
    </v-combobox>

</template>

<script>
export default {
    props: {
        value: {
            type: Object | Array,
            required: true
        },
        items: {
            type: Array,
            default: () => {
                return []
            }
        },
        label: {
            type: String
        },
        showRequired: {
            type: Boolean,
            default: false,
        },
        rules: {
            type: Object | Array,
        },
        multiple: {
            type: Boolean,
            default: false
        },
        showSelectAll: {
            type: Boolean,
            default: true
        },
        countShowValues: {
            type: Number,
            default: 1
        },
        itemText: {
            type: String,
            default: 'nombre'
        },
        messageToCreate:{
            type:String,
            default:'Presiona TAB o ENTER para agregar el tag'
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
    data() {
        return {
            localSelected: null,
            searchTag: null,

        }
    },
    methods: {
        updateValue(value) {
            let vue = this
            vue.$emit('input', value || null)
            vue.$emit('onChange')
        },
    }
}
</script>
