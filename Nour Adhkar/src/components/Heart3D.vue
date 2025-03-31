<template>
    <div class="heart" :style="heartStyle">
        <font-awesome-icon icon="fa-solid fa-heart" class="heart-icon" />
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

.heart-icon {
    color: red;
}

</style>