#!/usr/bin/env node
/**
 * Generates a styled HTML file from STACK_TECHNIQUE.md
 * Then you can open it in a browser and print to PDF (Cmd+P → Save as PDF)
 * OR if Puppeteer is available, it generates a PDF directly.
 */
const fs = require('fs');
const path = require('path');
const { marked } = require('marked');

const target = process.argv[2] || 'STACK_TECHNIQUE';
const mdPath = path.join(__dirname, target + '.md');
const htmlPath = path.join(__dirname, target + '.html');

const md = fs.readFileSync(mdPath, 'utf-8');
const body = marked.parse(md, { gfm: true, breaks: false });

const html = `<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vivat  Stack Technique Backend</title>
<style>
  @page {
    size: A4;
    margin: 25mm 20mm;
  }
  * { box-sizing: border-box; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    font-size: 11pt;
    line-height: 1.6;
    color: #1a1a2e;
    max-width: 210mm;
    margin: 0 auto;
    padding: 30px 40px;
    background: #fff;
  }
  h1 {
    font-size: 24pt;
    color: #16213e;
    border-bottom: 3px solid #0f3460;
    padding-bottom: 10px;
    margin-top: 0;
    page-break-after: avoid;
  }
  h2 {
    font-size: 16pt;
    color: #0f3460;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 6px;
    margin-top: 35px;
    page-break-after: avoid;
  }
  h3 {
    font-size: 13pt;
    color: #1a365d;
    margin-top: 25px;
    page-break-after: avoid;
  }
  h4 {
    font-size: 11pt;
    color: #2d3748;
    margin-top: 20px;
    page-break-after: avoid;
  }
  blockquote {
    border-left: 4px solid #0f3460;
    margin: 15px 0;
    padding: 10px 20px;
    background: #f0f4ff;
    border-radius: 0 6px 6px 0;
    font-size: 10pt;
    color: #4a5568;
  }
  blockquote p { margin: 4px 0; }
  table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    font-size: 10pt;
    page-break-inside: avoid;
  }
  th {
    background: #0f3460;
    color: #fff;
    font-weight: 600;
    text-align: left;
    padding: 8px 12px;
  }
  td {
    padding: 7px 12px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
  }
  tr:nth-child(even) { background: #f7fafc; }
  tr:hover { background: #edf2f7; }
  code {
    background: #edf2f7;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'SF Mono', 'Fira Code', Menlo, monospace;
    font-size: 9.5pt;
    color: #c53030;
  }
  pre {
    background: #1a202c;
    color: #e2e8f0;
    padding: 16px 20px;
    border-radius: 8px;
    overflow-x: auto;
    font-size: 9pt;
    line-height: 1.5;
    page-break-inside: avoid;
    margin: 15px 0;
  }
  pre code {
    background: none;
    color: inherit;
    padding: 0;
    font-size: inherit;
  }
  hr {
    border: none;
    border-top: 2px solid #e2e8f0;
    margin: 30px 0;
  }
  strong { color: #1a202c; }
  a { color: #2b6cb0; text-decoration: none; }
  ul, ol { padding-left: 24px; }
  li { margin: 4px 0; }
  p { margin: 8px 0; }

  /* Print-specific */
  @media print {
    body { padding: 0; max-width: none; }
    h1, h2, h3, h4 { page-break-after: avoid; }
    table, pre, blockquote { page-break-inside: avoid; }
    a { color: #2b6cb0; }
    a::after { content: none; }
  }

  /* Table of contents links */
  a[href^="#"] { color: #0f3460; font-weight: 500; }

  /* Cover-like header */
  h1:first-of-type {
    text-align: center;
    font-size: 28pt;
    margin-bottom: 5px;
  }
  body > blockquote:first-of-type {
    text-align: center;
    border-left: none;
    background: none;
    padding: 0;
    margin-bottom: 30px;
  }
</style>
</head>
<body>
${body}
</body>
</html>`;

fs.writeFileSync(htmlPath, html, 'utf-8');
console.log('HTML genere : ' + htmlPath);
console.log('');
console.log('Pour generer le PDF :');
console.log('  1. Ouvrir le fichier HTML dans Chrome/Safari');
console.log('  2. Cmd+P (ou Ctrl+P)');
console.log('  3. "Save as PDF" / "Enregistrer en PDF"');
console.log('  4. Format A4, marges par defaut');
