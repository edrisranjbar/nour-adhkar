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