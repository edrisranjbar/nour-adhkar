<template>
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
                    <input type="file" ref="fileInput" @change="handlePhotoUpload" accept="image/*" class="hidden" />
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
</template>

<script>
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import { useToast } from "vue-toastification";

export default {
    name: 'ProfileCard',
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    setup() {
        const toast = useToast();
        return { toast }
    },
    data() {
        return {
            defaultAvatar: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNFMkU4RjAiLz48cGF0aCBkPSJNMTAwIDEwNUM4NS4wMzggMTA1IDczIDkyLjk2MiA3MyA3OEM3MyA2My4wMzggODUuMDM4IDUxIDEwMCA1MUMxMTQuOTYyIDUxIDEyNyA2My4wMzggMTI3IDc4QzEyNyA5Mi45NjIgMTE0Ljk2MiAxMDUgMTAwIDEwNVoiIGZpbGw9IiM5NEEzQjgiLz48cGF0aCBkPSJNMTY1IDE2NS41QzE2NSAxNjUuNSAxNTQuNSAxMzUgMTAwIDEzNUM0NS41IDEzNSAzNSAxNjUuNSAzNSAxNjUuNVYxODBIMTY1VjE2NS41WiIgZmlsbD0iIzk0QTNCOCIvPjwvc3ZnPg==',
            isUploading: false,
            uploadProgress: 0,
            circumference: 2 * Math.PI * 26,
        }
    },
    computed: {
        dashOffset() {
            return this.circumference * (1 - this.uploadProgress / 100);
        }
    },
    methods: {
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
        handleImageError(e) {
            e.target.src = this.defaultAvatar;
        },
    }
}
</script>

<style scoped>
.profile-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
}

.profile-header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-bottom: 1.5rem;
}

.profile-photo-container {
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

.profile-photo-wrapper {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
    background: rgba(0, 0, 0, 0.5);
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
    background: rgba(0, 0, 0, 0.7);
}

@media (max-width: 768px) {
    .profile-card {
        padding: 1.5rem;
    }
}
</style>