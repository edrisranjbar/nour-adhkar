<template>
    <div class="badge" :class="{ 'locked': !earned }">
        <div class="badge-icon">
            <font-awesome-icon :icon="icon" />
        </div>
        <div class="badge-info">
            <h4>{{ title }}</h4>
            <p>{{ description }}</p>
            <div class="badge-progress" v-if="!earned && progress !== undefined">
                <div class="progress-bar">
                    <div class="progress" :style="{ width: `${progressPercentage}%` }"></div>
                </div>
                <span class="progress-text">{{ progress }}/{{ target }}</span>
            </div>
            <span v-if="earned" class="earned-date">کسب شده در {{ formatDate(earnedDate) }}</span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Badge',
    props: {
        title: {
            type: String,
            required: true
        },
        description: {
            type: String,
            required: true
        },
        icon: {
            type: String,
            default: 'fa-solid fa-star'
        },
        earned: {
            type: Boolean,
            default: false
        },
        earnedDate: {
            type: String,
            default: null
        },
        progress: {
            type: Number,
            default: undefined
        },
        target: {
            type: Number,
            default: undefined
        }
    },
    computed: {
        progressPercentage() {
            if (this.progress === undefined || this.target === undefined) return 0;
            return Math.min((this.progress / this.target) * 100, 100);
        }
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            // Format date to Persian style
            return new Intl.DateTimeFormat('fa-IR').format(date);
        }
    }
}
</script>

<style scoped>
.badge {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    transition: transform 0.2s ease;
    border: 2px solid #e5e5e5;
}

.badge:not(.locked):hover {
    transform: translateY(-2px);
    border-color: #1cb0f6;
    box-shadow: 0 4px 12px rgba(28, 176, 246, 0.1);
}

.badge.locked {
    opacity: 0.7;
}

.badge-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #f0f8ff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.badge-icon svg {
    font-size: 24px;
    color: #1cb0f6;
}

.locked .badge-icon svg {
    color: #94a3b8;
}

.badge-info {
    flex: 1;
}

.badge-info h4 {
    margin: 0 0 0.25rem;
    color: #2c3e50;
    font-size: 1rem;
}

.badge-info p {
    margin: 0;
    color: #64748b;
    font-size: 0.875rem;
}

.badge-progress {
    margin-top: 0.5rem;
}

.progress-bar {
    height: 6px;
    background: #e5e5e5;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 0.25rem;
}

.progress {
    height: 100%;
    background: linear-gradient(90deg, #1cb0f6, #58cc02);
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.75rem;
    color: #64748b;
}

.earned-date {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: #58cc02;
}
</style>
