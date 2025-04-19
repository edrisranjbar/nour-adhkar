// store.js
import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';
import axios from 'axios';
import { BASE_API_URL } from '@/config';

const store = createStore({
    state: {
        user: null,
        token: null
    },
    getters: {
        isAuthenticated: state => !!state.token,
        isAdmin: state => state.user && state.user.role === 'admin',
        user: state => state.user
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
        }
    },
    actions: {
        async login({ commit }, credentials) {
            try {
                const response = await axios.post(`${BASE_API_URL}/login`, credentials);
                if (response.data.token) {
                    commit('setToken', response.data.token);
                    commit('setUser', response.data.user);
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Login error:', error);
                return false;
            }
        },
        async register({ commit }, userData) {
            try {
                const response = await axios.post(`${BASE_API_URL}/register`, userData);
                if (response.data.token) {
                    commit('setToken', response.data.token);
                    commit('setUser', response.data.user);
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Registration error:', error);
                return false;
            }
        },
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
                commit('clearUser');
                localStorage.removeItem('token');
            }
        },
        async refreshToken({ commit, state }) {
            try {
                const response = await axios.post(`${BASE_API_URL}/refresh`, {}, {
                    headers: {
                        Authorization: `Bearer ${state.token}`
                    }
                });
                
                if (response.data.token) {
                    commit('setToken', response.data.token);
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Token refresh error:', error);
                commit('clearUser');
                return false;
            }
        }
    },
    plugins: [createPersistedState()]
});

export default store;