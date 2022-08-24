import Vue from "vue";
import VueRouter from 'vue-router';

import SecretKeyPage from  '../pages/SecretKeyPage.vue';
import TokenPage from  '../pages/TokenPage.vue';

import CriteriosPage from  '../pages/CriteriosPage.vue';
import ValoresPage from  '../pages/ValoresPage.vue';
import UserPage from  '../pages/UserPage.vue';
import InactivatePage from  '../pages/InactivatePage.vue';
import ActivatePage from  '../pages/ActivatePage.vue';

Vue.use(VueRouter);
const routes = [
	{
        path: "/documentacion-api",
		name: "index",
		component:SecretKeyPage,
    },
    {
        path: "/documentacion-api/secret-key",
		name: "secret_key",
		component:SecretKeyPage,
    },
	{
        path: "/documentacion-api/token",
		name: "TokenPage",
		component:TokenPage,
    },
	{
        path: "/documentacion-api/criterions",
		name: "CriteriosPage",
		component:CriteriosPage,
    },
	{
        path: "/documentacion-api/criterion-values",
		name: "ValoresPage",
		component:ValoresPage,
    },
	{
        path: "/documentacion-api/update-create-users",
		name: "UserPage",
		component:UserPage,
    },
	{
        path: "/documentacion-api/inactivate-users",
		name: "inactivar_usuarios",
		component:InactivatePage,
    },
	{
        path: "/documentacion-api/activate-users",
		name: "inactivar_usuarios",
		component:ActivatePage,
    }
	
]
const router = new VueRouter({
	mode: "history",
	routes
});
export default router;