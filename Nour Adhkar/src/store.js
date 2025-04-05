// store.js
import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';
import axios from 'axios';
import { BASE_API_URL } from '@/config';

const store = createStore({
    state: {
        user: {heart_score:0},
        token: null,
    },
    getters: {
        isAuthenticated: state => !!state.token,
        isAdmin: state => state.user && state.user.role === 'admin'
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
                state.user.heart_score = score; // Update heart score in user state
            }
        },
    },
    actions: {
        async logoutUser({ commit }) {
            try {
                if (this.state.token) {
                    await axios.post(`${BASE_API_URL}/logout`, {}, {
                        headers: {
                            Authorization: `Bearer ${this.state.token}`
                        }
                    });
                }
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                // Clear user data regardless of API success
                commit('clearUser');
                localStorage.removeItem('token');
            }
        }
    },
    plugins: [createPersistedState()] // This will persist the state in localStorage
});

export default store;