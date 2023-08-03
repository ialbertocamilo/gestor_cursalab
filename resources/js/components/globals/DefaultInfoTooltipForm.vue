<template>
    <div class="bx_tooltip">
        <v-tooltip :top="true">
            <template v-slot:activator="{ on, attrs}">
                <v-icon
                    small
                    color="primary"
                    dark
                    v-bind="attrs"
                    v-on="on"
                    v-text="'mdi-information'"
                    class="icon_tooltip"
                />
            </template>
            <span>Fecha en la que se libera el beneficio como confirmado y se confirma a los colaboradores en lista.</span>
        </v-tooltip>
    </div>
</template>
<script>
export default {
    data() {
        return {
            show: false
        }
    },
}
</script>
<style lang="scss">
.bx_tooltip {
    position: absolute;
    top: -10px;
    right: -5px;
}
</style>
