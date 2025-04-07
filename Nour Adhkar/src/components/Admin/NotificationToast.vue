<template>
  <div class="notification-container" v-if="visible">
    <div class="notification-toast" :class="notification.type">
      <div class="notification-icon">
        <i class="fas" :class="iconClass"></i>
      </div>
      <div class="notification-content">
        <div class="notification-message">{{ notification.message }}</div>
      </div>
      <button @click="close" class="notification-close">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotificationToast',
  props: {
    notification: {
      type: Object,
      required: true,
      default: () => ({
        type: 'info',
        message: ''
      })
    },
    autoClose: {
      type: Boolean,
      default: true
    },
    duration: {
      type: Number,
      default: 3000
    }
  },
  data() {
    return {
      visible: false,
      timeout: null
    };
  },
  computed: {
    iconClass() {
      switch (this.notification.type) {
        case 'success':
          return 'fa-check-circle';
        case 'error':
          return 'fa-exclamation-circle';
        case 'warning':
          return 'fa-exclamation-triangle';
        default:
          return 'fa-info-circle';
      }
    }
  },
  watch: {
    notification: {
      handler(newVal) {
        if (newVal && newVal.message) {
          this.show();
        }
      },
      deep: true
    }
  },
  methods: {
    show() {
      this.visible = true;
      
      if (this.autoClose) {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
          this.close();
        }, this.duration);
      }
    },
    close() {
      this.visible = false;
      this.$emit('close');
    }
  },
  beforeUnmount() {
    clearTimeout(this.timeout);
  }
};
</script>

<style scoped>
.notification-container {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1100;
  min-width: 300px;
  max-width: 450px;
}

.notification-toast {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-radius: 8px;
  background-color: white;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
  animation: fadeInUp 0.3s ease-out forwards;
}

.notification-toast.success {
  border-left: 4px solid #28a745;
}

.notification-toast.error {
  border-left: 4px solid #dc3545;
}

.notification-toast.warning {
  border-left: 4px solid #ffc107;
}

.notification-toast.info {
  border-left: 4px solid #17a2b8;
}

.notification-icon {
  margin-right: 12px;
  font-size: 1.25rem;
}

.notification-toast.success .notification-icon {
  color: #28a745;
}

.notification-toast.error .notification-icon {
  color: #dc3545;
}

.notification-toast.warning .notification-icon {
  color: #ffc107;
}

.notification-toast.info .notification-icon {
  color: #17a2b8;
}

.notification-content {
  flex: 1;
}

.notification-message {
  color: #343a40;
  font-size: 0.9rem;
}

.notification-close {
  background: none;
  border: none;
  color: #6c757d;
  cursor: pointer;
  padding: 4px;
  margin-left: 8px;
  border-radius: 4px;
  transition: all 0.2s;
  font-family: inherit;
}

.notification-close:hover {
  background-color: #f8f9fa;
  color: #343a40;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 576px) {
  .notification-container {
    width: 90%;
    max-width: none;
  }
}
</style> 