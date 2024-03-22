<template>
    <DefaultDialog
        width="940px"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <v-row>
               <v-col cols="12">
                   <p>
                       Selecciona los cursos a los cuales quieres copiar la
                       segmentación previamente realizada.
                   </p>
               </v-col>
            </v-row>
            <v-row>
                <v-col cols="5">
                    <DefaultInput
                        clearable dense
                        v-model="filters.q"
                        label="Buscar cursos por nombre o ID"
                        @onEnter=""
                        @clickAppendIcon=""
                        append-icon="mdi-magnify"
                    />
                </v-col>
                <v-col cols="7" class="d-flex justify-content-end align-items-center">
                    <p>
                        Seleccionados: 0/{{ selectionLimit }}
                    </p>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">

                    <div
                        ref="scrollContainer"
                        @scroll="handleScroll()"
                        class="courses-list">

                        <div class="container p-0">
                            <div  v-for="course of courses"
                                  :key="course.id" class="row course">
                                <v-col cols="8"
                                       class="d-flex pl-0 justify-content-start align-items-center">

                                    <v-checkbox
                                        class="my-0"
                                        label=""
                                        color="primary"
                                        v-model="checked"
                                        value="selected"
                                        hide-details="false"
                                    />

                                    <v-icon
                                        size="20"
                                        :color="'#2A3649'"
                                        class="">
                                        mdi-book
                                    </v-icon>
                                    <span class="name">
                                        {{ course.name }}
                                    </span>
                                </v-col>
                                <v-col cols="1"
                                       class="d-flex justify-content-end align-items-center">
                                    ID: {{ course.id }}
                                </v-col>
                                <v-col
                                    cols="3"
                                    class="d-flex justify-content-end align-items-center">
                                    <div
                                        class="segmentation-alert"
                                        :class="{
                                                    filled: n % 2 === 0,
                                                    outline: n % 2 !== 0
                                                }">
                                        <v-icon
                                            size="15"
                                            :color="'#fff'"
                                            class="ml-3 mr-3">
                                            mdi-account-group
                                        </v-icon>

                                        <v-icon
                                            size="12"
                                            :color="'white'"
                                            class="ok-icon">
                                            mdi-check-circle
                                        </v-icon>
                                        <!--                                                <v-icon class="alert-icon">-->
                                        <!--                                                    mdi-alert-->
                                        <!--                                                </v-icon>-->
                                        <span class="mr-3">
                                            Sin segmentación
                                        </span>
                                    </div>

                                </v-col>
                            </div>
                        </div>

                    </div>

                    <p class="text-red pt-3">
                        *Haz seleccionado cursos que cuentan con segmentación. Al confirmar se modificará la segmentación de estos cursos.
                    </p>
                </v-col>
            </v-row>

        </template>
    </DefaultDialog>
</template>

<script>
export default {
    props: {
        originCourseId: {
            type: Number,
            required: false
        },
        subworkspacesIds: {
            type: Array,
            default: [],
            required: true
        },
        options: {
            type: Object,
            required: true
        }
    },
    watch: {
        /**
         * Load data when modal has been open
         */
        'options.open' :  {
            handler(newValue, oldValue) {
                if (newValue) {
                    this.loadData(1);
                }
            },
            deep: true
        },
    },
    data() {
        return {
            selectionLimit: 10,
            loadedPages: [],
            courses: [],
            checked: true,
            filters : {
                q: ''
            }
        }
    },
    methods: {
        onCancel() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {

        },
        onConfirm() {
            let vue = this
            vue.$emit('onConfirm', true);
        },
        async loadData(page = 1) {

            // Page has already been loaded

            if (this.loadedPages.includes(page))
                return;

            try {

                let workspaces = this.subworkspacesIds.map(encodeURIComponent)
                workspaces = 'segmented_module[]=' + workspaces.join('&segmented_module[]=')
                let url = `/cursos/search/?page=${page}&paginate=10&${workspaces}`

                const response = await axios({
                    method: 'get',
                    url
                });
                console.log(`Page ${page} has been loaded`)
                this.loadedPages.push(page)

                // Get courses

                response.data.data.data.forEach(c => this.courses.push({
                    id: c.id,
                    name: c.name,
                    selected: false
                }))

            } catch (ex) {
                console.log(ex)
            }
        },
        loadSelects() {

        },
        handleScroll(e) {

            const container = this.$refs.scrollContainer;
            const isAtBottom = container.scrollHeight - container.scrollTop === container.clientHeight;

            if (isAtBottom) {

                const lastPage = this.loadedPages[this.loadedPages.length - 1];
                if (lastPage) {
                    this.loadData(lastPage + 1);
                }
            }
        }
    }
}
</script>

<style scoped lang="scss">

$main-color: #5358E8;

.row {
    padding-left: 10px;
    padding-right: 10px;
}

p {
    font-size: 16px;
    margin: 0;
}

p.text-red {
    color: #FF4560;
}

.courses-list {
    height: 317px;
    overflow-y: scroll;
    overflow-x: hidden;
    background: #F5F5F5;
    border-radius: 4px;
    color: #2A3649;
    padding: 24px;

    .course {

        .name, .id {
            font-size: 16px;
        }

        .segmentation-alert {
            position: relative;
            display: flex;
            justify-content: start;
            align-items: center;
            height: 31px;
            border-radius: 20px;
            font-size: 11px;

            &.filled {
                background: $main-color;
                color: white;
            }

            &.outline {
                background: white;
                border: 1px solid $main-color;
                color: $main-color;
            }

            .ok-icon, .alert-icon {
                position: absolute;
                top: 3px;
                left: 25px;
            }
        }
    }
}
</style>
