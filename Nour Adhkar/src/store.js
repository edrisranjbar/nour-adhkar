// store.js
import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';
import axios from 'axios';
import { BASE_API_URL } from '@/config';

const store = createStore({
    state: {
        user: null,
        token: localStorage.getItem('token') || null,
        completedDays: []
    },
    getters: {
        isAuthenticated: state => !!state.token,
        isAdmin: state => state.user && state.user.role === 'admin',
        user: state => state.user,
        completedDays: state => state.completedDays
    },
    mutations: {
        setUser(state, user) {
            state.user = user;
        },
        setToken(state, token) {
            state.token = token;
            localStorage.setItem('token', token);
        },
        setCompletedDays(state, days) {
            state.completedDays = days;
        },
        clearAuth(state) {
            state.user = null;
            state.token = null;
            localStorage.removeItem('token');
        },
        updateUserPhoto(state, photoUrl) {
            if (state.user) {
                state.user = {
                    ...state.user,
                    photo: photoUrl
                };
            }
        },
        updateUserName(state, name) {
            if (state.user) {
                state.user = {
                    ...state.user,
                    name: name
                };
            }
        },
        updateUser(state, userData) {
            if (state.user) {
                state.user = {
                    ...state.user,
                    ...userData
                };
            }
        }
    },
    actions: {
        async fetchUserStats({ commit, state }) {
            try {
                const response = await axios.get(`${BASE_API_URL}/user/stats`, {
                    headers: {
                        Authorization: `Bearer ${state.token}`
                    }
                });
                console.log('API Response:', response.data); // Debug log
                if (response.data) {
                    // Create a user object with the stats
                    const userData = {
                        ...state.user, // Preserve existing user data including name
                        streak: response.data.streak,
                        heart_score: response.data.heart_score,
                        today_count: response.data.today_count,
                        favorite_count: response.data.favorite_count,
                        total_dhikrs: response.data.total_dhikrs
                    };
                    console.log('User data to commit:', userData); // Debug log
                    commit('setUser', userData);
                    return response.data;
                }
                throw new Error('Invalid response format');
            } catch (error) {
                console.error('Error fetching user stats:', error);
                throw error;
            }
        },
        async fetchCompletedDays({ commit, state }) {
            try {
                const response = await axios.get(`${BASE_API_URL}/user/completed-days`, {
                    headers: {
                        Authorization: `Bearer ${state.token}`
                    }
                });
                console.log('Completed days response:', response.data); // Debug log
                if (response.data && response.data.dates) {
                    commit('setCompletedDays', response.data.dates);
                    return response.data.dates;
                }
                throw new Error('Invalid response format');
            } catch (error) {
                console.error('Error fetching completed days:', error);
                throw error;
            }
        },
        async refreshUserData({ commit, state }) {
            if (!state.token) return;

            try {
                console.log('Refreshing user data with token:', state.token);
                const response = await axios.get(`${BASE_API_URL}/user/profile`, {
                    headers: {
                        Authorization: `Bearer ${state.token}`
                    }
                });
                console.log('User profile response:', response.data);
                if (response.data.profile) {
                    const userData = {
                        ...response.data.profile,
                        name: response.data.profile.name || 'کاربر'
                    };
                    commit('setUser', userData);
                    console.log('Updating user data in store:', userData);
                }
            } catch (error) {
                console.error('Error refreshing user data:', error);
                commit('clearAuth');
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
            commit('clearAuth');
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
                commit('clearAuth');
                return false;
            }
        }
    },
    plugins: [createPersistedState()]
});

export default store;