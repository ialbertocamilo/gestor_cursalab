<template>
	<DefaultDialog
		:options="options"
		:width="width"
		@onCancel="closeModal(), $emit('onCancel')"
		@onConfirm="confirmModal"
		:persistent="true"
	>
		<template v-slot:content>
			<v-form ref="storageForm">
				<DefaultErrors :errors="errors"/>
				
				<v-alert type="error" v-model="errorAlert" >
					Seleccione almacenamiento o ingrese n° de usuarios.
				</v-alert>

				<v-row>
					<v-col cols="6">
						<DefaultSelect
							clearable
							v-model="resource.limit_allowed_storage" 
							:items="storageItems"
							item-text="name"
							item-id="value"
							label="Almacenamiento"
						/>
					</v-col>
					<v-col cols="6">
						<DefaultInput
								clearable
								v-model="resource.limit_allowed_users"
								label="Usuarios"
								type="number"
								min="0"
							/>
					</v-col>
					<v-col cols="12">
						<DefaultTextArea
							clearable
							v-model="resource.description"
							label="Descripción"
							rows="5"
						/>
					</v-col>
				</v-row>
			</v-form>
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
			errors: [],
			errorAlert: false,
			resource: {
				limit_allowed_storage: null,
				limit_allowed_users: null,
				description: null
			},
			storageItems: [
				{ name: '8 Gb', value: 8 },
				{ name: '16 Gb', value: 16 },
				{ name: '32 Gb', value: 32 },
				{ name: '64 Gb', value: 64 }
			]
		};
	},
	methods: {
		closeModal() {
			let vue = this;

			vue.resetSelects();
			vue.resetValidation();

			vue.errorAlert = false;
		},
		resetValidation() {
			let vue = this;
			vue.$refs.storageForm.resetValidation();
		},

		confirmModal() {
			let vue = this;
			vue.errors = [];

			const validateForm = vue.validateForm("storageForm");

			const val_limit_allowed_storage = (vue.TypeOf(vue.resource.limit_allowed_storage) === 'null') ? '' : vue.resource.limit_allowed_storage.trim();
			const val_limit_allowed_users = (vue.TypeOf(vue.resource.limit_allowed_users) === 'null') ? '' : vue.resource.limit_allowed_users.trim();

			
			if (!val_limit_allowed_storage.length && !val_limit_allowed_users.length) 
				return vue.errorAlert = true;
			vue.errorAlert = false;
			

			if (validateForm) {
				vue.showLoader();
				vue.$http.put(`${vue.options.base_endpoint}/workspace-plan`, { ...vue.resource })
				.then((res) => {
					
					vue.hideLoader();
					vue.closeModal();
					vue.$emit("onConfirm");

					// vue.showAlert(data.data.msg);
				}).catch(error => {
					if (error && error.errors) vue.errors = error.errors;
				});
			}
	
		},
		resetSelects() {
			let vue = this;

			vue.resource.limit_allowed_storage = null;
			vue.resource.limit_allowed_users = null;
			vue.resource.description = null;
		},
		async loadData(resource) {
		},
		loadSelects() {
		},
	}
};
</script>
