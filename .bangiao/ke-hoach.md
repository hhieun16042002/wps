# KẾ HOẠCH TRIỂN KHAI V2 GIAO DIỆN ĐỨC TRÍ 226 — PHONG CÁCH STEEP
**Agent**: Kiến trúc sư phần mềm (Planner Agent) — Ship Workflow  
**Dự án**: Website Đức Trí 226 (`wp-content/themes/mozlex`)  
**Design Reference**: `# Steep — Style Reference` (Serif analytics on warm paper, Light editorial magazine spread)

---

## I. TỔNG QUAN YÊU CẦU & ĐỊNH HƯỚNG KIẾN TRÚC

### 1. Mục tiêu
Nâng cấp toàn diện giao diện Website Đức Trí 226 (Nhà phân phối khóa thông minh Mozlex, tổng thầu Xây dựng - Thương mại - Công nghệ) từ phong cách cũ sang **Steep V2 Design System**:
- **Bản sắc thương hiệu**: Đức Trí 226 (Xây dựng • Thương mại • Công nghệ Mozlex chính hãng).
- **Ngôn ngữ thẩm mỹ (Steep)**:
  - Tạp chí kiến trúc & sản phẩm cao cấp (Editorial Product Magazine Spread).
  - Tiêu đề Serif Signifier / Source Serif 4 cỡ lớn (90px / 64px / 44px) ở độ dày chuẩn 400 (Regular) thanh tao, uy quyền mà không dùng bold. Cụm từ nhấn nhá in nghiêng (*italicized*).
  - Typography UI / Body: Sans (Plus Jakarta Sans / Inter) với các nấc font-weight vi mô (400, 430, 450, 480, 500).
  - Bảng màu Achromatic 97% + Điểm nhấn ấm Peach Blush (`#fbe1d1`) & Sienna Brown (`#5d2a1a`) như mực in trên giấy kraft.
  - Hình khối: Nút bấm hình con nhộng Pill (`border-radius: 9999px`), Thẻ nội dung bo góc mềm (`border-radius: 24px`), Bề mặt sản phẩm nổi Floating Artifacts (`border-radius: 20px` với đổ bóng 3 lớp siêu nhẹ 10%).
  - Bố cục Hero dạng Collage: Tiêu đề typography mạnh mẽ ở trung tâm, bao quanh bởi các thẻ floating artifact (spec an ninh, sản phẩm Mozlex, badge chuyên gia KTS/ĐT, AI Composer tư vấn cửa).

---

## II. DANH SÁCH FILE CẦN THAY ĐỔI & TẠO MỚI (ĐƯỜNG DẪN CHÍNH XÁC)

1. `[SỬA] wp-content/themes/mozlex/functions.php`:
   - Nạp Google Fonts chuẩn tiếng Việt: `Source Serif 4:ital,opsz,wght@0,8..60,400;1,8..60,400` (thay thế Signifier) và `Plus Jakarta Sans:ital,wght@0,400;0,430;0,450;0,480;0,500;0,600;1,400` (thay thế Sohne).
   - Nâng cấp hằng số phiên bản `MOZLEX_VERSION` lên `3.0.0` để tự động làm mới cache CSS/JS.

2. `[SỬA] wp-content/themes/mozlex/assets/css/main.css`:
   - Tái cấu trúc toàn bộ hệ thống biến CSS `:root` chuẩn Steep Tokens:
     - Màu sắc: `--color-ink-black: #17191c;`, `--color-paper-white: #ffffff;`, `--color-mist-gray: #f2f2f3;`, `--color-fog-white: #fafafb;`, `--color-slate-gray: #777b86;`, `--color-ash-gray: #979799;`, `--color-smoke-gray: #a3a6af;`, `--color-blush-peach: #fbe1d1;`, `--color-sienna-brown: #5d2a1a;`.
     - Phông chữ: `--font-signifier: 'Source Serif 4', Georgia, serif;`, `--font-sohne: 'Plus Jakarta Sans', system-ui, sans-serif;`.
     - Spacing 4px base unit & Border radius (buttons: 9999px, cards: 24px, artifacts: 20px, inputs: 16px).
     - Hệ thống bóng đổ 3 cấp `--shadow-subtle`, `--shadow-subtle-2`, `--shadow-subtle-3`.
   - Cài đặt các component Steep:
     - `.btn-pill-primary`, `.btn-pill-ghost`, `.btn-pill-peach`.
     - `.card-neutral` (nền Mist Gray #f2f2f3, bo 24px, không viền, không bóng).
     - `.card-accent-peach` (nền Blush Peach #fbe1d1, chữ & viền Sienna Brown #5d2a1a, bo 24px).
     - `.floating-artifact` (nền Paper White, bo 20px, đổ bóng hairline subtle-3, viền 1px mờ).
     - `.steep-composer` (hộp thoại tư vấn mô phỏng AI composer phong cách Steep: input 16px radius, nút gửi tròn đen #17191c).
     - `.avatar-bubble` (con trỏ tương tác thời gian thực của kỹ sư/KTS).

3. `[SỬA] wp-content/themes/mozlex/header.php`:
   - Thiết kế lại Header phong cách Steep: Whisper-quiet, trong suốt trên nền trắng giấy, đường nét hairline thanh thoát.
   - Logo Đức Trí 226 tinh gọn kết hợp tên công ty và huy hiệu đối tác độc quyền Mozlex.
   - Menu điều hướng tối giản, link không gạch chân, nút CTA hình con nhộng đen Ink Black (`border-radius: 9999px`) "Nhận tư vấn" kèm hotline `03.555.14.686`.

4. `[SỬA] wp-content/themes/mozlex/front-page.php`:
   - **Hero Section V2**:
     - Display headline 72-90px Serif Source Serif 4 (weight 400), có đoạn in nghiêng tạo nhịp điệu báo chí tạp chí.
     - Cặp nút bấm con nhộng Steep: Filled Ink Black (`#17191c`) + Ghost (`border: 1px solid #17191c`).
     - Bố cục 3 Floating Artifacts vây quanh headline:
       - Artifact 1: Thẻ Thông số An ninh C-Grade (Chuẩn lõi khóa 6068, inox 304, vân tay FPC Thụy Điển).
       - Artifact 2: Thẻ Sản phẩm Nổi bật Mozlex với ảnh sắc nét, đánh giá 4.9★ và avatar bubble của Kỹ thuật viên Đức Trí.
       - Artifact 3: AI / Consultation Composer ("Hỏi chuyên gia Đức Trí 226 về đố cửa, loại khóa...").
   - **Section 3 Nhánh Đức Trí 226**:
     - 3 cột thẻ bo góc 24px chuẩn Steep: 2 thẻ Neutral Mist Gray (`#f2f2f3`) cho Xây dựng & Thương mại, 1 thẻ Signature Accent Blush Peach (`#fbe1d1`) chữ Sienna Brown (`#5d2a1a`) cho Công nghệ & Khóa thông minh Mozlex.
   - **Section Giới thiệu & Triết lý Đức Trí 226**:
     - Thẻ trích dẫn Editorial với câu nói thương hiệu trên nền giấy ấm (`#fafafb`), các số liệu ấn tượng (13+ năm, 5000+ công trình, 100% chính hãng).
   - **Section Bộ sưu tập Khóa Mozlex**:
     - Lưới thẻ sản phẩm Floating Artifacts bo 20px, bóng nhẹ 10%, nhãn danh mục dạng typographic ghost tags (`#979799`).
   - **Section Dịch vụ & Quy trình tư vấn**:
     - Quy trình 4 bước tinh giản với đường line kết nối mảnh và số thứ tự typography thanh nhã.

5. `[SỬA] wp-content/themes/mozlex/footer.php`:
   - Footer chuẩn Steep Paper White / Fog White với viền hairline mỏng nhẹ.
   - Typography phân cấp rõ ràng: Tên doanh nghiệp đầy đủ "Công ty TNHH Xây dựng Thương mại và Công nghệ Đức Trí 226", địa chỉ, mã số thuế, hotline, danh mục giải pháp.
   - Nút liên hệ nhanh dạng Pill và cam kết chất lượng.

6. `[SỬA] wp-content/themes/mozlex/inc/seo-schema.php`:
   - Chuẩn hóa Schema Organization & metadata cho "Đức Trí 226".

---

## III. THIẾT KẾ CHI TIẾT & GIAO DIỆN (INTERFACES & SPECIFICATIONS)

### 1. Hệ thống Tokens CSS (Steep Specifications)
```css
:root {
  /* Colors */
  --color-ink-black: #17191c;
  --color-paper-white: #ffffff;
  --color-mist-gray: #f2f2f3;
  --color-fog-white: #fafafb;
  --color-slate-gray: #777b86;
  --color-ash-gray: #979799;
  --color-smoke-gray: #a3a6af;
  --color-blush-peach: #fbe1d1;
  --color-sienna-brown: #5d2a1a;

  /* Typography */
  --font-signifier: 'Source Serif 4', Georgia, 'Times New Roman', serif;
  --font-sohne: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;

  /* Font Weights */
  --fw-regular: 400;
  --fw-430: 430;
  --fw-450: 450;
  --fw-480: 480;
  --fw-medium: 500;
  --fw-semibold: 600;

  /* Radii */
  --radius-buttons: 9999px;
  --radius-cards: 24px;
  --radius-elevated: 20px;
  --radius-inputs: 16px;
  --radius-images: 12px;

  /* Shadows */
  --shadow-subtle: 0 0 0 1px rgba(0, 0, 0, 0.05), 0 4px 24px 0 rgba(0, 0, 0, 0.04);
  --shadow-subtle-2: 0 0 0 1px rgba(0, 0, 0, 0.05), 0 8px 40px 0 rgba(0, 0, 0, 0.06);
  --shadow-subtle-3: 0 0 0 1px rgba(4, 23, 43, 0.05), 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
}
```

### 2. Các Quy tắc Do's and Don'ts theo Steep Spec
- **Do**:
  - Dùng Serif Source Serif 4 ở weight 400 (Regular) cho tất cả tiêu đề H1/H2 lớn, không dùng bold ở các kích thước lớn.
  - Thẻ Accent Peach (`#fbe1d1`) chỉ dùng tối đa 1 thẻ trong mỗi màn hình/section để tạo sự tương phản kraft-paper độc đáo.
  - Bo tròn nút bấm tối đa `9999px` (Pill button) và ghép cặp nút Filled `#17191c` đi kèm nút Ghost `border: 1px solid #17191c`.
  - Giữ khoảng cách thở rộng rãi (`section-gap: 80px`), max-width container `1200px`.
- **Don't**:
  - Không dùng các màu rực rỡ ngoài cặp Peach `#fbe1d1` và Sienna Brown `#5d2a1a`.
  - Không dùng drop-shadow đậm trên các thẻ thông thường; chỉ dùng bóng nổi nhẹ trên các Floating Artifacts.
  - Không gạch chân link mặc định; chỉ hiển thị mũi tên `→` và gạch chân khi hover.

---

## IV. CÁC TRƯỜNG HỢP BIÊN (EDGE CASES)
1. **Màn hình Mobile & Tablet (< 768px)**:
   - Các tiêu đề 90px / 64px tự động co giãn bằng `clamp(2.2rem, 7vw, 5.6rem)` để không bị tràn màn hình.
   - Các thẻ Floating Artifacts ở Hero được chuyển thành dạng vertical stack có thứ tự ưu tiên hợp lý hoặc cuộn ngang mềm mại.
   - Menu drawer mobile giữ phong cách phẳng tối giản, nút bấm pill dễ chạm (min touch target 44px).
2. **Không đủ sản phẩm hoặc chưa upload hình ảnh**:
   - Có sẵn ảnh fallback chất lượng cao của Mozlex và Đức Trí 226, không làm vỡ layout lưới.
3. **Trình duyệt không hỗ trợ font ngoại tuyến**:
   - Font stack có Georgia/Times New Roman cho Serif và system-ui cho Sans để bảo toàn tỷ lệ văn bản.

---

## V. CÂU HỎI BỎ NGỎ
- Không có câu hỏi ngăn trở. Mọi chỉ dẫn trong Steep Spec đã được chuẩn hóa và ánh xạ 1:1 vào theme WordPress `mozlex` của Đức Trí 226.

---
**Kế hoạch đã sẵn sàng để bàn giao cho Coder Agent thực thi.**
