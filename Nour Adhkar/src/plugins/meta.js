// Meta plugin for SEO

// Default meta values
const defaultMeta = {
  title: 'اذکار نور | مجموعه کامل اذکار و ادعیه',
  description: 'دسترسی آسان به اذکار صبحگاهی، شامگاهی و ادعیه مختلف در یک اپلیکیشن سبک و کاربرپسند',
  image: '/images/logo.png',
  url: 'https://nour-adhkar.ir',
  type: 'website',
  locale: 'fa_IR'
};

// Helper function to set meta tags
function setMetaTag(name, content) {
  // Find existing meta tag or create a new one
  let meta = document.querySelector(`meta[name="${name}"], meta[property="${name}"]`);
  
  if (!meta) {
    meta = document.createElement('meta');
    
    // Determine if it's a name or property
    if (name.startsWith('og:') || name.startsWith('twitter:')) {
      meta.setAttribute('property', name);
    } else {
      meta.setAttribute('name', name);
    }
    
    document.head.appendChild(meta);
  }
  
  // Set content
  meta.setAttribute('content', content);
}

// Meta plugin
export default {
  install: (app) => {
    // Create a reactive method to update meta tags
    app.config.globalProperties.$setMeta = (meta = {}) => {
      // Merge with default meta
      const metaData = { ...defaultMeta, ...meta };
      
      // Set document title
      document.title = metaData.title;
      
      // Update meta tags
      setMetaTag('description', metaData.description);
      
      // Open Graph meta tags
      setMetaTag('og:title', metaData.title);
      setMetaTag('og:description', metaData.description);
      setMetaTag('og:image', metaData.image);
      setMetaTag('og:url', metaData.url);
      setMetaTag('og:type', metaData.type);
      setMetaTag('og:locale', metaData.locale);
      
      // Twitter meta tags
      setMetaTag('twitter:card', 'summary_large_image');
      setMetaTag('twitter:title', metaData.title);
      setMetaTag('twitter:description', metaData.description);
      setMetaTag('twitter:image', metaData.image);
    };
  }
}; 