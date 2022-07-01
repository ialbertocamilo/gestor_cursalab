<template>
	<v-dialog v-model="dialog" width="700">
		<v-card >
			<v-card-title class="text-h5 primary text-white"> {{ title }} </v-card-title>
			<v-list class="mx-3 my-2">
				<v-list-item-group prepend-icon="mdi-account-circle">
					<slot></slot>
				</v-list-item-group>
			</v-list>

			<v-divider></v-divider>

			<v-card-actions>
				<v-spacer></v-spacer>
				<v-btn color="primary" @click="closeModal" class="font-weight-bold"> Cerrar </v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script>
export default {
	props: {
        dialog: {
            type: Boolean,
            default: false
        },
        title: {
            type: String,
        	default: 'Columnas del reporte'
        },
    },
 	methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
    }
};
</script>

<style>
.btn-resumen {
	right: 0;
	top: 0;
}
</style>