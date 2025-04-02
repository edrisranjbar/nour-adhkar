<template>
    <div class="container">
        <div class="register-form">
            <h2>ثبت نام</h2>
            <form @submit.prevent="register">
                <input v-model="name" type="text" placeholder="نام" required />
                <input v-model="email" type="email" placeholder="ایمیل" required />
                <input v-model="password" type="password" placeholder="رمز عبور" required />
                <button type="submit">ثبت نام</button>
            </form>
            <p v-if="error" class="error">{{ error }}</p>
        </div>
    </div>
</template>

<style scoped>
button,a,input {
    font-family: "Vazirmatn FD", sans-serif !important;
}
.container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100vh;
    padding: 20px;
}
.register-form {
    font-family: "Vazirmatn FD", sans-serif !important;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px;
    background-color: #f7f9fc;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    margin: auto;
}

.register-form h2 {
    margin-bottom: 20px;
    font-size: 2rem;
    color: #333;
}

.register-form input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
}

.register-form input:focus {
    border-color: #A79277;
    outline: none;
}

.register-form button {
    width: 100%;
    padding: 10px;
    background-color: #A79277;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
}

.register-form button:hover {
    background-color: #9C8466;
}

.register-form .error {
    color: red;
    margin-top: 10px;
    font-size: 0.875rem;
}
</style>

<script>
import axios from 'axios';
import { BASE_API_URL } from '@/config';

export default {
    data() {
        return {
            name: '',
            email: '',
            password: '',
            error: '',
        };
    },
    methods: {
        async register() {
            try {
                const response = await axios.post(`${BASE_API_URL}/register`, {
                    name: this.name,
                    email: this.email,
                    password: this.password,
                });

                if (response.data.success) {
                    localStorage.setItem('token', response.data.token);
                    localStorage.setItem('user', JSON.stringify(response.data.user));
                    this.$router.push('/dashboard');
                }
            } catch (err) {
                this.error = err.response?.data?.errors || 'ثبت نام با مشکل مواجه شد';
            }
        },
    },
};
</script>