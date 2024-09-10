<style scoped>
.counter-button {
    width: 280px;
    aspect-ratio: 1/1;
    background-color: #9C8466;
    background-image: url("../assets/images/counterButton.svg");
    background-repeat: no-repeat;
    background-size: cover;
    border-radius: 50%;
    position: relative;
    z-index: 1;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-left: auto;
    margin-right: auto;
    margin-top: 32px;
    cursor: pointer;
}

.counter-label {
    font-weight: 700;
    font-size: 96px;
    color: #ffffff;
    text-align: center;
    user-select: none;
}
</style>

<template>
    <div class="counter-button" @click="this.count()">
        <span class="counter-label">{{ counter }}</span>
    </div>
</template>

<script>
import tapSound from "@/assets/audios/click.mp3"

export default {
    data() {
        return {
            counter: 0,
            tapSoundAudioPath: tapSound,
        };
    },
    methods: {
        count: function () {
            this.counter++;
            this.playAudio(this.tapSoundAudioPath);
        },
        playAudio: function (audioPath) {
            const audio = new Audio(audioPath);
            audio.play();
        },
        handleKeydown: function (event) {
            if (event.code === 'Space') {
                this.count();
            }
        }
    },
    mounted() {
        window.addEventListener('keydown', this.handleKeydown);
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.handleKeydown);
    },
    name: 'CounterButton'
}
</script>
