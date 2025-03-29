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
                    <p class="score">نمره: {{ user.heartScore }}/100</p>
                    <span class="heart-icon" :style="heartIconStyle">❤️</span>
                    <button @click="increaseHeartScore" class="btn-adhkar">
                        خواندن ذکر (+5)
                    </button>
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
import axios from 'axios';

export default {
    data() {
        return {
            user: {
                name: "کاربر",
                email: "user@example.com",
                avatar: '',
                heartScore: Number(localStorage.getItem('heartScore')) || 30, // Default score
            },
            isChangeNameModalOpen: false,
            isChangePasswordModalOpen: false,
            newName: '',
            newPassword: '',
        };
    },
    computed: {
        heartIconStyle() {
            // Returns CSS filters to color the emoji
            if (this.user.heartScore < 30) {
                return { filter: 'grayscale(100%) brightness(0.5)' }; // Black
            } else if (this.user.heartScore < 70) {
                return { filter: 'sepia(100%) saturate(300%) hue-rotate(-10deg)' }; // Orange
            } else {
                return { filter: 'saturate(200%) brightness(1.1)' }; // Vibrant Red
            }
        }
    },
    created() {
        this.fetchUserData();
    },
    methods: {
        async fetchUserData() {
            try {
                const response = await axios.get('http://localhost:8000/api/user', {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });

                if (response.data.user) {
                    this.user = response.data.user;
                    localStorage.setItem('heartScore', this.user.heart_score);
                }
            } catch (error) {
                console.error('Error fetching user data:', error);
            }
        },
        async logout() {
            try {
                await axios.post('http://localhost:8000/api/logout', {}, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
            } catch (error) {
                console.error('Logout error:', error);
            }

            localStorage.removeItem('token');
            localStorage.removeItem('user');
            localStorage.removeItem('heartScore');
            this.$router.push('/login');
        },
        increaseHeartScore() {
            if (this.user.heartScore < 100) {
                this.user.heartScore = Math.min(this.user.heartScore + 5, 100);
                localStorage.setItem('heartScore', this.user.heartScore);

                // Optionally send the updated heart score to the backend
                this.updateHeartScoreOnServer();
            }
        },
        async updateHeartScoreOnServer() {
            try {
                await axios.patch('http://localhost:8000/api/user/heart', {
                    score: this.user.heartScore
                }, {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
                });
            } catch (err) {
                console.error("Error updating heart score:", err);
            }
        },
        openChangeNameModal() {
            this.isChangeNameModalOpen = true;
        },
        async changeName() {
            if (this.newName) {
                try {
                    const response = await axios.patch(
                        'http://localhost:8000/api/user/name',
                        { name: this.newName },
                        {
                            headers: {
                                Authorization: `Bearer ${localStorage.getItem('token')}`,
                            },
                        }
                    );

                    if (response.data.success) {
                        this.user.name = this.newName;
                        localStorage.setItem('user', JSON.stringify(this.user));
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
                        'http://localhost:8000/api/user/password',
                        { password: this.newPassword },
                        {
                            headers: {
                                Authorization: `Bearer ${localStorage.getItem('token')}`,
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