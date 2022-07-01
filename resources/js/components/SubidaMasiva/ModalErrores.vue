<template>
    <v-row justify="center">
	    <v-btn depressed color="error" :disabled="!(q_error>0)" @click="get_errores()" v-text="'Ver errores en la plantilla ('+q_error+')'"></v-btn>
        <v-dialog
			v-model="dialog_err_masiva"
			fullscreen
			hide-overlay
			transition="dialog-bottom-transition"
			scrollable
			style="z-index:503"
		>
			<v-card class="p-3">
                <v-overlay :value="overlay">
                    <div style="display: flex !important; align-items: flex-end !important">
                        <p class="text-h4 mr-4">Espere...</p>
                        <v-progress-circular indeterminate size="64"></v-progress-circular>
                    </div>
			    </v-overlay>
				<v-toolbar flat dark color="primary">
					<v-btn icon dark @click="dialog_err_masiva = false">
						<v-icon>mdi-close</v-icon>
					</v-btn>
					<v-toolbar-title>Errores encontrados en la plantilla</v-toolbar-title>
					<v-spacer></v-spacer>
					<v-row class="mt-2">
						<v-col cols="12" md="3">
							<v-select v-model="s_multi" @change="get_errores()" v-show="multi_page.length>0" :items="multi_page" item-text="value" item-value="value" ></v-select>
						</v-col>
						<v-col cols="12" md="9">
							<v-text-field
								v-model="search"
								append-icon="mdi-magnify"
								label="Busqueda"
								single-line
								hide-details
							></v-text-field>
						</v-col>
					</v-row>
					<v-spacer></v-spacer>
					<v-toolbar-items>
						<!-- <v-btn dark text @click="descargar_reporte()"> Reporte </v-btn> -->
						<v-btn dark text @click="dialog_delete_varios = true"> Eliminar </v-btn>
						<v-btn dark text @click="guardar_data()"> Guardar </v-btn>
					</v-toolbar-items>
				</v-toolbar>
				<div style="height: 100vh">
					<v-alert :type="tipo_alert" v-html="message_alert" v-model="alert"> </v-alert>
					<v-data-table
						v-model="selected"
						:headers="headers"
						:items="errors"
						class="elevation-1"
						item-key="id"
						show-select
						:expanded.sync="expanded"
						show-expand
						:search="search"
						:fixed-header="fixed_header"
					>
						<template v-slot:[`item.actions`]="{ item }">
							<v-icon small @click="open_dialog_delete(item.id)"> mdi-delete </v-icon>
						</template>
                        <template v-slot:expanded-item="{ item }">
                            <td></td>
                            <td></td>
                            <td v-for="error in item.error" :key="error.id_e">
								<div v-if="error.error" >
									<p v-text="error.data.mensajes" class="mr-5 mt-2" style="color:red;font-weight: bold;text-align: center;"></p>
									<div v-if="error.tipo=='input'">
										<v-text-field  
											name="quantity"
											type="text"
											@input="change_model($event,item.id,error.atributo)"
											v-model="error.value_model" 
										>
										</v-text-field>
									</div>
                                    <v-select v-if="error.tipo=='select'" v-model="error.value_model" :items="error.data_extra" :item-value="error.item_id" :item-text="error.item_text" return-object @change='change_select($event,error.change_select,item.id,error.atributo,error.atr_change,item.err_data,error.table_info)'></v-select>
                                </div>
                                <div v-else style="text-align: center;">-</div>
                            </td>
						</template>
					</v-data-table>
				</div>
			</v-card>
		</v-dialog>
		<!-- DIALOG PARA ELIMINAR -->
		<v-dialog v-model="dialog_delete" max-width="290">
			<v-card>
				<v-card-title class="headline"> Eliminar registro </v-card-title>

				<v-card-text> Una vez eliminado el registro no se podra recuperar </v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn color="green darken-1" text @click="dialog_delete = false"> Cancelar </v-btn>

					<v-btn color="green darken-1" text @click="eliminar_error()">
						Aceptar
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
		<!-- DIALOG PARA ELIMINAR -->
		<v-dialog v-model="dialog_delete_varios" max-width="290">
			<v-card>
				<v-card-title class="headline"> Eliminar registro </v-card-title>
				<v-card-text> Una vez eliminado estos registros no se podran recuperar </v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn color="green darken-1" text @click="dialog_delete_varios = false">
						Cancelar
					</v-btn>

					<v-btn color="green darken-1" text @click="eliminar_errores()">
						Aceptar
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
    </v-row>
</template>
<script>
export default {
	props:['q_error','tipo'],
	data() {
		return {
            errors:[],
            headers:[],
            multi_page:[],
            selected:[],
			fixed_header:true,
            dialog_err_masiva:false,
			dialog_delete_varios:false,
			dialog_delete:false,
			selected: [],
			expanded: [],
			search: "",
            tipo_alert: "warning",
			message_alert: "",
			alert: false,
			overlay: false,
			id_delete:null,
			s_multi:'cursos',
        }
    },
    methods:{
        async get_errores(){
			let tipo = this.tipo;
			this.overlay = true;
			if(this.multi_page.length>0) tipo = this.s_multi; 
            await axios.get("/masivo/errores/get_errores/"+tipo).then((res) => {
                this.headers = res.data.headers;
				this.errors = res.data.errors;
				this.multi_page = res.data.multi_page;
                this.selected = [];
				this.overlay = false;
				this.dialog_err_masiva=true;
            });
        },
		async change_select(e,change,id,atributo,atr_change,err_data,table_info){
			let index = this.errors.findIndex(x => x.id === id);
			if(index!=null){
				this.errors[index].err_data[atributo] = e.id;
				let data ={
					'tipo' : change,
					'err_data':err_data,
					'table_info':table_info
				};
				if(atr_change){
					this.errors[index].err_data[atr_change.val] = e[atr_change.atr_table];
				}
				if(data.tipo){
					await axios.post("/masivo/errores/get_change",data).then((re) => {
						let rptas=re.data;
						if(rptas.length>0){
							rptas.forEach(element => {
								let index_error = this.errors[index].error.findIndex(x => x.atributo === element.atributo);
								if(element.error){
									this.errors[index].error[index_error].error=true;
									this.errors[index].error[index_error].data_extra=element.data_extra;
									this.errors[index].error[index_error].item_text=element.item_text;
									this.errors[index].error[index_error].data.mensajes=element.mensajes;
								}else{
									this.errors[index].error[index_error].error=false;
									this.errors[index].error[index_error].item_text='';
									this.errors[index].error[index_error].data.mensajes=[];
									this.errors[index].err_data[element.atributo] = element.atributo_id;
								}
							});
						}
					});
				}
			}else{
				alert('error');
			}
		},
		change_model(e,id,atributo){
			let index = this.errors.findIndex(x => x.id === id);
			if(index!=null){
				this.errors[index].err_data[atributo] = e;
			}else{
				alert('error');
			}
		},
		async eliminar_errores() {
			let vue = this;
			vue.reset_alert();
			vue.dialog_delete_varios = false;
			vue.overlay = true;
			let err_selected = vue.selected;
			let data = {
				items: err_selected,
			};
			if (err_selected.length > 0) {
				await axios.post("/masivo/eliminar_errores", data).then((res) => {
                    vue.overlay = false;
					vue.show_alert("success", "Los registros se han eliminado correctamente");
                    vue.get_errores();
				});
			}
		},
		async eliminar_error() {
			this.reset_alert();
			this.dialog_delete = false;
			console.log(this.dialog_delete);
			this.overlay = true;
			await axios.delete("/masivo/eliminar_error/" + this.id_delete).then((res) => {
				this.show_alert("success", "El registro se ha eliminado correctamente");
                this.get_errores();
			    this.overlay = false;
			});
		},
		show_alert(tipo, message) {
			this.tipo_alert = tipo;
			this.message_alert = message;
			this.alert = true;
		},
		reset_alert() {
			this.alert = false;
			this.message_alert = "";
			this.tipo_alert = "warning";
		},
		open_dialog_delete(id) {
			this.id_delete = id;
			this.dialog_delete = true;
		},
		async guardar_data() {
			let err_selected = this.selected;
			if (err_selected.length > 0) {
				this.reset_alert();
				let prueba = true;
				err_selected.forEach((err_item) => {
					err_item.error.forEach(e => {
						if(!e.could_be_empty){
							let req = e.rules.findIndex(e => e =='required');
							if(req!=-1 && e.atributo !='no' && e.atributo != ''){
								console.log(err_item.err_data[e.atributo],e.atributo);
								if (this.isEligible(err_item.err_data[e.atributo])) prueba = false;
							}else{
								if(e.extra_rules && e.atributo !='no'){
									if(e.extra_rules.rule=='required_if_dif' && err_item.err_data[e.extra_rules.atr] != e.extra_rules.val){
										if (this.isEligible(err_item.err_data[e.atributo])) prueba = false;
									}
								}
							}
						}
					});
				});
				if (prueba) {
					this.overlay = true;
					let tipo = this.tipo;
					if(this.multi_page.length>0) tipo = this.s_multi; 
					let data = {
						items: err_selected,
						tipo:tipo,
					};
					await axios
						.post("/masivo/errores/guardar_data", data)
						.then((res) => {
							let success = res.data;
							success.success.forEach((er_id)=>{
								let idx = this.errors.findIndex(x => x.id === er_id);
								this.errors.splice(idx, 1)
							})
							this.show_alert("success", "Se ha guardado la data correctamente");
							
						})
						.catch((e) => {
							let error = e.response.data;
                            if(error.errors){
								error.errors.forEach((er)=>{
									er.msg.forEach(element => {
										let idx = this.errors.findIndex(x => x.id === element.id);
										let idx_2=this.errors[idx].error.findIndex(x => x.id_e === element.id_e);
										this.errors[idx].error[idx_2].data.mensajes = element.mensaje;
									});
								});
							}
							if(error.success){
								error.success.forEach((er_id)=>{
									let idx = this.errors.findIndex(x => x.id === er_id);
									this.errors.splice(idx, 1)
								})
							}
						});
					this.overlay = false;
					this.selected = [];
				} else {
					this.show_alert("warning", "Falta corregir la data de algunos items seleccionados.");
				}
			}else{
				this.show_alert("warning", "No ha seleccionado ningun usuario");
			}
		},
		isEligible(value) {
			if (value == false || value == null || value == 0 || value == "") {
				return true;
			}
			return false;
		},
		descargar_reporte(){
			window.open('/masivo/reporte_errores/'+this.tipo).attr("href");
		}
    }
}
</script>