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
  background: var(--admin-surface);
  border-radius: 10px;
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.35);
  overflow: hidden;
  border: 1px solid var(--admin-border);
  color: var(--admin-text);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 1rem;
  text-align: right;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: var(--admin-text);
}

.data-table th {
  background-color: rgba(255, 255, 255, 0.05);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  color: var(--admin-muted);
}

.data-table tr:hover {
  background-color: rgba(59, 130, 246, 0.08);
}

.actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .data-table {
    display: block;
    overflow-x: auto;
  }
}
</style>