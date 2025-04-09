<template>
  <div class="profile-settings">
    <h1>تنظیمات حساب کاربری</h1>

    <div class="settings-container">
      <section class="settings-section">
        <form @submit.prevent="saveProfile" class="settings-form">
          <!-- Profile Info -->
          <div class="form-section">
            <h2>اطلاعات شخصی</h2>
            <div class="form-group">
              <label for="name">نام</label>
              <input
                id="name"
                v-model="profile.name"
                type="text"
                required
                placeholder="نام خود را وارد کنید"
              />
            </div>

            <div class="form-group">
              <label for="email">ایمیل</label>
              <input
                id="email"
                v-model="profile.email"
                type="email"
                required
                placeholder="ایمیل خود را وارد کنید"
              />
            </div>
          </div>

          <!-- Password Change (Optional) -->
          <div class="form-section">
            <div class="section-header">
              <h2>تغییر رمز عبور</h2>
              <span class="optional-text">(اختیاری)</span>
            </div>
            
            <div class="form-group">
              <label for="currentPassword">رمز عبور فعلی</label>
              <input
                id="currentPassword"
                v-model="profile.currentPassword"
                type="password"
                placeholder="رمز عبور فعلی خود را وارد کنید"
              />
            </div>

            <div class="form-group">
              <label for="newPassword">رمز عبور جدید</label>
              <input
                id="newPassword"
                v-model="profile.newPassword"
                type="password"
                placeholder="رمز عبور جدید را وارد کنید"
                minlength="8"
              />
            </div>

            <div class="form-group">
              <label for="confirmPassword">تکرار رمز عبور جدید</label>
              <input
                id="confirmPassword"
                v-model="profile.confirmPassword"
                type="password"
                placeholder="رمز عبور جدید را تکرار کنید"
              />
            </div>
          </div>

          <button type="submit" class="save-button">
            <font-awesome-icon icon="fa-solid fa-save" />
            ذخیره تغییرات
          </button>
        </form>
      </section>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProfileSettingsView',
  data() {
    return {
      profile: {
        name: '',
        email: '',
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      }
    }
  },
  methods: {
    async saveProfile() {
      try {
        // Check if password change is requested
        if (this.profile.currentPassword || this.profile.newPassword || this.profile.confirmPassword) {
          if (!this.profile.currentPassword) {
            // Show error toast: Current password is required
            return
          }
          if (!this.profile.newPassword) {
            // Show error toast: New password is required
            return
          }
          if (this.profile.newPassword !== this.profile.confirmPassword) {
            // Show error toast: Passwords don't match
            return
          }
        }

        // Update profile
        await this.$store.dispatch('updateProfile', {
          name: this.profile.name,
          email: this.profile.email
        })

        // Update password if provided
        if (this.profile.currentPassword && this.profile.newPassword) {
          await this.$store.dispatch('changePassword', {
            currentPassword: this.profile.currentPassword,
            newPassword: this.profile.newPassword
          })
          
          // Clear password fields
          this.profile.currentPassword = ''
          this.profile.newPassword = ''
          this.profile.confirmPassword = ''
        }

        // Show success toast
      } catch (error) {
        console.error('Error updating profile:', error)
        // Show error toast
      }
    },
    async loadProfile() {
      try {
        const profile = await this.$store.dispatch('loadProfile')
        this.profile.name = profile.name
        this.profile.email = profile.email
      } catch (error) {
        console.error('Error loading profile:', error)
        // Show error toast
      }
    }
  },
  async created() {
    await this.loadProfile()
  }
}
</script>

<style scoped>
.profile-settings {
  padding: 1rem;
}

.settings-container {
  display: grid;
  gap: 2rem;
  max-width: 800px;
  margin: 2rem auto;
}

.settings-section {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-section {
  margin-bottom: 2rem;
}

.form-section:last-of-type {
  margin-bottom: 1rem;
}

.section-header {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.section-header h2 {
  margin: 0;
  color: #333;
  font-size: 1.25rem;
}

.optional-text {
  color: #666;
  font-size: 0.9rem;
}

.settings-form {
  display: grid;
  gap: 1rem;
}

.form-group {
  display: grid;
  gap: 0.5rem;
}

.form-group label {
  color: #333;
  font-weight: 500;
}

.form-group input {
  padding: 0.75rem;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  font-size: 1rem;
  font-family: inherit;
}

.save-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background-color: #A79277;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.2s ease;
}

.save-button:hover {
  background-color: #8a7660;
}

body.dark-mode {
  .settings-section {
    background-color: #333;
  }

  .section-header h2,
  .form-group label {
    color: #fff;
  }

  .optional-text {
    color: #aaa;
  }

  .form-group input {
    background-color: #444;
    border-color: #555;
    color: #fff;
  }
}

@media (max-width: 768px) {
  .settings-container {
    gap: 1rem;
  }
}
</style> 