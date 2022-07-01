<template>
    <v-btn
        class="mx-1"
        text
        elevation="0"
        :ripple="false"
        color="primary"
        @click="emitClick"
    >
        {{ label }}
    </v-btn>
</template>


<script>
export default {
    props: {
        label: {
            type: String,
            default: 'Botón Modal'
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
