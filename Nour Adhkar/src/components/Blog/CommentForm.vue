<template>
  <div class="comment-form">
    <h3 class="form-title">ارسال نظر</h3>
    <form @submit.prevent="submitComment">
      <div v-if="!isAuthenticated" class="form-group">
        <label for="author_name">نام</label>
        <input
          type="text"
          id="author_name"
          v-model="form.author_name"
          class="form-control"
          :class="{ 'is-invalid': errors.author_name }"
          required
        />
        <div class="invalid-feedback" v-if="errors.author_name">
          {{ errors.author_name[0] }}
        </div>
      </div>

      <div v-if="!isAuthenticated" class="form-group">
        <label for="author_email">ایمیل</label>
        <input
          type="email"
          id="author_email"
          v-model="form.author_email"
          class="form-control"
          :class="{ 'is-invalid': errors.author_email }"
          required
        />
        <div class="invalid-feedback" v-if="errors.author_email">
          {{ errors.author_email[0] }}
        </div>
      </div>

      <div class="form-group">
        <label for="content">نظر شما</label>
        <textarea
          id="content"
          v-model="form.content"
          class="form-control"
          :class="{ 'is-invalid': errors.content }"
          rows="4"
          required
        ></textarea>
        <div class="invalid-feedback" v-if="errors.content">
          {{ errors.content[0] }}
        </div>
      </div>

      <button type="submit" class="submit-button" :disabled="loading">
        <span v-if="loading" class="spinner"></span>
        {{ loading ? 'در حال ارسال...' : 'ارسال نظر' }}
      </button>
    </form>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import axios from 'axios';

export default {
  name: 'CommentForm',
  props: {
    postId: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      form: {
        author_name: '',
        author_email: '',
        content: ''
      },
      errors: {},
      loading: false
    };
  },
  computed: {
    ...mapGetters(['isAuthenticated'])
  },
  methods: {
    async submitComment() {
      this.loading = true;
      this.errors = {};

      try {
        const response = await axios.post('/api/comments', {
          ...this.form,
          post_id: this.postId
        });

        this.$toast.success(response.data.message);
        this.form.content = '';
        this.$emit('comment-submitted');
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors;
        } else {
          this.$toast.error('خطا در ارسال نظر');
        }
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.comment-form {
  background-color: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.form-title {
  font-size: 1.25rem;
  margin-bottom: 1.5rem;
  color: #333;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #555;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-control:focus {
  border-color: #A79277;
  outline: none;
}

.form-control.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.submit-button {
  background-color: #A79277;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.submit-button:hover {
  background-color: #8b7a63;
}

.submit-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.spinner {
  width: 1rem;
  height: 1rem;
  border: 2px solid #fff;
  border-top: 2px solid transparent;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Dark mode styles */
body.dark-mode .comment-form {
  background-color: #2a2a2a;
}

body.dark-mode .form-title {
  color: #eee;
}

body.dark-mode .form-group label {
  color: #bbb;
}

body.dark-mode .form-control {
  background-color: #333;
  border-color: #444;
  color: #eee;
}

body.dark-mode .form-control:focus {
  border-color: #A79277;
}

body.dark-mode .submit-button {
  background-color: #A79277;
}

body.dark-mode .submit-button:hover {
  background-color: #8b7a63;
}
</style> 