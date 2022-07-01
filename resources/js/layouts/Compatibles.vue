<template>
	<div>
		<v-container fluid>
			<v-card>
				<v-row>
					<v-col cols="12" align="right">
						<v-btn
							class="ma-2"
							outlined
							color="indigo"
							@click="descargar_reporte()"
						>
							Descargar Reporte
							<v-icon class="ml-2">mdi-download</v-icon>
						</v-btn>
					</v-col>
				</v-row>
				<!-- PRIMERA LISTA -->
				<v-row>
					<v-col cols="12" md="12" sm="12">
						<v-card>
							<v-card-title>
								<v-row>
									<v-col cols="12" md="4" sm="12">
										<v-select
											:items="modulos"
											item-text="etapa"
											item-value="etapa"
											label="Módulo"
											dense
											outlined
											hide-details
											v-model="slt_mod_lista"
										></v-select>
									</v-col>
									<v-col cols="12" md="4" sm="12">
										<v-text-field
											dense
											outlined
											hide-details
											v-model="b_escuela_l1"
											label="Buscar por escuela"
										>
										</v-text-field>
									</v-col>
									<v-col cols="12" md="4" sm="12">
										<v-text-field
											dense
											outlined
											hide-details
											v-model="b_curso_l1"
											label="Buscar por curso"
										>
										</v-text-field>
									</v-col>
								</v-row>
							</v-card-title>
							<v-card-text>
								<v-data-table
									:headers="h_lista1"
									:items="filtroLista"
									item-key="id"
									class="elevation-1"
									:items-per-page="4"
									v-model="selectedRows"
								>
									<template v-slot:item="{ item }">
										<tr :class="selectedRows.indexOf(item.id) > -1 ? 'cyan' : ''">
											<td v-text="item.config.etapa"></td>
											<td v-text="item.categoria.nombre"></td>
											<td v-text="item.id + ' - ' + item.nombre"></td>
											<td>
												<v-chip
													small
													class="mx-1 my-1"
													pill
													v-for="curricula in item.curricula"
													:key="curricula.id"
													v-text="curricula.carrera.nombre + ' - ' + curricula.ciclo.nombre"
												>
												</v-chip>
											</td>
											<td>
												<v-badge bordered color="pink" :content="item.temas.length" overlap
													><v-btn
														@click="
															buscar_coincidencia(item.temas, item.id, item.config_id),
																rowClicked(item)
														"
														color="blue"
														>Ver</v-btn
													></v-badge
												>
											</td>
										</tr>
									</template>
								</v-data-table>
							</v-card-text>
						</v-card>
					</v-col>
				</v-row>
				<v-row v-if="click_save == 1">
					<!-- SEGUNDA LISTA -->
					<v-col cols="12" md="7" sm="7">
						<v-card>
							<v-card-title>
								<v-row>
									<v-col cols="12" md="4" sm="12">
										<v-select
											:items="modulos"
											item-text="etapa"
											item-value="etapa"
											label="Módulo"
											dense
											outlined
											hide-details
											v-model="slt_mod_lista2"
										></v-select>
									</v-col>
									<v-col cols="12" md="4" sm="12">
										<v-text-field
											dense
											outlined
											hide-details
											v-model="b_escuela_l2"
											label="Buscar por escuela"
										>
										</v-text-field>
									</v-col>
									<v-col cols="12" md="4" sm="12">
										<v-text-field
											dense
											outlined
											hide-details
											v-model="b_curso_l2"
											label="Buscar por curso"
										>
										</v-text-field>
									</v-col>
								</v-row>
								<v-row>
									<v-col cols="12" md="8" sm="12">
										<v-text-field
											dense
											outlined
											hide-details
											v-model="b_tema_l2"
											label="Buscar por tema"
											@input="buscar_tema"
										>
										</v-text-field>
									</v-col>
									<v-col cols="12" md="4" sm="12">
										<v-btn v-if="ver_compa == 0" @click="compatibles_lista()"
											>Ver compatibles</v-btn
										>
										<v-btn v-else @click="ver_todo()">Ver todos</v-btn>
									</v-col>
								</v-row>
							</v-card-title>
							<v-card-text>
								<v-data-table
									:headers="h_lista2"
									:items="filtroCoinci"
									item-key="id"
									class="elevation-1"
									:items-per-page="4"
									:expanded.sync="expanded"
									show-expand
									:loading="load_table_2"
								>
									<template v-slot:expanded-item="{ item }">
										<td colspan="5">
											<draggable
												:list="item.temas"
												:group="{ name: 'posteos', pull: 'clone', put: false, animation: 120 }"
											>
												<transition-group name="fade" tag="div">
													<v-chip
														small
														class="mx-1 my-1"
														pill
														v-for="tema in item.temas"
														:key="tema.id"
														v-text="tema.id + ' - ' + tema.nombre"
														:style="[
															tema.selec
																? {
																		background: 'chartreuse',
																		'border-radius': '12px',
																		cursor: 'move',
																  }
																: { background: 'yellow', 'border-radius': '12px', cursor: 'move' },
														]"
													>
													</v-chip>
												</transition-group>
											</draggable>
										</td>
									</template>
									<template v-slot:item.curricula="{ item }">
										<v-chip
											small
											class="mx-1 my-1"
											pill
											v-for="curricula in item.curricula"
											:key="curricula.id"
											v-text="curricula.carrera.nombre + ' - ' + curricula.ciclo.nombre"
										>
										</v-chip>
									</template>
									<template v-slot:item.nombre="{ item }">
										<td v-text="item.id + ' - ' + item.nombre"></td>
									</template>
								</v-data-table>
							</v-card-text>
						</v-card>
					</v-col>
					<!-- LISTA DE RELACION -->
					<v-col cols="12" md="5" sm="5">
						<v-card >
							<v-card-title>Relaciones</v-card-title>
							<div style="overflow: auto; max-height: 366px">
								<v-card
									v-for="(group, idx_grupo) in dropGroups"
									:key="group.name"
									style="border-left: 4px solid #ba68c8"
									class="mx-2 mb-4 pt-0"
								>
									<v-card-text class="py-0">
										<v-card-subtitle
											v-text="'tema: '+group.tema_id+'-'+group.name"
											style="padding-left: 0; padding-top: 1rem"
										></v-card-subtitle>
										<draggable
											:list="group.children"
											group="posteos"
											@change="cloneItem"
											class="list-group"
										>
											<transition-group
												name="fade"
												tag="div"
												class="mb-2 pt-3"
												style="
													display: grid;
													background: #e5e5e5;
													justify-content: space-between;
													grid-template-columns: repeat(2, 1fr);
													border-radius: 10px;
												"
											>
												<p
													v-for="(element, idx) in group.children"
													:key="element.id"
													class="text-center rounded-lg mx-2 px-1"
													style="background: chartreuse; font-size: 0.8125rem"
												>
													{{ element.id + " - " + element.nombre }}
													<i
														class="fa fa-times close"
														style="font-size: large"
														@click="removeAt(idx_grupo, idx, element)"
													></i>
												</p>
											</transition-group>
										</draggable>
									</v-card-text>
								</v-card>
							</div>
							<v-card-actions v-if="dropGroups.length > 0">
								<v-spacer></v-spacer>
								<v-btn color="green darken-1" text @click="guardar_compatibles()"> Guardar </v-btn>
							</v-card-actions>
						</v-card>
					</v-col>
				</v-row>
			</v-card>
		</v-container>
		<!-- OVERLAY -->
		<v-overlay :value="overlay">
			<div style="display: flex !important; align-items: flex-end !important">
				<p class="text-h4 mr-4">Espere...</p>
				<v-progress-circular indeterminate size="64"></v-progress-circular>
			</div>
		</v-overlay>
	</div>
</template>
<script>
import draggable from "vuedraggable";
export default {
	components: {
		draggable,
	},
	data() {
		return {
			debounce: null,
			h_lista1: [
				{ text: "MODULO", value: "config.etapa", align: "center" },
				{ text: "ESCUELA", value: "categoria.nombre", align: "center" },
				{ text: "CURSO", value: "nombre", align: "center" },
				{ text: "CARRERAS - CICLOS", value: "curricula", align: "center" },
				{ text: "COMPATIBLES", value: "accion", align: "center" },
			],
			h_lista2: [
				{ text: "MODULO", value: "config.etapa", align: "center" },
				{ text: "ESCUELA", value: "categoria.nombre", align: "center" },
				{ text: "CURSO", value: "nombre", align: "center" },
				{ text: "CARRERAS - CICLOS", value: "curricula", align: "center" },
			],
			click_save: 0,
			selectedRows: [],
			curs_tot: [],
			cur_l2: [],
			cursos_lBK:[],
			modulos: [],
			carreras: [],
			coincidencias: [],
			dropGroups: [],
			slt_mod_lista: "Todos",
			b_escuela_l1: "",
			b_curso_l1: "",
			slt_mod_lista2: "Todos",
			b_escuela_l2: "",
			b_curso_l2: "",
			b_tema_l2: "",
			esc_filtro: [],
			overlay: true,
			d_carreras: false,
			expanded: [],
			ver_compa: 0,
			c_f : [],
			curso_id:null,
			load_table_2:false,
		};
	},
	mounted() {
		this.get_cursos();
	},
	computed: {
		filtroLista() {
			let vue = this;
			let s_mdl = vue.slt_mod_lista.toLowerCase();
			let data_r = [];
			if (vue.slt_mod_lista != "Todos") {
				data_r = vue.curs_tot.filter((item) => {
					return item.config.etapa.toLowerCase().indexOf(s_mdl) != -1;
				});
			} else {
				data_r = vue.curs_tot;
			}
			if (vue.b_escuela_l1) {
				let text = vue.b_escuela_l1.toLowerCase();
				data_r = data_r.filter((item) => {
					return item.categoria.nombre.toLowerCase().indexOf(text) != -1;
				});
			}
			if (vue.b_curso_l1) {
				let text = vue.b_curso_l1.toLowerCase();
				data_r = data_r.filter((item) => {
					return item.nombre.toLowerCase().indexOf(text) != -1;
				});
			}

			return data_r;
		},
		filtroCoinci() {
			let vue = this;
			let s_mdl = vue.slt_mod_lista2.toLowerCase();
			let data_r = [];
			if (vue.slt_mod_lista2 != "Todos") {
				data_r = vue.cur_l2.filter((item) => {
					return item.config.etapa.toLowerCase().indexOf(s_mdl) != -1;
				});
			} else {
				data_r = vue.cur_l2;
			}
			
			if (vue.b_escuela_l2) {
				let text = vue.b_escuela_l2.toLowerCase();
				data_r = data_r.filter((item) => {
					return item.categoria.nombre.toLowerCase().indexOf(text) != -1;
				});
			}
			if (vue.b_curso_l2) {
				let text = vue.b_curso_l2.toLowerCase();
				data_r = data_r.filter((item) => {
					return item.nombre.toLowerCase().indexOf(text) != -1;
				});
			}
			if(this.curso_id){
				data_r = data_r.filter((item) => {
					return item.id != this.curso_id;
				});
			}
			return data_r;
		},
	},
	methods: {
		async get_cursos() {
			await axios.get("/get_cursos_compatibles").then((res) => {
				let data = res.data.data;
				this.curs_tot = data.cursos;
				this.cur_l2 = data.cursos;
				this.cursos_lBK = data.cursos;
				this.modulos = data.modulos;
				this.modulos.unshift({
					id: 0,
					etapa: "Todos",
				});
				this.overlay = false;
			});
			return 1;
		},
		async buscar_coincidencia(temas, curso_id, config_id) {
			//LIMPIAR LAS BUSQUEDAS
			this.slt_mod_lista2 = 'Todos';
			this.b_escuela_l2 = '';
			this.b_curso_l2 = '';
			this.b_tema_l2 = '';
			this.overlay = true;
			this.ver_compa = 0;
			return this.get_cursos().then((e) => {
				return this.get_coincidencias(temas, curso_id, config_id);
			});
		},
		get_coincidencias(temas, curso_id, config_id) {
			let data = {
				curso_id: curso_id,
				temas: temas,
			};
			this.dropGroups = [];
			this.coincidencias = [];
			axios.post("/get_coincidencias", data).then((res) => {
				let data = res.data.data;
				let relaciones = data.relaciones;
				if (data) {
					temas.forEach((e) => {
						let s_rel = [];
						if (relaciones.length > 0) {
							let e_find = relaciones.filter((element) => element.id_compatible == e.id);
							e_find.forEach((element) => {
								this.change_etd_tema(element, "add");
								s_rel.push(element);
							});
						}
						this.dropGroups.push({
							tema_id: e.id,
							curso_id: e.curso_id,
							config_id: config_id,
							name: e.nombre,
							children: s_rel,
							ids_eliminados: [],
						});
					});
                }
				this.overlay = false;
				this.click_save = 1;
			});
			this.curso_id = curso_id;
			return 1;
		},
		cloneItem({ added }) {
			if (added) {
				let item = added.element;
				this.change_etd_tema(item, "add");
			}
		},
		removeAt(idx_grupo, idx, element) {
			this.change_etd_tema(element, "remove");
			this.dropGroups[idx_grupo].ids_eliminados.push(element.id);
			this.dropGroups[idx_grupo].children.splice(idx, 1);
		},
		change_etd_tema(item, tipo) {
			let idx_curso = this.cur_l2.findIndex((e) => {
				return e.id == item.curso_id;
			});
			let idx_tema = this.cur_l2[idx_curso].temas.findIndex((e) => {
				return e.id == item.id;
			});
			let estado = tipo == "add" ? 1 : 0;
			if (idx_tema != -1) {
				this.cur_l2[idx_curso].temas[idx_tema].selec = estado;
			}
		},
		guardar_compatibles() {
			let data = {
				nuevos_compatibles: this.dropGroups,
			};
			this.overlay = true;
			axios.post("/guardar_compatibles", data).then((res) => {
				this.overlay = false;
				alert("Guardado");
			});
		},
		compatibles_lista() {
			this.ver_compa = 1;
			axios.post("/compatibles_lista", this.dropGroups).then((res) => {
				let compa = res.data.data;
				compa.forEach((cur) => {
					cur.temas.forEach((tema) => {
						this.dropGroups.forEach((dr) => {
							dr.children.forEach((ch) => {
								if (ch.id == tema.id) {
									tema.selec = 1;
								}
							});
						});
					});
				});
				this.cur_l2 = compa;
			});
		},
		ver_todo() {
			//LIMPIAR LAS BUSQUEDAS
			this.slt_mod_lista2 = 'Todos';
			this.b_escuela_l2 = '';
			this.b_curso_l2 = '';
			this.b_tema_l2 = '';
			this.ver_compa = 0;
			this.cur_l2 = this.cursos_lBK;
		},
		buscar_tema(event) {
			this.load_table_2 = true;
			clearTimeout(this.debounce);
			this.debounce = setTimeout(() => {
				this.typing = null;
				if (this.b_tema_l2 != "") {
					let data = {
						s_modulo: this.slt_mod_lista2,
						s_tema: this.b_tema_l2,
						s_escuela: this.b_escuela_l2,
						b_curso_l2: this.b_curso_l2,
					};
					axios.post("/search_tema", data).then((res) => {
						this.cur_l2 = res.data.data;
						this.cur_l2.forEach(cur => {
							cur.temas.forEach(tema => {
								this.dropGroups.forEach((dr) => {
									dr.children.forEach((ch) => {
										if (ch.id == tema.id) {
											this.change_etd_tema(tema, "add");
											tema.selec = 1;
										}
									});
								});
							});
						});
						this.load_table_2 = false;
					});
				} else {
					this.ver_todo();
					this.load_table_2 = false;
				}
			}, 1200);
		},
		rowClicked(row) {
			this.toggleSelection(row.id);
		},
		toggleSelection(keyID) {
			this.selectedRows = [];
			if (this.selectedRows.includes(keyID)) {
				this.selectedRows = this.selectedRows.filter((selectedKeyID) => selectedKeyID !== keyID);
			} else {
				this.selectedRows.push(keyID);
			}
		},
		descargar_reporte(){
        	window.open('/compatible/reporte').attr("href");
		}
	},
};
</script>