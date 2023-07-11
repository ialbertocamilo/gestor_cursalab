<template>
	<DefaultDialog
		:options="options"
		:width="width"
		@onCancel="closeModal"
		@onConfirm="$emit('onConfirm')"
		header-class="text-body bg-white ml-24-porcent"
		color-close-icon="primary"
		:persistent="true"
	>
		<template v-slot:content>
			<v-row class="justify-content-center">
				
				<div class="text-center w-50">
					<p>
						Nuestro equipo comercial se comunicará contigo en breve para procesar 
						<b>tu solicitud.</b>
					</p>
					<span class="fas fa-paper-plane fa-7x my-4 text-primary-sub"></span>
				</div>

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
		},
		width: String,
		code: {
			type: String,
			default: null
		},
	},
	data() {
		return {
		};
	},
	methods: {
		closeModal() {
			const vue = this;
			vue.$emit('onCancel');
		},
		resetValidation() {},
		confirmModal() {},
		resetSelects() {},
		async loadData(resource) {},
		loadSelects() {
			let vue = this;
		},
	}
};
</script>
