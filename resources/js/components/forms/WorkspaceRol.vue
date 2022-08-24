<template>
    <div class="box_workspaces">
        <DefaultAutocomplete
            dense
            label="Roles"
            v-model="resource.roles"
            :items="selects.roles"
            item-text="name"
            item-value="id"
            multiple
            @input="upd"
            :clearable=true
            :countShowValues=3
        />
    </div>
</template>
<style>
.c-white {
    color: white;
}
.v-text-field--outlined.v-input--dense .v-label--active {
    top: 0;
}
</style>
<script>
const fields = ['workspaces','roles','toworkspace'];
export default {
    props: [
        "workspaces","roles","toworkspace","roleselects"
    ],
    data(){
        return {
        applicants:[
            {
                previous: '',
                expiration:''
            }
            ],
                resourceDefault: {
                    workspaces: [],
                    roles: [],
                    toworkspace: [],
                },
                resource: {},
                selects: {
                    workspaces: [],
                    roles: [],
                },
        }
    },
    mounted() {
        let vue = this
        vue.selects.roles = vue.roles;
        vue.resource.roles =vue.roleselects;
        let sel = '';

        if (vue.roleselects !== undefined){
            vue.roleselects.filter((value, index) => {
                if(index > 0)
                    sel+=','
                sel += value.id
            });
            vue.$root.$refs['roles_'+vue.toworkspace].value = sel
        }
    },
    methods : {
        upd(value) {
            let vue = this

            vue.$root.$refs['roles_'+vue.toworkspace].value = value
        },
    }
};
</script>
