<template>
    <div>
        <v-textarea
            :ref="refTextArea"
            outlined
            :label="label"
            :placeholder="placeholder"
            :value="localText"
            :rules="rules"
            :clearable="clearable"
            :disabled="disabled"
            @input="updateValue"
            hide-details="auto"
            :rows="rows"
            :loading="loading"
            :counter="counter"
        >
            <template v-slot:label v-if="showRequired">
                {{ label }}<RequiredFieldSymbol/>
            </template>
            <template v-slot:append-outer v-if="showButtonIaGenerate">
                <div class="custom-textarea-addon d-flex align-items-center" @click="eventGenerateIA">
                    <div>Generar con IA </div>
                    <img 
                        width="22px" 
                        class="ml-2" 
                        src="/img/ia_convert.svg"
                    >
                    <span class="span-limits" v-if="limits.limit_descriptions_jarvis < 1000">{{limits.ia_descriptions_generated}}/{{ limits.limit_descriptions_jarvis }}</span>
                </div>
            </template>
        </v-textarea>
    </div>
</template>
<script>
export default {
    props: {
        label: {
            type: String,
            required: true
        },
        value: {
            required: true
        },
        placeholder: {
            required: false,
            default: ''
        },
        rules: {
            type: Object | Array
        },
        showRequired: {
            type: Boolean,
            default: false,
        },
        clearable: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        rows: {
            default: 3
        },
        refTextArea:{
            type:String,
            default:'text_area'
        },
        showButtonIaGenerate:{
            type: Boolean,
            default: false
        },
        loading:{
            type: Boolean,
            default: false
        },
        limits:{
            required: false,
            type: Object | Array,
        },
        counter:{
            type: Boolean,
            default: false,
        }
    },
    data() {
        return {
            localText: null
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
    methods: {
        updateValue(value) {
            let vue = this
            vue.$emit('input', value || null)
        },
        eventGenerateIA(){
            let vue = this
            vue.$emit('eventGenerateIA')
        }
    }
}
</script>
<style scoped>
.custom-textarea-addon {
  position: absolute;
  bottom: 8px;
  right: 8px;
  color: #888; 
  font-size: 12px; 
  cursor: pointer;
}
.span-limits{
    color: white;
    background: rgb(87, 191, 227);
    padding: 5px;
    border-radius: 16px;
    margin-right: 8px;
    margin-left: 2px;
}
</style>
