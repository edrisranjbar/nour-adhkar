<template>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="user-brief">
            <img :src="user.avatar || defaultAvatar" :key="user.avatar" @error="handleImageError" class="mini-avatar" />
            <span class="user-name">{{ user.name }}</span>
        </div>
        <div class="stats">
            <div class="stat-item">
                <Heart3D :score="user.heart_score || 0" />
                <span>{{ user.heart_score ?? 0 }}</span>
            </div>
            <button @click="openLogoutModal" class="logout-button">
                <font-awesome-icon icon="fa-solid fa-sign-out-alt" />
                <span>خروج</span>
            </button>
        </div>
    </div>
</template>

<style>
.top-bar {
    background: white;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 100;
}

.user-brief {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.mini-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.stats {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #1cb0f6;
}
.logout-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #fff1f1;
    color: #ff4b4b;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.logout-button:hover {
    background: #ffe5e5;
    transform: translateY(-1px);
}

.logout-button svg {
    font-size: 1.1em;
}

.logout-modal {
    max-width: 360px;
}

.modal-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.logout-icon {
    font-size: 1.5rem;
    color: #ff4b4b;
}

.modal-header h2 {
    color: #ff4b4b;
    margin: 0;
}

.logout-modal p {
    color: #666;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

.btn-danger {
    background: #ff4b4b;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.btn-danger:hover {
    background: #ff3333;
    transform: translateY(-1px);
}

.btn-danger svg {
    font-size: 1.1em;
}

@media (max-width: 768px) {
    .logout-button span {
        display: none;
    }

    .logout-button {
        padding: 0.5rem;
    }
}
</style>

<script>
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import Heart3D from '@/components/Heart3D.vue';

export default {
    name: 'TopBar',
    components: {
        Heart3D,
    },
    props: {
        user: {
            type: Object,
            required: true
        },
        defaultAvatar: {
            type: String,
            default: 'path/to/default/avatar.png' // Set your default avatar path here
        }
    },
    methods: {
        async logout() {
            try {
                await axios.post(`${BASE_API_URL}/logout`, {}, {
                    headers: {
                        Authorization: `Bearer ${this.$store.state.token}`,
                    },
                });
            } catch (error) {
                console.error('Logout error:', error);
            }

            this.$store.commit('clearUser'); // Clear user from Vuex
            this.$router.push('/login');
        },
        openLogoutModal() {
            this.isLogoutModalOpen = true;
        },
        async confirmLogout() {
            try {
                await axios.post(`${BASE_API_URL}/logout`, {}, {
                    headers: {
                        Authorization: `Bearer ${this.$store.state.token}`,
                    },
                });
                this.$store.commit('clearUser');
                this.toast.success('با موفقیت خارج شدید');
                this.$router.push('/login');
            } catch (error) {
                console.error('Logout error:', error);
                this.toast.error('خطا در خروج از حساب کاربری');
            }
        },
    }
}
</script>