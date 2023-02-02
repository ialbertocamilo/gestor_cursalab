<template>
    <div>
        <DefaultDialog
            :options="{
            title: 'Confirmar solicitud',
            confirmLabel: 'Entendido',
            persistent: true,
            open: isOpen,
            hideCancelBtn: true
         }"
            :width="'390px'"
            :showCardActionsBorder="false"
            @onCancel="close"
            @onConfirm="confirm"
        >
            <template v-slot:content>
                <p>
                    Esta acción puede tomar unos minutos, por lo que te notificaremos cuando esté listo para su descarga.
                </p>
                <p>
                    Podrás ver el estado de tu solicitud en la sección de Reportes generados.
                </p>

                <v-text-field
                    v-model="reportName"
                    label="Nombre del reporte"
                    :placeholder="placeholder"
                ></v-text-field>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>
export default {
    props: {
        placeholder: {
            type: String,
            default: ''
        },
        isOpen: {
            type: Boolean,
            default: false
        }
    },
    data () {
        return {
            reportName: '',
            rules: {
                name: this.getRules(['required', 'max:255'])
            }
        }
    },
    methods: {
        close() {
            this.$emit('cancel')
            this.reportName = ''
        },
        confirm() {
            this.$emit('confirm', { reportName: this.reportName })
            this.reportName = ''
        }
    }
}
</script>


