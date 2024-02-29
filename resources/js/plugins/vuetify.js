// src/plugins/vuetify.js

import Vue from "vue";
import Vuetify from "vuetify";
import "vuetify/dist/vuetify.min.css";
import "@mdi/font/css/materialdesignicons.min.css";

Vue.use(Vuetify);

const opts = {
	icons: {
		iconfont: "mdi"
	},
	theme: {
		themes: {
			light: {
				primary: "#5458EA",
				"main-background": "#f8f8fb",
				"main-text": "#768598"
			}
		},
		defaultFont: {
			family: 'Nunito'
		}
	}
};

export default new Vuetify(opts);
