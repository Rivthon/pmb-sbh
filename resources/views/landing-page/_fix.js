// Fix script - reads index.blade.php, removes orphan code
const fs = require('fs');
const path = 'resources/views/landing-page/index.blade.php';
const content = fs.readFileSync(path, 'utf8');
const lines = content.split('\n');

// Find key markers
let shortsEndFirst = -1;
let faqStart = -1;

for (let i = 0; i < lines.length; i++) {
    const l = lines[i].trim();
    if (l === '<!-- YouTube Shorts Carousel: End -->' && shortsEndFirst === -1) {
        shortsEndFirst = i;
    }
    if (l === '<!-- FAQ: Start -->' && faqStart === -1) {
        // Find the LAST occurrence of FAQ: Start (the real one near section)
    }
    if (l.includes('id="landingFAQ"')) {
        faqStart = i - 1; // The FAQ comment is the line before
    }
}

console.log('First Shorts End:', shortsEndFirst + 1);
console.log('FAQ section start:', faqStart + 1);

if (shortsEndFirst > 0 && faqStart > shortsEndFirst) {
    // Keep: lines 0..shortsEndFirst, then blank, then faqStart..end
    const part1 = lines.slice(0, shortsEndFirst + 1);
    const part2 = lines.slice(faqStart);
    const result = [...part1, '', '', ...part2];
    fs.writeFileSync(path, result.join('\n'));
    console.log('Done! Lines:', result.length);
}
