import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)

export default new Vuex.Store({
    state: {

        User: {
            rol : null
        } // Current user
    },
    mutations: {
        setUser: (state, newUser) => state.User = newUser
    }
})
