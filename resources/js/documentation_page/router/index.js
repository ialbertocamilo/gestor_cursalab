import Vue from "vue";
import VueRouter from 'vue-router';

import SecretKeyPage from  '../pages/SecretKeyPage.vue';
import TokenPage from  '../pages/TokenPage.vue';

import UserPage from  '../pages/UserPage.vue';
import InactivatePage from  '../pages/InactivatePage.vue';
import ActivatePage from  '../pages/ActivatePage.vue';

Vue.use(VueRouter);
const routes = [
	{
        path: "/documentation-api",
		name: "index",
		component:SecretKeyPage,
    },
    {
        path: "/documentation-api/secret-key",
		name: "secret_key",
		component:SecretKeyPage,
    },
	{
        path: "/documentation-api/token",
		name: "TokenPage",
		component:TokenPage,
    },
	{
        path: "/documentation-api/update-create-users",
		name: "UserPage",
		component:UserPage,
    },
	{
        path: "/documentation-api/inactivate-users",
		name: "inactivar_usuarios",
		component:InactivatePage,
    },
	{
        path: "/documentation-api/activate-users",
		name: "inactivar_usuarios",
		component:ActivatePage,
    }
	
]
const router = new VueRouter({
	mode: "history",
	routes
});
export default router;