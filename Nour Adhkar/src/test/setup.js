import { config } from '@vue/test-utils'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { 
  faNewspaper, 
  faUsers, 
  faEye, 
  faComments, 
  faPlus, 
  faList, 
  faSync, 
  faPen,
  faArrowLeft
} from '@fortawesome/free-solid-svg-icons'

// Add icons to the library
library.add(
  faNewspaper, 
  faUsers, 
  faEye, 
  faComments, 
  faPlus, 
  faList, 
  faSync, 
  faPen,
  faArrowLeft
)

// Register FontAwesomeIcon globally
config.global.components = {
  'font-awesome-icon': FontAwesomeIcon
}

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
}) 