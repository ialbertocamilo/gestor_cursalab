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
        ref="fieldtext"
    >
        <template v-slot:append>
            <v-btn v-if="type == 'password'" width="32" height="32" plain icon
                   @click="onClickSeePassword">
                <span :class="`far ${iconSeePass ? 'fa-eye' : 'fa-eye-slash' } fa-lg`"></span>
            </v-btn>

            <v-btn  v-if="emojiable" plain icon :ripple="false"
                v-click-outside="{ handler: onClickOutside }">
                <v-icon class="emoji-icon-button" @click="showEmojiPicker = !showEmojiPicker" >mdi mdi-emoticon</v-icon>
                <Picker :data="emojiIndex" set="google" @select="showEmoji" :show-preview="false"  :show-skin-tones="false" style="position: absolute; z-index: 100; top: 20px; right: 10px;" v-show="showEmojiPicker" :useButton="false" :native="false" />
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

import data from "emoji-mart-vue-fast/data/all.json";
import "emoji-mart-vue-fast/css/emoji-mart.css";
import { Picker, EmojiIndex } from "emoji-mart-vue-fast";

let emojiIndex = new EmojiIndex(data);

export default {
    components: {Picker},
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
        emojiable: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            emojiIndex: emojiIndex,
            emojisOutput: "",

            localText: null,
            localType: this.type,

            iconSeePass: true,
            showEmojiPicker: false,
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
        onClickOutside () {
            this.showEmojiPicker = false
        },
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
        // showEmoji(emoji) {
        //     // this.emojisOutput = this.emojisOutput + emoji.native;
        //     this.setEmojiAtPosition(emoji.native)

        // },
        showEmoji(emoji) {

            let pos = this.$refs.fieldtext.$refs.input.selectionStart
            const len = this.localText.length

            if (pos === undefined) {
                pos = 0
            }

            const before = this.localText.substr(0, pos)
            const after = this.localText.substr(pos, len)

            let inserted = before + emoji.native

            this.localText = inserted + after

            this.updateValue(this.localText)

            this.$nextTick(() => {
                this.$refs.fieldtext.$refs.input.selectionEnd = inserted.length
            });
        },
        keepPosition() {
            const field = this.$refs.fieldtext.$refs.input
        },
    }
}
</script>
<style lang="scss">
    i.v-icon.icon_tooltip {
        color: #000 !important;
    }
    .emoji-mart-category {
        white-space: initial !important;
        cursor: auto !important;
    }
    .emoji-mart {
        margin: 0 auto;
        text-align: left;
        padding: 0 !important;
    }
    .emoji-type-image, .emoji-type-native {
        cursor: pointer !important;
    }


    // .v-input.custom-default-input.v-input--dense.v-text-field .v-btn__content button.v-icon {
    .v-input.custom-default-input.v-text-field .v-btn__content button.v-icon {
        margin-top: -5px !important;
    }

    // .emoji-icon-button:before {
    //     background-color: white !important;
    // }

    // .v-btn:not(.v-btn--text):not(.v-btn--outlined):hover:before {
    //     opacity: .08;
    //     background: white;
    // }

    .emoji-mart-search {
        display: none !important;
    }
</style>
