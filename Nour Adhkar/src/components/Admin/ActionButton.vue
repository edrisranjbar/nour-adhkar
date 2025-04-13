<template>
  <button
    :class="['action-button', `${type}-button`]"
    :title="title"
    @click="$emit('click')"
  >
    <font-awesome-icon :icon="iconName" />
  </button>
</template>

<script>
export default {
  name: 'ActionButton',
  props: {
    type: {
      type: String,
      required: true,
      validator: (value) => ['edit', 'delete', 'view', 'add', 'save', 'cancel'].includes(value)
    },
    title: {
      type: String,
      default: ''
    }
  },
  computed: {
    iconName() {
      const icons = {
        edit: 'fa-solid fa-edit',
        delete: 'fa-solid fa-trash',
        view: 'fa-solid fa-eye',
        add: 'fa-solid fa-plus',
        save: 'fa-solid fa-save',
        cancel: 'fa-solid fa-times'
      };
      return icons[this.type] || icons.edit;
    }
  },
  emits: ['click']
};
</script>

<style scoped>
.action-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: none;
  background-color: transparent;
  cursor: pointer;
  margin: 0 4px;
  transition: all 0.2s ease;
  color: #666;
}

.action-button:hover {
  color: #333;
  transform: translateY(-1px);
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .action-button {
    color: #aaa;
  }
  
  .action-button:hover {
    color: #fff;
  }
}
</style> 