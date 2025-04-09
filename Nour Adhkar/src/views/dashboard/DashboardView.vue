<template>
    <div class="dashboard">
        <div class="main-content">
            <h1>داشبورد</h1>

            <!-- Stats Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <font-awesome-icon icon="fa-solid fa-pray" />
                    </div>
                    <div class="stat-content">
                        <h3>اذکار امروز</h3>
                        <p class="stat-value">{{ todayDhikrCount }}</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <font-awesome-icon icon="fa-solid fa-heart" />
                    </div>
                    <div class="stat-content">
                        <h3>اذکار مورد علاقه</h3>
                        <p class="stat-value">{{ favoriteCount }}</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <font-awesome-icon icon="fa-solid fa-calendar-check" />
                    </div>
                    <div class="stat-content">
                        <h3>روزهای متوالی</h3>
                        <p class="stat-value">{{ user?.streak || 0 }} روز</p>
                    </div>
                </div>
            </div>

            <ProfileCard :user="user" />
            <BadgesSection :user="user" />

            <!-- Profile Card and Badges -->
            <StreakCalendar :user="user" />
            <div class="quick-actions">
                <h2>دسترسی سریع</h2>
                <div class="action-buttons">
                    <RouterLink to="/dashboard/adhkar/daily" class="action-button">
                        <font-awesome-icon icon="fa-solid fa-sun" />
                        <span>اذکار روزانه</span>
                    </RouterLink>
                    <RouterLink to="/dashboard/adhkar/favorites" class="action-button">
                        <font-awesome-icon icon="fa-solid fa-heart" />
                        <span>اذکار مورد علاقه</span>
                    </RouterLink>
                    <RouterLink to="/dashboard/adhkar/custom" class="action-button">
                        <font-awesome-icon icon="fa-solid fa-edit" />
                        <span>اذکار شخصی</span>
                    </RouterLink>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <div v-if="isLogoutModalOpen" class="modal-overlay" @click.self="closeModals">
            <div class="modal-container">
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
import ProfileCard from '@/components/Admin/ProfileCard.vue';
import BadgesSection from '@/components/Admin/BadgesSection.vue';
import StreakCalendar from '@/components/StreakCalendar.vue';

export default {
    name: 'DashboardView',
    components: {
        TopBar,
        ProfileCard,
        BadgesSection,
        StreakCalendar
    },
    async mounted() {
        try {
            const response = await axios.get(`${BASE_API_URL}/user`, {
                headers: {
                    'Authorization': `Bearer ${this.$store.state.token}`,
                    'Accept': 'application/json'
                }
            });

            if (response.data) {
                this.user = response.data.user;
                this.$store.commit('setUser', response.data.user);
                this.$store.commit('setToken', this.$store.state.token);
            }

            await this.loadStats();
        } catch (error) {
            if (error.response?.status === 401) {
                this.toast.error('لطفا دوباره وارد شوید');
                this.$store.commit('clearUser');
                this.$router.push('/login');
            }
        }
    },
    setup() {
        const toast = useToast();
        return { toast }
    },
    data() {
        return {
            user: store.state.user,
            isLogoutModalOpen: false,
            todayDhikrCount: 0,
            favoriteCount: 0
        };
    },
    methods: {
        async loadStats() {
            try {
                const stats = await this.$store.dispatch('loadUserStats');
                this.todayDhikrCount = stats.todayCount || 0;
                this.favoriteCount = stats.favoriteCount || 0;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },
        closeModals() {
            this.isLogoutModalOpen = false;
        },
        async confirmLogout() {
            try {
                await this.$store.dispatch('logout');
                this.$router.push('/login');
            } catch (error) {
                console.error('Error during logout:', error);
                this.toast.error('خطا در خروج از حساب کاربری');
            }
        }
    }
}
</script>

<style scoped>
.dashboard {
    min-height: 100vh;
    background-color: #f8f9fa;
}

.main-content {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.main-content h1 {
    margin-bottom: 2rem;
    color: #333;
}

.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.stat-icon {
    width: 48px;
    height: 48px;
    background: #A79277;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-content h3 {
    margin: 0;
    font-size: 1rem;
    color: #666;
}

.stat-value {
    margin: 0.25rem 0 0;
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
}

.dashboard-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.quick-actions {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.quick-actions h2 {
    margin: 0 0 1rem;
    font-size: 1.25rem;
    color: #333;
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.action-button {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    color: #333;
    text-decoration: none;
    transition: all 0.2s ease;
}

.action-button:hover {
    background: #A79277;
    color: white;
    transform: translateY(-2px);
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-container {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    width: 90%;
    max-width: 500px;
}

.modal-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.modal-header h2 {
    margin: 0;
    color: #333;
}

.logout-icon {
    color: #dc3545;
    font-size: 1.5rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-secondary {
    padding: 0.75rem 1.5rem;
    background: #e9ecef;
    color: #333;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.btn-secondary:hover {
    background: #dee2e6;
}

@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .main-content {
        padding: 1rem;
    }

    .dashboard-stats {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        grid-template-columns: 1fr;
    }
}
</style>