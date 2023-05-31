<template>
	<div class="card border border-primary p-2 rounded m-auto">
		<div class="row">
			<div class="col-4 text-center">
				<div class="text-primary d-flex flex-column">
					<span class="f-lg fas" :class="currentFileCard.info.icon"></span>
					<span class="text-uppercase" 
								v-text="currentFileCard.info.ext === 'html' ? 'SCORM' : currentFileCard.info.ext"></span>
				</div>
			</div>
			<div class="col-8 align-self-center">
				<span class="d-inline" v-text="currentFileCard.name"></span>
			</div>
		</div>
	</div>
</template>

<script>
	import { getParseByUrl } from '../utils/UtlPathUrls.js';
	import { getFileExtension } from '../utils/UtlGeneral.js';
	import { checkType } from '../utils/UtlValidators.js';
	const { isType } = checkType;
	
	function getNameByPathName(pathname, prefix) {
		const stcNamePath = pathname.split(prefix);
		return stcNamePath[stcNamePath.length - 1];
	}

	export default {
		name: 'vue-card-file',
		props: { 
			data: { type:[ Object, String, File ] } 
		},
		computed: {
			currentFileCard() {
				const { data, mixin_extensiones } = this;
				const isPath = isType(data, 'string');
				// const isCardeable = (ext) => !(mixin_extensiones.image).includes(ext);
				
				let name, info;
				if(isPath) {
					const { pathname } = getParseByUrl(data);

					info = getFileExtension(data);
					name = (info.ext === 'html') ? 
									getNameByPathName(pathname, 'scorm') : 
									getNameByPathName(pathname, '/');
				} else {
	  		  name = data.name;
	  		  info = getFileExtension(data.name);
				} 
				
				return { name, info };

			}
		}
	}
</script>
