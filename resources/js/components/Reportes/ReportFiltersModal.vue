<template>
    <DefaultDialog
        :options="{
            title: 'Filtro&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Detalle / descripción',
            open: isOpen,
            showFloatingCloseButton: true
         }"
        @onCancel="close"
        :width="'667px'"
        :showCardActions="false"
    >
        <template v-slot:content>
            <v-row
                v-for="(values, label) in filters"
                :key="label">
                <v-col cols="4">
                    <span class="label">{{ label }}:</span>
                </v-col>
                <v-col cols="8">
                    <div
                        v-if="!isArray(values)"
                        class="value">
                        {{ values }}
                    </div>
                    <div
                        v-if="isArray(values)"
                        class="value">
                        <div v-for=" name of values">
                            {{ name }}
                        </div>
                    </div>

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
        isArray(arr) {
            return Array.isArray(arr)
        }
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
        max-height: 100px;
        overflow-y: auto;
    }
</style>
