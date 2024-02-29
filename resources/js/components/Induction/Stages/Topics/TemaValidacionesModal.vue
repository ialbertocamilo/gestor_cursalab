<template>
    <DefaultDialog
        :options="options"
        :width="width"
        :show-close-icon="showCloseIcon"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <!--            <h5>{{ options.action }}</h5>-->
            <!--            <h5>{{ resource }}</h5>-->
            <!--            <h5>{{ validateData }}</h5>-->
            <div v-if="options.action === 'showAlertEvaluacion'" class="d-flex justify-content-center">
                <strong v-text="validateData.data[0]"/> <br>
            </div>
            <div v-else-if="options.action === 'validations-before-update'">
                <div
                    v-if="resource && (validateData.selectedType && validateData.selectedType.code === 'qualified') && (resource.assessable === 0 || resource.assessable === null)">
                    Estás a punto de cambiar el tipo de evaluación de evaluable calificada a no evaluable. Recuerda
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
                <div
                    v-if="validateData.list && validateData.list.length > 0"
                    v-for="(validation,i) in validateData.list" :key="i">
                    <strong v-text="validation.title ||'' "/> <br>
                    {{ validation.subtitle || "" }}
                    <ul class="mt-1">
                        <li v-for="(item, i) in validation.list" :key="i" v-html="item"/>
                    </ul>
                </div>
                <div class="mt-2">
                    <label class="form-check-label"
                           v-text="'Si deseas descargar el reporte antes del cambio haz click aquí 👇'"/>
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
            <div
                v-else
                v-for="(validation,i) in validateData.list" :key="i">
                <strong v-text="validation.title ||'' "/> <br>
                {{ validation.subtitle || "" }}
                <ul class="mt-1">
                    <li v-for="(item, i) in validation.list" :key="i" v-html="item"/>
                </ul>
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
            required: false
        },
        showCloseIcon: {
            type: Boolean,
            default: true
        },
    },
    data() {
        return {
            validateData: [],
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
