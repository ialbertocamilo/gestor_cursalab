<template>
	<div class="d-flex justify-content-between align-items-center">
		<v-btn color="primary" :loading="currLoader" :disabled="currDisabled || disabled" 
					 @click="sendCurrentEmail(index, subindex)">
			<!-- <span class="fas fa-envelope mr-2"></span> Enviar  -->
			Enviar correo 
		</v-btn>
	</div>
</template>

<script>
	
	export default {
		name:'BoxEmailNotification',
		props:{ 
			url: { type: String },
			index: { type: Number },
			subindex: { type: Number },
			disabled: { type: Boolean, default: false }
	  },
		data() {
			return {
				currLoader: false,
				currDisabled: false
			}
		},
		methods:{
			sendCurrentEmail(index, subindex) {
				const vm = this;
				// console.log('id_announcement', index, 'id_summoned', subindex);
				vm.currLoader = true;
				vm.currDisabled = true;

				vm.$http.put(vm.url+'/send_email', { campaign_id: index, id_summoned: subindex })
					.then((res) => {
					
					const { data } = res.data;

					if(data) vm.showAlert('Notificación enviada correctamente.');
					else vm.showAlert('Ooops el usuario no tiene email.', 'error');
				
					vm.currLoader = false;
					vm.currDisabled = false;
				});
			}
		}
	}

</script>