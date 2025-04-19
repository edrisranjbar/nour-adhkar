// store.js
import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';
import axios from 'axios';
import { BASE_API_URL } from '@/config';

const store = createStore({
    state: {
        user: {heart_score:0},
        token: null
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
                state.user.heart_score = score;
            }
        },
        updateProfile(state, profile) {
            if (state.user) {
                state.user = { ...state.user, ...profile };
            }
        }
    },
    actions: {
        async loadProfile({ commit, state }) {
            try {
                const response = await axios.get(`${BASE_API_URL}/user/profile`, {
                    headers: {
                        Authorization: `Bearer ${state.token}`
                    }
                });
                if (response.data.success) {
                    commit('updateProfile', response.data.profile);
                }
                return response.data.profile;
            } catch (error) {
                console.error('Error loading profile:', error);
                throw error;
            }
        },
        async updateProfile({ commit, state }, profileData) {
            try {
                const response = await axios.put(`${BASE_API_URL}/user/profile`, profileData, {
                    headers: {
                        Authorization: `Bearer ${state.token}`
                    }
                });
                if (response.data.success) {
                    commit('updateProfile', response.data.profile);
                }
                return response.data;
            } catch (error) {
                console.error('Error updating profile:', error);
                throw error;
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
        },
        async loadUserStats({ state }) {
            try {
                const response = await axios.get(`${BASE_API_URL}/user/stats`, {
                    headers: {
                        Authorization: `Bearer ${state.token}`
                    }
                });
                return response.data;
            } catch (error) {
                console.error('Error loading user stats:', error);
                return {
                    todayCount: 0,
                    favoriteCount: 0
                };
            }
        }
    },
    plugins: [createPersistedState()] // This will persist the state in localStorage
});

export default store;