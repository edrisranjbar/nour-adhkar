<template>
  <div :class="{'category-card':true, 'card-sm': size==='small'}"
       :style="{'background-image': `url(${imageSrc})`}"
       @click="openModal(morningCollection);">
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    title: String,
    imageSrc: String,
    size: String
  }
}
</script>