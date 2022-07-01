<template>
    <v-btn icon :ripple="false" @click="emitClick">
        <v-icon v-text="'mdi-delete'"/>
    </v-btn>
</template>
<script>
export default {
    props: {
        color: {
            type: String,
            default: ''
        }
    },
    methods: {
        emitClick(){
            let vue = this
            vue.$emit('click')
        }
    }

}
</script>
