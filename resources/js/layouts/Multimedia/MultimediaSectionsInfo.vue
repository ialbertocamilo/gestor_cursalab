<template>
	<v-col cols="12" class="py-0" v-if="resource && resource.length > 0">
		<v-row class="text-center mx-5">
			<!-- <v-col cols="12" class="multimedia-label py-0" v-text="label"/> -->
		    <v-col cols="12" class="py-0">
		        <ul class="mb-0 pl-0 list-style-none pb-0">
		            <li v-for="res of resource">
		                <!-- <span v-text="res.name"></span> -->
		                <span v-if="Array.isArray(res.url)">
		                	<span
		                		v-for="url of res.url"
		                        :key="url"
		                        class="text-overflow-ellipsis"
		                	>
		                		
			                	<label class="multimedia-label my-0 mr-2" v-text="label" />
			                	<a href="javascript:;" @click="openInOtherTab(url)">
			                		{{ res.name }}
			                	</a>
		                	</span>
		                    <!-- <v-btn 
		                     	class="default-modal-action-button"
		                        v-for="url of res.url"
		                        :key="url"
		                        text
		                        small
		                        elevation="0"
                                :ripple="false"
		                        color="primary"
		                        @click="openInOtherTab(url)">
		                        <v-icon>
		                            mdi-open-in-new
		                        </v-icon>
		                    </v-btn> -->
		                </span>
		                <span v-else class="text-overflow-ellipsis">
		                	<label class="multimedia-label my-0 mr-2" v-text="label" />
		                	<!-- <span @click="openInOtherTabUrl(res)" v-text="res.name"></span> -->
		                	<a href="javascript:;" @click="openInOtherTabUrl(res)">
		                		{{ res.name }}
		                	</a>
		                    <!-- <v-btn 
		                     	class="default-modal-action-button"
		                        text
		                        small
		                        elevation="0"
                                :ripple="false"
		                        color="primary"
		                        @click="openInOtherTabUrl(res)">
		                        <v-icon>
		                            mdi-open-in-new
		                        </v-icon>
		                    </v-btn> -->
		                </span>
		            </li>
		        </ul>
		       <!--  <div v-show="resource && resource.length === 0">
		            Sin <span class="text-lowercase" v-text="label"></span> asociados
		        </div> -->
	    	</v-col>
		</v-row>
	</v-col>

</template>
<script>
	export default {
		name: 'MultimediaSectionsInfo',
		props: {
			label: String,
			resource: {
				type: Array,
				require: false
			}
		},
		data() {
			return {

			}
		},
		methods: {
			openInOtherTabUrl(res) {
    	        let vue = this;
    	        const check_url = vue.resource.every((ele) => (ele.url_filters !== undefined));

    	        console.log('openInOtherTabUrl')

    	        if(check_url) {
    	        	console.log('check_url')
    	        	const url = new URL(res.url); 
    	        	const resourceParams = new URLSearchParams(url.search);

    	        	const media_data_key = resourceParams.get('media_data');
    	        	localStorage.setItem(media_data_key, JSON.stringify(res.url_filters));

    	        	// console.log('resourceParams', { resourceParams, url, media_data:  });
    	        	vue.openInOtherTab(res.url);
    	        }
			},
	        openInOtherTab(url) {
        	    this.openInNewTab(url);
        	},
		}
	}

</script>