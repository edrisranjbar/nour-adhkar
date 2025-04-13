<template>
    <div v-if="show" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-container">
        <div class="modal-header">
          <h2>{{ title }}</h2>
        </div>
        <form @submit.prevent="$emit('submit')">
          <slot></slot>
          
          <div class="modal-actions">
            <button type="button" class="cancel-button" @click="$emit('close')">
              انصراف
            </button>
            <button type="submit" class="save-button">
              {{ submitText }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'FormModal',
    props: {
      show: {
        type: Boolean,
        required: true
      },
      title: {
        type: String,
        required: true
      },
      submitText: {
        type: String,
        default: 'ذخیره'
      }
    }
  }
  </script>
  
  <style scoped>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  
  .modal-container {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
  }
  
  .modal-header {
    margin-bottom: 1.5rem;
  }
  
  .modal-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
  }
  
  .cancel-button,
  .save-button {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.2s ease;
    font-family: inherit;
  }
  
  .cancel-button {
    background-color: #f8f9fa;
    color: #666;
  }
  
  .save-button {
    background-color: #A79277;
    color: white;
  }
  
  .cancel-button:hover {
    background-color: #e9ecef;
  }
  
  .save-button:hover {
    background-color: #8a7660;
  }
  
  /* Dark mode styles */
  body.dark-mode {
    .modal-container {
      background-color: #333;
    }
  
    .modal-header h2 {
      color: #fff;
    }
  
    .cancel-button {
      background-color: #444;
      color: #fff;
    }
  
    .cancel-button:hover {
      background-color: #555;
    }
  }
  </style>