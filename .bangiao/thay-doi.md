# BÁO CÁO THAY ĐỔI MÃ NGUỒN (CODER REPORT)

**Dự án:** WordPress Đức Trí 226 / Mozlex  
**Ngày thực hiện:** 19/09/2026  
**Vai trò:** Coder Agent  
**Tình trạng:** Hoàn tất triển khai mã nguồn  

---

## 1. Tóm tắt nội dung thay đổi
Đã xây dựng và tích hợp hệ thống quản lý trạng thái **Bật / Tắt (Ẩn / Hiển thị)** thống nhất, tiện lợi và an toàn trực tiếp trong bảng quản trị WordPress (`wp-admin`) cho cả 4 đối tượng:
1. **Sản phẩm (`product`)**
2. **Tin tức / Bài viết (`post`)**
3. **Dự án tiêu biểu (`ductri_project`)**
4. **Danh mục (`product_category` và `category`)**

---

## 2. Chi tiết các tệp tin đã chỉnh sửa

### 2.1. Plugin `wp-content/plugins/ductri-content-manager/ductri-content-manager.php`
- **Mở rộng danh sách Post Types hỗ trợ:**
  ```php
  function dt226_visibility_post_types() {
      return array( 'post', 'ductri_project', 'product' );
  }
  ```
  Thêm cột "Bật / Tắt" vào màn hình danh sách Sản phẩm (`edit.php?post_type=product`), Tin tức (`edit.php`), Dự án (`edit.php?post_type=ductri_project`).
- **Nút bấm Toggle 1-click cho Post Types:**
  - Trạng thái `publish`: Hiển thị nhãn xanh **● Đang bật**, nút **Tắt**.
  - Trạng thái `draft`: Hiển thị nhãn xám **○ Đang tắt**, nút **Bật**.
  - Xử lý qua action `admin_post_dt226_toggle`, kiểm tra nonce và quyền hạn `edit_post` / `publish_posts` chặt chẽ.
- **Xây dựng hệ thống Ẩn / Hiện Danh mục (Taxonomy Terms):**
  - Đăng ký Term Meta `_dt226_term_hidden` (`boolean`).
  - Thêm ô chọn "Trạng thái hiển thị" (Bật / Ẩn) vào form Thêm mới và Sửa danh mục cho cả `product_category` (Danh mục sản phẩm) và `category` (Danh mục tin tức).
  - Thêm cột "Bật / Tắt" vào bảng danh sách danh mục (`edit-tags.php`) với nhãn trạng thái và nút bấm 1-click chuyển đổi nhanh.
  - Action xử lý `admin_post_dt226_term_toggle` cập nhật tức thì qua AJAX/Form POST.
- **Bộ lọc Frontend tự động (Frontend Exclusion):**
  - Hook `get_terms_args`: Tự động loại trừ các term có meta `_dt226_term_hidden = 1` khỏi toàn bộ menu, widget danh mục, bộ lọc tìm kiếm sản phẩm và trang chủ đối với khách vãng lai (`! is_admin()`).
  - Hook `template_redirect`: Chặn khách vãng lai truy cập trực tiếp vào URL archive của danh mục bị ẩn, chuyển hướng 404 an toàn. Người dùng quản trị đăng nhập vẫn xem và quản lý được.
- **Tối ưu giao diện WP-Admin:**
  - CSS Badge trạng thái tinh tế, bo tròn chuẩn mực.
  - Thông báo hướng dẫn quản trị viên trực quan ngay đầu danh sách.

### 2.2. Script `wp-content/plugins/ductri-content-manager/admin.js`
- Bổ sung listener xử lý click cho cả hai loại nút bấm:
  - `.dt226-toggle`: Chuyển đổi trạng thái Sản phẩm, Tin tức, Dự án.
  - `.dt226-term-toggle`: Chuyển đổi trạng thái Danh mục sản phẩm, Danh mục tin tức.
- Thêm hiệu ứng `updating-message` và disable nút tránh click lặp (duplicate requests).

---

## 3. Bàn giao cho Tester Agent
Mã nguồn đã sẵn sàng, không có lỗi cú pháp. Tester Agent có thể tiến hành kiểm thử các thao tác Bật/Tắt trong WP-Admin và kiểm tra kết quả hiển thị tương ứng ngoài Frontend.
