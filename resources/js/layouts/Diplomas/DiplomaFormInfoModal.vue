<template>
    <DefaultDialog
        width="30vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>
            <v-row>
                <v-col cols="12">
                    <span class="text-start">
                        <ul>
                            <li>Personaliza tu diploma ubicando el nombre del curso y de los usuarios, la fecha de emisión y el texto descriptivo.</li>
                            <li>El detalle del diploma se mostraría dentro de la imagen que definas como plantilla.</li>
                            <li>Puedes usar la herramienta de "Previsualizar" y realizar ediciones a tu progreso.</li>
                            <li>Haz clic en Guardar para guardar tu progreso.</li>
                        </ul>
                    </span>
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
            vue.$emit('onConfirm', true);
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