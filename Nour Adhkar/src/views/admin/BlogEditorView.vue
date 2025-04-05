<template>
  <div class="blog-editor-container">
    <AppHeader :title="isEditing ? 'ویرایش مقاله' : 'ایجاد مقاله جدید'" description="مدیریت مقالات" />
    
    <main class="container">
      <div class="editor-header">
        <h2>{{ isEditing ? 'ویرایش مقاله' : 'ایجاد مقاله جدید' }}</h2>
        <div class="action-buttons">
          <button @click="saveDraft" class="save-draft-button" :disabled="saving">ذخیره پیش‌نویس</button>
          <button @click="publishPost" class="publish-button" :disabled="saving">
            {{ isEditing && post.status === 'published' ? 'بروزرسانی' : 'انتشار' }}
          </button>
          <RouterLink to="/admin/blog" class="cancel-button">انصراف</RouterLink>
        </div>
      </div>
      
      <div class="form-container">
        <div class="form-group">
          <label for="title">عنوان مقاله</label>
          <input 
            type="text" 
            id="title" 
            v-model="post.title" 
            placeholder="عنوان مقاله را وارد کنید" 
            class="form-control"
          >
        </div>
        
        <div class="form-group">
          <label for="slug">نامک (slug)</label>
          <div class="slug-input-group">
            <input 
              type="text" 
              id="slug" 
              v-model="post.slug" 
              placeholder="نامک-مقاله-بدون-فاصله" 
              class="form-control"
              :disabled="isEditing"
            >
            <button v-if="!isEditing" @click="generateSlug" class="generate-slug-btn" type="button">تولید خودکار</button>
          </div>
          <small class="form-text">استفاده از حروف انگلیسی، اعداد و خط تیره مجاز است. فاصله‌ها با خط تیره جایگزین می‌شوند.</small>
        </div>
        
        <div class="form-group">
          <label for="excerpt">خلاصه مقاله</label>
          <textarea 
            id="excerpt" 
            v-model="post.excerpt" 
            placeholder="خلاصه‌ای از مقاله را وارد کنید" 
            class="form-control"
            rows="3"
          ></textarea>
          <small class="form-text">خلاصه مقاله در صفحه اصلی بلاگ و در نتایج جستجو نمایش داده می‌شود.</small>
        </div>
        
        <div class="form-group">
          <label for="image">تصویر شاخص (URL)</label>
          <input 
            type="text" 
            id="image" 
            v-model="post.image" 
            placeholder="آدرس تصویر شاخص را وارد کنید" 
            class="form-control"
          >
          <div class="image-preview" v-if="post.image">
            <img :src="post.image" alt="Preview">
          </div>
        </div>
        
        <div class="form-group">
          <label for="content">متن مقاله</label>
          <div class="editor-toolbar">
            <button @click="insertTag('h2')" type="button" class="toolbar-btn" title="عنوان">H2</button>
            <button @click="insertTag('h3')" type="button" class="toolbar-btn" title="زیرعنوان">H3</button>
            <button @click="insertTag('p')" type="button" class="toolbar-btn" title="پاراگراف">P</button>
            <button @click="insertTag('strong')" type="button" class="toolbar-btn" title="متن پررنگ"><b>B</b></button>
            <button @click="insertTag('em')" type="button" class="toolbar-btn" title="متن مورب"><i>I</i></button>
            <button @click="insertTag('a')" type="button" class="toolbar-btn" title="لینک">🔗</button>
            <button @click="insertImage()" type="button" class="toolbar-btn" title="تصویر">🖼️</button>
            <button @click="insertList('ul')" type="button" class="toolbar-btn" title="لیست نامرتب">•</button>
            <button @click="insertList('ol')" type="button" class="toolbar-btn" title="لیست مرتب">1.</button>
            <button @click="insertTag('blockquote')" type="button" class="toolbar-btn" title="نقل قول">❝</button>
          </div>
          <textarea 
            id="content" 
            v-model="post.content" 
            placeholder="متن مقاله را وارد کنید" 
            class="form-control content-editor"
            rows="20"
            ref="contentEditor"
          ></textarea>
          <div class="editor-help">
            <p>راهنما: از HTML برای قالب‌بندی متن استفاده کنید. برای مثال:</p>
            <ul>
              <li><code>&lt;h2&gt;تیتر&lt;/h2&gt;</code> - عنوان بخش</li>
              <li><code>&lt;p&gt;پاراگراف&lt;/p&gt;</code> - متن پاراگراف</li>
              <li><code>&lt;a href="لینک"&gt;متن لینک&lt;/a&gt;</code> - لینک</li>
              <li><code>&lt;img src="آدرس تصویر" alt="توضیح"&gt;</code> - تصویر</li>
              <li><code>&lt;ul&gt;&lt;li&gt;آیتم لیست&lt;/li&gt;&lt;/ul&gt;</code> - لیست</li>
            </ul>
          </div>
        </div>
        
        <div class="form-group">
          <label for="status">وضعیت مقاله</label>
          <select id="status" v-model="post.status" class="form-control">
            <option value="draft">پیش‌نویس</option>
            <option value="published">منتشر شده</option>
          </select>
        </div>
        
        <div class="form-group" v-if="post.status === 'published'">
          <label for="published_at">تاریخ انتشار</label>
          <input 
            type="datetime-local" 
            id="published_at" 
            v-model="publishedAt" 
            class="form-control"
          >
          <small class="form-text">اگر تاریخ انتشار را تنظیم نکنید، تاریخ و زمان فعلی استفاده می‌شود.</small>
        </div>
      </div>
    </main>
    
    <!-- Toast notification -->
    <div class="toast-notification" v-if="showToast">
      <p>{{ toastMessage }}</p>
    </div>
  </div>
</template>

<script>
import AppHeader from '@/components/Header.vue';
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import { mapGetters } from 'vuex';

export default {
  components: {
    AppHeader
  },
  data() {
    return {
      post: {
        title: '',
        slug: '',
        excerpt: '',
        content: '',
        image: '',
        status: 'draft',
        published_at: null
      },
      saving: false,
      showToast: false,
      toastMessage: '',
      isEditing: false,
      postId: null
    };
  },
  computed: {
    ...mapGetters(['isAuthenticated']),
    publishedAt: {
      get() {
        if (!this.post.published_at) return '';
        return new Date(this.post.published_at).toISOString().slice(0, 16);
      },
      set(value) {
        this.post.published_at = value ? new Date(value).toISOString() : null;
      }
    }
  },
  mounted() {
    if (!this.isAuthenticated) {
      this.$router.push('/login');
      return;
    }
    
    // Check if we're editing an existing post
    const postId = this.$route.params.id;
    if (postId) {
      this.isEditing = true;
      this.postId = postId;
      this.fetchPost(postId);
    }
  },
  methods: {
    async fetchPost(id) {
      try {
        const token = this.$store.state.token;
        const response = await axios.get(`${BASE_API_URL}/admin/blog/${id}`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        
        if (response.data.success) {
          this.post = response.data.post;
        } else {
          this.showToastMessage('خطا در دریافت اطلاعات مقاله');
        }
      } catch (error) {
        console.error('Error fetching post:', error);
        this.showToastMessage('خطا در ارتباط با سرور');
      }
    },
    async saveDraft() {
      await this.savePost('draft');
    },
    async publishPost() {
      await this.savePost('published');
    },
    async savePost(status) {
      if (!this.post.title) {
        this.showToastMessage('لطفا عنوان مقاله را وارد کنید');
        return;
      }
      
      if (!this.post.content) {
        this.showToastMessage('لطفا متن مقاله را وارد کنید');
        return;
      }
      
      if (!this.post.slug && status === 'published') {
        // Auto-generate slug if publishing and no slug provided
        this.generateSlug();
      }
      
      this.saving = true;
      this.post.status = status;
      
      // If publishing for the first time without a date, set to now
      if (status === 'published' && !this.post.published_at) {
        this.post.published_at = new Date().toISOString();
      }
      
      try {
        const token = this.$store.state.token;
        let response;
        
        if (this.isEditing) {
          response = await axios.put(`${BASE_API_URL}/blog/${this.postId}`, this.post, {
            headers: {
              Authorization: `Bearer ${token}`
            }
          });
        } else {
          response = await axios.post(`${BASE_API_URL}/blog`, this.post, {
            headers: {
              Authorization: `Bearer ${token}`
            }
          });
        }
        
        if (response.data.success) {
          const statusText = status === 'published' ? 'منتشر' : 'ذخیره';
          this.showToastMessage(`مقاله با موفقیت ${statusText} شد`);
          
          if (!this.isEditing) {
            // Redirect to edit mode if created new post
            setTimeout(() => {
              this.$router.push(`/admin/blog/edit/${response.data.post.id}`);
            }, 1000);
          }
        } else {
          this.showToastMessage('خطا در ذخیره مقاله');
        }
      } catch (error) {
        console.error('Error saving post:', error);
        this.showToastMessage('خطا در ارتباط با سرور');
      } finally {
        this.saving = false;
      }
    },
    generateSlug() {
      if (!this.post.title) {
        this.showToastMessage('ابتدا عنوان مقاله را وارد کنید');
        return;
      }
      
      // Convert to lowercase and replace spaces with dashes
      let slug = this.post.title
        .trim()
        .toLowerCase()
        // Replace Persian/Arabic numbers with English numbers
        .replace(/[۰-۹]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
        // Replace Persian characters with English equivalents (simplified)
        .replace(/[آاإأ]/g, 'a')
        .replace(/[بپ]/g, 'b')
        .replace(/[تط]/g, 't')
        .replace(/ث/g, 's')
        .replace(/[جچ]/g, 'j')
        .replace(/ح/g, 'h')
        .replace(/[خ]/g, 'kh')
        .replace(/[دذ]/g, 'd')
        .replace(/ر/g, 'r')
        .replace(/ز/g, 'z')
        .replace(/[سص]/g, 's')
        .replace(/[شژ]/g, 'sh')
        .replace(/ض/g, 'z')
        .replace(/[طظ]/g, 't')
        .replace(/ع/g, 'a')
        .replace(/غ/g, 'gh')
        .replace(/[فڤ]/g, 'f')
        .replace(/ق/g, 'gh')
        .replace(/ک/g, 'k')
        .replace(/گ/g, 'g')
        .replace(/ل/g, 'l')
        .replace(/م/g, 'm')
        .replace(/ن/g, 'n')
        .replace(/[وؤ]/g, 'v')
        .replace(/ه/g, 'h')
        .replace(/[یيئ]/g, 'y')
        // Replace non-alphanumeric characters with dash
        .replace(/[^a-z0-9]/g, '-')
        // Replace multiple dashes with single dash
        .replace(/-+/g, '-')
        // Remove leading and trailing dashes
        .replace(/^-|-$/g, '');
      
      this.post.slug = slug;
    },
    insertTag(tag) {
      const editor = this.$refs.contentEditor;
      if (!editor) return;
      
      const start = editor.selectionStart;
      const end = editor.selectionEnd;
      const selectedText = this.post.content.substring(start, end);
      
      let taggedText = '';
      
      if (tag === 'a') {
        taggedText = `<a href="http://example.com">${selectedText || 'متن لینک'}</a>`;
      } else {
        taggedText = `<${tag}>${selectedText || `متن ${tag}`}</${tag}>`;
      }
      
      // Insert the tagged text
      this.post.content = this.post.content.substring(0, start) + taggedText + this.post.content.substring(end);
      
      // After React updates the DOM, set the caret position
      this.$nextTick(() => {
        editor.focus();
        const newCursorPos = start + taggedText.length;
        editor.setSelectionRange(newCursorPos, newCursorPos);
      });
    },
    insertList(type) {
      const editor = this.$refs.contentEditor;
      if (!editor) return;
      
      const start = editor.selectionStart;
      const selectedText = this.post.content.substring(start, editor.selectionEnd);
      
      let listItems = '';
      if (selectedText) {
        // If text is selected, each line becomes a list item
        const lines = selectedText.split('\n');
        for (const line of lines) {
          if (line.trim()) {
            listItems += `  <li>${line.trim()}</li>\n`;
          }
        }
      } else {
        // If no text is selected, add three empty list items
        listItems = '  <li>آیتم 1</li>\n  <li>آیتم 2</li>\n  <li>آیتم 3</li>\n';
      }
      
      const listHtml = `<${type}>\n${listItems}</${type}>`;
      
      // Insert the list at cursor position
      this.post.content = this.post.content.substring(0, start) + listHtml + this.post.content.substring(editor.selectionEnd);
      
      // After React updates the DOM, set the caret position
      this.$nextTick(() => {
        editor.focus();
        const newCursorPos = start + listHtml.length;
        editor.setSelectionRange(newCursorPos, newCursorPos);
      });
    },
    insertImage() {
      const editor = this.$refs.contentEditor;
      if (!editor) return;
      
      const imageUrl = prompt('آدرس تصویر را وارد کنید:', 'https://');
      if (!imageUrl) return;
      
      const altText = prompt('متن جایگزین تصویر را وارد کنید:', 'توضیح تصویر');
      
      const imageHtml = `<img src="${imageUrl}" alt="${altText || 'تصویر'}" class="img-fluid">`;
      
      const start = editor.selectionStart;
      this.post.content = this.post.content.substring(0, start) + imageHtml + this.post.content.substring(editor.selectionEnd);
      
      // After React updates the DOM, set the caret position
      this.$nextTick(() => {
        editor.focus();
        const newCursorPos = start + imageHtml.length;
        editor.setSelectionRange(newCursorPos, newCursorPos);
      });
    },
    showToastMessage(message) {
      this.toastMessage = message;
      this.showToast = true;
      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    }
  }
};
</script>

<style scoped>
.blog-editor-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 16px;
  flex: 1;
}

.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.editor-header h2 {
  font-size: 1.5rem;
  color: #333;
  margin: 0;
}

.action-buttons {
  display: flex;
  gap: 10px;
}

.save-draft-button,
.publish-button,
.cancel-button {
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.save-draft-button {
  background-color: #f8f9fa;
  color: #495057;
  border: 1px solid #ced4da;
}

.publish-button {
  background-color: #A79277;
  color: white;
  border: none;
}

.cancel-button {
  background-color: #6c757d;
  color: white;
  border: none;
}

.save-draft-button:hover {
  background-color: #e9ecef;
}

.publish-button:hover {
  background-color: #8a7660;
}

.cancel-button:hover {
  background-color: #5a6268;
}

.form-container {
  background-color: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #333;
}

.form-text {
  display: block;
  margin-top: 4px;
  font-size: 0.85rem;
  color: #6c757d;
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 1rem;
  color: #495057;
}

.form-control:focus {
  border-color: #A79277;
  outline: 0;
  box-shadow: 0 0 0 2px rgba(167, 146, 119, 0.25);
}

.content-editor {
  font-family: monospace;
  resize: vertical;
  min-height: 300px;
  white-space: pre-wrap;
}

.image-preview {
  margin-top: 8px;
  border-radius: 8px;
  overflow: hidden;
  max-width: 300px;
}

.image-preview img {
  width: 100%;
  height: auto;
  display: block;
}

.slug-input-group {
  display: flex;
  gap: 8px;
}

.generate-slug-btn {
  background-color: #6c757d;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 0 16px;
  cursor: pointer;
  white-space: nowrap;
}

.generate-slug-btn:hover {
  background-color: #5a6268;
}

.editor-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 8px;
  padding: 8px;
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
  border-radius: 4px 4px 0 0;
}

.toolbar-btn {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: white;
  border: 1px solid #ced4da;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  color: #495057;
}

.toolbar-btn:hover {
  background-color: #e9ecef;
}

.content-editor {
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

.editor-help {
  margin-top: 16px;
  padding: 16px;
  border-radius: 8px;
  background-color: #f8f9fa;
  border: 1px solid #e9ecef;
}

.editor-help p {
  margin-top: 0;
  font-weight: 500;
  color: #495057;
}

.editor-help ul {
  margin-bottom: 0;
  padding-right: 20px;
}

.editor-help li {
  margin-bottom: 4px;
}

.editor-help code {
  background-color: #e9ecef;
  padding: 2px 4px;
  border-radius: 4px;
  font-family: monospace;
}

.toast-notification {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #333;
  color: white;
  padding: 12px 20px;
  border-radius: 8px;
  z-index: 1100;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  animation: fadeInOut 3s ease-in-out forwards;
}

@keyframes fadeInOut {
  0% { opacity: 0; transform: translate(-50%, 20px); }
  10% { opacity: 1; transform: translate(-50%, 0); }
  90% { opacity: 1; transform: translate(-50%, 0); }
  100% { opacity: 0; transform: translate(-50%, 20px); }
}

/* Dark mode */
body.dark-mode .editor-header h2 {
  color: #f0f0f0;
}

body.dark-mode .form-container {
  background-color: #262626;
}

body.dark-mode .form-group label {
  color: #e0e0e0;
}

body.dark-mode .form-text {
  color: #aaa;
}

body.dark-mode .form-control {
  background-color: #333;
  border-color: #444;
  color: #e0e0e0;
}

body.dark-mode .form-control:focus {
  border-color: #C5B192;
  box-shadow: 0 0 0 2px rgba(197, 177, 146, 0.25);
}

body.dark-mode .editor-toolbar {
  background-color: #333;
  border-color: #444;
}

body.dark-mode .toolbar-btn {
  background-color: #444;
  border-color: #555;
  color: #e0e0e0;
}

body.dark-mode .toolbar-btn:hover {
  background-color: #555;
}

body.dark-mode .editor-help {
  background-color: #333;
  border-color: #444;
}

body.dark-mode .editor-help p,
body.dark-mode .editor-help li {
  color: #e0e0e0;
}

body.dark-mode .editor-help code {
  background-color: #444;
  color: #e0e0e0;
}

body.dark-mode .save-draft-button {
  background-color: #444;
  color: #e0e0e0;
  border-color: #555;
}

body.dark-mode .save-draft-button:hover {
  background-color: #555;
}

/* Responsive */
@media (max-width: 768px) {
  .editor-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .action-buttons {
    width: 100%;
  }
  
  .save-draft-button,
  .publish-button,
  .cancel-button {
    flex: 1;
    white-space: nowrap;
  }
  
  .slug-input-group {
    flex-direction: column;
  }
  
  .generate-slug-btn {
    align-self: flex-start;
  }
  
  .editor-toolbar {
    justify-content: center;
  }
}
</style> 