<template>
    <div class="bx_tooltip">
        <v-tooltip :top="true" attach="">
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
            <span>{{ tooltip }}</span>
        </v-tooltip>
    </div>
</template>
<script>
export default {
    props: {
        tooltip: {
            type: String,
            required: true
        }
    },
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
.bx_tooltip .v-tooltip__content {
    width: 300px;
    text-align: center;
    border: 1px solid #5757EA;
    background: #fff;
    color: #5757EA;
    border-radius: 8px;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
    opacity: 1 !important;
    left: -150px !important;
    top: 18px !important;
}
</style>
