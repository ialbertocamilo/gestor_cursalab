<template>
    <v-btn
        class="mx-1 "
        :class="{'font-weight-bold': textBold}"
        elevation="0"
        :ripple="false"
        :color="color"
        @click="click"
        :block="block"
        :disabled="disabled"
        :outlined="outlined"
        :text="text"
        :style="`${min_content ? 'max-width: min-content;' : 'width:100%;'}`"
        :icon="isIconButton"
    >
        <v-icon :small="smallIcon" class="mr-1" v-text="icon" v-if="icon"/>
        {{ label }}
        <v-icon :small="smallIcon" class="ml-1" v-text="appendIcon" v-if="appendIcon"/>
    </v-btn>
</template>


<script>
export default {
    props: {
        color: {
            type:String,
            default:'primary'
        },
        label: {
            type: String,
            default: 'Botón Modal'
        },
        icon: {
            type: String
        },
        appendIcon: {
            type: String
        },
        block: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        outlined: {
            type: Boolean,
            default: false
        },
        textBold: {
            type: Boolean,
            default: false
        },
        text: {
            type: Boolean,
            default: false
        },
        smallIcon: {
            type: Boolean,
            default: false
        },
        min_content:{
            type:Boolean,
            default:true,
        },
        isIconButton:{
            type:Boolean,
            default:false,
        }
    },
    methods: {
        click() {
            let vue = this;
            vue.$emit('click')
        }
    }
}
</script>
