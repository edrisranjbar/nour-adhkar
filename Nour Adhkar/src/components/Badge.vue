<template>
    <div class="badge" :class="{ 'earned': earned }">
        <div class="badge-icon" :class="{ 'shine-effect': earned }">
            <font-awesome-icon :icon="icon" />
            <div class="shine"></div>
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
            <span v-if="earned" class="earned-date">
                <font-awesome-icon icon="fa-solid fa-check-circle" class="check-icon" />
                کسب شده در {{ formatDate(earnedDate) }}
            </span>
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
            required: true
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
    padding: 1.5rem;
    background: white;
    border-radius: 16px;
    transition: all 0.3s ease;
    border: 2px solid #e5e5e5;
    position: relative;
    overflow: hidden;
}

.badge:not(.earned) {
    opacity: 0.7;
    background: #f8fafc;
}

.badge.earned {
    border-color: #58cc02;
    background: linear-gradient(to right, #ffffff, #f0fff4);
    box-shadow: 0 4px 12px rgba(88, 204, 2, 0.1);
}

.badge.earned:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(88, 204, 2, 0.15);
}

.badge-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: #f0f8ff;
    transition: all 0.3s ease;
}

.badge:not(.earned) .badge-icon {
    background: #f1f5f9;
}

.badge.earned .badge-icon {
    background: #58cc02;
    box-shadow: 0 0 20px rgba(88, 204, 2, 0.2);
}

.badge-icon svg {
    font-size: 24px;
    color: #94a3b8;
    transition: all 0.3s ease;
    z-index: 1;
}

.badge.earned .badge-icon svg {
    color: white;
}

/* Shine effect */
.shine-effect {
    position: relative;
    overflow: hidden;
}

.shine {
    position: absolute;
    top: -100%;
    left: -100%;
    width: 250%;
    height: 250%;
    background: linear-gradient(
        45deg,
        transparent 30%,
        rgba(255, 255, 255, 0.4) 40%,
        rgba(255, 255, 255, 0.6) 50%,
        rgba(255, 255, 255, 0.4) 60%,
        transparent 70%
    );
    transform: rotate(45deg);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% {
        top: -100%;
        left: -100%;
    }
    20% {
        top: 100%;
        left: 100%;
    }
    100% {
        top: 100%;
        left: 100%;
    }
}

.badge-info {
    flex: 1;
}

.badge-info h4 {
    margin: 0 0 0.25rem;
    color: #2c3e50;
    font-size: 1.1rem;
    transition: color 0.3s ease;
}

.badge.earned .badge-info h4 {
    color: #58cc02;
}

.badge-info p {
    margin: 0;
    color: #64748b;
    font-size: 0.9rem;
    line-height: 1.4;
}

.badge-progress {
    margin-top: 0.75rem;
}

.progress-bar {
    height: 6px;
    background: #e2e8f0;
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
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.75rem;
    font-size: 0.85rem;
    color: #58cc02;
    font-weight: 500;
}

.check-icon {
    font-size: 1em;
}

/* Hover effects */
.badge:hover .badge-icon {
    transform: scale(1.05);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .badge {
        padding: 1rem;
    }

    .badge-icon {
        width: 48px;
        height: 48px;
    }

    .badge-icon svg {
        font-size: 20px;
    }
}
</style>
