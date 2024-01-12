<template>
    <v-row justify="center" align="center">
        <v-col cols="9" class="d-flex flex-row" style="justify-content: space-evenly">
            <strong>+ Agregar nuevo multimedia</strong>
        </v-col>
        <v-col cols="9" class="d-flex flex-row" style="justify-content: space-evenly">
            <MultimediaBox
                :limits="limits"
                v-for="box in mixin_multimedias"
                :key="box.label"
                :data="box"
                @addMultimedia="emitAdMultimedia"
            />
        </v-col>
    </v-row>
</template>
<script>
import MultimediaBox from "./MultimediaBox";

export default {
    components: {MultimediaBox},
    props:['limits'],
    data() {
        return {

        }
    },
    methods: {
        emitAdMultimedia(data) {
            console.log(data);
            let vue = this
            vue.$emit('addMultimedia', data)
        }
    }
}
</script>
