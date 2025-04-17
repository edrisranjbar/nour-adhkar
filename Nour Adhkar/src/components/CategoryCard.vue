<template>
    <div :class="{'category-card':true, 'card-sm': size==='small'}">
      <slot/>
    </div>
</template>

<script>
export default {
  props: {
    title: String,
    imageSrc: String,
    size: String,
    path: String
  }
}
</script>

<style scoped>
.category-card {
  aspect-ratio: 16/9;
  border-radius: 16px;
  overflow: hidden;
  height: 140px;
  width: 100%;
  position: relative;
  transition: all 0.3s ease;
}

:slotted(div) {
  position: relative;
  z-index: 2;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 1.25rem;
}

:slotted(h2) {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: white;
  margin-bottom: 0.25rem;
}

:slotted(span) {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.85rem;
}
</style>