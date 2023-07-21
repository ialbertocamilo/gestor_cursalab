<template>
    <v-text-field 
        :label="label"
        :clearable="clearable"
        v-model="localColor"
        :disabled="disabled"
        :rules="rules"
        @click:clear="onClear"
        @input="updateValue"
        hide-details 
        class="ma-0 pa-0" 
        solo
        >
        <template v-slot:append>
            <v-menu 
                v-model="menu" 
                top 
                nudge-bottom="105" 
                nudge-left="20" 
                :close-on-content-click="false"
            >
                <template v-slot:activator="{ on }">
                    <div :style="swatchStyle" v-on="on" />
                </template>
                <v-card>
                    <v-card-text class="pa-0">
                        <v-color-picker v-model="localColor" flat />
                    </v-card-text>
                </v-card>
            </v-menu>
        </template>
    </v-text-field>
</template>

<script>
export default {
    props: {
        value: {
            required: true
        },
        type: {
            type: String,
            default: 'text',
        },
        placeholder: {
            type: String
        },
        label: {
            type: String
        },
        clearable: {
            type: Boolean,
            default: false,
        },
        dense: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        rules: {
            type: Object | Array,
        },
    },
    data() {
        return {
            localColor: null,
            localType: this.type,
            menu: false,
        }
    },
    computed: {
        swatchStyle() {
            const { localColor, menu } = this
            return {
                backgroundColor: localColor,
                cursor: 'pointer',
                height: '30px',
                width: '30px',
                borderRadius: menu ? '50%' : '4px',
                transition: 'border-radius 200ms ease-in-out'
            }
        }
    },
    methods:{
        onFocus() {
            this.$emit('onFocus')
        },
        updateValue(value) {
            let vue = this
            vue.$emit('input', value || null)
        },
        onClear() {
            let vue = this
            vue.localText = ''
            vue.updateValue()
            // vue.onKeyPressEnter()
        },
        onKeyPressEnter() {
            let vue = this
            vue.$emit('onEnter')
        },
        onClickAppendIcon(){
            let vue = this
            // console.log('onClickAppendIcon')
            vue.$emit('clickAppendIcon')
        }
    }
}
</script>
