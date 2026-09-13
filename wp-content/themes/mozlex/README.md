# MOZLEX — WordPress Theme (Luxury Architectural Hardware)

Theme tuỳ biến cho website catalogue thương hiệu Mozlex. Không dùng page builder,
không WooCommerce, không ACF — chỉ WordPress core + một file CSS + một file JS.

## Cấu trúc

```
mozlex/
├── style.css                  # Khai báo theme
├── functions.php              # Bootstrap, enqueue assets, image sizes
├── header.php / footer.php    # Header sticky + drawer mobile + modal search; footer + sticky CTA mobile
├── front-page.php             # Trang chủ (hero, brand statement, bộ sưu tập, nổi bật, wizard teaser)
├── archive-product.php        # Archive sản phẩm: filter (giá/unlock/cửa/màu) + sort + compare bar
├── taxonomy-product_category.php  # Nạp lại archive layout cho từng nhóm
├── single-product.php         # Chi tiết sản phẩm: hero, highlights, gallery, spec table, related
├── page.php                   # Generic page + blog index + kết quả tìm kiếm
├── page-templates/
│   ├── template-about.php         # Về Mozlex (/ve-mozlex/)
│   ├── template-warranty.php      # Bảo hành (/bao-hanh/)
│   ├── template-consultation.php  # Wizard 5 bước (/tu-van/)
│   └── template-contact.php       # Liên hệ (/lien-he/)
├── inc/
│   ├── cpt.php                # CPT `product` (rewrite /san-pham/) + taxonomies + seed terms
│   ├── meta-boxes.php         # Meta box dữ liệu sản phẩm (không cần plugin)
│   ├── theme-options.php      # Hotline/Zalo/email/địa chỉ/bảo hành — quản lý tập trung
│   ├── contact-handler.php    # Form liên hệ: nonce + honeypot + validate server-side, lưu submission
│   ├── seo-schema.php         # Meta/OG/canonical fallback + JSON-LD (Organization/Product/Breadcrumb)
│   ├── rest.php               # REST autocomplete + so sánh
│   ├── importer.php           # Import catalogue từ inc/data/products.json
│   └── data/products.json     # Dữ liệu thật trích từ Catalogue Mozlex 2026 (44 model)
└── assets/
    ├── css/main.css           # Design tokens + layout (Be Vietnam Pro)
    └── js/main.js             # Micro-interactions, wizard, compare, lightbox (defer, vanilla)
```

## Cài đặt

1. Có sẵn Docker stack ở `../wp_ecommerce` (`compose.yml`, port **8080**):
   ```bash
   cd ../wp_ecommerce && docker compose up -d
   ```
2. Copy theme vào WordPress:
   ```bash
   cp -r mozlex ../wp_ecommerce/wordpress/wp-content/themes/
   ```
3. WP Admin → Appearance → Themes → kích hoạt **Mozlex Luxury Catalogue**.
4. WP Admin → Settings → Permalinks → chọn **Post name**, bấm Save (flush rewrite rules).

## Nhập sản phẩm (dữ liệu catalogue thật)

- **Admin:** Tools → Import Mozlex → bấm *Chạy import*.
- **WP-CLI:** `docker compose exec wordpress wp mozlex import --allow-root`

Importer tạo 4 nhóm (Khóa thông minh, Khóa tay gạt, Khóa kéo đẩy, Phụ kiện) và
tất cả model có trong `inc/data/products.json` cùng giá/thông số lấy trực tiếp từ
catalogue PDF 2026. Chạy lại là update theo slug, không tạo trùng.

**Thêm sản phẩm mới:** Products/Sản phẩm Mozlex → Add New → điền box *Dữ liệu Mozlex*
(model, giá, thông số), gán terms (nhóm/tính năng/màu/loại cửa), tick *Nổi bật* nếu muốn
lên trang chủ, đặt featured image.

## Quản trị nhanh

| Việc | Đặt ở đâu |
|---|---|
| Hotline / Zalo / Email / Địa chỉ / Social | Settings → Mozlex |
| Số tháng bảo hành / đổi mới | Settings → Mozlex → dùng trên /bao-hanh/ |
| Logo | Appearance → Customize → Site Identity |
| Menu chính | Appearance → Menus → gán vào vị trí *Menu chính* (có fallback tự động) |
| Nội dung trang Bảo hành | Pages → Bảo hành → sửa nội dung body |
| SEO title/description | Cài Rank Math hoặc Yoast — theme tự tắt meta riêng khi phát hiện plugin |

## SEO & cấu trúc URL

- URL chuẩn: `/khoa-thong-minh/`, `/khoa-co/`, `/san-pham/a16/`, `/ve-mozlex/`, `/bao-hanh/`, `/tu-van/`, `/lien-he/`
- Filter/sort là query param → tự động `noindex,follow`
- JSON-LD: Product chỉ khai báo giá khi có giá thật; không fake rating/availability
- H1 duy nhất mỗi trang, breadcrumb mọi nơi, alt text tiếng Việt

## Pages cần tạo thủ công sau khi kích hoạt

Tạo Page với slug tương ứng và gán Template:

| Page | Slug | Template |
|---|---|---|
| Về Mozlex | `ve-mozlex` | *Về Mozlex* |
| Bảo hành | `bao-hanh` | *Bảo hành* |
| Tư vấn | `tu-van` | *Tư vấn chọn khóa* |
| Liên hệ | `lien-he` | *Liên hệ* |
| Blog | `blog` | (mặc định, set làm Posts Page nếu muốn /blog/) |
| Trang chủ | (trống) | mặt định dùng front-page.php — set làm Homepage trong Settings → Reading |

## Ảnh sản phẩm

Theme chưa kèm ảnh (theo quy tắc không dùng ảnh giả). Gắn Featured Image + Gallery ID
cho từng sản phẩm sau khi upload ảnh chụp/catalogue thật. Hiển thị giữ nguyên tỷ lệ
(`object-fit: contain`). Card placeholder hiển thị tên model khi chưa có ảnh.

## Xử lý sự cố

- Lỗi 404 ở `/san-pham/...`: Settings → Permalinks → Save lần nữa để flush rules.
- Autocomplete không chạy: kiểm tra REST API còn bật (nó dùng `/wp-json/mozlex/v1/products`).
