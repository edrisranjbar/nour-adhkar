<template>
    <div class="heart-container">
        <div class="heart" :style="heartStyle">
            <!-- Outline heart -->
            <div class="heart-piece heart-left outline"></div>
            <div class="heart-piece heart-right outline"></div>

            <!-- Filling heart -->
            <div class="heart-fill">
                <div class="heart-piece heart-left"></div>
                <div class="heart-piece heart-right"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Heart3D',
    props: {
        score: {
            type: Number,
            default: 0,
            validator: value => value >= 0 && value <= 100 // Ensure score is between 0 and 100
        }
    },
    computed: {
        heartStyle() {
            const fillPercentage = this.score;
            return {
                '--fill-percentage': `${fillPercentage}%`,
                '--fill-color': '#ff3366',
            };
        }
    }
}
</script>

<style scoped>

.heart-container {
    position: relative;
    width: 40px;
    height: 40px;
}

.heart {
    position: relative;
    width: 100%;
    height: 100%;
    transition: all 0.3s ease;
}

.heart-piece {
    position: absolute;
    width: 50%;
    height: 80%;
    top: 0;
    border-radius: 50px 50px 0 0;
}

/* Outline styles */
.outline {
    background: transparent;
    border: 2px solid #ff3366;
}

.heart-left {
    left: 50%;
    transform: rotate(-45deg);
    transform-origin: 0 100%;
}

.heart-right {
    right: 50%;
    transform: rotate(45deg);
    transform-origin: 100% 100%;
}

/* Filling heart container */
.heart-fill {
    position: absolute;
    width: 100%;
    height: 100%;
    clip-path: polygon(0 calc(100% - var(--fill-percentage)), 100% calc(100% - var(--fill-percentage)), 100% 100%, 0 100%);
    transition: all 0.3s ease;
}

/* Filling heart pieces */
.heart-fill .heart-piece {
    background: var(--fill-color);
    border: none;
}

</style>