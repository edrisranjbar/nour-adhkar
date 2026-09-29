// First-party, cookie-free analytics for the public site. Data goes only to our own API
// (see backend AnalyticsController); the server stores no IPs and ignores bots.
import axios from 'axios'

const UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign']

// Random id for this browser tab only (sessionStorage), used to count sessions.
function sessionId() {
  try {
    let id = sessionStorage.getItem('nour-sid')
    if (!id) {
      id = (crypto.randomUUID && crypto.randomUUID()) || `${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`
      sessionStorage.setItem('nour-sid', id)
    }
    return id
  } catch (_) {
    return null
  }
}

// Campaign tags from the landing URL, kept for the rest of the session.
function utm() {
  try {
    const params = new URLSearchParams(window.location.search)
    const found = Object.fromEntries(UTM_KEYS.filter((k) => params.get(k)).map((k) => [k, params.get(k).slice(0, 100)]))
    if (Object.keys(found).length) sessionStorage.setItem('nour-utm', JSON.stringify(found))
    return found.utm_source ? found : JSON.parse(sessionStorage.getItem('nour-utm') || '{}')
  } catch (_) {
    return {}
  }
}

export function trackVisit(path) {
  axios.post('analytics/visit', {
    path,
    referrer: document.referrer || null,
    ua: navigator.userAgent,
    session_id: sessionId(),
    lang: (navigator.language || '').slice(0, 8) || null,
    ...utm()
  }).catch(() => {})
}

export function trackEvent(name, label = null) {
  axios.post('analytics/event', {
    name,
    label,
    path: window.location.pathname,
    session_id: sessionId()
  }).catch(() => {})
}
