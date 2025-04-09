<template>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="user-brief">
            <img :src="user.avatar || defaultAvatar" :key="user.avatar" @error="handleImageError" class="mini-avatar" />
            <span class="user-name">{{ user.name }}</span>
        </div>
        <div class="stats">
            <div class="stat-item score-item">
                <font-awesome-icon icon="fa-solid fa-trophy" class="trophy-icon" />
                <span class="score-value">{{ user.score || 0 }}</span>
            </div>
            <div class="stat-item text-danger">
                <Heart3D :score="user.heart_score || 0" />
                <span>{{ user.heart_score ?? 0 }}</span>
            </div>
            <div class="stat-item text-danger">
                <StreakIndicator :streak="userStreak" />
            </div>
            <button @click="$emit('open-logout-modal')" class="logout-button">
                <font-awesome-icon icon="fa-solid fa-sign-out-alt" />
                <span>خروج</span>
            </button>
        </div>
    </div>
</template>

<style>
.top-bar {
    background: white;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 100;
}

.user-brief {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.mini-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.stats {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #e70b28;
}

.score-item {
    color: #ffc107;
    /* Gold color for score */
    background: rgba(255, 193, 7, 0.1);
    padding: 0.5rem 0.8rem;
    border-radius: 8px;
}

.trophy-icon {
    font-size: 1.1em;
    color: #ffc107;
}

.score-value {
    color: #333;
}

.logout-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #fff1f1;
    color: #ff4b4b;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: inherit;
}

.logout-button:hover {
    background: #ffe5e5;
    transform: translateY(-1px);
}

.logout-button svg {
    font-size: 1.1em;
}

.logout-modal {
    max-width: 360px;
}

.modal-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.logout-icon {
    font-size: 1.5rem;
    color: #ff4b4b;
}

.modal-header h2 {
    color: #ff4b4b;
    margin: 0;
}

.logout-modal p {
    color: #666;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

.btn-danger {
    background: #ff4b4b;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.btn-danger:hover {
    background: #ff3333;
    transform: translateY(-1px);
}

.btn-danger svg {
    font-size: 1.1em;
}

@media (max-width: 768px) {
    .logout-button span {
        display: none;
    }

    .logout-button {
        padding: 0.5rem;
    }
}
</style>

<script>
import Heart3D from '@/components/Heart3D.vue';
import StreakIndicator from '@/components/Admin/StreakIndicator.vue';

export default {
    name: 'TopBar',
    components: {
        Heart3D,
        StreakIndicator
    },
    props: {
        user: {
            type: Object,
            required: true
        },
        defaultAvatar: {
            type: String,
            default: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNFMkU4RjAiLz48cGF0aCBkPSJNMTAwIDEwNUM4NS4wMzggMTA1IDczIDkyLjk2MiA3MyA3OEM3MyA2My4wMzggODUuMDM4IDUxIDEwMCA1MUMxMTQuOTYyIDUxIDEyNyA2My4wMzggMTI3IDc4QzEyNyA5Mi45NjIgMTE0Ljk2MiAxMDUgMTAwIDEwNVoiIGZpbGw9IiM5NEEzQjgiLz48cGF0aCBkPSJNMTY1IDE2NS41QzE2NSAxNjUuNSAxNTQuNSAxMzUgMTAwIDEzNUM0NS41IDEzNSAzNSAxNjUuNSAzNSAxNjUuNVYxODBIMTY1VjE2NS41WiIgZmlsbD0iIzk0QTNCOCIvPjwvc3ZnPg=='
        }
    },
    emits: ['open-logout-modal'], // Define the emit
    methods: {
        // Remove openLogoutModal method as we're now emitting an event
        handleImageError(e) {
            e.target.src = this.defaultAvatar;
        }
    }
}
</script>