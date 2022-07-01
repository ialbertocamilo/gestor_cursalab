<template>
	<div class="col">
		<small class="text-muted text-bold">Estado del Tema :</small>
		<div class="d-flex mt-2">
			<div class="p-0 mr-auto d-flex align-center">
				<v-checkbox
					class="my-0 mr-4"
					label="Temas Activos"
					color="primary"
					v-model="temasActivos"
					hide-details="false"
				/>
				<div
					tooltip="Temas a los que los usuarios, que lo tienen asignado, pueden acceder."
					tooltip-position="top"
				>
					<v-icon class="info-icon">mdi-information-outline</v-icon>
				</div>
			</div>
			<div class="p-0 mr-auto d-flex align-center">
				<v-checkbox
					class="my-0 mr-4"
					label="Temas Inactivos"
					color="gray"
					v-model="temasInactivos"
					hide-details="false"
				/>
				<div
					tooltip="Temas a los que los usuarios no pueden acceder, así lo tengan asignado. "
					tooltip-position="top"
				>
					<v-icon class="info-icon">mdi-information-outline</v-icon>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			temasActivos: true,
			temasInactivos: true
		};
	}
};
</script>

<style>
</style>