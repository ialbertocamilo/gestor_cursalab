<template>
	<v-container>
		<v-card>
			<v-data-table
				:items="boticas"
				:headers="headers"
				hide-default-footer
				class="elevation-0"
				:items-per-page="items_per_page"
				:loading="loading_table"
			>
				<template v-slot:top>
					<v-toolbar flat>
						<v-toolbar-title>Boticas</v-toolbar-title>
						<v-divider class="mx-4" inset vertical></v-divider>
						<v-text-field
							dense
							outlined
							hide-details
							v-model="b_botica"
							label="Buscar por botica"
							@input="page=1;buscar_botica()"
						>
						</v-text-field>
						<v-spacer></v-spacer>
						<v-btn @click="abriDialogBotica(botica,'new')"> <v-icon>mdi-plus</v-icon> Crear</v-btn>
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
								@click="abriDialogBotica(item,'edit')"
							>
								<v-icon> mdi-tooltip-edit </v-icon>
							</v-btn>
						</template>
						<span>Editar</span>
					</v-tooltip>
					<v-tooltip right attach="">
						<template v-slot:activator="{ on, attrs }">
							<v-btn
								icon
								color="primary"
								dark	
								v-bind="attrs"
								v-on="on"
								@click="abrirDialogDelete(item)"
							>
								<v-icon> mdi-tooltip-remove </v-icon>
							</v-btn>
						</template>
						<span>Eliminar</span>
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
		<v-dialog v-model="dialog_botica" @click:outside="dialog_botica=false" max-width="500px" lazy-validation>
			<v-form  ref="form_botica" v-model="form_botica" lazy-validation>
				<v-card>
					<v-card-tex>
						<b-alert v-text="text_error" :show="dismissCountDown" @dismissed="dismissCountDown=0" @dismiss-count-down="countDownChanged" variant="warning">Esta botica ya existe</b-alert>
					</v-card-tex>
					<v-card-title v-text="title_modal"> Crear Botica </v-card-title>
					<v-card-text>
							<v-row>
								<v-col cols="12" md="6" lg="6">
									<v-autocomplete @change="get_criterios()" :rules="nameRules" required v-model="botica.config_id" label="Módulo" :items="modulos" item-value="id" item-text="etapa"></v-autocomplete>
								</v-col>
								<v-col cols="12" md="6" lg="6">
									<v-autocomplete :rules="nameRules" required v-model="botica.criterio_id" label="Grupo" :items="criterios" item-value="id" item-text="valor"></v-autocomplete>
								</v-col>
								<v-col cols="12" md="6" lg="6">
									<v-text-field :rules="nameRules" required v-model="botica.codigo_local" label="Codigo Local"></v-text-field>
								</v-col>
								<v-col cols="12" md="6" lg="6">
									<v-text-field :rules="nameRules" required v-model="botica.nombre" label="Nombre"></v-text-field>
								</v-col>
							</v-row>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn @click="dialog_botica = false">Cancelar</v-btn>
						<v-btn :disabled="!form_botica" class="ml-3" @click="save_or_edit()">Guardar</v-btn>
					</v-card-actions>
				</v-card>
			</v-form>
		</v-dialog>
		<v-dialog v-model="dialogDelete" @click:outside="dialogDelete = false" max-width="500px">
          <v-card>
            <v-card-title class="headline" v-text="text_delete"></v-card-title>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="closeDelete">Cancelar</v-btn>
              <v-btn color="blue darken-1" text @click="delete_botica" v-if="btn_delete">Aceptar</v-btn>
              <v-spacer></v-spacer>
            </v-card-actions>
          </v-card>
        </v-dialog>
	</v-container>
</template>

<script>
export default {
	data() {
		return {
			title_modal:'Crear Botica',
			b_botica:'',
			dialog_botica: false,
			dialogDelete:false,
			loading_table: true,
			form_botica:true,
			boticas: [],
			criterios : [],
			modulos : [],
			items_per_page: 15,
			headers: [
				{ text: "Módulo", value: "config.etapa" },
				{ text: "Grupo", value: "criterio.valor" },
				{ text: "Código Local", value: "codigo_local" },
				{ text: "Nombre Botica", value: "nombre" },
				{ text: "Opciones", value: "editar", align: "center", class: "mr-5" },
			],
			nameRules: [
				v => !!v || 'El campo es requerido'
			],
			pageCount: 1,
			page: 1,
			botica: {
				id:0,
				config_id:0,
				criterio_id: 0,
				nombre: "",
				codigo_local:"",
			},
			delete_id:0,
			text_delete:'¿Estás seguro de eliminar esta botica?',
			btn_delete:false,
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
			axios.get("/boticas/getInitialData?page=" + vue.page).then((res) => {
				vue.boticas = res.data.data;
				vue.pageCount = res.data.total_paginas;
				vue.modulos = res.data.modulos;
				vue.loading_table = false;
			});
		},
		cambiar_pagina(page) {
			let vue = this;
			if (this.b_botica != "") {
				vue.buscar_botica(page);
			}else{
				vue.getInitialData(page);
			}
		},
		abriDialogBotica(item,tipo) {
			let vue = this;
			if(tipo=='new'){
				this.criterios = [];
				vue.title_modal = 'Crear Botica';
				vue.botica = {
					id:0,
					config_id: 0,
					criterio_id: 0,
					nombre: '',
					codigo_local:'',
				};
			}else{
				this.botica.config_id = item.config_id;
				vue.title_modal = 'Editar Botica';
				this.get_criterios();
				vue.botica = {
					id:item.id,
					config_id: item.config_id,
					criterio_id: item.criterio_id,
					nombre: item.nombre,
					codigo_local:item.codigo_local,
				};
			}
			vue.dialog_botica = true;
		},
		save_or_edit(){
			if(this.$refs.form_botica.validate()){
				axios.post("/boticas/insert_or_edit",this.botica).then((res) => {
					if(res.data.error){
						this.text_error = res.data.mensaje;
						this.dismissCountDown = 10;
					}else{
						this.b_botica = ""
						this.getInitialData();
						this.dialog_botica = false;
					}
				});
			}
		},
		abrirDialogDelete (item) {
			this.dialogDelete = true;
			if(item.usuarios_count>0){
				this.text_delete = `No puedes eliminar esta botica, debido a que tiene ${item.usuarios_count} usuario(s) asociado(s).`;
				this.btn_delete = false;
			}else{
				this.text_delete = '¿Estás seguro de eliminar esta botica?';
				this.delete_id = item.id;
				this.btn_delete = true;
			}
		},
		closeDelete(){
			this.delete_id =0;
			this.dialogDelete = false;
		},
		delete_botica(){
			axios.delete('/boticas/delete_botica/'+this.delete_id).then((e)=>{
				this.dialogDelete = false;
				this.getInitialData();
			})
		},
		get_criterios(){
			axios.get('/boticas/get_criterios/'+this.botica.config_id).then((e)=>{
				this.criterios = e.data.criterios;
			})
		},
		buscar_botica(){
			this.loading_table = true;
			clearTimeout(this.debounce);
			this.debounce = setTimeout(() => {
				this.typing = null;
				if (this.b_botica != "") {
					axios.get("/boticas/buscar/"+this.b_botica+"?page=" + this.page).then((res) => {
						this.boticas = res.data.data;
						this.pageCount = res.data.total_paginas;
						this.loading_table = false;
					});
				} else {
					this.getInitialData();
					this.loading_table = false;
				}
			}, 1200);
		},
		countDownChanged(dismissCountDown) {
        	this.dismissCountDown = dismissCountDown
      	},
	},
};
</script>