export default {
	methods: {
		$can(permissionSlug) {
			// console.log(Permissions.indexOf(permissionSlug),permissionSlug);
			return Permissions.indexOf(permissionSlug) !== -1;
		}
	}
};
