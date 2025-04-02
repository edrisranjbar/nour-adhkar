#!/usr/bin/env node

// Import necessary modules
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import process from 'process';
import { globby } from 'globby';

// Get __dirname equivalent in ESM
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Site URL
const siteUrl = 'https://nour-adhkar.ir';

/**
 * Generate XML sitemap
 */
async function generateSitemap() {
  console.log('Generating sitemap...');
  
  try {
    // Get dynamic routes from Vue router
    const { routes } = await import('../src/router/routes.js');
    
    if (!routes || routes.length === 0) {
      console.warn('Warning: No routes found or routes could not be loaded');
    }
    
    // Filter out routes that should not be indexed
    const dynamicPages = routes
      .filter(route => !route.meta?.noindex)
      .map(route => ({
        url: route.path,
        changefreq: route.meta?.changefreq || 'monthly',
        priority: route.meta?.priority || '0.5'
      }));
    
    // Find all static assets, like images
    const staticAssets = await globby([
      'public/**/*.{png,jpg,jpeg,svg,webp}',
      '!public/icons',  // Exclude icon files
      '!public/favicon.ico'
    ]);
    
    // Convert static assets to sitemap entries
    const staticPages = staticAssets.map(file => {
      const path = file.replace('public', '');
      return {
        url: path,
        changefreq: 'monthly',
        priority: '0.3'
      };
    });
    
    // Combine all pages
    const allPages = [...dynamicPages, ...staticPages];

    // Generate sitemap XML
    const sitemap = generateSitemapXML(allPages);
    
    // Write sitemap to public directory
    const outputPath = path.resolve(__dirname, '../public/sitemap.xml');
    fs.writeFileSync(outputPath, sitemap);
    
    console.log(`Sitemap generated successfully with ${allPages.length} URLs at ${outputPath}`);
  } catch (error) {
    console.error('Error generating sitemap:', error);
    process.exit(1);
  }
}

/**
 * Generate the XML content for the sitemap
 * @param {Array<{url: string, changefreq: string, priority: string}>} pages
 * @returns {string} XML content
 */
function generateSitemapXML(pages) {
  // XML sitemap header
  let xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
`;

  // Add each page to the sitemap
  pages.forEach(page => {
    xml += `  <url>
    <loc>${siteUrl}${page.url}</loc>
    <changefreq>${page.changefreq}</changefreq>
    <priority>${page.priority}</priority>
  </url>
`;
  });

  // Close XML
  xml += `</urlset>`;
  
  return xml;
}

// Execute the function
generateSitemap().catch(error => {
  console.error('Error generating sitemap:', error);
  process.exit(1);
}); 