<template>
    <v-text-field
        class="custom-default-input"
        outlined
        :dense="dense"
        hide-details="auto"
        :clearable="clearable"
        :placeholder="placeholder"
        :label="label"
        v-model="localText"
        :disabled="disabled"
        @click:clear="onClear"
        @input="updateValue"
        @keypress.enter="onKeyPressEnter"
        @keypress="isNumber($event)"
        :rules="rules"
        :counter="counter"
        :type="localType"
        :max="max"
        :min="min"
        :step="step"
        :single-line="singeLine"
        :suffix="suffix"
        :prefix="prefix"
        :loading="loading"
        :autocomplete="autocomplete"
        @focus="onFocus"
    >
        <template v-slot:append>
            <v-btn v-if="type == 'password'" width="32" height="32" plain icon
                   @click="onClickSeePassword">
                <span :class="`far ${iconSeePass ? 'fa-eye' : 'fa-eye-slash' } fa-lg`"></span>
            </v-btn>
            <v-btn v-if="appendIcon" plain icon :ripple="false" @click="onClickAppendIcon">
                <v-icon>{{ appendIcon }}</v-icon>
            </v-btn>
            <DefaultInfoTooltipForm v-if="tooltip != ''" :tooltip="tooltip" />
        </template>
        <template v-slot:label v-if="showRequired">
            {{ label }}<RequiredFieldSymbol/>
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
        autocomplete: {
            type: String,
            default: null,
        },
        suffix: {
            type: String,
            default: null,
        },
        prefix: {
            type: String,
            default: null,
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
        showRequired: {
            type: Boolean,
            default: false,
        },
        min: {
            type: Number | String,
            default: 0,
        },

        step: {
            type: Number | String,
            default: 1,
        },

        max: {
            type: Number | String,
            default: 255,
        },

        dense: {
            type: Boolean,
            default: false,
        },
        singeLine: {
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
        counter: {
            type: String | Number,
            default: false,
        },
        appendIcon: {
            default: null
        },
        numbersOnly: {
            type: Boolean,
            default: false,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        tooltip: {
            type: String,
            default: ''
        },
    },
    data() {
        return {
            localText: null,
            localType: this.type,

            iconSeePass: true
        }
    },
    created() {
        if (this.value) {
            this.localText = this.value // set initial value
        }
    },
    watch: {
        value(val) {
            this.localText = val // watch change from parent component
        }
    },
    computed:{
        enableBtnSeePassword() {
            const vue = this;
            return (vue.type == 'password');
        }
    },
    methods: {
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
        },
        isNumber: function(evt) {

            if (!this.numbersOnly) return true;

            evt = (evt) ? evt : window.event;
            let expect = evt.target.value.toString() + evt.key.toString();

            if (!/^[-+]?[0-9]*\.?[0-9]*$/.test(expect)) {
                evt.preventDefault();
            } else {
                return true;
            }
        },
        resetTypePassword() {
            const vue = this;

            vue.localType = 'password';
            vue.iconSeePass = true;
        },
        onClickSeePassword() {
            const vue = this;

            vue.iconSeePass = !vue.iconSeePass;
            vue.localType = !vue.iconSeePass ? 'text' : 'password';
        },
    }
}
</script>
<style lang="scss">
i.v-icon.icon_tooltip {
    color: #000 !important;
}
</style>
