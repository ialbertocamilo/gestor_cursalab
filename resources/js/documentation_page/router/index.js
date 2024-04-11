import Vue from "vue";
import VueRouter from 'vue-router';

import SecretKeyPage from  '../pages/SecretKeyPage.vue';
import TokenPage from  '../pages/TokenPage.vue';

import CriteriosPage from  '../pages/CriteriosPage.vue';
import ValoresPage from  '../pages/ValoresPage.vue';
import UserPage from  '../pages/UserPage.vue';
import InactivatePage from  '../pages/InactivatePage.vue';
import ActivatePage from  '../pages/ActivatePage.vue';
import ProgressPage from  '../pages/ProgressPage.vue';
import WorkspacePage from '../pages/WorkspacePage.vue'
import CoursePage from '../pages/CoursePage.vue'
import UsersPage from '../pages/UsersPage.vue'
import CourseDetailPage from '../pages/CourseDetailPage.vue'
import UserUpdatePage from '../pages/UserUpdatePage.vue'
import UserCreatePage from '../pages/UserCreatePage.vue'
import LimitationsPage from "../pages/LimitationsPage.vue";


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
        path: "/documentation-api/limitations",
        name: "limitations",
        component:LimitationsPage,
    },
	{
        path: "/documentation-api/token",
		name: "TokenPage",
		component:TokenPage,
    },
	{
        path: "/documentation-api/criterions",
		name: "CriteriosPage",
		component:CriteriosPage,
    },
	{
        path: "/documentation-api/criterion-values",
		name: "ValoresPage",
		component:ValoresPage,
    },
	{
        path: "/documentation-api/update-create-users",
		name: "UserPage",
		component:UserPage,
    },
	{
        path: "/documentation-api/create-users",
		name: "UserCreatePage",
		component:UserCreatePage,
    },
	{
        path: "/documentation-api/update-users",
		name: "UserUpdatePage",
		component:UserUpdatePage,
    },
	{
        path: "/documentation-api/workspace",
		name: "WorkspacePage",
		component:WorkspacePage,
    },
	{
        path: "/documentation-api/inactivate-users",
		name: "inactivar_usuarios",
		component:InactivatePage,
    },
	{
        path: "/documentation-api/activate-users",
		name: "activar_usuarios",
		component:ActivatePage,
    },
	{
        path: "/documentation-api/users",
		name: "users",
		component:UsersPage,
    },
	{
        path: "/documentation-api/progress",
		name: "progress_page",
		component:ProgressPage,
    },
	{
        path: "/documentation-api/courses",
		name: "coursePage",
		component:CoursePage,
    },
	{
        path: "/documentation-api/course-progress",
		name: "courseDetailPage",
		component:CourseDetailPage,
    }

]
const router = new VueRouter({
	mode: "history",
	routes
});
export default router;
