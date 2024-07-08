<style scoped>
a {
    text-decoration: none;
}

.container {
    position: fixed;
    top: 50%;
    transform: translateY(-50%);
}

#congrats-card {
    background-color: rgba(255, 255, 255, 0.67);
    border-radius: 16px;
    box-shadow: 0 4px 4px rgba(0, 0, 0, 0.25);
    width: 100%;
    min-height: 50vh;
    max-height: 461px;
    padding: 66px 16px 16px 16px;
    text-align: center;
    position: relative;
}

.balloon {
    width: 100px;
    height: 100px;
    position: absolute;
    margin: auto;
    top: -50px;
    left: 0;
    right: 0;
}

.card-title {
    font-size: 36px;
    font-weight: 900;
    color: #9C8466;
    margin-bottom: 16px;
}

.card-description {
    font-size: 20px;
    font-weight: 300;
    color: #1E1E1E;
    margin-bottom: 32px;
}

.button-primary-outline {
    font-size: 20px;
    font-weight: 700;
    color: #2F2F2F;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 2px solid #9C8466;
    border-radius: 8px;
    height: 85px;
    width: 100%;
    margin-bottom: 8px;
}

.button-primary {
    font-size: 20px;
    font-weight: 700;
    color: #F0E9DF;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #A79277;
    border-radius: 8px;
    height: 85px;
    width: 100%;
}
</style>

<template>
    <Header title="اذکار نور" description="پلتفرم فارسی اذکار و ادعیه اسلامی" />

    <main class="container">
        <section id="congrats-card">
            <img src="@/assets/images/balloon.png" class="balloon" alt="تبریک پایان ذکر">
            <h1 class="card-title">بارک الله فیکم</h1>
            <p class="card-description">
                خیلی هم عالی👍 فراموش نکن که یاد الله آرام بخش دل هاست. حالا که اذکارت رو خوندی برو به کارهات برس. خدا
                به همراه🌹
            </p>
            <RouterLink to="/">
                <div class="button-primary-outline">
                    میخوام یه مقدار ذکر بگم
                </div>
            </RouterLink>
            <RouterLink to="/">
                <div class="button-primary">
                    رفتن به صفحه اصلی
                </div>
            </RouterLink>
        </section>
    </main>

</template>

<script>
import Header from '@/components/Header.vue';

export default {
    components: {
        Header,
    }
}
</script>
