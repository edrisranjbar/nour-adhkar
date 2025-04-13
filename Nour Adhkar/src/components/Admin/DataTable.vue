<template>
    <div class="data-table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th v-for="column in columns" :key="column.key">{{ column.label }}</th>
            <th v-if="showActions">عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td v-for="column in columns" :key="column.key">
              <template v-if="column.formatter">
                {{ column.formatter(item[column.key]) }}
              </template>
              <template v-else>
                {{ item[column.key] || '—' }}
              </template>
            </td>
            <td v-if="showActions" class="actions">
              <slot name="actions" :item="item">
                <ActionButton
                  type="edit"
                  title="ویرایش"
                  @click="$emit('edit', item)"
                />
                <ActionButton
                  type="delete"
                  title="حذف"
                  @click="$emit('delete', item)"
                />
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
      
      <slot name="pagination"></slot>
    </div>
  </template>
  
  <script>
  import ActionButton from './ActionButton.vue';
  
  export default {
    name: 'DataTable',
    components: {
      ActionButton
    },
    props: {
      columns: {
        type: Array,
        required: true,
        // Example: [{ key: 'title', label: 'عنوان', formatter: (value) => value.toUpperCase() }]
      },
      items: {
        type: Array,
        required: true
      },
      showActions: {
        type: Boolean,
        default: true
      }
    }
  }
  </script>
  
  <style scoped>
  .data-table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }
  
  .data-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .data-table th,
  .data-table td {
    padding: 1rem;
    text-align: right;
    border-bottom: 1px solid #eee;
  }
  
  .data-table th {
    background-color: #f8f9fa;
    font-weight: 600;
  }
  
  .data-table tr:hover {
    background-color: #f5f5f5;
  }
  
  .actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
  }
  
  /* Dark mode styles */
  body.dark-mode {
    .data-table-container {
      background-color: #333;
    }
  
    .data-table th {
      background-color: #444;
      color: #ddd;
    }
  
    .data-table td {
      border-bottom-color: #444;
    }
  
    .data-table tr:hover {
      background-color: #3a3a3a;
    }
  }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .data-table {
      display: block;
      overflow-x: auto;
    }
  }
  </style>