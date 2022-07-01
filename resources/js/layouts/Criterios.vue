<template>
	<v-container>
		<v-card>
			<v-data-table
				:items="criterios"
				:headers="headers"
				hide-default-footer
				class="elevation-0"
				:items-per-page="items_per_page"
				:loading="loading_table"
			>
				<template v-slot:top>
					<v-toolbar flat>
						<v-toolbar-title>Criterios</v-toolbar-title>
						<v-divider class="mx-4" inset vertical></v-divider>
						<v-text-field
							dense
							outlined
							hide-details
							v-model="b_criterio"
							label="Buscar criterio"
							@input="buscar_criterio"
						>
						</v-text-field>
						<v-spacer></v-spacer>
						<v-btn @click="abriDialogCriterio(criterio,'new')"> <v-icon>mdi-plus</v-icon> Crear</v-btn>
					</v-toolbar>
				</template>
				<template v-slot:[`item.editar`]="{ item }">
					<v-tooltip right attach="">
						<template v-slot:activator="{ on, attrs }">
							<v-btn
								icon
								color="primary"
								dark
								v-bind="attrs"
								v-on="on"
								@click="abriDialogCriterio(item,'edit')"
							>
								<v-icon> mdi-tooltip-edit </v-icon>
							</v-btn>
						</template>
						<span>Editar</span>
					</v-tooltip>
					<v-tooltip right attach="">
						<template v-slot:[`activator`] ="{}">
							<v-badge
								color="green"
								:content="item.usuarios_count"
								>
								<v-icon color="primary"> mdi-account-circle </v-icon>
							</v-badge>
						</template>
						<span>Cantidad de usuarios</span>
					</v-tooltip>
				</template>
			</v-data-table>
			<v-divider></v-divider>
			<div class="text-center pb-2">
				<v-pagination
					@input="cambiar_pagina"
					:value="page"
					class="mx-12"
					v-model="page"
					:length="pageCount"
				></v-pagination>
			</div>
		</v-card>
		<v-dialog v-model="dialog_criterio"  @click:outside="dialog_criterio=false" max-width="450px">
			<v-form ref="form_criterio" v-model="form_criterio" lazy-validation>
				<v-card>
					<v-card-tex>
						<b-alert v-text="text_error" :show="dismissCountDown" @dismissed="dismissCountDown=0" @dismiss-count-down="countDownChanged" variant="warning">Esta botica ya existe</b-alert>
					</v-card-tex>
					<v-card-title> Crear un criterio </v-card-title>
					<v-card-text>
						<v-row>
							<v-col cols="12" md="6" lg="6">
								<v-autocomplete :rules="nameRules" required v-model="criterio.config_id" label="Módulo" :items="modulos" item-value="id" item-text="etapa"></v-autocomplete>
							</v-col>
							<v-col cols="12" md="6" lg="6">
								<v-autocomplete :rules="nameRules" :items="tipos_criterios" item-text="nombre" item-value="nombre" v-model="criterio.tipo_criterio" label="Tipo"></v-autocomplete>
							</v-col>
							<v-col cols="12" md="6" lg="6">
								<v-text-field :rules="nameRules" v-model="criterio.valor" label="Nombre"></v-text-field>
							</v-col>
						</v-row>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn @click="dialog_criterio = false">Cancelar</v-btn>
						<v-btn class="ml-3" :disabled="!form_criterio" @click="save_or_edit()">Guardar</v-btn>
					</v-card-actions>
				</v-card>
			</v-form>
		</v-dialog>
	</v-container>
</template>

<script>
export default {
	data() {
		return {
			b_criterio:'',
			title_modal:'Crear Botica',
			form_criterio:true,
			dialog_criterio: false,
			loading_table: true,
			criterios: [],
			modulos:[],
			items_per_page: 15,
			headers: [
				{ text: "Modulo", value: "config.etapa" },
				{ text: "Nombre criterio", value: "valor" },
				{ text: "Tipo", value: "tipo_criterio" },
				{ text: "Opciones", value: "editar", align: "center", class: "mr-5" },
			],
			pageCount: 1,
			page: 1,
			criterio: {
				id:0,
				config_id:0,
				tipo_criterio: 0,
				valor: "",
			},
			tipos_criterios:[
				{nombre:'GRUPO'}
			],
			nameRules: [
				v => !!v || 'El campo es requerido'
			],
			tipo_criterio_id:0,
			dismissCountDown: 0,
			text_error:'',
		};
	},
	created() {
		let vue = this;
		vue.getInitialData();
	},
	methods: {
		getInitialData(page) {
			let vue = this;
			vue.loading_table = true;
			axios.get("/criterios/getInitialData?page=" + vue.page).then((res) => {
				vue.criterios = res.data.data;
				vue.modulos = res.data.modulos;
				vue.pageCount = res.data.total_paginas;
				vue.loading_table = false;
			});
		},
		cambiar_pagina(page) {
			let vue = this;
			vue.getInitialData(page);
		},
		abriDialogCriterio(item,tipo) {
			let vue = this;
			if(tipo=='new'){
				vue.title_modal = 'Crear Criterio';
				vue.criterio = {
					id:0,
					config_id:0,
					tipo_criterio: '',
					valor: '',
				};
			}else{
				vue.title_modal = 'Editar Criterio';
				vue.criterio = {
					id:item.id,
					tipo_criterio: item.tipo_criterio,
					config_id: item.config_id,
					valor: item.valor,
				};
			}
			vue.dialog_criterio = true;
		},
		save_or_edit(){
			if(this.$refs.form_criterio.validate()){
				axios.post("/criterios/insert_or_edit",this.criterio).then((res) => {
					if(res.data.error){
						this.text_error = res.data.mensaje;
						this.dismissCountDown = 10;
					}else{
						this.dialog_criterio = false;
						this.b_criterio = ""
						this.getInitialData();
					}
				})
			}
		},
		buscar_criterio(){
			this.loading_table = true;
			clearTimeout(this.debounce);
			this.debounce = setTimeout(() => {
				this.typing = null;
				if (this.b_criterio != "") {
					axios.get("/criterios/buscar/"+this.b_criterio+"?page=" + this.page).then((res) => {
						this.criterios = res.data.data;
						this.pageCount = res.data.total_paginas;
						this.loading_table = false;
					});
				} else {
					this.getInitialData();
					this.loading_table = false;
				}
			}, 1200);
		}
	},
};
</script>