<template>
    <div class="dashboard">
        <TopBar :user="user" />

        <!-- Main Content -->
        <div class="main-content">
            
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <div>
                        <div class="profile-photo-container" @click="triggerFileInput">
                            <div class="profile-photo-wrapper">
                                <img :src="user.avatar || defaultAvatar" :key="user.avatar" alt="تصویر پروفایل"
                                    class="profile-photo" @error="handleImageError" />
                                <div class="photo-overlay" :class="{ 'uploading': isUploading }">
                                    <template v-if="!isUploading">
                                        <font-awesome-icon icon="fa-solid fa-camera" />
                                        <span>تغییر تصویر</span>
                                    </template>
                                    <div v-else class="upload-progress">
                                        <svg class="progress-ring" width="60" height="60">
                                            <circle class="progress-ring-circle" :stroke-dasharray="circumference"
                                                :stroke-dashoffset="dashOffset" stroke-width="4" stroke="#fff"
                                                fill="transparent" r="26" cx="30" cy="30" />
                                        </svg>
                                        <span class="progress-text">{{ uploadProgress }}%</span>
                                    </div>
                                </div>
                            </div>
                            <input type="file" ref="fileInput" @change="handlePhotoUpload" accept="image/*"
                                class="hidden" />
                        </div>
                    </div>
                    <h1>{{ user.name }}</h1>
                    <div class="level-indicator">
                        <span class="level">سطح {{ Math.floor((user.score || 0) / 10) + 1 }}</span>
                    </div>
                </div>

                <div class="progress-bar">
                    <div class="progress" :style="{ width: `${(user.heart_score || 0)}%` }"></div>
                </div>
            </div>

            <!-- Badges Section -->
            <div class="badges-section">
                <h3 class="section-title">
                    <font-awesome-icon icon="fa-solid fa-award" />
                    نشان‌های من
                </h3>
                <div class="badges-grid">
                    <Badge title="تازه‌کار" description="اولین ذکر را ثبت کنید" icon="fa-solid fa-star"
                        :earned="user.badges?.beginner" :earned-date="user.badges?.beginner_date" />
                    <Badge title="پرتلاش" description="۱۰۰ ذکر ثبت کنید" icon="fa-solid fa-fire"
                        :earned="user.badges?.hardworker" :earned-date="user.badges?.hardworker_date"
                        :progress="user.total_dhikrs || 0" :target="100" />
                    <Badge title="مداوم" description="۷ روز پشت سر هم ذکر ثبت کنید" icon="fas fa-calendar-check"
                        :earned="user.badges?.consistent" :earned-date="user.badges?.consistent_date"
                        :progress="user.streak || 0" :target="7" />
                    <Badge title="قلب طلایی" description="به امتیاز قلب ۱۰۰ برسید" icon="fas fa-heart"
                        :earned="user.badges?.golden_heart" :earned-date="user.badges?.golden_heart_date"
                        :progress="user.heart_score || 0" :target="100" />
                </div>
            </div>

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
                    <input v-model="newName" type="text" placeholder="نام جدید" />
                    <div class="modal-actions">
                        <button @click="changeName" class="btn-primary">ذخیره</button>
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

export default {
    components: {
        TopBar,
        Badge,
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
            defaultAvatar: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNFMkU4RjAiLz48cGF0aCBkPSJNMTAwIDEwNUM4NS4wMzggMTA1IDczIDkyLjk2MiA3MyA3OEM3MyA2My4wMzggODUuMDM4IDUxIDEwMCA1MUMxMTQuOTYyIDUxIDEyNyA2My4wMzggMTI3IDc4QzEyNyA5Mi45NjIgMTE0Ljk2MiAxMDUgMTAwIDEwNVoiIGZpbGw9IiM5NEEzQjgiLz48cGF0aCBkPSJNMTY1IDE2NS41QzE2NSAxNjUuNSAxNTQuNSAxMzUgMTAwIDEzNUM0NS41IDEzNSAzNSAxNjUuNSAzNSAxNjUuNVYxODBIMTY1VjE2NS41WiIgZmlsbD0iIzk0QTNCOCIvPjwvc3ZnPg==',
            isUploading: false,
            uploadProgress: 0,
            circumference: 2 * Math.PI * 26,
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
        dashOffset() {
            return this.circumference * (1 - this.uploadProgress / 100);
        }
    },
    methods: {
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
        triggerFileInput() {
            this.$refs.fileInput.click();
        },
        async handlePhotoUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type and size
            if (!file.type.startsWith('image/')) {
                this.toast.error('لطفا یک تصویر انتخاب کنید');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                this.toast.error('حجم تصویر باید کمتر از ۵ مگابایت باشد');
                return;
            }

            this.isUploading = true;
            this.uploadProgress = 0;
            const formData = new FormData();
            formData.append('avatar', file);

            try {
                const response = await axios.post(`${BASE_API_URL}/user/avatar`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        Authorization: `Bearer ${this.$store.state.token}`,
                    },
                    onUploadProgress: (progressEvent) => {
                        this.uploadProgress = Math.round(
                            (progressEvent.loaded * 100) / progressEvent.total
                        );
                    }
                });

                if (response.data.success) {
                    this.user = {
                        ...this.user,
                        avatar: response.data.avatar_url
                    };
                    this.$store.commit('setUser', this.user);
                    this.toast.success('تصویر پروفایل با موفقیت بروزرسانی شد');
                }
            } catch (error) {
                const errorMessage = error.response?.data?.message || 'خطا در آپلود تصویر';
                this.toast.error(errorMessage);
                console.error('Error uploading avatar:', error);
            } finally {
                this.isUploading = false;
                this.uploadProgress = 0;
            }
        },
        closeModals() {
            this.isChangeNameModalOpen = false;
            this.isChangePasswordModalOpen = false;
            this.isLogoutModalOpen = false;
        },
        handleImageError(e) {
            e.target.src = this.defaultAvatar;
        },
    },
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

.profile-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.profile-header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-bottom: 1.5rem;
}

.profile-photo-container{
    margin-left: 16px;
}

.level-indicator {
    margin-right: 16px;
    background: #1cb0f6;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: bold;
}

.progress-bar {
    height: 12px;
    background: #e5e5e5;
    border-radius: 6px;
    overflow: hidden;
}

.progress {
    height: 100%;
    background: linear-gradient(90deg, #1cb0f6, #58cc02);
    transition: width 0.3s ease;
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

@media (max-width: 768px) {
    .top-bar {
        padding: 1rem;
    }

    .main-content {
        margin: 1rem auto;
    }

    .profile-card {
        padding: 1.5rem;
    }
}

.profile-photo-wrapper {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.profile-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
    color: white;
    cursor: pointer;
}

.photo-overlay svg {
    font-size: 24px;
    margin-bottom: 8px;
}

.profile-photo-wrapper:hover .photo-overlay {
    opacity: 1;
}

.profile-photo-wrapper:hover .profile-photo {
    transform: scale(1.1);
}

.upload-progress {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-ring {
    transform: rotate(-90deg);
}

.progress-ring-circle {
    transition: stroke-dashoffset 0.3s;
}

.progress-text {
    position: absolute;
    font-size: 14px;
    font-weight: bold;
}

.photo-overlay.uploading {
    opacity: 1;
    background: rgba(0,0,0,0.7);
}

/* Override toast styles for RTL */
:deep(.Vue-Toastification__toast) {
    font-family: "Vazirmatn FD", sans-serif;
}

.badges-section {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 0 1.5rem;
    color: #2c3e50;
}

.section-title svg {
    color: #f1c40f;
}

.badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .badges-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>