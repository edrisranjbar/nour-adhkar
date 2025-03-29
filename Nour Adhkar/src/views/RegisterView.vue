<template>
    <div class="register-form">
        <h2>Register</h2>
        <form @submit.prevent="register">
            <input v-model="name" type="text" placeholder="Name" required />
            <input v-model="email" type="email" placeholder="Email" required />
            <input v-model="password" type="password" placeholder="Password" required />
            <button type="submit">Register</button>
        </form>
        <p v-if="error" class="error">{{ error }}</p>
    </div>
</template>

<script>
import axios from 'axios';

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
                const response = await axios.post('http://localhost:8000/api/register', {
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
                this.error = err.response?.data?.errors || 'Registration failed';
            }
        },
    },
};
</script>