<template>
    <div class="streak-indicator" :title="'تداوم: ' + streak + ' روز'">
        <font-awesome-icon icon="fa-solid fa-fire" class="streak-icon" />
        <span class="streak-count">{{ streak }}</span>
    </div>
</template>

<script>
export default {
    name: 'StreakIndicator',
    props: {
        streak: {
            type: Number,
            default: 0
        }
    }
}
</script>

<style scoped>
.streak-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.streak-indicator:hover {
    background: var(--surface-color-hover);
}

.streak-icon {
    width: 20px;
    height: 20px;
    color: orange;
}

.streak-count {
    font-weight: bold;
    color: var(--primary-color);
    font-size: 1rem;
}

@media (max-width: 480px) {
    .streak-indicator {
        padding: 6px 10px;
    }

    .streak-icon {
        width: 18px;
        height: 18px;
    }

    .streak-count {
        font-size: 0.9rem;
    }
}
</style> 