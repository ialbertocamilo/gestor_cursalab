<template>
	<div>
		<DefaultDialog
			:options="options"
			:width="width"
			@onCancel="closeModal"
			@onConfirm="confirmModal"
		>
			<template v-slot:content>
				<v-row>
					<v-col class="d-flex justify-content-center" cols="12">
						<img src="/img/storage/storage-limit-alert.png" alt="limite de alamacenamiento">
					</v-col>
					<v-col class="text-center py-0" cols="12">
						<h4 class="text-body">
							Has superado la capacidad de almacenamiento dentro de la plataforma
						</h4>

						<p class="mb-0 mt-4">
							Si deseas puedes solicitar una mayor cantidad de almacenamiento
						</p>
					</v-col>
				</v-row>
			</template>
		</DefaultDialog>
	</div>
</template>
<script>
	export default {
		props: {
			options: {
				type: Object,
				required: true
			},
			width: String,
		},
		data() {
			return {
			}
		},
		methods: {
			closeModal() {
				const vue = this;
				vue.$emit('onCancel');
			},
			confirmModal() {
				const vue = this;
				vue.$emit('onConfirm');
			},
			loadData(){
			},
			resetValidation(){
			},
			loadSelects(){
			}
		}
	}
</script>