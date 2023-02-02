import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        reportIsReady: false,
        reportHasResults: false,
        finishedReportMessage: '',
        finishedReportLink: '',
        reportLinkText: '',
        reportLinkAction: '',

        User: {
            rol : null
        } // Current user
    },
    mutations: {
        setUser: (state, newUser) => state.User = newUser,
        setFinishedReportMessage: (state, newMessage) => state.finishedReportMessage = newMessage,
        setReportLinkText: (state, newValue) => state.reportLinkText = newValue,
        setReportLinkAction: (state, newValue) => state.reportLinkAction = newValue,
        setReportHasResults: (state, newStatus) => state.reportHasResults = newStatus,
        showReportIsReadyNotification: (state) => state.reportIsReady = true,
        hideReportIsReadyNotification: (state) => state.reportIsReady = false,
    }
})
