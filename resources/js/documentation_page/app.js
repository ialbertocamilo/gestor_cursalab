/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
 window.Vue = require("vue");
 import vuetify from "../plugins/vuetify";
 import router from "./router/index";
//  import mixin from '../mixin'
 import VueNotification from "@kugatsu/vuenotification";
 Vue.use(VueNotification, {
     timer: 20,
     success: {
         background: "#d4edda",
         color: "#0f5132"
     }
 });
 import "./../../sass/input_forms.css";
 import Vue from "vue";

 Vue.component("documentation-page", require("./pages/HomePage.vue"));
 Vue.mixin(require('./mixin'));
//  import VuePrism from 'vue-prism';
import "prismjs";
import "prismjs/themes/prism.css";
import "prismjs/plugins/line-numbers/prism-line-numbers";
import "prismjs/plugins/line-numbers/prism-line-numbers.css";
import 'prismjs/components/prism-json';
//  Vue.use(VuePrism);
 import 'prismjs/themes/prism.css';
 const app = new Vue({
    router,
    vuetify,
    el: "#app",
 });
 