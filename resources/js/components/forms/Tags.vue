<template>
    <div class="form-group row">
        <div class="col-sm-3 form-control-label">
            <h5>Tags</h5>
            <!-- <span>(para convalidación)</span> -->
        </div>
        <div class="col-sm-9">
            <v-select
                v-model="selectTags"
                name="tags"
                attach
                solo
                chips
                clearable
                multiple
                hide-details="false"
                :items="Tags"
            ></v-select>
            <small>(Puedes seleccionar hasta un maximo de <b>3 tags</b>)</small>
        </div>
        <!-- {{ tema }} -->
    </div>
</template>

<script>
export default {
    props: ["tema"],
    data() {
        return {
            selectTags: [],
            Tags: []
        };
    },
    methods: {
        cargarTags() {
            axios
                .post("/tags", {
                    tipo: "tema"
                })
                .then(res => {
                    console.log("Tags ", res);
                    res.data.forEach(el => {
                        let NewJson = {};
                        NewJson.text = el.nombre;
                        NewJson.value = el.id;
                        this.Tags.push(NewJson);
                    });
                })
                .catch(err => {
                    console.log(err);
                });
        }
    },
    // watch: {
    //     selectTags: function() {
    //         // console.log(this.selectTags);
    //         // return this.selectTags;
    //         if (this.selectTags.length <= 2) {
    //             // this.selectTags.forEach(el => {
    //             console.log("Hay mas de 2 tags seleccionados");
    //             // });
    //             // this.selectTags.splice(2,1)
    //         } else {
    //             return false;
    //         }
    //     }
    // },
    created() {
        console.log(JSON.parse(this.tema));
        if (this.tema) {
            let NewTags = JSON.parse(this.tema);
            NewTags.forEach(el => {
                let NewJson = {};
                NewJson.text = el.element_type;
                NewJson.value = el.tag_id;
                this.selectTags.push(NewJson);
            });
        }
        this.cargarTags();
    },
    updated() {
        console.log("Updating...");
        console.log("Tags lenght ",this.selectTags.length);
        if (this.selectTags.length > 3) {
            // this.selectTags.forEach(el => {
            console.log("Hay mas de 2 tags seleccionados");
            // });
            this.selectTags.splice(3,1)
        } else {
            return false;
        }
    }
};
</script>
