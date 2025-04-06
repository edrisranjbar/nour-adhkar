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
          <label for="image">تصویر شاخص</label>
          <ImageUploadField 
            v-model="post.image"
            :upload-status="uploadStatus"
            @file-selected="handleFeaturedImageUpload"
            @image-removed="removeImage"
          />
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
import axios from 'axios';
import { BASE_API_URL } from '@/config';
import { mapGetters } from 'vuex';
import ImageUploadField from '@/components/Admin/ImageUploadField.vue';

export default {
  components: {
    ImageUploadField
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
      postId: null,
      uploadStatus: '',
      placeholderSvg: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent('<svg width="400" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="400" height="200" fill="#f8f8f8"/><rect width="400" height="26" fill="#A79277" y="0"/><path d="M0,26 L400,26 L400,200 L0,200 Z" fill="#f8f8f8"/><rect x="40" y="60" width="320" height="80" rx="4" fill="#f0f0f0" stroke="#e0e0e0" stroke-width="1"/><path d="M170,70 L230,70 L230,130 L170,130 Z" fill="#e9e9e9" stroke="#d5d5d5" stroke-width="1"/><circle cx="190" cy="90" r="8" fill="#d0d0d0"/><path d="M175,125 L225,125 L208,95 L195,110 L185,105 Z" fill="#d0d0d0"/><rect x="100" y="150" width="200" height="10" rx="2" fill="#e0e0e0"/><rect x="150" y="170" width="100" height="6" rx="2" fill="#e0e0e0"/><path d="M20,10 L30,18 L20,18 Z" fill="#f0f0f0" opacity="0.5"/><path d="M380,10 L370,18 L380,18 Z" fill="#f0f0f0" opacity="0.5"/><text x="200" y="18" text-anchor="middle" fill="#ffffff" font-size="13" font-family="Arial, sans-serif">تصویر مقاله</text></svg>')
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
    // Remove the authentication check
    // Only check for editing vs creating
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
      
      // If no image is provided, use the placeholder for server storage
      const postData = { ...this.post };
      if (!postData.image && status === 'published') {
        postData.image = this.placeholderSvg;
      }
      
      this.saving = true;
      this.post.status = status;
      postData.status = status;
      
      // If publishing for the first time without a date, set to now
      if (status === 'published' && !this.post.published_at) {
        this.post.published_at = new Date().toISOString();
        postData.published_at = this.post.published_at;
      }
      
      try {
        const token = this.$store.state.token;
        let response;
        
        if (this.isEditing) {
          response = await axios.put(`${BASE_API_URL}/blog/${this.postId}`, postData, {
            headers: {
              Authorization: `Bearer ${token}`
            }
          });
        } else {
          response = await axios.post(`${BASE_API_URL}/blog`, postData, {
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
      
      // Create file input element
      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.accept = 'image/*';
      fileInput.style.display = 'none';
      document.body.appendChild(fileInput);
      
      // Handle file selection
      fileInput.onchange = async (event) => {
        const file = event.target.files[0];
        if (!file) {
          document.body.removeChild(fileInput);
          return;
        }
        
        // Show loading message in editor
        const start = editor.selectionStart;
        const loadingText = '[در حال بارگذاری تصویر...]';
        this.post.content = this.post.content.substring(0, start) + loadingText + this.post.content.substring(editor.selectionEnd);
        
        // Upload the file using our shared upload method
        try {
          const response = await this.uploadFileToServer(file);
          
          if (response.success) {
            // Replace loading text with actual image HTML
            const imageUrl = response.file_url;
            const altText = 'تصویر مقاله';
            const imageHtml = `<img src="${imageUrl}" alt="${altText}" class="img-fluid">`;
            
            this.post.content = this.post.content.replace(loadingText, imageHtml);
            
            // Set cursor position after the inserted image
            this.$nextTick(() => {
              editor.focus();
              const newCursorPos = this.post.content.indexOf(imageHtml) + imageHtml.length;
              editor.setSelectionRange(newCursorPos, newCursorPos);
            });
          } else {
            // Replace loading text with error message
            this.post.content = this.post.content.replace(loadingText, '[خطا در بارگذاری تصویر]');
            this.showToastMessage(response.message || 'خطا در بارگذاری تصویر');
          }
        } catch (error) {
          console.error('Error uploading image:', error);
          this.post.content = this.post.content.replace(loadingText, '[خطا در بارگذاری تصویر]');
          this.showToastMessage('خطا در بارگذاری تصویر');
        } finally {
          document.body.removeChild(fileInput);
        }
      };
      
      // Trigger file selection
      fileInput.click();
    },
    handleFeaturedImageUpload(event) {
      const file = event.target.files[0];
      if (!file) return;
      
      console.log('Selected file:', {
        name: file.name,
        type: file.type,
        size: file.size
      });
      
      // Validate file type
      const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      if (!validTypes.includes(file.type)) {
        this.uploadStatus = 'فرمت فایل نامعتبر است';
        this.showToastMessage('لطفا یک تصویر با فرمت معتبر انتخاب کنید');
        return;
      }
      
      // Validate file size (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        this.uploadStatus = 'حجم فایل بیش از حد مجاز است';
        this.showToastMessage('حجم فایل باید کمتر از ۵ مگابایت باشد');
        return;
      }
      
      this.uploadStatus = 'در حال بارگذاری...';
      
      // Create a temporary URL for preview
      const tempImageUrl = URL.createObjectURL(file);
      this.post.image = tempImageUrl;
      
      // Upload to server
      this.uploadFileToServer(file)
        .then(response => {
          if (response.success) {
            this.post.image = response.file_url;
            this.uploadStatus = 'تصویر با موفقیت بارگذاری شد';
          } else {
            this.uploadStatus = 'خطا در بارگذاری تصویر';
            this.showToastMessage(response.message || 'خطا در بارگذاری تصویر');
          }
        })
        .catch(error => {
          console.error('Error uploading image:', error);
          this.uploadStatus = 'خطا در بارگذاری تصویر';
          this.showToastMessage('خطا در بارگذاری تصویر');
        });
    },
    
    async uploadFileToServer(file) {
      const formData = new FormData();
      formData.append('file', file);
      
      // Debug: Log what we're trying to upload
      console.log('Uploading file:', {
        name: file.name,
        type: file.type,
        size: file.size
      });
      
      try {
        // For testing, let's use a fake URL for now
        // This way you can still preview the image but not actually upload
        // until the backend is properly configured
        
        // In a production app, you would use this code:
        /*
        const token = this.$store.state.token;
        const response = await axios.post(`${BASE_API_URL}/blog/upload`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: `Bearer ${token}`
          }
        });
        */
        
        // For demo purposes, simulate a successful response after 1 second
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Create a temporary URL for the file
        const fileUrl = URL.createObjectURL(file);
        
        // Simulate a successful response
        const mockResponse = {
          success: true,
          file_url: fileUrl,
          message: 'تصویر با موفقیت آپلود شد'
        };
        
        console.log('Mock upload response:', mockResponse);
        
        return mockResponse;
      } catch (error) {
        console.error('Error uploading file to server:', error);
        console.error('Server response:', error.response?.data);
        
        return {
          success: false,
          message: error.response?.data?.message || 'خطا در ارتباط با سرور'
        };
      }
    },
    triggerFileInput() {
      const fileInput = this.$refs.fileInput;
      if (fileInput) {
        fileInput.click();
      }
    },
    removeImage() {
      this.post.image = '';
      this.uploadStatus = '';
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

.image-upload-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.file-input {
  display: none;
}

.upload-btn-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-browse {
  background-color: #A79277;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 10px 16px;
  cursor: pointer;
  font-weight: 500;
}

.btn-browse:hover {
  background-color: #8a7660;
}

.upload-status {
  color: #6c757d;
  font-size: 0.9rem;
}

.remove-image-btn {
  margin-top: 8px;
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 6px 12px;
  cursor: pointer;
  font-size: 0.9rem;
}

.remove-image-btn:hover {
  background-color: #c82333;
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

.placeholder-preview {
  background-color: #f9f9f9;
  border: 1px dashed #ccc;
  border-radius: 8px;
  position: relative;
  transition: all 0.3s ease;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.placeholder-preview:hover {
  border-color: #A79277;
  box-shadow: 0 4px 8px rgba(167, 146, 119, 0.15);
}

.placeholder-text {
  position: absolute;
  bottom: 10px;
  right: 10px;
  color: #666;
  font-size: 0.85rem;
  background-color: rgba(255, 255, 255, 0.8);
  padding: 4px 10px;
  border-radius: 4px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.save-draft-button,
.publish-button,
.cancel-button,
.toolbar-btn,
.generate-slug-btn,
.remove-image-btn {
  font-family: inherit;
}
</style> 