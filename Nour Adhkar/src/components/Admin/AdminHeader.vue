<template>
    <div class="admin-header">
      <h1>{{ title }}</h1>
      <button v-if="showAddButton" class="add-button" @click="$emit('add')">
        <font-awesome-icon icon="fa-solid fa-plus" />
        {{ addButtonText }}
      </button>
    </div>
  </template>
  
  <script>
  export default {
    name: 'AdminHeader',
    props: {
      title: {
        type: String,
        required: true
      },
      showAddButton: {
        type: Boolean,
        default: true
      },
      addButtonText: {
        type: String,
        default: 'افزودن'
      }
    }
  }
  </script>
  
  <style scoped>
  .admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
  }
  
  .add-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: #A79277;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    font-family: inherit;
  }
  
  .add-button:hover {
    background-color: #8a7660;
  }
  </style>