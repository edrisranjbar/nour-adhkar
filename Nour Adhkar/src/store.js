// store.js
import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';

const store = createStore({
    state: {
        user: null,
        token: null,
        heartScore: 0,
    },
    mutations: {
        setUser(state, user) {
            state.user = user;
        },
        setToken(state, token) {
            state.token = token;
        },
        clearUser(state) {
            state.user = null;
            state.token = null;
        },
        updateHeartScore(state, score) {
            if (state.user) {
                state.user.heartScore = score; // Update heart score in user state
            }
        },
    },
    plugins: [createPersistedState()] // This will persist the state in localStorage
});

export default store;