<template>
  <div class="confirm-dialog-backdrop" v-if="show" @click.self="cancel">
    <div class="confirm-dialog" @click.stop>
      <div class="confirm-dialog-header">
        <h3>{{ title }}</h3>
        <button class="close-btn" @click="cancel">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="confirm-dialog-body">
        <div class="confirm-dialog-icon" v-if="showIcon">
          <i :class="iconClass"></i>
        </div>
        <div class="confirm-dialog-message">
          {{ message }}
        </div>
      </div>
      
      <div class="confirm-dialog-footer">
        <button 
          @click="cancel" 
          class="cancel-btn"
        >
          {{ cancelText }}
        </button>
        <button 
          @click="confirm" 
          class="confirm-btn"
          :class="confirmButtonClass"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ConfirmDialog',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: 'تأیید'
    },
    message: {
      type: String,
      default: 'آیا از انجام این عملیات اطمینان دارید؟'
    },
    confirmText: {
      type: String,
      default: 'تأیید'
    },
    cancelText: {
      type: String,
      default: 'لغو'
    },
    type: {
      type: String,
      default: 'warning', // warning, danger, info, success
      validator: (value) => ['warning', 'danger', 'info', 'success'].includes(value)
    },
    showIcon: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    iconClass() {
      const baseClass = 'fas';
      switch (this.type) {
        case 'warning':
          return `${baseClass} fa-exclamation-triangle`;
        case 'danger':
          return `${baseClass} fa-trash`;
        case 'info':
          return `${baseClass} fa-info-circle`;
        case 'success':
          return `${baseClass} fa-check-circle`;
        default:
          return `${baseClass} fa-question-circle`;
      }
    },
    confirmButtonClass() {
      switch (this.type) {
        case 'danger':
          return 'confirm-btn-danger';
        case 'success':
          return 'confirm-btn-success';
        case 'info':
          return 'confirm-btn-info';
        default:
          return 'confirm-btn-warning';
      }
    }
  },
  methods: {
    confirm() {
      this.$emit('confirm');
    },
    cancel() {
      this.$emit('cancel');
    }
  }
};
</script>

<style scoped>
.confirm-dialog-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
}

.confirm-dialog {
  background-color: white;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
  animation: fadeInScale 0.2s ease-out;
}

.confirm-dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
}

.confirm-dialog-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #343a40;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #6c757d;
  cursor: pointer;
  padding: 0.25rem;
  transition: color 0.2s;
  font-family: inherit;
}

.close-btn:hover {
  color: #343a40;
}

.confirm-dialog-body {
  padding: 1.5rem;
  display: flex;
  align-items: center;
}

.confirm-dialog-icon {
  margin-right: 1rem;
  font-size: 2rem;
  flex-shrink: 0;
}

.confirm-dialog-icon i {
  display: flex;
  align-items: center;
  justify-content: center;
}

.confirm-dialog-icon .fa-exclamation-triangle {
  color: #ffc107;
}

.confirm-dialog-icon .fa-trash {
  color: #dc3545;
}

.confirm-dialog-icon .fa-info-circle {
  color: #17a2b8;
}

.confirm-dialog-icon .fa-check-circle {
  color: #28a745;
}

.confirm-dialog-message {
  color: #495057;
  line-height: 1.5;
}

.confirm-dialog-footer {
  display: flex;
  justify-content: flex-end;
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  gap: 0.75rem;
}

.cancel-btn, .confirm-btn {
  padding: 0.5rem 1rem;
  cursor: pointer;
  border-radius: 4px;
  font-weight: 500;
  transition: all 0.2s;
  border: none;
  font-family: inherit;
}

.cancel-btn {
  background-color: #f8f9fa;
  color: #6c757d;
  border: 1px solid #ced4da;
}

.cancel-btn:hover {
  background-color: #e9ecef;
}

.confirm-btn {
  color: white;
  border: none;
}

.confirm-btn-warning {
  background-color: #ffc107;
}

.confirm-btn-warning:hover {
  background-color: #e0a800;
}

.confirm-btn-danger {
  background-color: #dc3545;
}

.confirm-btn-danger:hover {
  background-color: #c82333;
}

.confirm-btn-info {
  background-color: #17a2b8;
}

.confirm-btn-info:hover {
  background-color: #138496;
}

.confirm-btn-success {
  background-color: #28a745;
}

.confirm-btn-success:hover {
  background-color: #218838;
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style> 