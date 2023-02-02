<template>
    <DefaultDialog
        :options="{
            title: 'Filtro&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Detalle / descripción',
            open: isOpen
         }"
        @onCancel="close"
        :width="'667px'"
        :showCardActions="false"
    >
        <template v-slot:content>
            <v-row
                v-for="(value, label) in filters"
                :key="label">
                <v-col cols="4">
                    <span class="label">{{ label }}:</span>
                </v-col>
                <v-col cols="8">
                    <div class="value">{{ value }}</div>
                </v-col>
            </v-row>
        </template>
    </DefaultDialog>
</template>

<script>
export default {
    props: {
        isOpen : {
            type: Boolean,
            required: true
        },
        filters : {
            type: Object,
            required: true,
            default: {}
        }
    }
    ,
    data () {
        return {

        }
    }
    ,
    methods: {
        close() {
            this.$emit('cancel')
        },
    }
}
</script>

<style scoped>
    .label {
        font-style: normal;
        font-weight: 500;
        font-size: 16px;
    }
    .value {
        background: #FFFFFF;
        border: 0.2px solid #9E9E9E;
        border-radius: 10px;
        padding: 4px;
        min-height: 28px;
    }
</style>
