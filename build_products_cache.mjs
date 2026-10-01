import fs from 'fs';

async function buildCache() {
  const res = await fetch('https://ductri226.vn/wp-json/wp/v2/product?per_page=100');
  const products = await res.json();

  const cleaned = products.map(p => {
    const content = p.content?.rendered || '';
    const imgRegex = /<img[^>]+src=["']([^"']+)["']/gi;
    let m;
    const imgs = [];
    while ((m = imgRegex.exec(content)) !== null) {
      if (!imgs.includes(m[1])) {
        imgs.push(m[1]);
      }
    }
    const featured = imgs.length > 0 ? imgs[0] : '';

    const name = (p.title?.rendered || '') + ' ' + p.slug;
    let cat = 'other';
    let catName = 'Khác';
    if (/binh-gs|bình gs|ac-quy|ắc quy/i.test(name)) {
      cat = 'battery';
      catName = 'Ắc quy GS chính hãng';
    } else if (/cua-thep|cửa thép|king bac|pharaon/i.test(name)) {
      cat = 'steel-door';
      catName = 'Cửa thép Luxury King Bac';
    } else if (/thong-minh|thông minh|van-tay|vân tay|a16|f7|ng09/i.test(name)) {
      cat = 'smart-lock';
      catName = 'Khóa cửa thông minh';
    } else if (/khoa|khóa|tay gat|tay gạt|kc|ks|ds01|than-khoa/i.test(name)) {
      cat = 'mech-lock';
      catName = 'Khóa tay gạt & Khóa cơ';
    }

    // Extract quick specs from content if available
    const liRegex = /<li><strong>([^:]+):?\s*<\/strong>\s*([^<]+)<\/li>/gi;
    let lm;
    const specs = [];
    while ((lm = liRegex.exec(content)) !== null) {
      specs.push({
        label: lm[1].replace(/:$/, '').trim(),
        value: lm[2].trim()
      });
    }

    return {
      id: p.id,
      title: p.title?.rendered || '',
      slug: p.slug,
      cat,
      catName,
      image: featured || 'https://ductri226.vn/wp-content/uploads/2026/09/Khoa-co-Anh-nen-trang-400x400-2.png',
      images: imgs,
      specs: specs.slice(0, 10),
      content: content,
      date: p.date
    };
  });

  fs.writeFileSync('D:/claudecode/wp_ecommerce/wordpress/products_data.json', JSON.stringify(cleaned, null, 2));
  console.log(`Saved ${cleaned.length} products to products_data.json.`);
}

buildCache().catch(console.error);
