<template>
	<DefaultDialog
		:options="options"
		:width="width"
		@onCancel="closeModal"
		@onConfirm="$emit('onConfirm')"
		:persistent="true"
	>
		<template v-slot:content>
			<v-row class="justify-content-center">
				<p class="text-body fa-2x mt-3 mb-8"> Muchas gracias por confiar en nosotros</p>
				<div class="text-center w-50">
					<p>
						Nuestro equipo comercial se comunicará contigo en breve para procesar 
						<b>tu solicitud.</b>
					</p>
					<img src="/img/mail-send.png" alt="mail-send" style="width: 50%;margin: 1rem 0;">
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
