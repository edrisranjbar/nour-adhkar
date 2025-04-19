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
  background: linear-gradient(135deg, #A79277 0%, #9C8466 100%);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.category-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('src/assets/images/pattern.svg') no-repeat;
  background-size: cover;
  opacity: 0.1;
  z-index: 1;
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
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

:slotted(span) {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.85rem;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
}

/* Dark mode adjustments */
body.dark-mode .category-card {
  background: linear-gradient(135deg, #8B7355 0%, #6B5A4A 100%);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
</style>