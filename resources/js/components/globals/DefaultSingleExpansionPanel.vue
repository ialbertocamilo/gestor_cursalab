<template>
    <v-expansion-panels tile
                        class="elevation-0"
                        v-model="panel"
                        multiple
    >
        <v-expansion-panel
        >
            <v-expansion-panel-header class="font-weight-bold">
                <slot name="header"/>
            </v-expansion-panel-header>
            <v-expansion-panel-content>
                <v-divider style="margin-top: .1rem !important; margin-bottom: .5rem !important;"/>
                <slot name="content"/>
            </v-expansion-panel-content>
        </v-expansion-panel>
    </v-expansion-panels>
</template>

<script>
export default {
    props: {},
    data() {
        return {
            panel: [0]
        }
    },
    methods: {
        closePanel() {
            let vue = this
            vue.panel = []
        },
        openPanel(){
            let vue = this
            vue.panel = [0]
        }
    }
}
</script>
