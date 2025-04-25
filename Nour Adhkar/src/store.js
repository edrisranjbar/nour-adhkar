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
        async refreshUserData({ commit, state }) {
            try {
                if (!state.token) {
                    console.log('No token available, skipping user data refresh');
                    return;
                }
                
                console.log('Refreshing user data with token:', state.token.substring(0, 10) + '...');
                
                // Set the token in axios headers
                axios.defaults.headers.common['Authorization'] = `Bearer ${state.token}`;
                
                const response = await axios.get(`${BASE_API_URL}/user/profile`);
                console.log('User profile response:', response.data);
                
                if (response.data.success) {
                    console.log('Updating user data in store:', response.data.profile);
                    commit('setUser', response.data.profile);
                } else {
                    console.warn('User profile response indicated failure:', response.data);
                }
            } catch (error) {
                console.error('Error refreshing user data:', error);
                if (error.response) {
                    console.error('Error response:', error.response.status, error.response.data);
                }
                
                if (error.response?.status === 401) {
                    console.log('Unauthorized, clearing user data');
                    commit('clearUser');
                    localStorage.removeItem('token');
                }
            }
        },
        async login({ commit }, credentials) {
            try {
                const response = await axios.post(`${BASE_API_URL}/auth/login`, credentials);
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
                const response = await axios.post(`${BASE_API_URL}/auth/register`, userData);
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
                    await axios.post(`${BASE_API_URL}/auth/logout`, {}, {
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
                const response = await axios.post(`${BASE_API_URL}/auth/refresh`, {}, {
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