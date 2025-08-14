<template>
  <div class="admin-layout">
    <div class="admin-content">
      <AdminSidebar />
      <main class="admin-main">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import AdminSidebar from '@/components/Admin/AdminSidebar.vue';

export default {
  name: 'AdminLayout',
  components: {
    AdminSidebar
  },
  computed: {
    ...mapState(['user']),
    userInitials() {
      if (!this.user || !this.user.name) return '';
      return this.user.name.split(' ')
        .map(word => word[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
    }
  },
  methods: {
    ...mapActions(['logoutUser']),
    toggleSidebar() {
      document.body.classList.toggle('admin-collapsed');
    },
    async logout() {
      await this.logoutUser();
      this.$router.push('/');
    }
  },
  created() {
    if (!this.$store.getters.isAdmin) {
      this.$router.push('/');
    }
  },
  mounted() {
    document.body.classList.add('admin-page');
  },
  beforeUnmount() {
    document.body.classList.remove('admin-page');
  }
}
</script>

<style scoped>
.admin-layout {
  min-height: 100vh;
  margin: 0;
  display: flex;
  flex-direction: column;
  /* Standard dark admin palette */
  --admin-bg: #0b1220;           /* page background */
  --admin-surface: #111827;      /* cards/surfaces */
  --admin-header: #0f172a;       /* header/nav */
  --admin-border: rgba(255,255,255,0.08);
  --admin-text: #e5e7eb;         /* primary text */
  --admin-muted: #9ca3af;        /* secondary text */
  --admin-accent: #3b82f6;       /* blue accent */
}

.admin-header {
  margin-top: 0;
  border-radius: 0;
  background: var(--admin-header);
  color: var(--admin-text);
  border-bottom: 1px solid var(--admin-border);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  z-index: 100;
}

.admin-header-container {
  padding: 0.75rem 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.admin-content {
  display: flex;
  flex: 1;
}

.admin-brand {
  display: flex;
  align-items: center;
}

.admin-logo {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: var(--admin-text);
  margin-left: 1rem;
  gap: 0.75rem;
}

.logo-image {
  width: 32px;
  height: 32px;
  margin-left: 0.5rem;
  transition: transform 0.3s ease;
}

.admin-logo:hover .logo-image {
  transform: rotate(5deg) scale(1.05);
}

.logo-text {
  font-size: 1.2rem;
  font-weight: 600;
  margin-right: 0.5rem;
}

.admin-badge {
  color: #fff;
  background-color: var(--admin-accent);
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 500;
}

.admin-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-right: auto;
  margin-left: 1rem;
}

.icon-btn {
  background: rgba(255,255,255,0.06);
  border: 1px solid var(--admin-border);
  color: var(--admin-text);
  padding: 0.35rem 0.55rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.icon-btn:hover { background: rgba(255,255,255,0.12); color: #fff; }

.admin-user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #A79277;
  color: white;
  font-size: 1.2rem;
  font-weight: 600;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.admin-user-name {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-name {
  font-weight: 500;
  color: var(--admin-text);
}

.logout-button {
  background: none;
  border: none;
  color: var(--admin-muted);
  cursor: pointer;
  padding: 0;
  font-family: inherit;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  transition: color 0.2s ease;
}

.logout-button:hover {
  color: var(--admin-text);
}

.admin-main {
  flex: 1;
  padding: 1.25rem;
  background: var(--admin-bg);
  overflow-y: auto;
}

body.dark-mode .admin-main {
  background: var(--admin-bg);
}

/* Standardize admin card surfaces using deep selector to override child scoped styles */
:deep(.section),
:deep(.stat-card),
:deep(.chart-card),
:deep(.top-pages) {
  background: var(--admin-surface) !important;
  color: var(--admin-text);
  border: 1px solid var(--admin-border);
}

:deep(.data-table th),
:deep(.data-table td) {
  border-bottom: 1px solid var(--admin-border) !important;
  color: var(--admin-text);
}

/* Tables */
:deep(.users-table),
:deep(.data-table) {
  color: var(--admin-text);
}

:deep(.users-table th),
:deep(.data-table th) {
  background-color: rgba(255,255,255,0.06) !important;
  color: var(--admin-text) !important;
}

:deep(.users-table td),
:deep(.data-table td) {
  border-bottom: 1px solid var(--admin-border) !important;
}

/* Generic table theming across admin */
:deep(table) {
  color: var(--admin-text);
}

:deep(table thead th) {
  background-color: rgba(255,255,255,0.06) !important;
  color: var(--admin-text) !important;
  border-bottom: 1px solid var(--admin-border) !important;
}

:deep(table tbody tr) {
  border-bottom: 1px solid var(--admin-border) !important;
}

:deep(table tbody tr:hover) {
  background: rgba(255,255,255,0.04) !important;
}

/* Forms */
:deep(input[type="text"]),
:deep(input[type="email"]),
:deep(input[type="number"]),
:deep(input[type="password"]),
:deep(select),
:deep(textarea) {
  background: #0b1220 !important;
  color: var(--admin-text) !important;
  border: 1px solid var(--admin-border) !important;
}

:deep(input::placeholder),
:deep(textarea::placeholder) {
  color: var(--admin-muted) !important;
}

/* Modals */
:deep(.modal),
:deep(.modal-content),
:deep(.v-modal) {
  background: var(--admin-surface) !important;
  color: var(--admin-text) !important;
  border-color: var(--admin-border) !important;
}

/* Generic cards */
:deep(.card) {
  background: var(--admin-surface) !important;
  color: var(--admin-text) !important;
  border: 1px solid var(--admin-border) !important;
}

:deep(.status-badge) {
  border: 1px solid var(--admin-border);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .admin-header-container {
    padding: 0.5rem;
  }

  .logo-text {
    display: none;
  }

  .admin-badge {
    display: none;
  }

  .user-name {
    display: none;
  }

  .admin-user-info {
    gap: 0.5rem;
  }

  .user-avatar {
    width: 32px;
    height: 32px;
  }

  .admin-content {
    flex-direction: column;
  }
}
</style>