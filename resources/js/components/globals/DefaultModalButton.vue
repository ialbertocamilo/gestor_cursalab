<template>
    <v-btn
        class="mx-1"
        :disabled="disabled"
        :class="{}"
        elevation="0"
        :ripple="false"
        color="primary"
        @click="emitClick"
    >
        <div class="icon_tmp" v-if="template">
            <img src="/img/anfitriones.png">
        </div>
        <v-icon class="" v-text="'mdi-plus'" v-if="icon"/>
        {{ label }}
    </v-btn>
</template>


<script>
export default {
    props: {
        label: {
            type: String,
            default: 'Botón Modal'
        },
        icon: {
            type: Boolean,
            default: true
        },
        template: {
            type: String
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        emitClick(){
            let vue = this;
            vue.$emit('click')
        }
    }
}
</script>
