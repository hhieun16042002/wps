# KẾ HOẠCH BÀN GIAO: BẬT / ẨN SẢN PHẨM, TIN TỨC, DỰ ÁN & DANH MỤC TRONG ADMIN WORDPRESS

**Dự án:** Đức Trí 226 / Mozlex E-commerce (WordPress)  
**Ngày lập:** 19/09/2026  
**Vai trò:** Planner Agent  
**Yêu cầu:** Cho phép phía Admin có thể Ẩn và Hiển thị:
1. **Sản phẩm** (Post Type `product`)
2. **Tin tức** (Post Type `post`)
3. **Dự án** (Post Type `ductri_project`)
4. **Danh mục** (Taxonomies `product_category` và `category`)

---

## 1. Khảo Sát Hiện Trạng Hệ Thống
1. **Sản phẩm (`product`):**
   - Được đăng ký trong theme `mozlex` tại `wp-content/themes/mozlex/inc/cpt.php`.
   - Đang dùng trạng thái chuẩn của WordPress (`publish`, `draft`), nhưng trong bảng quản trị `edit.php?post_type=product` chưa có nút Bật/Tắt 1-click trực tiếp.
2. **Tin tức (`post`) & Dự án (`ductri_project`):**
   - Đã được đăng ký trong plugin `ductri-content-manager`.
   - Đã có cột "Bật / Tắt" và nút toggle chuyển đổi giữa `publish` (Đang bật) và `draft` (Đang tắt).
3. **Danh mục (`product_category` & `category`):**
   - Chưa có cơ chế Bật/Ẩn độc lập cho Taxonomy Terms.
   - Khi muốn ẩn một danh mục tạm thời, người quản trị chưa có nút Bật/Ẩn mà phải xóa hoặc ẩn toàn bộ sản phẩm bên trong.

---

## 2. Giải Pháp Kỹ Thuật (Architecture & Implementation)

### 2.1. Hợp nhất Quản Lý Bật/Tắt Bài Viết, Dự Án & Sản Phẩm
- Cập nhật danh sách post types hỗ trợ trong plugin `ductri-content-manager`:
  ```php
  $supported_types = array( 'post', 'ductri_project', 'product' );
  ```
- Hiển thị cột **"Bật / Tắt"** trên bảng danh sách của cả 3 loại nội dung:
  - Bài viết / Tin tức: `/wp-admin/edit.php`
  - Dự án tiêu biểu: `/wp-admin/edit.php?post_type=ductri_project`
  - Sản phẩm Mozlex: `/wp-admin/edit.php?post_type=product`
- Nút bấm 1-click đổi trạng thái ngay tại bảng:
  - **Đang bật (Publish):** Hiển thị công khai ngoài website.
  - **Đang tắt (Draft):** Ẩn hoàn toàn khỏi website và đường dẫn công khai, không xóa dữ liệu, có thể bật lại bất cứ lúc nào chỉ với 1 click.

### 2.2. Xây Dựng Cơ Chế Ẩn / Hiển Thị Danh Mục (Taxonomy Terms Visibility)
- **Áp dụng cho 2 taxonomy chính:**
  - `product_category` (Danh mục sản phẩm)
  - `category` (Danh mục tin tức)
- **Lưu trữ trạng thái:**
  - Sử dụng Term Meta: `_dt226_term_hidden` (`1` = Đang ẩn, `0` hoặc không có = Đang bật).
- **Giao diện trong WP-Admin:**
  - Trong form **Thêm danh mục mới** & form **Chỉnh sửa danh mục**:
    - Thêm ô chọn: **Trạng thái hiển thị** ("Hiển thị công khai" / "Ẩn khỏi website").
  - Trong bảng **Danh sách danh mục** (`edit-tags.php`):
    - Thêm cột **"Bật / Tắt"**.
    - Hiển thị nhãn trạng thái trực quan: **Đang bật** (màu xanh lá) / **Đang ẩn** (màu xám/đỏ cam).
    - Nút bấm 1-click AJAX để Admin chuyển đổi nhanh mà không cần vào trang sửa chi tiết.
- **Xử lý hiển thị ngoài Frontend (Frontend Exclusion):**
  - Hook `get_terms_args` và `get_terms`: Tự động loại trừ các danh mục bị ẩn khỏi thanh menu, widget danh mục, bộ lọc tìm kiếm sản phẩm và trang chủ đối với khách vãng lai.
  - Hook `template_redirect`: Nếu khách vãng lai truy cập trực tiếp vào URL của danh mục đang bị ẩn (ví dụ `/nhom-san-pham/danh-muc-bi-an/`), chuyển hướng an toàn sang 404 hoặc về trang danh sách chính để đảm bảo danh mục được bảo mật ẩn hoàn toàn.
  - Người dùng có quyền quản trị (`manage_categories`) khi đăng nhập vẫn xem trước và chỉnh sửa bình thường.

---

## 3. Danh Sách Tệp Cần Chỉnh Sửa
1. `wp-content/plugins/ductri-content-manager/ductri-content-manager.php`:
   - Bổ sung `'product'` vào danh sách post types.
   - Thêm các hooks quản lý Term Meta và cột giao diện cho Taxonomies (`category`, `product_category`).
   - Thêm handler `admin_post_dt226_term_toggle` để xử lý nút chuyển đổi trạng thái danh mục.
   - Thêm logic lọc frontend tự động ẩn các term bị tắt.
2. `wp-content/plugins/ductri-content-manager/admin.js`:
   - Mở rộng hỗ trợ xử lý click toggle cho cả nút danh mục (`.dt226-term-toggle`).

---

## 4. Trình Tự Thực Hiện
1. `Planner`: Hoàn thiện `.bangiao/ke-hoach.md`.
2. `Coder`: Triển khai code vào `ductri-content-manager.php` và `admin.js`, lập `.bangiao/thay-doi.md`.
3. `Tester`: Chạy kiểm thử cú pháp PHP lint, kiểm thử logic lưu meta, kiểm thử query frontend, lập `.bangiao/ket-qua-test.md`.
4. `Reviewer`: Thẩm định chất lượng tổng thể, lập `.bangiao/danh-gia.md` và `[CHỐT]`.
