<template>
	<div class="px-3">
		<!-- <v-alert ty>
			<slot name="resumen"></slot>
		</v-alert> -->
		<v-alert dense text color="primary" width="80%">
			<slot name="resumen"></slot>
		</v-alert>
		<v-dialog v-model="dialog" width="700">
			<template v-slot:activator="{ on, attrs }">
				<v-btn
					color="indigo lighten-5 text-muted text-inherit font-weight-bold btn-resumen absolute"
					absolute
					v-bind="attrs"
					v-on="on"
				>
					Ver detalles
					<v-icon class="pl-2 text-muted"> mdi-information </v-icon>
				</v-btn>
			</template>

			<v-card>
				<v-card-title class="text-h5 grey lighten-2"> Columnas del reporte </v-card-title>
				<v-list dense>
					<v-list-item-group prepend-icon="mdi-account-circle">
						<!-- <v-list-item-content class="px-5"> -->
						<!-- <slot name="resumen"></slot> -->
						<!-- </v-list-item-content> -->
						<!-- <v-divider></v-divider> -->
						<slot></slot>
					</v-list-item-group>
				</v-list>

				<v-divider></v-divider>

				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn color="primary" @click="dialog = false" class="font-weight-bold"> Cerrar </v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>

<script>
export default {
	data() {
		return {
			dialog: false
		};
	}
};
</script>

<style>
.btn-resumen {
	right: 0;
	top: 0;
}
</style>