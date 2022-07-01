<template>
	<v-container>
		<v-card>
			<v-data-table
				:items="cargos"
				:headers="headers"
				hide-default-footer
				class="elevation-0"
				:items-per-page="items_per_page"
				:loading="loading_table"
			>
				<template v-slot:top>
					<v-toolbar flat>
						<v-toolbar-title>Cargos</v-toolbar-title>
						<v-divider class="mx-4" inset vertical></v-divider>
						<v-text-field
							dense
							outlined
							hide-details
							v-model="b_cargo"
							label="Buscar cargo"
							@input="page=1;buscar_cargo()"
						>
						</v-text-field>
						<v-spacer></v-spacer>
						<v-btn @click="abriDialogBotica(cargo,'new')"> <v-icon>mdi-plus</v-icon> Crear</v-btn>
					</v-toolbar>
				</template>
				<template v-slot:[`item.editar`] ="{ item }">
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
		<v-dialog v-model="dialog_cargo"  @click:outside="dialog_cargo=false" max-width="500px" lazy-validation>
			<v-form  ref="form_cargo" v-model="form_cargo" lazy-validation>
				<v-card>
					<v-card-title v-text="title_modal"> Crear Cargo </v-card-title>
					<v-card-text>
							<v-row>
								<v-col cols="12" md="12" lg="12">
									<v-text-field :rules="nameRules" required v-model="cargo.nombre" label="Nombre"></v-text-field>
								</v-col>
							</v-row>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn @click="dialog_cargo = false">Cancelar</v-btn>
						<v-btn :disabled="!form_cargo" class="ml-3" @click="save_or_edit()">Guardar</v-btn>
					</v-card-actions>
				</v-card>
			</v-form>
		</v-dialog>
		<v-dialog v-model="dialogDelete" @click:outside="dialogDelete=false" max-width="500px">
          <v-card>
            <v-card-title class="headline">¿Estás seguro de eliminar esta cargo?</v-card-title>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="closeDelete">Cancelar</v-btn>
              <v-btn color="blue darken-1" text @click="delete_cargo">Aceptar</v-btn>
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
			b_cargo:'',
            title_modal:'Crear Cargo',
			dialog_cargo: false,
			dialogDelete:false,
			loading_table: true,
			form_cargo:true,
			cargos: [],
			items_per_page: 15,
			headers: [
				{ text: "Nombre Cargo", value: "nombre" },
				{ text: "Opciones", value: "editar", align: "center", class: "mr-5" },
			],
			nameRules: [
				v => !!v || 'El campo es requerido'
			],
			pageCount: 1,
			page: 1,
			cargo: {
				id:0,
				nombre: "",
			},
			delete_id:0,
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
			axios.get("/cargos/getInitialData?page=" + vue.page).then((res) => {
				vue.cargos = res.data.data;
				vue.pageCount = res.data.total_paginas;
				vue.loading_table = false;
			});
		},
		cambiar_pagina(page) {
			let vue = this;
			if (this.b_cargo != "") {
				vue.buscar_botica(page);
			}else{
				vue.getInitialData(page);
			}
		},
		abriDialogBotica(item,tipo) {
			let vue = this;
			if(tipo=='new'){
                vue.title_modal = 'Crear cargo';
				vue.cargo = {
					id:0,
					nombre: '',
				};
			}else{
                vue.title_modal = 'Editar cargo';
				vue.cargo = {
					id:item.id,
					nombre: item.nombre,
				};
			}
			vue.dialog_cargo = true;
		},
		save_or_edit(){
			if(this.$refs.form_cargo.validate()){
				axios.post("/cargos/insert_or_edit",this.cargo).then((res) => {
					this.dialog_cargo = false;
					this.getInitialData();
				});
			}
		},
		abrirDialogDelete (item) {
			this.delete_id = item.id;
			this.dialogDelete = true;
		},
		closeDelete(){
			this.delete_id =0;
			this.dialogDelete = false;
		},
		delete_cargo(){
			axios.delete('/cargos/delete_cargo/'+this.delete_id).then((e)=>{
				this.dialogDelete = false;
				this.getInitialData();
			})
		},
		buscar_cargo(){
			this.loading_table = true;
			clearTimeout(this.debounce);
			this.debounce = setTimeout(() => {
				this.typing = null;
				if (this.b_cargo != "") {
					axios.get("/cargos/buscar/"+this.b_cargo).then((res) => {
						this.cargos = res.data.data;
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