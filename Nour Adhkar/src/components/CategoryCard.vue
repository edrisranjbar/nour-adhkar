<template>
    <div :class="{'category-card':true, 'card-sm': size==='small'}"
         :style="{'background-image': `url(${imageSrc})`}"
    >
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
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  border-radius: 12px;
  overflow: hidden;
  height: 100%;
}

.card-sm {
  aspect-ratio: 3/2;
}
</style>