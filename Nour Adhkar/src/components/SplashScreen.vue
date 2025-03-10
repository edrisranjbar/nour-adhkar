<template>
    <div class="splash-screen">
        <div>
            <img src="@/assets/icons/android-chrome-192x192.png" alt="App Logo" class="splash-logo" />
            <p class="splash-title">
                اذکار
                <strong>حرفه‌ای</strong>
            </p>
            <div class="progress-container">
                <div class="progress-bar" :style="{ width: progress + '%' }"></div>
            </div>
            <p class="loading-text">
                درحال بارگذاری<span class="dots">...</span>
            </p>
        </div>
        <div>
            <p class="version-number">
                نسخه آزمایشی ۰.۱
            </p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SplashScreen',
    props: {
        progress: {
            type: Number,
            required: true,
        },
    }
};
</script>

<style scoped>

.splash-screen {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: #D1BB9E;
    text-align: center;
}

.splash-title{
    font-size: 48px;
    font-weight: 400;
    color: #9C8466;
    margin-bottom: 40px;
}

.splash-logo {
    width: 130px;
    height: auto;
    margin-bottom: 24px;
}

.progress-container {
    width: 100%;
    height: 16px;
    background-color: #b09b81;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 8px;
}

.progress-bar {
    height: 100%;
    background-color: #907554;
    transition: width 0.1s ease;
}

.loading-text {
    font-size: 24px;
    color: #9c8466;
    margin-bottom: 40px;
}

.dots {
    display: inline-block;
    width: 0.75em;
    overflow: hidden;
    vertical-align: bottom;
    animation: typing 6s 3;
    animation-fill-mode: both;
}

@keyframes typing {
    0% {
        width: 0;
    }

    50% {
        width: 0.75em;
    }

    100% {
        width: 0;
    }
}

.version-number {
    font-size: 20px;
    font-weight: 100;
    text-align: center;
    margin-bottom: 16px;
    position: absolute;
    bottom: 16px;
    left: 0;
    right: 0;
}
</style>