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
                        v-model="search"
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

                        <div v-if="coursesListIsVisible"
                            class="container p-0">
                            <div  v-for="course of currentCollection"
                                  :key="course.id" class="row course">
                                <v-col cols="7"
                                       class="d-flex pl-0 justify-content-start align-items-center">

                                    <v-checkbox
                                        class="my-0"
                                        label=""
                                        color="primary"
                                        v-model="course.selected"
                                        value="selected"
                                        hide-details="false"
                                        @change="updateSegmentacionAlert()"
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
                                <v-col cols="2"
                                       class="d-flex justify-content-start align-items-center">
                                    ID: {{ course.id }}
                                </v-col>
                                <v-col
                                    cols="3"
                                    class="d-flex justify-content-end align-items-center">
                                    <div
                                        class="segmentation-alert"
                                        :class="{
                                                    filled: course.isSegmented,
                                                    outline: !course.isSegmented
                                                }">
                                        <v-icon
                                            size="15"
                                            :color="course.segmented ? '#5358E8' : '#fff'"
                                            class="ml-3 mr-3">
                                            mdi-account-group
                                        </v-icon>

                                        <v-icon
                                            v-if="course.isSegmented"
                                            size="12"
                                            :color="'white'"
                                            class="ok-icon">
                                            mdi-check-circle
                                        </v-icon>

                                        <v-icon
                                            v-if="!course.isSegmented"
                                            size="12"
                                            :color="'#5358E8'"
                                            class="alert-icon">
                                            mdi-alert
                                        </v-icon>
                                        <span
                                            v-if="!course.isSegmented"
                                            class="mr-3">
                                            Sin segmentación
                                        </span>
                                        <span
                                            v-if="course.isSegmented"
                                            class="mr-3">
                                            Con segmentación
                                        </span>
                                    </div>

                                </v-col>
                            </div>
                        </div>

                    </div>

                    <p v-if="showNotificationAlert"
                       class="text-red pt-3">
                        *Haz seleccionado cursos que cuentan con segmentación. Al confirmar se modificará la segmentación de estos cursos.
                    </p>
                </v-col>
            </v-row>

        </template>
    </DefaultDialog>
</template>

<script>

import _ from 'lodash';

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
        search: _.throttle(function(newValue) {
            if (newValue)
                this.loadData(1);
            else
                // Reset search results and syncronize selected results
                this.syncSelectedSearchResults();
        }, 400)
    },
    data() {
        return {
            coursesListIsVisible: true,
            selectionLimit: 10,
            loadedPages: [],
            lastPage: -1,
            courses: [],
            searchedCourses: [],
            showNotificationAlert: false,
            search: ''
        }
    },
    computed: {
        currentCollection() {
            return this.search ? this.searchedCourses : this.courses;
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
            this.submitData()
        },
        /**
         * Perform request to clone segmentation
         */
        async submitData() {

            this.showLoader()
            this.syncSelectedSearchResults();
            const selectedCourses = this.courses.filter(c => !!c.selected)
            const selectedCoursesIds = selectedCourses.map(c => c.id)

            try {

                let res = await axios({
                    url: '/segments/multiple-segmentation',
                    method: 'post',
                    data: {
                        originCourseId: this.originCourseId,
                        destinationCoursesIds: selectedCoursesIds
                    }
                });

            } catch (ex) {
                console.log(ex)
            }

            this.hideLoader()
            this.$emit('onConfirm', true);
        },
        async loadData(page = 1) {

            // Page has already been loaded

            if (!this.search) {
                if (this.loadedPages.includes(page))
                    return;

                if (page > this.lastPage && this.lastPage !== -1)
                    return;
            }

            try {

                let url = ''
                if (this.search) {
                    url = `/cursos/search/?q=${this.search}&page=1&paginate=10`
                } else {

                    let workspaces = this.subworkspacesIds.map(encodeURIComponent)
                    workspaces = 'segmented_module[]=' + workspaces.join('&segmented_module[]=')

                    url = `/cursos/search/?page=${page}&paginate=10&${workspaces}`
                }

                // Perform courses request

                const response = await axios({
                    method: 'get',
                    url
                });

                if (!this.search) {
                    this.loadedPages.push(page)
                    this.lastPage = response.data.data.last_page
                }

                // Get courses

                response.data.data.data.forEach(c => {
                    const course = {
                        id: c.id,
                        name: c.name,
                        selected: false,
                        isSegmented: c.segments_count > 0
                    };
                    if (this.search) {
                        // Add to search results only when
                        // is not already included

                        if (!this.searchedCourses.some(c => c.id === course.id))
                            this.searchedCourses.push(course);
                    } else {
                        if (!this.courses.some(c => c.id === course.id))
                            this.courses.push(course);
                    }
                });

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

                this.loadNextPage()
            }
        },
        loadNextPage() {
            const lastPage = this.loadedPages[this.loadedPages.length - 1];
            if (lastPage) {
                this.loadData(lastPage + 1);
            }
        },
        /**
         * Show alert when at least one segmented course is selected
         */
        updateSegmentacionAlert() {

            // Execute only when is NOT showing search results

            if (this.search) return;

            this.showNotificationAlert = this.courses.some(
                c => c.selected && c.isSegmented
            );

            this.courses.sort((a, b) => {
                if (a.selected && !b.selected) {
                    return -1;
                } else if (!a.selected && b.selected) {
                    return 1;
                } else {
                    return 0;
                }
            });
        },
        syncSelectedSearchResults() {

            let selectedInSearch =  this.searchedCourses.filter(sc => sc.selected)
            if (selectedInSearch.length) {

                selectedInSearch.forEach(sis => {

                    let courseIndex = this.courses.findIndex(c => c.id === sis.id)
                    if (courseIndex >= 0) {
                        this.courses[courseIndex].selected = 'selected'
                    } else {
                        this.courses.push(sis)
                    }
                })
                this.updateSegmentacionAlert();
                this.searchedCourses = [];
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
