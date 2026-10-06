import axios from 'axios';

// Laravel's panel uses its existing session and CSRF token; the website keeps its JWT client.
const panel = !!window.NOUR_INSTAGRAM_PANEL;
const client = panel ? axios.create({
  baseURL: '/admin/instagram-api/',
  headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
}) : axios;

export const instagramApi = Object.fromEntries(['get', 'post', 'put', 'delete'].map(method => [method,
  (path, ...args) => client[method](panel ? path.replace(/^admin\//, '') : path, ...args),
]));
