<template>
    <div class="dashboard">
        <TopBar :user="user" @open-logout-modal="isLogoutModalOpen = true" />
        <div class="main-content">

            <ProfileCard :user="user" />

            <BadgesSection :user="user" />

            <!-- Action Cards -->
            <div class="action-cards">
                <div class="action-card" @click="openChangeNameModal">
                    <div class="card-icon">
                        <font-awesome-icon icon="fa-solid fa-user" />
                    </div>
                    <h3>تغییر نام</h3>
                    <p>نام خود را تغییر دهید</p>
                </div>

                <div class="action-card" @click="openChangePasswordModal">
                    <div class="card-icon">
                        <font-awesome-icon icon="fa-solid fa-lock" />
                    </div>
                    <h3>تغییر رمز عبور</h3>
                    <p>رمز عبور خود را تغییر دهید</p>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <div v-if="isChangeNameModalOpen || isChangePasswordModalOpen || isLogoutModalOpen" class="modal-backdrop"
            @click="closeModals">
            <div class="modal-content" @click.stop>
                <template v-if="isChangeNameModalOpen">
                    <h2>تغییر نام</h2>
                    <input 
                        v-model.trim="newName" 
                        type="text" 
                        placeholder="نام جدید"
                        @keyup.enter="changeName"
                    />
                    <div class="modal-actions">
                        <button 
                            @click="changeName" 
                            class="btn-primary"
                            :disabled="!newName.trim()"
                        >
                            ذخیره
                        </button>
                        <button @click="closeModals" class="btn-secondary">انصراف</button>
                    </div>
                </template>

                <template v-if="isChangePasswordModalOpen">
                    <h2>تغییر رمز عبور</h2>
                    <input v-model="newPassword" type="password" placeholder="رمز عبور جدید" />
                    <div class="modal-actions">
                        <button @click="changePassword" class="btn-primary">ذخیره</button>
                        <button @click="closeModals" class="btn-secondary">انصراف</button>
                    </div>
                </template>

                <template v-if="isLogoutModalOpen">
                    <div class="modal-header">
                        <font-awesome-icon icon="fa-solid fa-sign-out-alt" class="logout-icon" />
                        <h2>خروج از حساب کاربری</h2>
                    </div>
                    <p>آیا مطمئن هستید که می‌خواهید از حساب کاربری خود خارج شوید؟</p>
                    <div class="modal-actions">
                        <button @click="confirmLogout" class="btn-danger">
                            <font-awesome-icon icon="fa-solid fa-sign-out-alt" />
                            خروج
                        </button>
                        <button @click="closeModals" class="btn-secondary">انصراف</button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import store from '@/store';
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import { useToast } from "vue-toastification";
import TopBar from '@/components/Admin/TopBar.vue';
import Badge from '@/components/Badge.vue';
import ProfileCard from '@/components/Admin/ProfileCard.vue';
import BadgesSection from '@/components/Admin/BadgesSection.vue';

export default {
    components: {
        TopBar,
        Badge,
        ProfileCard,
        BadgesSection,
    },
    setup() {
        const toast = useToast();
        return { toast }
    },
    data() {
        return {
            user: store.state.user,
            isChangeNameModalOpen: false,
            isChangePasswordModalOpen: false,
            newName: '',
            newPassword: '',
            isLogoutModalOpen: false,
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
        },
    },
    methods: {
        openChangeNameModal() {
            this.isChangeNameModalOpen = true;
        },
        openChangePasswordModal() {
            this.isChangePasswordModalOpen = true;
        },
        closeModals() {
            this.isChangeNameModalOpen = false;
            this.isChangePasswordModalOpen = false;
            this.isLogoutModalOpen = false;
            this.newName = '';
            this.newPassword = '';
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
            this.closeModals();
        },
        async changeName() {
            if (!this.$store.state.token) {
                this.toast.error('لطفا دوباره وارد شوید');
                this.$router.push('/login');
                return;
            }

            if (!this.newName.trim()) {
                this.toast.error('لطفا نام جدید را وارد کنید');
                return;
            }

            try {
                const response = await axios.patch(`${BASE_API_URL}/user/name`, {
                    name: this.newName
                }, {
                    headers: {
                        'Authorization': `Bearer ${this.$store.state.token}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                if (response.data.success) {
                    // Update local user state
                    this.user = response.data.user;
                    // Update Vuex store
                    this.$store.commit('setUser', response.data.user);
                    
                    this.toast.success('نام با موفقیت تغییر کرد');
                    this.closeModals();
                }
            } catch (error) {
                if (error.response?.status === 401) {
                    this.toast.error('لطفا دوباره وارد شوید');
                    this.$store.commit('clearUser');
                    this.$router.push('/login');
                } else {
                    console.error('Change name error:', error);
                    this.toast.error(error.response?.data?.message || 'خطا در تغییر نام');
                }
            }
        },
    }
};
</script>

<style scoped>
:global(button), :global(input) {
    font-family: "Vazirmatn FD", sans-serif !important;
}
.dashboard {
    position: absolute;
    left: 0;
    right: 0;
    min-height: 100vh;
    width: 100vw;
    padding: 0;
    background: #f5f7fa;
}

.main-content {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.action-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.action-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #e5e5e5;
}

.action-card:hover {
    transform: translateY(-5px);
    border-color: #1cb0f6;
    box-shadow: 0 8px 20px rgba(28, 176, 246, 0.1);
}

.card-icon {
    width: 60px;
    height: 60px;
    background: #f0f8ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.card-icon svg {
    font-size: 24px;
    color: #1cb0f6;
}

.action-card h3 {
    color: #333;
    margin: 0 0 0.5rem;
}

.action-card p {
    color: #666;
    margin: 0;
    font-size: 0.9rem;
}

.icon-button {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.3s;
}

.icon-button:hover {
    color: #1cb0f6;
}

/* Modal Styles */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    width: 90%;
    max-width: 400px;
}

.modal-content h2 {
    margin: 0 0 1.5rem;
    color: #2c3e50;
}

.modal-content input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn-primary {
    background: #3498db;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
}

.btn-secondary {
    background: #eee;
    color: #333;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
}

/* Override toast styles for RTL */
:deep(.Vue-Toastification__toast) {
    font-family: "Vazirmatn FD", sans-serif;
}
</style>