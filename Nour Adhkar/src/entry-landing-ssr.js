// Build-time render of the landing page (see scripts/prerender-landing.mjs). The output is placed
// inside #app in dist/index.html, so the hero paints straight from the HTML and the client then
// hydrates it instead of waiting for the JavaScript to build the page.
import { createSSRApp, h } from 'vue'
import { renderToString } from 'vue/server-renderer'
import LandingView from './views/LandingView.vue'

// Matches what <router-link> renders for a plain internal link.
const RouterLink = {
  props: { to: { type: String, required: true } },
  setup(props, { slots }) {
    return () => h('a', { href: props.to }, slots.default?.())
  }
}

export async function render() {
  // Same root element as App.vue, so hydration lines up.
  const app = createSSRApp({ render: () => h('div', { class: 'app-container' }, [h(LandingView)]) })
  app.component('RouterLink', RouterLink)
  return renderToString(app)
}
