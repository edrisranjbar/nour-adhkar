<template>
    <div class="dashboard">
        <div class="dashboard-header">
            <h1>{{ user.name }} خوش آمدید</h1>
            <button @click="logout" class="button">خروج</button>
        </div>

        <div class="user-card">
            <div class="user-info">
                <img :src="user.avatar || 'https://via.placeholder.com/100'" alt="تصویر پروفایل" class="avatar" />
                <div class="user-details">
                    <p class="email">{{ user.email }}</p>
                    <p class="name">{{ user.name }}</p>
                    <p class="score">نمره: {{ user.heart_score ?? 0 }}/100</p>
                    <span class="heart-icon" :style="heartIconStyle">❤️</span>
                </div>
            </div>
            <div class="actions">
                <button @click="openChangeNameModal" class="action-btn">تغییر نام</button>
                <button @click="openChangePasswordModal" class="action-btn">تغییر رمز عبور</button>
            </div>
        </div>

        <!-- Change Name Modal -->
        <div v-if="isChangeNameModalOpen" class="modal">
            <div class="modal-content">
                <h2>تغییر نام</h2>
                <input v-model="newName" type="text" placeholder="نام جدید" />
                <button @click="changeName">تأیید</button>
                <button @click="isChangeNameModalOpen = false">بستن</button>
            </div>
        </div>

        <!-- Change Password Modal -->
        <div v-if="isChangePasswordModalOpen" class="modal">
            <div class="modal-content">
                <h2>تغییر رمز عبور</h2>
                <input v-model="newPassword" type="password" placeholder="رمز عبور جدید" />
                <button @click="changePassword">تأیید</button>
                <button @click="isChangePasswordModalOpen = false">بستن</button>
            </div>
        </div>
    </div>
</template>

<script>
import store from '@/store';
import axios from 'axios';
import { BASE_API_URL } from '@/config';

export default {
    data() {
        return {
            user: store.state.user,
            isChangeNameModalOpen: false,
            isChangePasswordModalOpen: false,
            newName: '',
            newPassword: '',
        };
    },
    computed: {
        heartIconStyle() {
            const score = this.user.heartScore;
            // Gradient of states from black (0) to bright red (100)
            if (score < 10) {
                return { filter: 'grayscale(100%) brightness(0.3)' }; // Darkest black
            } else if (score < 20) {
                return { filter: 'grayscale(80%) brightness(0.4)' }; // Slightly lighter black
            } else if (score < 30) {
                return { filter: 'grayscale(60%) brightness(0.6)' }; // Medium gray
            } else if (score < 40) {
                return { filter: 'sepia(100%) brightness(0.7) hue-rotate(30deg)' }; // Dark brown
            } else if (score < 50) {
                return { filter: 'sepia(100%) brightness(0.8) hue-rotate(20deg)' }; // Medium brown
            } else if (score < 60) {
                return { filter: 'sepia(80%) saturate(200%) brightness(0.9) hue-rotate(10deg)' }; // Light orange-brown
            } else if (score < 70) {
                return { filter: 'sepia(60%) saturate(300%) brightness(1) hue-rotate(-5deg)' }; // Dark orange
            } else if (score < 80) {
                return { filter: 'sepia(40%) saturate(400%) brightness(1.1)' }; // Orange-red
            } else if (score < 90) {
                return { filter: 'sepia(20%) saturate(600%) brightness(1.1)' }; // Bright red-orange
            } else {
                return { filter: 'saturate(1000%) brightness(1.2) contrast(1.5)' }; // Vibrant glowing red
            }
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
        openChangeNameModal() {
            this.isChangeNameModalOpen = true;
        },
        async changeName() {
            if (this.newName) {
                try {
                    const response = await axios.patch(
                        `${BASE_API_URL}/user/name`,
                        { name: this.newName },
                        {
                            headers: {
                                Authorization: `Bearer ${this.$store.state.token}`,
                            },
                        }
                    );

                    if (response.data.success) {
                        this.user.name = this.newName;
                        this.$store.commit('setUser', this.user); // Commit updated user to Vuex store
                        this.isChangeNameModalOpen = false; // Close the modal
                        this.newName = ''; // Clear the input
                    }
                } catch (error) {
                    console.error('Error updating name:', error);
                }
            }
        },
        openChangePasswordModal() {
            this.isChangePasswordModalOpen = true;
        },
        async changePassword() {
            if (this.newPassword) {
                try {
                    const response = await axios.patch(
                        `${BASE_API_URL}/user/password`,
                        { password: this.newPassword },
                        {
                            headers: {
                                Authorization: `Bearer ${this.$store.state.token}`,
                            },
                        }
                    );

                    if (response.data.success) {
                        this.isChangePasswordModalOpen = false; // Close the modal
                        this.newPassword = ''; // Clear the input
                    }
                } catch (error) {
                    console.error('Error updating password:', error);
                }
            }
        },
    },
};
</script>

<style scoped>
.dashboard {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    background: #ffffff;
    direction: rtl;
}

.dashboard button,a{
    font-family: "Vazirmatn FD", sans-serif !important;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.dashboard-header h1 {
    font-size: 1.5rem;
    color: #333;
    font-weight: 500;
    margin: 0;
}

.user-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
}

.user-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.user-details p {
    margin: 0;
}

.score {
    color: #007bff;
    font-weight: bold;
}

.heart-icon {
    font-size: 24px;
    display: inline-block;
    transition: filter 0.3s ease;
}

.actions {
    margin-top: 1rem;
}

.action-btn {
    padding: 0.5rem 1rem;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-right: 1rem;
}

.action-btn:hover {
    background: #0056b3;
}

.btn-adhkar {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
}

.btn-adhkar:hover {
    background: #45a049;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    width: 300px;
    text-align: center;
}

.modal-content h2 {
    margin-bottom: 1rem;
}

.modal-content input {
    margin-bottom: 1rem;
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 100%;
}

.modal-content button {
    margin: 0.5rem 0;
    padding: 0.5rem 1rem;
}
</style>