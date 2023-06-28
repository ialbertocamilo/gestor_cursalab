<template>
	<main>
			<!-- el buscador por defecto  -->
		<div class="form-row">
			<div class="form-group col-6" v-show="searchable">
				<DefaultInput class="mt-7" label="Buscar campaña" v-model="currentSearch" :label="labelSearch" 
											maxlength="70" />
			</div>
			<!-- si deseas agregar mas componentes o filtros a esta tabla -->
			<slot name="options"></slot>
		</div>
		<div>
			<v-data-table class="m-0 elevation-1"
										 hide-default-footer 
										:headers="currHeaders" :items="currData" :loading="currLoader"
										:items-per-page="currPageItems"
										loading-text="Buscando datos ..."
										:no-data-text="noDataText" >
				
				<!-- referencia a items permitidos  -->
				<template v-for="currkey of currentItemsKeys"
								v-slot:[`item.${currkey}`]="{ item }">
					<slot :name="currkey" :item="item"></slot>
				</template>

			</v-data-table>

			<!-- paginacion dinamica -->
			<div class="py-7">
				<div class="row justify-content-end">
					<div class="d-flex">
						<div>
							<v-select dense hide-details="auto" attach :items="[5, 10, 15, 20, 25, 30]" 
												v-model="currPageItems" :menu-props="{ top: true, offsetY: true }"
	                      class="table-default-items-per-page">
	                <template v-slot:prepend>
	                  <div class="d-flex align-items-center">
	                    <small v-text="'Filas por página'"/>
	                  </div>
	                </template>
	            </v-select>
						</div>

						<div>
							<v-icon v-text="'mdi-chevron-left'" @click.prevent="setPagAndGetData(stcPage - 1, true)" :disabled="stcPrev">
							</v-icon>
							<v-btn class="mx-3" small elevation="2" v-text="stcPage"></v-btn>
							<v-icon v-text="'mdi-chevron-right'" @click.prevent="setPagAndGetData(stcPage + 1, false)" :disabled="stcNext">
							</v-icon>
						</div>
					</div>
				</div>
			</div>

		</div>
	</main>
</template>

<script>
	import { getStaticParams } from '../utils/UtlComponents.js';

	let Fake_Load; //scope local

	export default {
		name:'vue-table',
		props: {
			// props principales y requeridos
			uri: { type:String, require: true }, // la direccion de tu api
			cols: { type:Object, require: true }, // cols:{ headers: Array , columns: Array}
			attr: { type:String, default:'search' }, // indica el campo que quieres buscar
			reload: { type:Boolean, require: true },
			
			// props opcionales
			autoLoad: { type:Boolean, default: true },
			emitData:{ type:Boolean, default: false },
			searchable: { type:Boolean, default: true },
			pagesItems: { type:Number, default: 5},
			options: { type: [ Object, Boolean ], default: false },
			labelSearch:{ type:String, default: 'Buscar datos.'},
			noDataText:{ type:String, default:'Ooops no se encontraron resultados.'}
		},
		data(){
			return {
				// para datos
				currHeaders: this.cols.headers,
				currData: [],
				currLoader: false,

				// buscador
				currentSearch: '',

				// paginacion
				currPage: 1,
				stcPage: 1,
				stcPrev: false,
				stcNext: false,

				currPageItems: this.pagesItems
			}
		}, 
		computed:{
			currentItemsKeys() {
				const { headers, columns } = this.cols;
				let currentData = [];

				headers.forEach( ({value}) => columns.includes(value) && currentData.push(value) )
				return currentData;
			}
		},
		watch:{
			// props watchers
			options:{
				handler() {
					this.prevLoadRequest();
				},
				deep: true,
		    // immediate: true - ejecuta al cargar el componente
			},
			reload:{
				handler() {
					
					this.currData = [];
					this.getDataByUri();
				}
			},
			// check if change uri
			uri:{
				handler(){
					this.currPage = 1;
					this.currData = [];
					this.getDataByUri();
				}
			},
			// local watchers
			currPageItems() {
				this.getDataByUri();	
			},
			currentSearch() {
				this.prevLoadRequest();	
			}
		},
		methods:{
			prevLoadRequest() {
				const vm = this;
				vm.currPage = 1;
				vm.currData = []; //vacio currData
				vm.currLoader = true;

				if(Fake_Load) clearTimeout(Fake_Load);
	 				 Fake_Load = setTimeout(() => vm.getDataByUri(), 1500); //time delay
			},
			setPagAndGetData(page, flag) {
				const vm = this;
				vm.currPage = page;

				if(flag) vm.stcPrev = true;
				else vm.stcNext = true;

				vm.getDataByUri();
			},
			async getDataByUri() {
				const vm = this;
				vm.currLoader = true;

				const currentSearch = (vm.currentSearch === null) ? '' : vm.currentSearch;
				const objectParams = (vm.options === false) ? { [vm.attr] : currentSearch }
																										: { [vm.attr] : currentSearch, ...vm.options };

				const INMUTABLE = `${vm.uri}?page=${vm.currPage}&perpage=${vm.currPageItems}`; // la pagina y cuantos por pagina son datos inmutables
				const ST_PARAMS =  getStaticParams(objectParams);

				const response = await vm.$http.get(INMUTABLE + ST_PARAMS);
				vm.currLoader = false; //loader

				const { data, ...paginate } = response.data.data;
				
				vm.currData = data;
				vm.makePagination(paginate); //para botones de paginacion prev - next

				if(vm.emitData) {
					vm.$emit('emitdata', data)
				}

			},
			makePagination(data) {
				const vm = this;
				const { current_page, next_page_url, prev_page_url } = data;
				
				vm.stcPage = current_page;
				// verificamos paginas 
				vm.stcPrev = prev_page_url === null;
				vm.stcNext = next_page_url === null;
			}
		},
		mounted() {
			const vm = this;
			vm.autoLoad && vm.getDataByUri(); //auto init
		}
	}
</script>