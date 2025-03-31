<template>
    <div class="badges-section">
        <h3 class="section-title">
            <font-awesome-icon icon="fa-solid fa-award" />
            نشان‌های من
        </h3>
        <div class="badges-grid">
            <Badge title="تازه‌کار" description="اولین ذکر را ثبت کنید" icon="fa-solid fa-star"
                :earned="user.badges?.beginner" :earned-date="user.badges?.beginner_date" />
            <Badge title="پرتلاش" description="۱۰۰ ذکر ثبت کنید" icon="fa-solid fa-fire"
                :earned="user.badges?.hardworker" :earned-date="user.badges?.hardworker_date"
                :progress="user.total_dhikrs || 0" :target="100" />
            <Badge title="مداوم" description="۷ روز پشت سر هم ذکر ثبت کنید" icon="fas fa-calendar-check"
                :earned="user.badges?.consistent" :earned-date="user.badges?.consistent_date"
                :progress="user.streak || 0" :target="7" />
            <Badge title="قلب طلایی" description="به امتیاز قلب ۱۰۰ برسید" icon="fas fa-heart"
                :earned="user.badges?.golden_heart" :earned-date="user.badges?.golden_heart_date"
                :progress="user.heart_score || 0" :target="100" />
        </div>
    </div>
</template>

<script>
import Badge from '@/components/Badge.vue';

export default {
    name: 'BadgesSection',
    components: {
        Badge
    },
    props: {
        user: {
            type: Object,
            required: true
        }
    }
}
</script>

<style scoped>
.badges-section {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 0 1.5rem;
    color: #2c3e50;
}

.section-title svg {
    color: #f1c40f;
}

.badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .badges-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>