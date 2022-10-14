<template>
    <DefaultDialog
        :width="width"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>
          <v-row>
            <v-col cols="12" class="text-center">
              <h5>Reunión : {{ options.resource.name }}</h5>
              <h5>Prefijo : {{ options.resource.prefix }}</h5>
            </v-col>
            <v-col cols="12" class="text-center">
                <h6>Inicio : {{ options.resource.starts_at }}</h6>
                <h6>Anfitrion : {{ options.resource.host }}</h6>
                <v-chip
                    class="ma-2 white--text"
                    :color="options.resource.status.color"
                    small
                >
                    {{ options.resource.status.text }}
                </v-chip>
            </v-col>
          </v-row>
        </template>
    </DefaultDialog>
</template>

<script>
export default {
    props: {
        width:{ type: String },
        options: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            resources: null
        }
    },
    methods: {
        onCancel() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {

        },
        onConfirm() {

        },
        loadData(resource) {
        },
        loadSelects() {

        }
    }
}
</script>
