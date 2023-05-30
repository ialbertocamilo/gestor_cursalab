<template>
    <DefaultDialog
        width="20vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>
            <v-row>
                <v-col cols="12" class="text-center">
                    
                    <div v-if="(options.resource.tipo === 'normal')">
                        ¿Estas seguro de realizar esta acción?
                    </div>
                    <div v-else>
                        Se eliminará toda la plantilla y todos sus elementos. <br> ¿Estas seguro de realizar esta acción?
                    </div>
                </v-col>
            </v-row>
        </template>
        
    </DefaultDialog>
</template>

<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            resource: null,
        }
    },
    methods: {
        resetValidation() {},
        loadData(resource) {},
        loadSelects() {},
        onCancel() {
            let vue = this;
            vue.$emit('onCancel')
        },
        onConfirm() {
            let vue = this
            vue.$emit('onConfirm', vue.options.resource);
        }
    }
}
</script>
<style>
    .bx_header .cont {
        color: #2A3649;
        font-size: 20px;
        font-family: "Nunito", sans-serif;
        font-weight: 700;
        margin-left: 29px;
        text-align: left;
        line-height: 25px;
    }
</style>