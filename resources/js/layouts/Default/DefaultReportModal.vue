<template>
	
	<ReportPromptModal
		:prefix="reportName"
		:isOpen="isAskingForNewReport"
		@cancel="cancelNewReport"
		@confirm="confirmNewReport($event)" 
	/>

</template>
<script>

const FileSaver = require("file-saver");
const moment = require("moment");
moment.locale("es");

import ReportPromptModal from "../../components/Reportes/ReportPromptModal.vue";

export default {
	props:{
		isOpen: {
			type: Boolean,
			default:false
		},
		report: {
			type: Object
		}
	},
	components: { ReportPromptModal },
	data() {
		return {
			workspaceId: 0,
			adminId: 0,
			isSuperUser: false,
			isAskingForNewReport: false,
			generateReportCallback: () => {},

			filenameDialog: false,
			reportName: '',
			isBeingProcessedNotification: false,
			isReadyNotification: false,
			reportDownloadUrl: null,
			reportFilename: null
		}	
	},
	watch:{
		isOpen(val) {
			const vue = this;
			if(val) {
				const { reportName } = vue.report; 
				vue.generateReport(vue.report, reportName);
			}
		}
	},
	methods: {
		generateReport(report, reportName) {
			this.generateReportCallback = report.callback;
			this.isAskingForNewReport = true;

			// Generate report name
			this.reportName = reportName+ ' ' +moment(new Date).format('MM-DD')
		},
		confirmNewReport(event) {

			this.generateReportCallback(event.reportName);
			this.isAskingForNewReport = false;
			this.$emit('onConfirm')

			// notify that the report has been added

			const message = event.reportName
				? `Tu solicitud de reporte "${event.reportName}" se añadió correctamente.`
				: `Tu solicitud de reporte se añadió correctamente.`

			this.$toast.warning({
				component: Vue.component('comp', {
					template: `
						<div>${message} <a href="javascript:"
										   @click.stop="clicked">Revisalo aquí</a>
						</div>`,
					methods: {
						clicked() { this.$emit('redirect') }
					}
				}),
				listeners: {
					redirect: () => { window.location.href = '/exportar/node' }
				}
			});
		},
		cancelNewReport(){
			this.isAskingForNewReport = false;
			this.$emit('onCancel')
		}
	}
}

</script>