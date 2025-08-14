<template>
  <div class="admin-analytics">
    <div class="header">
      <h1 class="title">آمار و تحلیل</h1>
      <div class="actions">
        <select v-model="days" @change="fetchAll" class="days-select">
          <option :value="7">۷ روز</option>
          <option :value="14">۱۴ روز</option>
          <option :value="30">۳۰ روز</option>
          <option :value="60">۶۰ روز</option>
        </select>
        <button class="refresh-btn" @click="fetchAll">بروزرسانی</button>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="label">بازدید صفحات</div>
        <div class="value">{{ formatNumber(totalVisits) }}</div>
      </div>
      <div class="stat-card">
        <div class="label">بازدیدکنندگان یکتا</div>
        <div class="value">{{ formatNumber(uniqueVisitors) }}</div>
      </div>
      <div class="stat-card">
        <div class="label">میانگین روزانه</div>
        <div class="value">{{ formatNumber(avgPerDay) }}</div>
      </div>
      <div class="stat-card">
        <div class="label">تعداد صفحات برتر</div>
        <div class="value">{{ formatNumber(topPages.length) }}</div>
      </div>
    </div>

    <div class="charts-grid">
      <div class="chart-card">
        <h2 class="chart-title">روند بازدید روزانه</h2>
        <canvas ref="dailyChart" height="120"></canvas>
      </div>
      <div class="chart-card">
        <h2 class="chart-title">مرورگرها</h2>
        <canvas ref="browsersChart" height="120"></canvas>
      </div>
      <div class="chart-card full">
        <h2 class="chart-title">کشورها</h2>
        <canvas ref="countriesChart" height="120"></canvas>
      </div>
    </div>

    <div class="top-pages">
      <h2 class="section-title">پر بازدیدترین صفحات</h2>
      <table class="data-table" v-if="topPages.length">
        <thead>
          <tr>
            <th>صفحه</th>
            <th>بازدید</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(p, i) in topPages" :key="i">
            <td class="path">{{ p.path }}</td>
            <td class="count">{{ formatNumber(p.visits) }}</td>
          </tr>
        </tbody>
      </table>
      <div class="empty" v-else>داده‌ای برای نمایش وجود ندارد.</div>
    </div>
  </div>
  
</template>

<script>
import axios from 'axios';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  BarElement,
  ArcElement,
  CategoryScale,
  LinearScale
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, BarElement, ArcElement, CategoryScale, LinearScale);

export default {
  name: 'AdminAnalyticsView',
  data() {
    return {
      loading: false,
      days: 14,
      daily: [],
      topPages: [],
      browsers: [],
      countries: [],
      totalVisits: 0,
      uniqueVisitors: 0,
      _charts: {}
    };
  },
  computed: {
    avgPerDay() {
      if (!this.daily.length) return 0;
      const sum = this.daily.reduce((s, d) => s + (d.visits || 0), 0);
      return Math.round(sum / this.daily.length);
    }
  },
  mounted() {
    this.fetchAll();
  },
  methods: {
    async fetchAll() {
      this.loading = true;
      try {
        const { data } = await axios.get('admin/analytics/overview', { params: { days: this.days } });
        if (data && data.success) {
          const a = data.data;
          this.daily = a.daily || [];
          this.topPages = a.topPages || [];
          this.browsers = a.browsers || [];
          this.countries = a.countries || [];
          this.totalVisits = a.total || 0;
          this.uniqueVisitors = a.uniqueVisitors || 0;
          this.$nextTick(() => this.renderCharts());
        }
      } catch (e) {
        // Silently fail; page remains empty
      } finally {
        this.loading = false;
      }
    },

    renderCharts() {
      this.renderDailyChart();
      this.renderBrowsersChart();
      this.renderCountriesChart();
    },

    renderDailyChart() {
      const ctx = this.$refs.dailyChart?.getContext('2d');
      if (!ctx) return;
      const labels = this.daily.map(d => new Date(d.date).toLocaleDateString('fa-IR'));
      const data = this.daily.map(d => d.visits);
      this.createOrUpdateChart('daily', ctx, 'line', labels, [{
        label: 'بازدید روزانه',
        data,
        borderColor: '#4a6fa5',
        backgroundColor: 'rgba(74,111,165,0.15)',
        tension: 0.3,
        fill: true,
        pointRadius: 2
      }]);
    },

    renderBrowsersChart() {
      const ctx = this.$refs.browsersChart?.getContext('2d');
      if (!ctx) return;
      const labels = this.browsers.map(b => b.browser);
      const data = this.browsers.map(b => b.visits);
      const colors = ['#4a6fa5', '#A79277', '#28a745', '#fd7e14', '#ef4444', '#0ea5e9', '#8b5cf6'];
      this.createOrUpdateChart('browsers', ctx, 'doughnut', labels, [{
        label: 'مرورگرها',
        data,
        backgroundColor: labels.map((_, i) => colors[i % colors.length])
      }]);
    },

    renderCountriesChart() {
      const ctx = this.$refs.countriesChart?.getContext('2d');
      if (!ctx) return;
      const labels = this.countries.map(c => c.code);
      const data = this.countries.map(c => c.visits);
      this.createOrUpdateChart('countries', ctx, 'bar', labels, [{
        label: 'کشورها',
        data,
        backgroundColor: '#A79277'
      }], {
        indexAxis: 'y'
      });
    },

    createOrUpdateChart(key, ctx, type, labels, datasets, extraOptions = {}) {
      if (this._charts[key]) {
        this._charts[key].data.labels = labels;
        this._charts[key].data.datasets = datasets;
        this._charts[key].update();
        return;
      }

      this._charts[key] = new ChartJS(ctx, {
        type,
        data: { labels, datasets },
        options: Object.assign({
          responsive: true,
          plugins: { legend: { position: 'top' }, title: { display: false } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }, extraOptions)
      });
    },

    formatNumber(num) {
      return new Intl.NumberFormat('fa-IR').format(num || 0);
    }
  }
};
</script>

<style scoped>
.admin-analytics {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}

.title { font-size: 1.6rem; color: var(--admin-text); margin: 0; }

.actions { display: flex; gap: 0.5rem; }
.days-select, .refresh-btn { padding: 0.4rem 0.6rem; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.stat-card {
  background: var(--admin-surface);
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.stat-card .label { color: var(--admin-muted); font-size: 0.9rem; }
.stat-card .value { color: var(--admin-text); font-size: 1.4rem; font-weight: 600; }

.charts-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}
.chart-card { background: var(--admin-surface); border-radius: 8px; padding: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.chart-card.full { grid-column: 1 / -1; }
.chart-title { margin: 0 0 0.75rem; color: var(--admin-text); font-size: 1.1rem; }

.top-pages { margin-top: 1.25rem; background: var(--admin-surface); border-radius: 8px; padding: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.section-title { margin: 0 0 0.75rem; color: var(--admin-text); font-size: 1.1rem; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { text-align: right; border-bottom: 1px solid var(--admin-border); padding: 0.5rem; color: var(--admin-text); }
.path { direction: ltr; font-family: monospace; }
.empty { color: var(--admin-muted); text-align: center; padding: 1.25rem; }

@media (max-width: 992px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .charts-grid { grid-template-columns: 1fr; }
}

/* Dark mode */
/* Dark mode handled via admin variables */
</style>


