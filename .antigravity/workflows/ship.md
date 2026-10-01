---
name: ship
description: Tự động gọi lần lượt 4 agent Planner -> Coder -> Tester -> Reviewer.
---
Quy trình thực hiện theo thứ tự:
1. Chạy subagent `planner` để tạo `.bangiao/ke-hoach.md`.
2. Chạy subagent `coder` dựa trên bản kế hoạch để tạo code và `.bangiao/thay-doi.md`.
3. Chạy subagent `tester` để kiểm thử code mới và ghi `.bangiao/ket-qua-test.md`.
4. Chạy subagent `reviewer` để chốt chất lượng vào `.bangiao/danh-gia.md`.
Dừng lại và thông báo cho người dùng khi hoàn tất.
