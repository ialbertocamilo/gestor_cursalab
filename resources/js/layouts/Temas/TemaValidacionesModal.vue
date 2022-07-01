
<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <!--            <h1>{{ options.action }}</h1>-->
            <!--            <h1>{{ resource }}</h1>-->
            <!--            <h1>{{ validateData }}</h1>-->
            <div
                v-if="['validateUpdateStatus', 'validateDeleteTema', 'validateUpdateTema', 'messagesActions'].includes(options.action)">
                <div
                    v-for="(validate,i) in validateData.data" :key="i">
                    <strong v-text="validate.title"/> <br>
                    {{ validate.subtitle || "" }}
                    <ul class="mt-1">
                        <li v-for="(item, i) in validate.list" :key="i" v-html="item"/>
                    </ul>
                </div>
            </div>
            <div v-else-if="options.action === 'showAlertEvaluacion'">
                <strong v-text="validateData.data[0].title"/> <br>
            </div>
            <div v-else-if="options.action === 'validacionFormPage'">
                <div
                    v-if="resource && resource.hide_tipo_ev === 'calificada' && resource.evaluable === 'no'">
                    Estas a punto de cambiar el tipo de evaluación de evaluable calificada a no evaluable. Recuerda
                    que es necesario si el avance se mantendrá o se borrará.<br/>
                    <div>
                        <span>¿Desea mantener el avance de los usuarios?</span><br/>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input"
                                   v-model="checkbox">
                            <label class="form-check-label" v-text="'Sí, deseo mantener el avance.'"/>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <label class="form-check-label"
                           v-text="'Si deseas descargar el reporte antes del cambio da click aquí 👇'"/>
                    <br>
                    <div class="d-flex justify-content-center">
                        <DefaultButton
                            class="mt-2"
                            small-icon
                            label="Descargar reporte"
                            @click="descargarReporte"
                            icon="mdi-download"
                        />
                    </div>
                </div>
            </div>
        </template>
    </DefaultDialog>
</template>

<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: {
            type: String,
            required: false
        },
        resource: {
            required: true
        },
    },
    data() {
        return {
            validateData: null,
            checkbox: false
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.checkbox = false
            // vue.validateData = null
        },
        resetSelects() {
            let vue = this
            vue.checkbox = false
        },
        confirmModal() {
            let vue = this
            let data = {
                checkbox: vue.checkbox,
                confirmMethod: vue.options.action
            }
            // console.log('CONFIRM DATA MODAL VALIDACION :: ', data)
            vue.$emit('onConfirm', data)
        },
        async loadData(validateData) {
            let vue = this
            // console.log("loadData :: ", validateData)
            vue.validateData = Object.assign({}, validateData)

            return 0;
        },
        loadSelects() {
            let vue = this

        },
        descargarReporte() {
            let vue = this
            // console.log(vue.validateData)
            // return
            vue.downloadReportFromNode(vue.validateData.url, vue.validateData)
        }

    },
}
</script>
