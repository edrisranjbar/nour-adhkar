// store.js
import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';
import axios from 'axios';
import { BASE_API_URL } from '@/config';

const store = createStore({
    state: {
        user: {heart_score:0},
        token: null,
        favorites: []
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
            state.favorites = [];
        },
        updateHeartScore(state, score) {
            if (state.user) {
                state.user.heart_score = score;
            }
        },
        setFavorites(state, favorites) {
            state.favorites = favorites;
        },
        addFavorite(state, favorite) {
            state.favorites.push(favorite);
        },
        removeFavorite(state, id) {
            state.favorites = state.favorites.filter(f => f.id !== id);
        }
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
                commit('clearUser');
                localStorage.removeItem('token');
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
        },
        async loadFavorites({ commit, state }) {
            try {
                const response = await axios.get(`${BASE_API_URL}/adhkar/favorites`, {
                    headers: {
                        Authorization: `Bearer ${state.token}`
                    }
                });
                if (response.data.success) {
                    commit('setFavorites', response.data.favorites);
                }
            } catch (error) {
                console.error('Error loading favorites:', error);
                throw error;
            }
        },
        async toggleFavorite({ commit, state }, id) {
            try {
                const response = await axios.post(
                    `${BASE_API_URL}/adhkar/favorites/${id}`,
                    {},
                    {
                        headers: {
                            Authorization: `Bearer ${state.token}`
                        }
                    }
                );
                if (response.data.success) {
                    if (response.data.isFavorite) {
                        const dhikr = await axios.get(`${BASE_API_URL}/adhkar/${id}`);
                        commit('addFavorite', dhikr.data);
                    } else {
                        commit('removeFavorite', id);
                    }
                }
                return response.data;
            } catch (error) {
                console.error('Error toggling favorite:', error);
                throw error;
            }
        }
    },
    plugins: [createPersistedState()] // This will persist the state in localStorage
});

export default store;