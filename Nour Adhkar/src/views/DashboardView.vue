<template>
    <div class="dashboard">
        <div class="dashboard-header">
            <h1>{{ user.name }} خوش آمدید</h1>
            <button @click="logout" class="logout-btn">خروج</button>
        </div>

        <div class="user-card">
            <div class="user-info">
                <img :src="user.avatar || 'https://via.placeholder.com/100'" alt="تصویر پروفایل" class="avatar" />
                <div class="user-details">
                    <p class="email">{{ user.email }}</p>
                    <p class="name">{{ user.name }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            user: JSON.parse(localStorage.getItem('user')) || {},
        };
    },
    methods: {
        async logout() {
            try {
                // Optional: Send a logout request to the backend (if needed)
                await axios.post('http://localhost:8000/api/logout', {}, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
            } catch (error) {
                console.error('Logout error:', error);
            }

            // Clear local storage and redirect to login
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            this.$router.push('/login');
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

.email {
    color: #666;
    font-size: 0.9rem;
}

.name {
    color: #333;
    font-weight: 500;
}

.logout-btn {
    padding: 0.5rem 1rem;
    background: #fff;
    color: #dc3545;
    border: 1px solid #dc3545;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.logout-btn:hover {
    background: #dc3545;
    color: #fff;
}
</style>