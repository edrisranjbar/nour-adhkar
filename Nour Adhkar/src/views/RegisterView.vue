<template>
    <div class="container">
        <div class="register-form">
            <h2>ثبت نام</h2>
            <Form @submit="register" v-slot="{ errors }">
                <div class="form-group">
                    <Field name="name" v-model="name" type="text" placeholder="نام" :rules="nameRules" />
                    <ErrorMessage name="name" class="error" />
                </div>
                
                <div class="form-group">
                    <Field name="email" v-model="email" type="email" placeholder="ایمیل" :rules="emailRules" />
                    <ErrorMessage name="email" class="error" />
                </div>
                
                <div class="form-group">
                    <Field name="password" v-model="password" type="password" placeholder="رمز عبور" :rules="passwordRules" />
                    <ErrorMessage name="password" class="error" />
                </div>
                
                <div class="form-group">
                    <Field name="passwordConfirm" v-model="passwordConfirm" type="password" placeholder="تکرار رمز عبور" :rules="confirmPasswordRules" />
                    <ErrorMessage name="passwordConfirm" class="error" />
                </div>
                
                <button 
                    type="submit" 
                    :disabled="Object.keys(errors).length > 0 || isProcessing"
                >
                    <span v-if="isProcessing" class="loading-spinner">
                        <font-awesome-icon icon="fa-solid fa-spinner" spin />
                    </span>
                    ثبت نام
                </button>
                <div v-if="serverError" class="error server-error">{{ serverError }}</div>
            </Form>
            <div class="footer">
                <p>قبلاً ثبت نام کرده‌اید؟ <RouterLink to="/login">ورود به حساب کاربری</RouterLink></p>
            </div>
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
    width: 100%;
}

.register-form h2 {
    margin-bottom: 20px;
    font-size: 2rem;
    color: #333;
}

.form-group {
    width: 100%;
    margin-bottom: 15px;
}

.register-form input {
    width: 100%;
    padding: 10px;
    margin-bottom: 5px;
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
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.register-form button:hover:not(:disabled) {
    background-color: #9C8466;
}

.register-form button:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}

.register-form .error {
    color: red;
    font-size: 0.875rem;
    margin-top: 4px;
}

.server-error {
    margin-top: 15px;
}

.footer {
    margin-top: 20px;
    text-align: center;
    font-size: 0.875rem;
}

.footer a {
    color: #A79277;
    text-decoration: none;
}

.footer a:hover {
    text-decoration: underline;
}

.loading-spinner {
    font-size: 0.9rem;
}
</style>

<script>
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import { Form, Field, ErrorMessage } from 'vee-validate';

export default {
    components: {
        Form,
        Field,
        ErrorMessage
    },
    data() {
        return {
            name: '',
            email: '',
            password: '',
            passwordConfirm: '',
            serverError: '',
            nameRules: 'required|min:3',
            emailRules: 'required|email',
            passwordRules: 'required|min:8',
            isProcessing: false
        };
    },
    computed: {
        confirmPasswordRules() {
            return `required|confirmed:${this.password}`;
        }
    },
    methods: {
        async register() {
            try {
                this.serverError = '';
                this.isProcessing = true;
                const response = await axios.post(`${BASE_API_URL}/auth/register`, {
                    name: this.name,
                    email: this.email,
                    password: this.password,
                });

                if (response.data.success) {
                    // Update Vuex store
                    this.$store.commit('setUser', response.data.user);
                    this.$store.commit('setToken', response.data.token);
                    
                    // Redirect to home
                    this.$router.push('/');
                }
            } catch (err) {
                if (err.response?.data?.errors) {
                    // Format validation errors
                    const errors = err.response.data.errors;
                    const errorMessages = [];
                    
                    for (const field in errors) {
                        if (Array.isArray(errors[field])) {
                            errorMessages.push(errors[field][0]);
                        }
                    }
                    
                    this.serverError = errorMessages.join(' | ');
                } else {
                    this.serverError = err.response?.data?.message || 'ثبت نام با مشکل مواجه شد';
                }
            } finally {
                this.isProcessing = false;
            }
        },
    },
};
</script>