import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// Import icons you need
import { 
    faSignOutAlt,
    faUser,
    faLock,
    faCamera,
    faAward,
    faStar,
    faFire,
    faCalendarCheck,
    faHeart
} from '@fortawesome/free-solid-svg-icons';

// Add icons to library
library.add(
    faSignOutAlt,
    faUser,
    faLock,
    faCamera,
    faAward,
    faStar,
    faFire,
    faCalendarCheck,
    faHeart
);

export default FontAwesomeIcon; 