<template>
    <div class="box_workspaces">
        <v-row>
            <v-col cols="12" md="6" lg="6">
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
            </v-col>
            <v-col>
                <DefaultAutocomplete
                    dense
                    label="Email"
                    v-model="resource.emails_information"
                    :items="selects.emails_information"
                    item-text="name"
                    item-value="id"
                    multiple
                    @input="updateInputBlade"
                    :clearable=true
                    :countShowValues=3
                />
            </v-col>
        </v-row>
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
const fields = ['workspaces', 'roles', 'toworkspace','emails_information'];
export default {
    props: [
        "workspaces", "roles", "toworkspace", "roleselects","emails_information",'emails_information_selected'
    ],
    data() {
        return {
            applicants: [
                {
                    previous: '',
                    expiration: ''
                }
            ],
            resourceDefault: {
                workspaces: [],
                roles: [],
                emails_information:[],
                toworkspace: [],
            },
            resource: {},
            selects: {
                workspaces: [],
                roles: [],
                emails_information:[]
            },
        }
    },
    mounted() {
        let vue = this
        vue.selects.roles = vue.roles;
        vue.resource.roles = vue.roleselects;
        vue.selects.emails_information = vue.emails_information;
        
        let sel = '';

        if (vue.roleselects !== undefined) {
            vue.roleselects.filter((value, index) => {
                if (index > 0)
                    sel += ','
                sel += value.id
            });
            vue.$root.$refs['roles_' + vue.toworkspace].value = sel
            let workspace = vue.workspaces.find(el => el.id == vue.toworkspace);
            const ckbx_id = `workspacessel[${workspace.slug}][]`;
            let wk_ckbx = document.getElementById(ckbx_id);
            wk_ckbx.checked = true;
        }
        if(vue.emails_information_selected !== undefined){
            let workspace = vue.workspaces.find(el => el.id == vue.toworkspace);
            vue.resource.emails_information = vue.emails_information_selected.map((email_information)=>{
                if(email_information.workspace_id == workspace.id){
                    this.updateInputBlade(email_information.type.id);
                    return email_information.type;
                }
            })
        }
    },
    methods: {
        upd(value) {
            let vue = this
            let checbox = vue.$root.$refs['roles_' + vue.toworkspace]

            checbox.value = value
            // console.log("UPDATE");
            // console.log(checbox.value);
            let workspace = vue.workspaces.find(el => el.id == vue.toworkspace);
            const ckbx_id = `workspacessel[${workspace.slug}][]`;
            let wk_ckbx = document.getElementById(ckbx_id);
            wk_ckbx.checked = checbox.value !== "";
        },
        updateInputBlade(value){
            let vue = this
            let checkbox = vue.$root.$refs['email_' + vue.toworkspace]
            console.log('value',value);
            if(value instanceof Array){
                let sel = '';
                value = value.filter((value, index) => {
                    if (index > 0)
                        sel += ','
                    sel += value
                });
                if(sel){
                    value = sel;
                }
                console.log(value,sel);
            }
            checkbox.value = value
            let workspace = vue.workspaces.find(el => el.id == vue.toworkspace);
            const ckbx_id = `workspaces_email[${workspace.slug}][]`;
            let wk_ckbx = document.getElementById(ckbx_id);
            wk_ckbx.checked = checkbox.value !== "";
        }
    }
};
</script>
