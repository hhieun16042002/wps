(function () {
  'use strict';

  document.addEventListener('click', function (event) {
    // 1. Toggle Post Status (Sản phẩm, Tin tức, Dự án)
    var postBtn = event.target.closest('.dt226-toggle');
    if (postBtn && !postBtn.disabled) {
      postBtn.disabled = true;
      postBtn.classList.add('updating-message');
      postBtn.textContent = 'Đang lưu...';
      var form = document.createElement('form');
      form.method = 'post';
      form.action = DT226Admin.url;
      var values = {
        action: 'dt226_toggle',
        post_id: postBtn.dataset.id,
        status: postBtn.dataset.status,
        _wpnonce: postBtn.dataset.nonce
      };
      Object.keys(values).forEach(function (key) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = values[key];
        form.appendChild(input);
      });
      document.body.appendChild(form);
      form.submit();
      return;
    }

    // 2. Toggle Term Visibility (Danh mục sản phẩm, Danh mục tin tức)
    var termBtn = event.target.closest('.dt226-term-toggle');
    if (termBtn && !termBtn.disabled) {
      termBtn.disabled = true;
      termBtn.classList.add('updating-message');
      termBtn.textContent = 'Đang lưu...';
      var termForm = document.createElement('form');
      termForm.method = 'post';
      termForm.action = DT226Admin.url;
      var termValues = {
        action: 'dt226_term_toggle',
        term_id: termBtn.dataset.id,
        hidden: termBtn.dataset.hidden,
        _wpnonce: termBtn.dataset.nonce
      };
      Object.keys(termValues).forEach(function (key) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = termValues[key];
        termForm.appendChild(input);
      });
      document.body.appendChild(termForm);
      termForm.submit();
      return;
    }
  });
})();
