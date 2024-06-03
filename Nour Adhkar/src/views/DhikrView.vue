<template>
  <section id="morning" class="modal">
    <header>
      <div class="d-flex">
        <RouterLink to="/">
          <img class="appbar-action-button" src="/src/assets/icons/back-button.svg"
               alt="برگشتن">
        </RouterLink>
        <h1 id="modal-title">{{ title }}</h1>
      </div>
      <span class="progressbar-container">
                <span class="progressbar-fill" style="width:5%;"></span>
            </span>
    </header>
    <main class="modal-container">
      <div class="content-top-bar">
        <h2 id="dhikr-title"></h2>
        <img id="share-button" class="share-button" src="/src/assets/icons/share.svg"
             alt="اشتراک گذاری" @click="share();">
      </div>
      <p id="dhikr-prefix"></p>
      <p id="dhikr-text"></p>
      <p id="dhikr-translation"></p>
    </main>
    <footer class="bottom-nav-bar">
      <span id="dhikr-count"></span>
      <span class="counter-button" @click="count()">۰</span>
      <span id="dhikr-progress-details"></span>
    </footer>
  </section>
</template>

<script>


export default {
  props: {
    title: String
  }
}
</script>