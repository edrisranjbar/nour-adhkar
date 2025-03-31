<template>
    <div class="dashboard">
        <div class="profile-section">
            <div class="profile-photo-container" @click="triggerFileInput">
                <img 
                    :src="user.avatar || defaultAvatar" 
                    :key="user.avatar"
                    alt="تصویر پروفایل" 
                    class="profile-photo" 
                    @error="handleImageError"
                />
                <div class="photo-overlay" :class="{ 'uploading': isUploading }">
                    <span v-if="!isUploading">تغییر تصویر</span>
                    <div v-else class="upload-progress">
                        <div class="spinner"></div>
                        <span>در حال آپلود...</span>
                    </div>
                </div>
                <input 
                    type="file" 
                    ref="fileInput" 
                    @change="handlePhotoUpload" 
                    accept="image/*" 
                    class="hidden"
                />
            </div>
            
            <div class="user-info">
                <h1>{{ user.name }}</h1>
                <p class="email">{{ user.email }}</p>
                <div class="score-container">
                    <span class="heart-icon" :style="heartIconStyle">❤️</span>
                    <span class="score">{{ user.heart_score ?? 0 }}/100</span>
                </div>
            </div>
        </div>

        <div class="actions-grid">
            <button @click="openChangeNameModal" class="action-card">
                <i class="fas fa-user"></i>
                <span>تغییر نام</span>
            </button>
            <button @click="openChangePasswordModal" class="action-card">
                <i class="fas fa-lock"></i>
                <span>تغییر رمز عبور</span>
            </button>
            <button @click="logout" class="action-card logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>خروج</span>
            </button>
        </div>

        <!-- Modals -->
        <div v-if="isChangeNameModalOpen || isChangePasswordModalOpen" class="modal-backdrop" @click="closeModals">
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
            defaultAvatar: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNFMkU4RjAiLz48cGF0aCBkPSJNMTAwIDEwNUM4NS4wMzggMTA1IDczIDkyLjk2MiA3MyA3OEM3MyA2My4wMzggODUuMDM4IDUxIDEwMCA1MUMxMTQuOTYyIDUxIDEyNyA2My4wMzggMTI3IDc4QzEyNyA5Mi45NjIgMTE0Ljk2MiAxMDUgMTAwIDEwNVoiIGZpbGw9IiM5NEEzQjgiLz48cGF0aCBkPSJNMTY1IDE2NS41QzE2NSAxNjUuNSAxNTQuNSAxMzUgMTAwIDEzNUM0NS41IDEzNSAzNSAxNjUuNSAzNSAxNjUuNVYxODBIMTY1VjE2NS41WiIgZmlsbD0iIzk0QTNCOCIvPjwvc3ZnPg==',
            isUploading: false,
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
        triggerFileInput() {
            this.$refs.fileInput.click();
        },
        async handlePhotoUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type and size
            if (!file.type.startsWith('image/')) {
                alert('لطفا یک تصویر انتخاب کنید');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('حجم تصویر باید کمتر از ۵ مگابایت باشد');
                return;
            }

            this.isUploading = true;
            const formData = new FormData();
            formData.append('avatar', file);

            try {
                const response = await axios.post(`${BASE_API_URL}/user/avatar`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        Authorization: `Bearer ${this.$store.state.token}`,
                    }
                });

                if (response.data.success) {
                    // Update both local user and store
                    this.user = {
                        ...this.user,
                        avatar: response.data.avatar_url
                    };
                    this.$store.commit('setUser', this.user);
                    
                    // Force image refresh by adding timestamp
                    this.user.avatar = `${response.data.avatar_url}?t=${new Date().getTime()}`;
                }
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || 'خطا در آپلود تصویر';
                alert(errorMessage);
                console.error('Error uploading avatar:', error);
            } finally {
                this.isUploading = false;
            }
        },
        closeModals() {
            this.isChangeNameModalOpen = false;
            this.isChangePasswordModalOpen = false;
        },
        handleImageError(e) {
            e.target.src = this.defaultAvatar;
        },
    },
};
</script>

<style scoped>
.dashboard {
    width: 100%;
    padding: 2rem 1rem;
    background: #ffffff;
    direction: rtl;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Add font-family to all buttons and interactive elements */
button, 
.action-card,
.btn-primary,
.btn-secondary,
.modal-content input,
.photo-overlay span {
    font-family: "Vazirmatn FD", sans-serif;
}

/* Update action cards to maintain consistent font */
.action-card span {
    font-family: "Vazirmatn FD", sans-serif;
    font-size: 0.9rem;
}

.profile-section {
    width: 100%;
    max-width: 800px; /* Or your preferred max-width */
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem 1rem;
    background: #f8f9fa;
    border-radius: 16px;
    margin: 0 auto; /* Center horizontally */
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.profile-photo-container {
    position: relative;
    width: 120px;
    height: 120px;
    margin-bottom: 1.5rem;
    cursor: pointer;
}

.profile-photo {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    opacity: 0;
    transition: opacity 0.3s;
}

.profile-photo-container:hover .photo-overlay {
    opacity: 1;
}

.user-info {
    text-align: center;
}

.user-info h1 {
    font-size: 1.5rem;
    margin: 0;
    color: #2c3e50;
}

.email {
    color: #666;
    margin: 0.5rem 0;
}

.score-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.heart-icon {
    font-size: 1.5rem;
}

.score {
    font-size: 1.2rem;
    color: #2c3e50;
    font-weight: 500;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 2rem;
}

.action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    background: #fff;
    border: 1px solid #eee;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.action-card i {
    font-size: 1.5rem;
    color: #3498db;
}

.action-card.logout i {
    color: #e74c3c;
}

.hidden {
    display: none;
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

@media (min-width: 768px) {
    .dashboard {
        padding: 3rem 2rem;
    }

    .profile-section {
        padding: 3rem;
    }
}

/* Add these new styles */
.upload-progress {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.spinner {
    width: 24px;
    height: 24px;
    border: 3px solid #ffffff;
    border-top: 3px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.photo-overlay.uploading {
    opacity: 1 !important;
    background: rgba(0,0,0,0.7);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>