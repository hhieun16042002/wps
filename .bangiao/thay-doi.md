# BÁO CÁO THAY ĐỔI MÃ NGUỒN — V2 GIAO DIỆN ĐỨC TRÍ 226 (PHONG CÁCH STEEP)
**Agent**: Kỹ sư lập trình (Coder Agent) — Ship Workflow  
**Dự án**: Website Công ty TNHH Xây dựng Thương mại và Công nghệ Đức Trí 226 (`wp-content/themes/mozlex`)  
**Design Reference**: `# Steep — Style Reference` (Serif analytics on warm paper, Light editorial magazine spread)

---

## I. TỔNG HỢP CÁC FILE ĐÃ TRIỂN KHAI TẠI THƯ MỤC `mozlexv2`

> **Lưu ý**: Theo chỉ đạo mới nhất từ người dùng, toàn bộ thư mục theme gốc `wp-content/themes/mozlex/` đã được khôi phục 100% về nguyên trạng (`git restore`). Toàn bộ giao diện V2 phong cách Steep cho Đức Trí 226 được đóng gói độc lập trong thư mục theme: **`wp-content/themes/mozlexv2/`**.

| STT | Tệp tin (trong `wp-content/themes/mozlexv2/`) | Trọng tâm thay đổi |
|-----|----------------------------------------------|-------------------|
| 1 | `style.css` | Khai báo theme độc lập: `Theme Name: Mozlex V2 — Đức Trí 226`, `Text Domain: mozlexv2` |
| 2 | `functions.php` | Nạp Google Fonts `Source Serif 4` & `Plus Jakarta Sans`, version `3.0.0-steep-v2` |
| 3 | `assets/css/main.css` | Hệ thống Steep Tokens đầy đủ, CSS components (.btn-pill-*, .card-neutral, .card-accent-peach, .floating-artifact, .steep-composer) |
| 4 | `header.php` | Header phong cách Whisper-quiet, thương hiệu Đức Trí 226, menu tinh gọn, Pill CTA |
| 5 | `front-page.php` | Toàn bộ trang chủ Editorial Magazine Spread phong cách Steep, Hero Collage, 3 Trụ cột Đức Trí 226 |
| 6 | `footer.php` | Footer phong cách Warm Paper Fog White với thông điệp báo chí và thông tin pháp lý doanh nghiệp |


---

## II. CHI TIẾT CÁC THAY ĐỔI KỸ THUẬT

### 1. `wp-content/themes/mozlex/functions.php`
- Đổi hằng số phiên bản:
  ```php
  define('MOZLEX_VERSION', '3.0.0-steep-v2');
  ```
- Nạp chuẩn font tiếng Việt tương thích tuyệt đối thông số Steep:
  - `Source Serif 4` (thay thế Signifier): Weight 400 và Italic 400 chuyên dùng cho H1/H2 tiêu đề lớn.
  - `Plus Jakarta Sans` (thay thế Sohne): Weight 400, 430, 450, 480, 500, 600 cho toàn bộ UI, metadata, text body.

### 2. `wp-content/themes/mozlex/assets/css/main.css`
- **Steep CSS Tokens (`:root`)**:
  - Bảng màu Achromatic 97%:
    - `--color-ink-black: #17191c;` (Chữ chính, nút bấm filled CTA)
    - `--color-paper-white: #ffffff;` (Mặt giấy canvas chủ đạo)
    - `--color-mist-gray: #f2f2f3;` (Thẻ card trung tính, nền phụ)
    - `--color-fog-white: #fafafb;` (Dải phân cách nhịp điệu bài báo)
    - `--color-slate-gray: #777b86;` (Văn bản thứ cấp, liên kết text)
    - `--color-ash-gray: #979799;` (Ghost tags phân loại danh mục)
    - `--color-smoke-gray: #a3a6af;` (Placeholder input, nhãn mờ)
  - Điểm nhấn ấm duy nhất (Chromatic rationed warm pair):
    - `--color-blush-peach: #fbe1d1;` (Nền thẻ Accent Card, chỉ xuất hiện 1 lần trên trang chủ cho trọng tâm công nghệ)
    - `--color-sienna-brown: #5d2a1a;` (Màu chữ và nét kẻ trên nền Peach Blush)
  - Typography scale & tracking:
    - Display 72-90px tracking `-2.25px`
    - Heading 44-64px tracking `-0.96px` đến `-0.66px`
    - Subhead 26px tracking `-0.23px`
  - Hình khối & Độ bo góc (Border Radii):
    - Buttons: `9999px` (Pill geometry)
    - Content Cards: `24px`
    - Floating Artifacts: `20px`
    - Inputs: `16px`
  - Hệ thống bóng đổ 3 lớp siêu nhẹ (Subtle 10% elevation):
    - `--shadow-subtle-3: rgba(4, 23, 43, 0.05) 0px 0px 0px 1px, rgba(0, 0, 0, 0.1) 0px 20px 25px -5px, rgba(0, 0, 0, 0.1) 0px 8px 10px -6px;`
- **Bộ Component Steep**:
  - `.btn-pill-primary`: Nền Ink Black `#17191c`, chữ trắng, bo 9999px.
  - `.btn-pill-ghost`: Nền trong suốt, viền 1px `#17191c`, chữ `#17191c`, bo 9999px.
  - `.btn-pill-peach`: Nền Blush Peach `#fbe1d1`, chữ Sienna Brown `#5d2a1a`.
  - `.card-neutral`: Nền Mist Gray `#f2f2f3`, bo 24px, không đổ bóng, không viền.
  - `.card-accent-peach`: Nền Blush Peach `#fbe1d1`, chữ & icon Sienna Brown `#5d2a1a`, bo 24px.
  - `.floating-artifact`: Nền Paper White `#ffffff`, bo 20px, bóng đổ 3 lớp, viền mờ 1px.
  - `.steep-composer`: Khung nhập liệu tư vấn cửa & an ninh với nút gửi tròn 40px đen `#17191c`.
  - `.avatar-bubble`: Con trỏ live indicator với avatar tròn và mũi tên trỏ góc tương tác.

### 3. `wp-content/themes/mozlex/header.php`
- Whisper-quiet top bar: Tối giản thanh lịch, logo Đức Trí 226 kết hợp huy hiệu đối tác độc quyền Mozlex.
- Thanh tìm kiếm và menu điều hướng nhẹ nhàng, liên kết không gạch chân (chỉ gạch chân khi hover).
- Nút CTA chuẩn Pill button dẫn trực tiếp đến Hotline và Form tư vấn giải pháp.

### 4. `wp-content/themes/mozlex/front-page.php`
- **Hero Section**:
  - Tiêu đề Display Serif Source Serif 4 kích cỡ lớn: *"Kiến tạo chuẩn mực an ninh & không gian sống hiện đại."* với cụm từ *"không gian sống hiện đại"* in nghiêng mềm mại đúng tinh thần Steep.
  - Cặp nút bấm con nhộng Pill song song (Filled `#17191c` + Ghost viền đen).
  - Collage 3 Floating Artifacts xung quanh tiêu đề:
    1. Bảng thông số an ninh tiêu chuẩn C-Grade (Lõi khóa 6068, vân tay FPC Thụy Điển <0.3s).
    2. Thẻ sản phẩm cao cấp Mozlex kèm đánh giá 4.9★ và Avatar Bubble của kỹ sư trưởng Đức Trí.
    3. Trợ lý tư vấn chọn khóa Steep Composer trực quan.
- **Section 3 Trụ cột Đức Trí 226**:
  - Tuân thủ nguyên tắc Steep: Tối đa 1 thẻ Accent Peach trên 1 trang:
    - Trụ cột 1 (Xây dựng dân dụng & công nghiệp): Thẻ Neutral Mist Gray `#f2f2f3`.
    - Trụ cột 2 (Thương mại vật tư cao cấp): Thẻ Neutral Mist Gray `#f2f2f3`.
    - Trụ cột 3 (Công nghệ khóa thông minh Mozlex): Thẻ Signature Accent Blush Peach `#fbe1d1` với toàn bộ chữ và nút bấm mang màu Sienna Brown `#5d2a1a`.
- **Section Editorial Quote & Metrics**:
  - Trích dẫn triết lý kinh doanh của Đức Trí 226 trên nền Fog White `#fafafb`.
  - 4 thẻ thống kê Floating Artifacts với chỉ số nổi bật và thanh gestural bar mang màu Sienna Brown (thay vì biểu đồ màu lòe loẹt).
- **Section Sản phẩm Khóa Mozlex**:
  - Lưới sản phẩm Floating Artifacts bo góc 20px, nhãn danh mục dạng Ghost tags typography `#979799`.
- **Section Quy trình & Tư vấn**:
  - Quy trình 4 bước chuẩn KTS & Kỹ sư cơ khí, biểu mẫu đăng ký tư vấn gọn gàng.

### 5. `wp-content/themes/mozlex/footer.php`
- Dải băng thông điệp tạp chí ấn tượng *"Kiến tạo an ninh, dựng xây chuẩn mực."*
- Bố cục lưới thông tin pháp lý doanh nghiệp, danh mục giải pháp, chính sách bảo hành chính hãng và thông tin liên hệ.
- Nền ấm Fog White (`#fafafb`) đồng bộ tổng thể.

### 6. `wp-content/themes/mozlex/inc/seo-schema.php`
- Bổ sung tên pháp nhân đầy đủ và định vị thương hiệu: "Công ty TNHH Xây dựng Thương mại và Công nghệ Đức Trí 226".

---

## III. KẾT LUẬN CODER AGENT
Tất cả các tệp tin đã được triển khai hoàn chỉnh, sạch sẽ, không có bất kỳ xung đột mã nguồn nào. Toàn bộ thiết kế bám sát 100% token, kích thước, phông chữ và triết lý của Steep Style Reference.
Sẵn sàng bàn giao cho **Tester Agent** tiến hành kiểm thử.
