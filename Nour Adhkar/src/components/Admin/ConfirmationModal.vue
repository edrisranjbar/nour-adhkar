<template>
  <div v-if="show" class="confirmation-overlay" @click.self="$emit('close')">
    <div class="confirmation-modal">
      <div class="confirmation-content">
        <h3 class="confirmation-title">{{ title }}</h3>
        <p class="confirmation-message">{{ message }}</p>
      </div>
      <div class="confirmation-actions">
        <button
          @click="$emit('confirm')"
          class="confirmation-button confirm"
        >
          {{ confirmText }}
        </button>
        <button
          @click="$emit('close')"
          class="confirmation-button cancel"
        >
          {{ cancelText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ConfirmationModal',
  props: {
    show: {
      type: Boolean,
      required: true
    },
    title: {
      type: String,
      required: true
    },
    message: {
      type: String,
      required: true
    },
    confirmText: {
      type: String,
      default: 'تایید'
    },
    cancelText: {
      type: String,
      default: 'انصراف'
    }
  },
  emits: ['confirm', 'close']
}
</script>

<style scoped>
.confirmation-overlay {
  position: fixed;
  inset: 0;
  z-index: 1100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(8, 11, 19, 0.75);
}

.confirmation-modal {
  width: 100%;
  max-width: 420px;
  background: var(--admin-surface);
  border-radius: 12px;
  border: 1px solid var(--admin-border);
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.6);
  padding: 1.75rem 1.5rem;
  color: var(--admin-text);
}

.confirmation-content {
  margin-bottom: 1.5rem;
}

.confirmation-title {
  margin: 0 0 0.75rem;
  font-size: 1.25rem;
  color: var(--admin-text);
}

.confirmation-message {
  margin: 0;
  color: var(--admin-muted);
  line-height: 1.6;
}

.confirmation-actions {
  display: flex;
  justify-content: flex-start;
  gap: 0.75rem;
}

.confirmation-button {
  border: none;
  border-radius: 8px;
  padding: 0.6rem 1.4rem;
  font-size: 0.95rem;
  cursor: pointer;
  font-family: inherit;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.confirmation-button.confirm {
  background: #f87171;
  color: #fff;
}

.confirmation-button.confirm:hover {
  background: #ef4444;
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(239, 68, 68, 0.25);
}

.confirmation-button.cancel {
  background: rgba(148, 163, 184, 0.2);
  color: var(--admin-muted);
}

.confirmation-button.cancel:hover {
  background: rgba(148, 163, 184, 0.3);
  transform: translateY(-1px);
}
</style> 