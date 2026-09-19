/**
 * JavaScript tương tác cho WP Product Crawler & Importer
 * Hỗ trợ State Persistence, Crash Recovery, Process Controls (Tiếp tục, Tạm dừng, Bỏ qua, Hủy) và Auto Publish
 */

(function ($) {
	'use strict';

	var currentJobId = 0;
	var itemsQueue = [];          // Hàng đợi bóc tách URL
	var importQueue = [];         // Hàng đợi ID sản phẩm cần nhập
	var extractedItems = {};      // Bộ nhớ tạm dữ liệu sản phẩm đã bóc tách
	var activeSessionData = null; // Dữ liệu phiên dở dang (Crash Recovery)

	var isPaused = false;
	var isCancelled = false;
	var isImporting = false;
	var isExtracting = false;
	var currentProcessingItemId = 0;
	var totalToImport = 0;
	var importedCount = 0;

	var stats = {
		found: 0,
		processed: 0,
		images: 0,
		errors: 0
	};

	$(document).ready(function () {
		initScanner();
		initPreviewTable();
		initProcessControls();
		initCrashRecovery();
		initEditModal();
		initSettings();
		initHistory();
	});

	/* =========================================================================
	 * 1. QUÉT WEBSITE & BÓC TÁCH TUẦN TỰ (SCANNER & EXTRACTION)
	 * ========================================================================= */
	function initScanner() {
		$('#dt-crawler-scan-btn').on('click', function (e) {
			e.preventDefault();

			var url = $.trim($('#dt-crawler-url').val());
			if (!url) {
				alert('Vui lòng nhập URL website hợp lệ.');
				$('#dt-crawler-url').focus();
				return;
			}

			// Reset giao diện và thống kê
			resetState();

			var $btn = $(this);
			$btn.prop('disabled', true).addClass('updating-message');
			$('#dt-crawler-progress-card').slideDown();
			updateProgress(5, dtCrawlerData.i18n.scanning);

			// Bước 1: Gửi request bắt đầu scan và tạo Job
			$.ajax({
				url: dtCrawlerData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'dt_crawler_start_scan',
					nonce: dtCrawlerData.nonce,
					url: url,
					import_mode: $('#dt-import-mode').val() || 'create_update'
				},
				success: function (res) {
					if (!res.success) {
						alert(res.data.message || dtCrawlerData.i18n.error);
						$btn.prop('disabled', false).removeClass('updating-message');
						$('#dt-crawler-progress-card').slideUp();
						return;
					}

					currentJobId = res.data.job_id;
					$('#dt-url-type-detected').show();
					$('#dt-url-type-text').text((res.data.url_type || 'UNKNOWN').toUpperCase());

					updateProgress(15, 'Đang khám phá liên kết sản phẩm...');
					discoverUrls();
				},
				error: function () {
					alert('Không thể kết nối đến máy chủ. Vui lòng kiểm tra lại mạng.');
					$btn.prop('disabled', false).removeClass('updating-message');
					$('#dt-crawler-progress-card').slideUp();
				}
			});
		});
	}

	function discoverUrls() {
		$.ajax({
			url: dtCrawlerData.ajaxUrl,
			type: 'POST',
			data: {
				action: 'dt_crawler_discover_urls',
				nonce: dtCrawlerData.nonce,
				job_id: currentJobId
			},
			success: function (res) {
				if (!res.success) {
					alert(res.data.message || 'Không tìm thấy sản phẩm.');
					$('#dt-crawler-scan-btn').prop('disabled', false).removeClass('updating-message');
					return;
				}

				itemsQueue = res.data.items || [];
				stats.found = itemsQueue.length;
				$('#dt-stat-found').text(stats.found);

				if (itemsQueue.length === 0) {
					alert('Không tìm thấy liên kết sản phẩm nào trên URL này.');
					$('#dt-crawler-scan-btn').prop('disabled', false).removeClass('updating-message');
					return;
				}

				$('#dt-crawler-preview-section').slideDown();
				updateProgress(25, dtCrawlerData.i18n.extracting);

				isExtracting = true;
				isPaused = false;
				isCancelled = false;

				// Hiển thị cụm nút điều khiển khi bắt đầu bóc tách
				$('#dt-progress-controls').show();
				$('#dt-control-import-top-btn').show().prop('disabled', false).removeClass('updating-message');
				$('#dt-control-pause-btn').show();
				$('#dt-control-resume-btn').hide();
				$('#dt-control-skip-btn').show();
				$('#dt-control-cancel-btn').show();

				// Bắt đầu bóc tách tuần tự theo hàng đợi
				processNextItem();
			},
			error: function () {
				alert('Lỗi trong quá trình khám phá URL sản phẩm.');
				$('#dt-crawler-scan-btn').prop('disabled', false).removeClass('updating-message');
			}
		});
	}

	function processNextItem() {
		if (isCancelled) {
			return;
		}

		if (isPaused) {
			updateProgress(
				Math.min(95, Math.round(25 + (stats.processed / stats.found) * 70)),
				'⏸️ Đang tạm dừng bóc tách (' + stats.processed + '/' + stats.found + '). Nhấn "Tiếp tục làm tiếp" để chạy tiếp, hoặc bấm "Nhập vào website ngay" để nhập ngay các sản phẩm đã cào.'
			);
			return;
		}

		if (itemsQueue.length === 0) {
			isExtracting = false;
			updateProgress(100, '✅ Đã bóc tách xong toàn bộ sản phẩm! Nhấn "Nhập vào website ngay" để bắt đầu lưu vào kho dữ liệu.');
			$('#dt-crawler-scan-btn').prop('disabled', false).removeClass('updating-message');

			// Giữ thanh controls hiển thị với nút Nhập nổi bật
			$('#dt-progress-controls').show();
			$('#dt-control-import-top-btn').show().prop('disabled', false).removeClass('updating-message');
			$('#dt-control-pause-btn').hide();
			$('#dt-control-resume-btn').hide();
			$('#dt-control-skip-btn').hide();
			$('#dt-control-cancel-btn').hide();
			return;
		}

		var item = itemsQueue.shift();
		currentProcessingItemId = item.item_id;
		var progressPercent = Math.min(95, Math.round(25 + (stats.processed / stats.found) * 70));
		updateProgress(progressPercent, dtCrawlerData.i18n.extracting + ' (' + (stats.processed + 1) + '/' + stats.found + ')');

		$.ajax({
			url: dtCrawlerData.ajaxUrl,
			type: 'POST',
			data: {
				action: 'dt_crawler_extract_item',
				nonce: dtCrawlerData.nonce,
				item_id: item.item_id
			},
			success: function (res) {
				stats.processed++;
				$('#dt-stat-processed').text(stats.processed);

				if (res.success && res.data && res.data.product) {
					var p = res.data.product;
					var dupStatus = res.data.duplicate_status || 'NEW';
					var dupReason = res.data.duplicate_reason || '';
					var catSuggest = res.data.category_suggest || {};

					if (res.data.categories && res.data.categories.length) {
						dtCrawlerData.categories = res.data.categories;
					}

					extractedItems[item.item_id] = p;

					// Đếm ảnh
					var imgCount = (p.main_image ? 1 : 0) + (p.gallery_images ? p.gallery_images.length : 0);
					stats.images += imgCount;
					$('#dt-stat-images').text(stats.images);

					renderPreviewRow(item.item_id, p, dupStatus, dupReason, catSuggest);
				} else {
					stats.errors++;
					$('#dt-stat-errors').text(stats.errors);
					addErrorToList(item.url, res.data ? res.data.message : 'Không rõ nguyên nhân');
				}

				currentProcessingItemId = 0;

				// Tiếp tục xử lý mục kế tiếp nếu không bị tạm dừng hoặc hủy
				if (!isPaused && !isCancelled) {
					setTimeout(processNextItem, 200);
				}
			},
			error: function () {
				stats.processed++;
				stats.errors++;
				$('#dt-stat-processed').text(stats.processed);
				$('#dt-stat-errors').text(stats.errors);
				addErrorToList(item.url, 'Lỗi kết nối HTTP');
				currentProcessingItemId = 0;

				if (!isPaused && !isCancelled) {
					setTimeout(processNextItem, 200);
				}
			}
		});
	}

	/* =========================================================================
	 * 2. HIỂN THỊ DÒNG PREVIEW (RENDER PREVIEW TABLE)
	 * ========================================================================= */
	function renderPreviewRow(itemId, product, dupStatus, dupReason, catSuggest) {
		// Tránh render trùng lặp nếu đã có dòng
		if ($('#dt-item-row-' + itemId).length > 0) {
			return;
		}

		var isDuplicate = (dupStatus === 'DUPLICATE');
		var isImported  = (product.status === 'imported');
		var isUpdated   = (product.status === 'updated');
		var isSkipped   = (product.status === 'skipped');

		var thumbHtml = product.main_image
			? '<img src="' + escapeHtml(product.main_image) + '" class="dt-thumb-img" alt="">'
			: '<div class="dt-thumb-placeholder"><span class="dashicons dashicons-format-image"></span></div>';

		var priceFormatted = product.price ? Number(product.price).toLocaleString('vi-VN') + ' ₫' : '<span style="color:#8c8f94;">Liên hệ</span>';
		var specsCount = product.specifications ? Object.keys(product.specifications).length : 0;
		var galleryCount = product.gallery_images ? product.gallery_images.length : 0;

		// Đồng bộ term_id của catSuggest vào danh sách danh mục nếu chưa có
		if (catSuggest.term_id && catSuggest.term_name) {
			var existsInList = false;
			if (!dtCrawlerData.categories) {
				dtCrawlerData.categories = [];
			}
			$.each(dtCrawlerData.categories, function (i, cat) {
				if (cat.id == catSuggest.term_id) {
					existsInList = true;
					return false;
				}
			});
			if (!existsInList) {
				dtCrawlerData.categories.push({ id: catSuggest.term_id, name: catSuggest.term_name });
			}
		}

		// Tạo Category Select options
		var catSelectHtml = '<select class="dt-row-category-select widefat" data-item-id="' + itemId + '">';
		catSelectHtml += '<option value="0">— Chưa gán —</option>';
		if (dtCrawlerData.categories && dtCrawlerData.categories.length > 0) {
			$.each(dtCrawlerData.categories, function (i, cat) {
				var selected = (catSuggest.term_id && catSuggest.term_id === cat.id) ? ' selected' : '';
				catSelectHtml += '<option value="' + cat.id + '"' + selected + '>' + escapeHtml(cat.name) + '</option>';
			});
		}
		catSelectHtml += '<option value="__new__">[+ Thêm danh mục mới...]</option>';
		catSelectHtml += '</select>';

		var sourceCatName = product.category || (catSuggest && catSuggest.term_name ? catSuggest.term_name : '');
		if (sourceCatName) {
			catSelectHtml += '<div style="font-size:11px; color:#135e96; margin-top:3px; font-weight:600; line-height:1.3;"><span class="dashicons dashicons-category" style="font-size:12px; width:12px; height:12px; vertical-align:middle;"></span> Nguồn: ' + escapeHtml(sourceCatName) + '</div>';
		} else if (catSuggest.term_name && !catSuggest.is_exact) {
			catSelectHtml += '<div style="font-size:11px; color:#0073aa; margin-top:3px;">Gợi ý: ' + escapeHtml(catSuggest.term_name) + ' (' + Math.round(catSuggest.confidence * 100) + '%)</div>';
		}

		// Xác định trạng thái checkbox & nhãn hiển thị
		// Nếu DUPLICATE: Mặc định KHÔNG CHỌN (unchecked) để tránh nhập trùng theo yêu cầu người dùng
		// Nếu đã IMPORTED: Không chọn và vô hiệu hóa
		var isChecked = !isDuplicate && !isImported && !isSkipped;
		var checkboxAttr = isChecked ? ' checked' : '';
		if (isImported) {
			checkboxAttr += ' disabled';
		}

		var badgeText = dupStatus;
		var badgeClass = 'dt-status-badge dt-status-' + dupStatus;

		if (isImported) {
			badgeText = 'IMPORTED';
			badgeClass = 'dt-status-badge dt-status-IMPORTED';
		} else if (isDuplicate) {
			badgeText = 'ĐÃ CÓ (BỎ QUA)';
			badgeClass = 'dt-status-badge dt-status-DUPLICATE';
		} else if (isSkipped) {
			badgeText = 'SKIPPED';
			badgeClass = 'dt-status-badge dt-status-SKIPPED';
		}

		// Link tiêu đề bài viết
		var titleLink = escapeHtml(product.source_url);
		var titleTarget = '_blank';
		if (isImported && product.post_id) {
			titleLink = 'post.php?post=' + product.post_id + '&action=edit';
		}

		var rowBgStyle = '';
		if (isImported) {
			rowBgStyle = ' style="background-color: #edf8ee;"';
		} else if (isDuplicate) {
			rowBgStyle = ' style="background-color: #fffdf5;"';
		}

		var descBadge = '';
		if (product.description && product.description.length > 50) {
			descBadge = '<br><span style="color:#00a32a; font-weight:600;" title="Đã bóc tách mô tả chi tiết (' + product.description.length + ' ký tự)">✓ Có bài mô tả</span>';
		} else if (product.short_description || product.description) {
			descBadge = '<br><span style="color:#2271b1;" title="Mô tả tóm tắt">ℹ Mô tả ngắn</span>';
		} else {
			descBadge = '<br><span style="color:#d63638;" title="Chưa tìm thấy mô tả">✗ Chưa có mô tả</span>';
		}

		var rowHtml = '<tr id="dt-item-row-' + itemId + '" data-item-id="' + itemId + '"' + rowBgStyle + '>' +
			'<td><input type="checkbox" class="dt-item-checkbox" value="' + itemId + '"' + checkboxAttr + '></td>' +
			'<td>' + thumbHtml + '</td>' +
			'<td>' +
				'<strong><a href="' + titleLink + '" target="' + titleTarget + '" class="dt-row-title">' + escapeHtml(product.name || 'Không có tên') + '</a></strong>' +
				'<div style="font-size:12px; color:#646970; margin-top:2px;">Mã/SKU: <code class="dt-row-sku">' + escapeHtml(product.sku || '—') + '</code>' +
				(product.brand ? ' | Hãng: ' + escapeHtml(product.brand) : '') +
				(isImported && product.post_id ? ' | <a href="post.php?post=' + product.post_id + '&action=edit" target="_blank" style="color:#00a32a; font-weight:600;">Xem bài viết # ' + product.post_id + '</a>' : '') +
				'</div>' +
			'</td>' +
			'<td class="dt-row-price">' + priceFormatted + '</td>' +
			'<td>' + catSelectHtml + '</td>' +
			'<td><span class="' + badgeClass + '" title="' + escapeHtml(dupReason) + '">' + badgeText + '</span></td>' +
			'<td style="font-size:12px;">' + (galleryCount + 1) + ' ảnh<br>' + specsCount + ' thông số' + descBadge + '</td>' +
			'<td style="text-align:center; white-space:nowrap;">' +
				'<button type="button" class="button button-small dt-edit-item-btn" data-item-id="' + itemId + '" title="Chỉnh sửa thông tin">Sửa</button> ' +
				'<button type="button" class="button button-small button-link-delete dt-delete-item-btn" data-item-id="' + itemId + '" title="Xóa khỏi danh sách"><span class="dashicons dashicons-trash"></span> Xóa</button>' +
			'</td>' +
		'</tr>';

		$('#dt-preview-tbody').append(rowHtml);
	}

	/* =========================================================================
	 * 3. XỬ LÝ IMPORT SẢN PHẨM ĐÃ CHỌN (IMPORT SELECTED PRODUCTS)
	 * ========================================================================= */
	function initPreviewTable() {
		// Chọn tất cả (chỉ chọn các mục chưa disabled)
		$('#dt-select-all, #dt-select-all-header').on('change', function () {
			var checked = $(this).is(':checked');
			$('.dt-item-checkbox:not(:disabled)').prop('checked', checked);
			$('#dt-select-all, #dt-select-all-header').prop('checked', checked);
		});

		// Chọn danh mục mới
		$(document).on('change', '.dt-row-category-select', function () {
			var $sel = $(this);
			var itemId = $sel.data('item-id');
			var val = $sel.val();

			if (val === '__new__') {
				var catName = prompt(dtCrawlerData.i18n.new_cat_prompt);
				if (catName && $.trim(catName)) {
					$.ajax({
						url: dtCrawlerData.ajaxUrl,
						type: 'POST',
						data: {
							action: 'dt_crawler_create_category',
							nonce: dtCrawlerData.nonce,
							name: $.trim(catName)
						},
						success: function (res) {
							if (res.success && res.data) {
								var newOpt = '<option value="' + res.data.id + '" selected>' + escapeHtml(res.data.name) + '</option>';
								$sel.find('option[value="__new__"]').before(newOpt);
								dtCrawlerData.categories.push({ id: res.data.id, name: res.data.name });
								saveRowCategory(itemId, res.data.id);
							} else {
								alert(res.data.message || 'Không thể tạo danh mục.');
								$sel.val('0');
							}
						}
					});
				} else {
					$sel.val('0');
				}
			} else {
				saveRowCategory(itemId, val);
			}
		});

		// Hàm khởi động tiến trình nhập sản phẩm
		function startImportExecution(selectedIds) {
			importQueue = selectedIds;
			totalToImport = importQueue.length;
			importedCount = 0;
			isPaused = false;
			isCancelled = false;
			isImporting = true;
			isExtracting = false;
			itemsQueue = [];

			$('#dt-start-import-btn').prop('disabled', true).addClass('updating-message');
			$('#dt-control-import-top-btn').prop('disabled', true).addClass('updating-message').hide();
			updateProgress(0, dtCrawlerData.i18n.importing);
			$('#dt-crawler-progress-card').slideDown();

			// Kích hoạt cụm nút điều khiển tiến trình
			$('#dt-progress-controls').show();
			$('#dt-control-pause-btn').show();
			$('#dt-control-resume-btn').hide();
			$('#dt-control-skip-btn').show();
			$('#dt-control-cancel-btn').show();

			importNext();
		}

		// Nút Bắt đầu nhập sản phẩm đã chọn (ở chân bảng)
		$('#dt-start-import-btn').on('click', function (e) {
			e.preventDefault();

			var selectedIds = [];
			$('.dt-item-checkbox:checked:not(:disabled)').each(function () {
				selectedIds.push($(this).val());
			});

			if (selectedIds.length === 0) {
				alert(dtCrawlerData.i18n.no_items_selected);
				return;
			}

			if (!confirm(dtCrawlerData.i18n.confirm_import + ' (' + selectedIds.length + ' sản phẩm)')) {
				return;
			}

			startImportExecution(selectedIds);
		});

		// Xóa một sản phẩm khỏi danh sách
		$(document).on('click', '.dt-delete-item-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var itemId = $btn.data('item-id');
			var p = extractedItems[itemId] || {};
			var name = p.name || 'sản phẩm này';
			var isImported = p.status === 'imported' || $('#dt-item-row-' + itemId).find('.dt-badge-success').length > 0;

			var deletePost = false;
			if (isImported) {
				if (!confirm('Sản phẩm "' + name + '" đã được nhập lên website.\n\nBạn có chắc chắn muốn xóa sản phẩm này không?')) {
					return;
				}
				deletePost = confirm('Bạn có muốn XÓA LUÔN bài viết trên website WordPress không?\n\n- Nhấn OK: Xóa cả bài viết trên web và khỏi danh sách này.\n- Nhấn Cancel: Chỉ xóa khỏi danh sách cào này (giữ bài viết trên web).');
			} else {
				if (!confirm(dtCrawlerData.i18n.confirm_delete || 'Bạn có chắc chắn muốn xóa "' + name + '" khỏi danh sách không?')) {
					return;
				}
			}

			$btn.prop('disabled', true).addClass('updating-message');

			$.ajax({
				url: dtCrawlerData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'dt_crawler_delete_item',
					_ajax_nonce: dtCrawlerData.nonce,
					item_id: itemId,
					delete_post: deletePost ? 1 : 0
				},
				success: function (res) {
					if (res.success) {
						var $row = $('#dt-item-row-' + itemId);
						$row.css('background-color', '#fbeaea').fadeOut(300, function () {
							$(this).remove();
							delete extractedItems[itemId];

							var idx = importQueue.indexOf(String(itemId));
							if (idx > -1) {
								importQueue.splice(idx, 1);
							}

							checkPreviewEmpty();
						});
					} else {
						alert(res.data && res.data.message ? res.data.message : 'Lỗi khi xóa sản phẩm.');
						$btn.prop('disabled', false).removeClass('updating-message');
					}
				},
				error: function () {
					alert('Không thể kết nối đến máy chủ.');
					$btn.prop('disabled', false).removeClass('updating-message');
				}
			});
		});

		// Xóa hàng loạt các sản phẩm đã chọn
		$('#dt-bulk-delete-btn').on('click', function (e) {
			e.preventDefault();

			var selectedIds = [];
			$('.dt-item-checkbox:checked').each(function () {
				selectedIds.push($(this).val());
			});

			if (selectedIds.length === 0) {
				alert(dtCrawlerData.i18n.no_items_selected_delete || 'Vui lòng chọn ít nhất một sản phẩm để xóa.');
				return;
			}

			var msg = dtCrawlerData.i18n.confirm_bulk_delete || 'Bạn có chắc chắn muốn xóa ' + selectedIds.length + ' sản phẩm đã chọn khỏi danh sách không?';
			if (!confirm(msg + ' (' + selectedIds.length + ' sản phẩm)')) {
				return;
			}

			var $btn = $(this);
			$btn.prop('disabled', true).addClass('updating-message');

			$.ajax({
				url: dtCrawlerData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'dt_crawler_delete_items',
					_ajax_nonce: dtCrawlerData.nonce,
					item_ids: selectedIds
				},
				success: function (res) {
					$btn.prop('disabled', false).removeClass('updating-message');
					if (res.success) {
						selectedIds.forEach(function (id) {
							var $row = $('#dt-item-row-' + id);
							$row.css('background-color', '#fbeaea').fadeOut(300, function () {
								$(this).remove();
								delete extractedItems[id];

								var idx = importQueue.indexOf(String(id));
								if (idx > -1) {
									importQueue.splice(idx, 1);
								}
								checkPreviewEmpty();
							});
						});
						$('#dt-select-all, #dt-select-all-header').prop('checked', false);
					} else {
						alert(res.data && res.data.message ? res.data.message : 'Lỗi khi xóa sản phẩm.');
					}
				},
				error: function () {
					$btn.prop('disabled', false).removeClass('updating-message');
					alert('Không thể kết nối đến máy chủ.');
				}
			});
		});

		function checkPreviewEmpty() {
			if ($('#dt-preview-tbody tr').length === 0) {
				$('#dt-preview-tbody').html('<tr><td colspan="8" style="text-align:center; padding:30px; color:#8c8f94;">Danh sách sản phẩm trống.</td></tr>');
			}
		}
	}

	function importNext() {
		if (isCancelled) {
			return;
		}

		if (isPaused) {
			updateProgress(
				Math.round((importedCount / totalToImport) * 100),
				'⏸️ Đang tạm dừng nhập tại sản phẩm ' + importedCount + '/' + totalToImport + '. Nhấn "Tiếp tục làm tiếp" để chạy tiếp.'
			);
			return;
		}

		if (importQueue.length === 0) {
			isImporting = false;
			$('#dt-progress-controls').hide();
			updateProgress(100, dtCrawlerData.i18n.completed + ' Đã nhập thành công ' + importedCount + ' sản phẩm lên website.');
			$('#dt-start-import-btn').prop('disabled', false).removeClass('updating-message');
			$('#dt-control-import-top-btn').prop('disabled', false).removeClass('updating-message').show();
			alert('Quá trình nhập sản phẩm đã hoàn tất thành công!');
			return;
		}

		var curId = importQueue.shift();
		currentProcessingItemId = curId;
		importedCount++;
		var pct = Math.round((importedCount / totalToImport) * 100);
		updateProgress(pct, dtCrawlerData.i18n.importing + ' (' + importedCount + '/' + totalToImport + ')');

		var $row = $('#dt-item-row-' + curId);
		$row.css('background-color', '#fff9e6');

		$.ajax({
			url: dtCrawlerData.ajaxUrl,
			type: 'POST',
			data: {
				action: 'dt_crawler_import_item',
				nonce: dtCrawlerData.nonce,
				item_id: curId,
				import_mode: $('#dt-import-mode').val() || 'create_update',
				post_status: $('#dt-import-status').val() || 'publish' // Mặc định là 'publish' (Đang hiển thị)
			},
			success: function (res) {
				if (res.success) {
					$row.css('background-color', '#edf8ee');
					var actionBadge = res.data.action === 'updated'
						? '<span class="dt-status-badge dt-status-UPDATED">UPDATED</span>'
						: (res.data.action === 'skipped'
							? '<span class="dt-status-badge dt-status-DUPLICATE">ĐÃ CÓ (BỎ QUA)</span>'
							: '<span class="dt-status-badge dt-status-IMPORTED">IMPORTED</span>');
					$row.find('td:nth-child(6)').html(actionBadge);

					if (res.data.post_id) {
						var editLink = 'post.php?post=' + res.data.post_id + '&action=edit';
						$row.find('.dt-row-title').attr('href', editLink);
					}

					// Bỏ chọn và disable checkbox sau khi nhập thành công
					$row.find('.dt-item-checkbox').prop('checked', false).prop('disabled', true);
				} else {
					$row.css('background-color', '#fcf0f1');
					$row.find('td:nth-child(6)').html('<span class="dt-status-badge dt-status-ERROR">ERROR</span>');
				}

				currentProcessingItemId = 0;

				if (!isPaused && !isCancelled) {
					setTimeout(importNext, 250);
				}
			},
			error: function () {
				$row.css('background-color', '#fcf0f1');
				$row.find('td:nth-child(6)').html('<span class="dt-status-badge dt-status-ERROR">FAIL</span>');
				currentProcessingItemId = 0;

				if (!isPaused && !isCancelled) {
					setTimeout(importNext, 250);
				}
			}
		});
	}

	function saveRowCategory(itemId, catId) {
		$.ajax({
			url: dtCrawlerData.ajaxUrl,
			type: 'POST',
			data: {
				action: 'dt_crawler_save_preview_item',
				nonce: dtCrawlerData.nonce,
				item_id: itemId,
				category_id: catId
			}
		});
	}

	/* =========================================================================
	 * 4. BỘ NÚT ĐIỀU KHIỂN TIẾN TRÌNH (PROCESS CONTROLS: PAUSE, RESUME, SKIP, CANCEL)
	 * ========================================================================= */
	function initProcessControls() {
		// Nút Nhập vào website ngay (nằm trên thanh điều khiển tiến trình)
		$('#dt-control-import-top-btn').on('click', function (e) {
			e.preventDefault();

			var selectedIds = [];
			$('.dt-item-checkbox:checked:not(:disabled)').each(function () {
				selectedIds.push($(this).val());
			});

			if (selectedIds.length === 0) {
				alert(dtCrawlerData.i18n.no_items_selected || 'Vui lòng chọn ít nhất 1 sản phẩm để nhập.');
				return;
			}

			var confirmMsg = (dtCrawlerData.i18n.confirm_import || 'Bạn có chắc chắn muốn nhập các sản phẩm đã chọn?') + ' (' + selectedIds.length + ' sản phẩm)';
			if (isExtracting) {
				confirmMsg = 'Tiến trình cào dữ liệu đang chạy. Bạn có muốn dừng cào và tiến hành nhập ngay ' + selectedIds.length + ' sản phẩm đã chọn vào website không?';
			}

			if (!confirm(confirmMsg)) {
				return;
			}

			startImportExecution(selectedIds);
		});

		// Nút Tạm dừng
		$('#dt-control-pause-btn').on('click', function (e) {
			e.preventDefault();
			isPaused = true;

			$('#dt-control-pause-btn').hide();
			$('#dt-control-resume-btn').show();

			if (currentJobId > 0) {
				$.ajax({
					url: dtCrawlerData.ajaxUrl,
					type: 'POST',
					data: {
						action: 'dt_crawler_pause_job',
						nonce: dtCrawlerData.nonce,
						job_id: currentJobId
					}
				});
			}

			if (isImporting) {
				updateProgress(
					Math.round((importedCount / totalToImport) * 100),
					'⏸️ ' + dtCrawlerData.i18n.paused + ' tại sản phẩm ' + importedCount + '/' + totalToImport + '. Nhấn "Tiếp tục làm tiếp" để chạy tiếp.'
				);
			} else if (isExtracting) {
				$('#dt-control-import-top-btn').show().prop('disabled', false).removeClass('updating-message');
				updateProgress(
					Math.min(95, Math.round(25 + (stats.processed / stats.found) * 70)),
					'⏸️ ' + dtCrawlerData.i18n.paused + ' bóc tách (' + stats.processed + '/' + stats.found + '). Nhấn "Tiếp tục làm tiếp" để chạy tiếp, hoặc bấm "Nhập vào website ngay" để nhập ngay.'
				);
			}
		});

		// Nút Tiếp tục làm tiếp
		$('#dt-control-resume-btn').on('click', function (e) {
			e.preventDefault();
			isPaused = false;

			$('#dt-control-resume-btn').hide();
			$('#dt-control-pause-btn').show();

			if (currentJobId > 0) {
				$.ajax({
					url: dtCrawlerData.ajaxUrl,
					type: 'POST',
					data: {
						action: 'dt_crawler_resume_job',
						nonce: dtCrawlerData.nonce,
						job_id: currentJobId
					}
				});
			}

			if (isImporting) {
				updateProgress(
					Math.round((importedCount / totalToImport) * 100),
					dtCrawlerData.i18n.resumed + ' Đang nhập (' + importedCount + '/' + totalToImport + ')'
				);
				importNext();
			} else if (isExtracting) {
				updateProgress(
					Math.min(95, Math.round(25 + (stats.processed / stats.found) * 70)),
					dtCrawlerData.i18n.resumed + ' Đang bóc tách (' + (stats.processed + 1) + '/' + stats.found + ')'
				);
				processNextItem();
			} else {
				// Nếu phiên được khôi phục từ Crash Recovery: Bắt đầu nhập các mục chưa nhập
				var pendingIds = [];
				$('.dt-item-checkbox:checked:not(:disabled)').each(function () {
					pendingIds.push($(this).val());
				});

				if (pendingIds.length > 0) {
					importQueue = pendingIds;
					totalToImport = importQueue.length;
					importedCount = 0;
					isImporting = true;
					$('#dt-start-import-btn').prop('disabled', true).addClass('updating-message');
					importNext();
				}
			}
		});

		// Nút Bỏ qua sản phẩm này
		$('#dt-control-skip-btn').on('click', function (e) {
			e.preventDefault();

			var skipId = currentProcessingItemId;
			if (!skipId && importQueue.length > 0) {
				skipId = importQueue.shift();
			}

			if (skipId) {
				var $row = $('#dt-item-row-' + skipId);
				$row.css('background-color', '#f0f0f1');
				$row.find('td:nth-child(6)').html('<span class="dt-status-badge dt-status-SKIPPED">SKIPPED</span>');
				$row.find('.dt-item-checkbox').prop('checked', false);

				$.ajax({
					url: dtCrawlerData.ajaxUrl,
					type: 'POST',
					data: {
						action: 'dt_crawler_skip_item',
						nonce: dtCrawlerData.nonce,
						item_id: skipId
					}
				});

				if (isPaused && isImporting) {
					isPaused = false;
					$('#dt-control-resume-btn').hide();
					$('#dt-control-pause-btn').show();
					importNext();
				}
			}
		});

		// Nút Hủy bỏ tiến trình
		// "khi mình cancel thì mấy sản phẩm xong r thì dc add lên website luôn còn sản phẩm sau bỏ đi"
		$('#dt-control-cancel-btn').on('click', function (e) {
			e.preventDefault();

			if (!confirm(dtCrawlerData.i18n.confirm_cancel)) {
				return;
			}

			isCancelled = true;
			isPaused = false;
			isImporting = false;
			isExtracting = false;
			importQueue = [];
			itemsQueue = [];

			var doneCount = importedCount;
			var jobIdToCancel = currentJobId;

			$.ajax({
				url: dtCrawlerData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'dt_crawler_cancel_job',
					nonce: dtCrawlerData.nonce,
					job_id: jobIdToCancel
				},
				success: function () {
					updateProgress(
						100,
						'Đã dừng tiến trình. ' + doneCount + ' sản phẩm đã nhập thành công được lưu giữ trên website. Các sản phẩm còn lại đã được hủy.'
					);
					$('#dt-progress-controls').hide();
					$('#dt-start-import-btn').prop('disabled', false).removeClass('updating-message');
					$('#dt-control-import-top-btn').prop('disabled', false).removeClass('updating-message');
					$('#dt-crawler-scan-btn').prop('disabled', false).removeClass('updating-message');
					alert('Đã hủy tiến trình nhập thành công. ' + doneCount + ' sản phẩm đã nhập trước đó vẫn được lưu giữ nguyên vẹn trên website.');
				}
			});
		});
	}

	/* =========================================================================
	 * 5. KHÔI PHỤC PHIÊN DỞ DANG KHI SẬP MÁY/TẮT TRÌNH DUYỆT (CRASH RECOVERY)
	 * ========================================================================= */
	function initCrashRecovery() {
		// Chỉ kiểm tra khi ở trang import
		if ($('#dt-recovery-banner').length === 0) {
			return;
		}

		$.ajax({
			url: dtCrawlerData.ajaxUrl,
			type: 'POST',
			data: {
				action: 'dt_crawler_get_active_session',
				nonce: dtCrawlerData.nonce
			},
			success: function (res) {
				if (res.success && res.data && res.data.has_session) {
					activeSessionData = res.data;
					if (res.data.categories && res.data.categories.length) {
						dtCrawlerData.categories = res.data.categories;
					}
					var job = activeSessionData.job;
					var sessionStats = activeSessionData.stats;

					// Chỉ hiển thị banner khôi phục khi thực sự còn sản phẩm dở dang (pending > 0)
					if (!sessionStats || sessionStats.pending <= 0) {
						return;
					}

					var desc = 'Phát hiện phiên cào dữ liệu từ URL : <strong>' + escapeHtml(job.source_url) + '</strong> (Mã phiên: #' + job.id + ') chưa hoàn tất.Xin vui lòng đợi.<br>' +
						'Tiến độ: Đã nhập <strong>' + sessionStats.imported + ' / ' + sessionStats.total + '</strong> sản phẩm lên website • Còn lại <strong>' + sessionStats.pending + '</strong> sản phẩm chưa nhập.';

					$('#dt-recovery-desc').html(desc);
					$('#dt-recovery-banner').slideDown();
				}
			}
		});

		// Bấm Khôi phục & Tiếp tục làm
		$('#dt-recovery-resume-btn').on('click', function (e) {
			e.preventDefault();
			if (!activeSessionData) return;

			$('#dt-recovery-banner').slideUp();

			var job = activeSessionData.job;
			var sessionStats = activeSessionData.stats;
			var items = activeSessionData.items;

			currentJobId = parseInt(job.id, 10);
			$('#dt-crawler-url').val(job.source_url);
			$('#dt-url-type-detected').show();
			$('#dt-url-type-text').text((job.url_type || 'UNKNOWN').toUpperCase());

			// Cập nhật chế độ nhập nếu có lưu
			if (job.import_mode) {
				$('#dt-import-mode').val(job.import_mode);
			}

			if (activeSessionData.categories && activeSessionData.categories.length) {
				dtCrawlerData.categories = activeSessionData.categories;
			}

			// Render lại danh sách Preview
			$('#dt-preview-tbody').empty();
			stats.found = sessionStats.total;
			stats.processed = sessionStats.imported + sessionStats.updated + sessionStats.duplicate + sessionStats.failed;
			stats.errors = sessionStats.failed;

			$('#dt-stat-found').text(stats.found);
			$('#dt-stat-processed').text(stats.processed);
			$('#dt-stat-errors').text(stats.errors);

			$.each(items, function (i, it) {
				var p = it.product || {};
				p.source_url = it.source_url || p.source_url;
				p.name = it.product_name || p.name;
				p.sku = it.sku || p.sku;
				p.price = it.price !== null ? it.price : p.price;
				p.status = it.status;
				p.post_id = it.post_id;

				extractedItems[it.id] = p;

				renderPreviewRow(it.id, p, it.duplicate_status || 'NEW', it.duplicate_reason || '', it.category_suggest || {});

				if (it.mapped_category_id > 0) {
					$('#dt-item-row-' + it.id).find('.dt-row-category-select').val(it.mapped_category_id);
				}
			});

			$('#dt-crawler-preview-section').slideDown();
			$('#dt-crawler-progress-card').slideDown();

			var pct = sessionStats.total > 0 ? Math.round((sessionStats.imported / sessionStats.total) * 100) : 0;
			updateProgress(
				pct,
				'Đã khôi phục phiên #' + currentJobId + '! Đã nhập ' + sessionStats.imported + '/' + sessionStats.total + ' sản phẩm lên website.'
			);

			if (sessionStats.pending > 0) {
				$('#dt-progress-controls').show();
				$('#dt-control-import-top-btn').show().prop('disabled', false).removeClass('updating-message');
				$('#dt-control-pause-btn').hide();
				$('#dt-control-resume-btn').show().html('<span class="dashicons dashicons-controls-play"></span> Tiếp tục nhập ' + sessionStats.pending + ' sản phẩm còn lại');
				$('#dt-control-skip-btn').hide();
				$('#dt-control-cancel-btn').show();
			}
		});

		// Bấm Bỏ qua phiên này
		$('#dt-recovery-discard-btn').on('click', function (e) {
			e.preventDefault();
			if (!activeSessionData) return;

			if (!confirm(dtCrawlerData.i18n.confirm_discard)) {
				return;
			}

			var $btn = $(this);
			$btn.prop('disabled', true);

			$.ajax({
				url: dtCrawlerData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'dt_crawler_cancel_job',
					nonce: dtCrawlerData.nonce,
					job_id: activeSessionData.job.id
				},
				success: function () {
					activeSessionData = null;
					$('#dt-recovery-banner').slideUp();
					resetState();
				},
				error: function () {
					$btn.prop('disabled', false);
					alert('Lỗi khi hủy phiên làm việc.');
				}
			});
		});
	}

	/* =========================================================================
	 * 6. MODAL CHỈNH SỬA DỮ LIỆU SẢN PHẨM (EDIT MODAL)
	 * ========================================================================= */
	function initEditModal() {
		$(document).on('click', '.dt-edit-item-btn', function (e) {
			e.preventDefault();
			var itemId = $(this).data('item-id');
			var p = extractedItems[itemId];
			if (!p) return;

			$('#dt-modal-item-id').val(itemId);
			$('#dt-modal-name').val(p.name || '');
			$('#dt-modal-sku').val(p.sku || p.model || '');
			$('#dt-modal-price').val(p.price || '');
			$('#dt-modal-short-desc').val(p.short_description || '');
			$('#dt-modal-desc').val(p.description || '');

			var currentCat = $('#dt-item-row-' + itemId).find('.dt-row-category-select').val();
			$('#dt-modal-category').val(currentCat || '0');

			// Hiển thị thông số kỹ thuật
			var $specsBox = $('#dt-modal-specs-preview').empty();
			if (p.specifications && Object.keys(p.specifications).length > 0) {
				$.each(p.specifications, function (k, v) {
					$specsBox.append('<div class="dt-spec-line"><strong>' + escapeHtml(k) + ':</strong> ' + escapeHtml(v) + '</div>');
				});
			} else {
				$specsBox.html('<em style="color:#8c8f94;">Không có thông số kỹ thuật.</em>');
			}

			$('#dt-edit-modal').fadeIn(150);
		});

		$('.dt-modal-close').on('click', function () {
			$(this).closest('.dt-modal').fadeOut(150);
		});

		$('#dt-modal-save-btn').on('click', function (e) {
			e.preventDefault();
			var itemId = $('#dt-modal-item-id').val();
			var name = $.trim($('#dt-modal-name').val());
			var sku = $.trim($('#dt-modal-sku').val());
			var price = $.trim($('#dt-modal-price').val());
			var catId = $('#dt-modal-category').val();
			var shortDesc = $.trim($('#dt-modal-short-desc').val());
			var desc = $.trim($('#dt-modal-desc').val());

			if (!name) {
				alert('Tên sản phẩm không được để trống.');
				return;
			}

			var $btn = $(this);
			$btn.prop('disabled', true);

			$.ajax({
				url: dtCrawlerData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'dt_crawler_save_preview_item',
					nonce: dtCrawlerData.nonce,
					item_id: itemId,
					name: name,
					sku: sku,
					price: price,
					category_id: catId,
					short_description: shortDesc,
					description: desc
				},
				success: function (res) {
					$btn.prop('disabled', false);
					$('#dt-edit-modal').fadeOut(150);

					if (res.success) {
						// Cập nhật lại DOM table row
						var $row = $('#dt-item-row-' + itemId);
						$row.find('.dt-row-title').text(name);
						$row.find('.dt-row-sku').text(sku || '—');
						$row.find('.dt-row-price').text(price ? Number(price).toLocaleString('vi-VN') + ' ₫' : 'Liên hệ');
						$row.find('.dt-row-category-select').val(catId);

						if (res.data.duplicate_status) {
							$row.find('td:nth-child(6)').html('<span class="dt-status-badge dt-status-' + res.data.duplicate_status + '" title="' + escapeHtml(res.data.duplicate_reason) + '">' + res.data.duplicate_status + '</span>');
						}

						// Cập nhật lại mảng local
						if (extractedItems[itemId]) {
							extractedItems[itemId].name = name;
							extractedItems[itemId].sku = sku;
							extractedItems[itemId].model = sku;
							extractedItems[itemId].price = price ? parseFloat(price) : null;
							extractedItems[itemId].short_description = shortDesc;
							extractedItems[itemId].description = desc;
						}
					}
				},
				error: function () {
					$btn.prop('disabled', false);
					alert('Lỗi lưu chỉnh sửa sản phẩm.');
				}
			});
		});
	}

	/* =========================================================================
	 * 7. CÀI ĐẶT CRAWLER (SETTINGS)
	 * ========================================================================= */
	function initSettings() {
		$('#dt-save-settings-btn').on('click', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var formObj = {};
			$('#dt-settings-form').serializeArray().forEach(function (item) {
				formObj[item.name] = item.value;
			});

			$btn.prop('disabled', true).addClass('updating-message');

			$.ajax({
				url: dtCrawlerData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'dt_crawler_save_settings',
					nonce: dtCrawlerData.nonce,
					settings: formObj
				},
				success: function (res) {
					$btn.prop('disabled', false).removeClass('updating-message');
					if (res.success) {
						$('#dt-settings-saved-notice').fadeIn().delay(2000).fadeOut();
					} else {
						alert(res.data.message || 'Lỗi lưu cài đặt.');
					}
				},
				error: function () {
					$btn.prop('disabled', false).removeClass('updating-message');
					alert('Lỗi kết nối máy chủ.');
				}
			});
		});
	}

	/* =========================================================================
	 * 8. LỊCH SỬ NHẬP & XEM LOGS (HISTORY)
	 * ========================================================================= */
	function initHistory() {
		$(document).on('click', '.dt-view-logs-btn', function (e) {
			e.preventDefault();
			var jobId = $(this).data('job-id');
			$('#dt-logs-modal-title').text('Nhật ký phiên cào #' + jobId);
			var $box = $('#dt-logs-container').html('<em>Đang tải nhật ký...</em>');
			$('#dt-logs-modal').fadeIn(150);

			$.ajax({
				url: dtCrawlerData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'dt_crawler_get_logs',
					nonce: dtCrawlerData.nonce,
					job_id: jobId
				},
				success: function (res) {
					$box.empty();
					if (res.success && res.data && res.data.logs && res.data.logs.length > 0) {
						$.each(res.data.logs, function (i, log) {
							$box.append('<div class="dt-log-entry dt-log-' + log.level + '">[' + log.created_at + '] [' + log.level + '] ' + escapeHtml(log.message) + '</div>');
						});
					} else {
						$box.html('<em>Không có bản ghi nhật ký nào.</em>');
					}
				},
				error: function () {
					$box.html('<span style="color:#f14c4c;">Lỗi tải nhật ký.</span>');
				}
			});
		});

		$(document).on('click', '.dt-rerun-job-btn', function (e) {
			e.preventDefault();
			var jobId = $(this).data('job-id');
			if (!confirm('Bạn có muốn quét lại nguồn website này không?')) return;

			var $btn = $(this);
			$btn.prop('disabled', true);

			$.ajax({
				url: dtCrawlerData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'dt_crawler_rerun_job',
					nonce: dtCrawlerData.nonce,
					job_id: jobId
				},
				success: function (res) {
					if (res.success && res.data) {
						window.location.href = 'edit.php?post_type=product&page=dt-crawler-import&rerun_url=' + encodeURIComponent(res.data.url);
					} else {
						$btn.prop('disabled', false);
						alert(res.data.message || 'Không thể chạy lại.');
					}
				},
				error: function () {
					$btn.prop('disabled', false);
					alert('Lỗi kết nối máy chủ.');
				}
			});
		});

		// Nếu có tham số rerun_url trong query string
		var urlParams = new URLSearchParams(window.location.search);
		var rerunUrl = urlParams.get('rerun_url');
		if (rerunUrl) {
			$('#dt-crawler-url').val(rerunUrl);
			$('#dt-crawler-scan-btn').trigger('click');
		}
	}

	/* =========================================================================
	 * TIỆN ÍCH TRỢ GIÚP (HELPERS)
	 * ========================================================================= */
	function resetState() {
		currentJobId = 0;
		itemsQueue = [];
		importQueue = [];
		extractedItems = {};
		isPaused = false;
		isCancelled = false;
		isImporting = false;
		isExtracting = false;
		currentProcessingItemId = 0;
		totalToImport = 0;
		importedCount = 0;

		stats = { found: 0, processed: 0, images: 0, errors: 0 };

		$('#dt-stat-found').text(0);
		$('#dt-stat-processed').text(0);
		$('#dt-stat-images').text(0);
		$('#dt-stat-errors').text(0);

		$('#dt-preview-tbody').empty();
		$('#dt-error-list').empty();
		$('#dt-error-notice-box').hide();
		$('#dt-url-type-detected').hide();
		$('#dt-progress-controls').hide();
	}

	function updateProgress(percent, title) {
		$('#dt-progress-bar').css('width', percent + '%');
		$('#dt-progress-percent').text(percent + '%');
		if (title) {
			$('#dt-progress-status-title').text(title);
		}
	}

	function addErrorToList(url, msg) {
		$('#dt-error-notice-box').show();
		$('#dt-error-list').append('<li><a href="' + escapeHtml(url) + '" target="_blank">' + escapeHtml(url) + '</a> — <span style="color:#d63638;">' + escapeHtml(msg) + '</span></li>');
	}

	function escapeHtml(str) {
		if (!str) return '';
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

})(jQuery);
