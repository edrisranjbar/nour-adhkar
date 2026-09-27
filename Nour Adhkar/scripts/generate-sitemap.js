#!/usr/bin/env node

// Import necessary modules
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import process from 'process';

// Get __dirname equivalent in ESM
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Site URL
const siteUrl = 'https://adhkar.ir';

// Define routes manually
const routes = [
  {
    path: '/',
    changefreq: 'monthly',
    priority: '1.0'
  }
];

// Generate current date for lastmod
const getFormattedDate = () => {
  const date = new Date();
  return date.toISOString().split('T')[0];
};

/**
 * Generate XML sitemap
 */
function generateSitemap() {
  console.log('Generating sitemap...');
  
  try {
    // Filter out routes that should not be indexed
    const pages = routes.filter(route => !route.noindex);
    
    // XML sitemap header
    let xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
`;

    // Add each page to the sitemap
    pages.forEach(page => {
      xml += `  <url>
    <loc>${siteUrl}${page.path}</loc>
    <lastmod>${getFormattedDate()}</lastmod>
    <changefreq>${page.changefreq}</changefreq>
    <priority>${page.priority}</priority>
  </url>
`;
    });

    // Close XML
    xml += `</urlset>`;
    
    // Write sitemap to public directory
    const outputPath = path.resolve(__dirname, '../public/sitemap.xml');
    fs.writeFileSync(outputPath, xml);
    
    console.log(`Sitemap generated successfully with ${pages.length} URLs at ${outputPath}`);
  } catch (error) {
    console.error('Error generating sitemap:', error);
    process.exit(1);
  }
}

// Execute the function
generateSitemap(); 