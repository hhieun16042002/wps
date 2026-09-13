/**
 * Mozlex front-end behaviors — vanilla JS, defer, không dependency.
 * Modules: header elevate, reveal, drawer/modal, autocomplete,
 * filter autosubmit, wizard, lightbox.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ---------- Header elevate ---------- */
	var header = document.getElementById('site-header');
	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 24);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* ---------- Reveal on scroll ---------- */
	var revealEls = document.querySelectorAll('.reveal, .reveal-image');
	if (!reduceMotion && 'IntersectionObserver' in window && revealEls.length) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-revealed');
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.12 });
		revealEls.forEach(function (el) { io.observe(el); });
	} else {
		revealEls.forEach(function (el) { el.classList.add('is-revealed'); });
	}

	/* ---------- Stats counter — đếm từ 0 lên target khi vào viewport ---------- */
	(function () {
		var stats = document.querySelectorAll('.stat-number[data-count]');
		if (!stats.length) return;
		if (reduceMotion) { stats.forEach(function (el) { el.textContent = el.getAttribute('data-count') + (el.getAttribute('data-suffix')||''); }); return; }
		var animated = new WeakSet();
		function animate(el) {
			if (animated.has(el)) return;
			animated.add(el);
			var target = parseInt(el.getAttribute('data-count'), 10) || 0;
			var suffix = el.getAttribute('data-suffix') || '';
			var duration = target > 1000 ? 1600 : target > 200 ? 1200 : 900;
			var start = performance.now();
			function step(now) {
				var p = Math.min((now - start) / duration, 1);
				var eased = 1 - Math.pow(1 - p, 3);
				var cur = Math.floor(eased * target);
				el.textContent = cur + suffix;
				if (p < 1) requestAnimationFrame(step);
				else el.textContent = target + suffix;
			}
			requestAnimationFrame(step);
		}
		if ('IntersectionObserver' in window) {
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (e) { if (e.isIntersecting) { animate(e.target); io.unobserve(e.target); } });
			}, { threshold: 0.4 });
			stats.forEach(function (el) { io.observe(el); });
		} else {
			stats.forEach(animate);
		}
	})();

	/* ---------- Stats label — gõ chữ dần (typewriter) ---------- */
	(function () {
		var labels = document.querySelectorAll('.stats-section .stat-label');
		if (!labels.length) return;
		if (reduceMotion) return;
		var typed = new WeakSet();
		labels.forEach(function (el) { el.setAttribute('data-text', el.textContent); el.textContent = ''; el.style.minHeight = '1.2em'; });
		function type(el) {
			if (typed.has(el)) return;
			typed.add(el);
			var text = el.getAttribute('data-text') || '';
			var i = 0;
			el.classList.add('is-typing');
			function tick() {
				if (i <= text.length) {
					el.textContent = text.slice(0, i) + (i < text.length ? '|' : '');
					i++;
					setTimeout(tick, 55);
				} else {
					el.textContent = text;
					el.classList.remove('is-typing');
				}
			}
			tick();
		}
		if ('IntersectionObserver' in window) {
			var io2 = new IntersectionObserver(function (entries) {
				entries.forEach(function (e) { if (e.isIntersecting) { type(e.target); io2.unobserve(e.target); } });
			}, { threshold: 0.5 });
			labels.forEach(function (el) { io2.observe(el); });
		} else {
			labels.forEach(type);
		}
	})();

	/* ---------- Mobile drawer ---------- */
	var navToggle = document.querySelector('.nav-toggle');
	var mobileDrawer = document.getElementById('mobile-drawer');

	var setDrawer = function (open) {
		if (!mobileDrawer) return;
		var searchPanel = document.getElementById('header-search-panel');
		if (open && searchPanel && !searchPanel.hidden) {
			searchPanel.hidden = true;
			var st = document.getElementById('search-toggle');
			if (st) st.setAttribute('aria-expanded', 'false');
		}
		if (open) {
			mobileDrawer.hidden = false;
			requestAnimationFrame(function () {
				mobileDrawer.classList.add('is-open');
			});
		} else {
			mobileDrawer.classList.remove('is-open');
			setTimeout(function () { mobileDrawer.hidden = true; }, 280);
		}
		if (navToggle) {
			navToggle.setAttribute('aria-expanded', String(open));
		}
		document.body.style.overflow = open ? 'hidden' : '';
	};

	navToggle && navToggle.addEventListener('click', function () {
		setDrawer(mobileDrawer.hidden);
	});
	mobileDrawer && mobileDrawer.addEventListener('click', function (e) {
		if (e.target.matches('.drawer-close') || e.target.classList.contains('mobile-drawer')) {
			setDrawer(false);
		}
	});
	// Accordion cho SẢN PHẨM trong drawer
	document.addEventListener('click', function (e) {
		var trigger = e.target.closest('.drawer-list .has-children > a');
		if (trigger && trigger.closest('.mobile-drawer')) {
			e.preventDefault();
			var li = trigger.parentElement;
			li.classList.toggle('is-open');
		}
	});
	// Đóng drawer khi chọn 1 chức năng (trừ parent SẢN PHẨM)
	mobileDrawer && mobileDrawer.addEventListener('click', function (e) {
		var link = e.target.closest('.drawer-list a');
		if (!link) return;
		// Nếu là parent có con thì đã xử lý accordion ở trên, không đóng
		if (link.closest('.has-children') && link.parentElement.classList.contains('has-children')) return;
		// Còn lại (TRANG CHỦ, GIỚI THIỆU, con của SẢN PHẨM...) thì đóng và cho điều hướng
		setDrawer(false);
	});

	/* ---------- Header search dropdown — premium, debounce, click-outside ---------- */
	(function () {
		var searchRoot = document.getElementById('header-search');
		var searchToggle = document.getElementById('search-toggle');
		var searchPanel = document.getElementById('header-search-panel');
		var searchForm = searchPanel ? searchPanel.querySelector('[data-autocomplete]') : null;
		if (!searchRoot || !searchToggle || !searchPanel) return;

		function setOpen(open) {
			if (open && mobileDrawer && !mobileDrawer.hidden) setDrawer(false);
			searchPanel.hidden = !open;
			searchToggle.setAttribute('aria-expanded', String(open));
			if (open) {
				var field = searchPanel.querySelector('input[type="search"]');
				field && field.focus();
			}
		}

		searchToggle.addEventListener('click', function (e) {
			e.stopPropagation();
			setOpen(searchPanel.hidden);
		});

		document.addEventListener('click', function (e) {
			if (!searchPanel.hidden && !e.target.closest('#header-search')) {
				setOpen(false);
			}
			var closer = e.target.closest('[data-modal-close]');
			if (closer) {
				var m = closer.closest('.modal');
				if (m) { m.hidden = true; document.body.style.overflow = ''; }
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				if (!searchPanel.hidden) setOpen(false);
				document.querySelectorAll('.modal:not([hidden])').forEach(function (m) { m.hidden = true; });
				document.body.style.overflow = '';
				var md = document.getElementById('mobile-drawer');
				md && !md.hidden && md.querySelector('.drawer-close').click();
			}
		});

		if (searchForm && window.MozlexData) {
			var input = searchForm.querySelector('input[type="search"]');
			var listBox = document.getElementById('suggest-box');
			var pills = searchPanel.querySelectorAll('[data-search-cat]');
			var activeCat = '';
			var timer = null;

			function doFetch() {
				var q = input.value.trim();
				if (q.length < 2) { listBox.innerHTML = ''; return; }
				var url = MozlexData.restUrl + 'products?s=' + encodeURIComponent(q);
				if (activeCat) url += '&product_category=' + encodeURIComponent(activeCat);
				fetch(url, { headers: { 'X-WP-Nonce': MozlexData.nonce } })
					.then(function (r) { return r.json(); })
					.then(function (data) {
						var items = data.items || data;
						renderSuggestions(items);
					})
					.catch(function () { listBox.innerHTML = '<li class="search-suggest-empty">Không tìm thấy sản phẩm</li>'; });
			}

			pills.forEach(function (pill) {
				pill.addEventListener('click', function () {
					pills.forEach(function (p) { p.classList.remove('is-active'); });
					pill.classList.add('is-active');
					activeCat = pill.getAttribute('data-search-cat') || '';
					if (input.value.trim().length >= 2) doFetch();
				});
			});

			searchForm.addEventListener('submit', function () {
				if (activeCat) {
					var catInput = searchForm.querySelector('input[name="product_category"]');
					if (!catInput) {
						catInput = document.createElement('input');
						catInput.type = 'hidden';
						catInput.name = 'product_category';
						searchForm.appendChild(catInput);
					}
					catInput.value = activeCat;
				}
			});

			input.addEventListener('input', function () {
				clearTimeout(timer);
				var q = input.value.trim();
				if (q.length < 2) { listBox.innerHTML = ''; return; }
				timer = setTimeout(doFetch, 260);
			});

			var activeIdx = -1;
			input.addEventListener('keydown', function (e) {
				var items = listBox.querySelectorAll('.search-suggest-item');
				if (!items.length) return;
				if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = Math.min(activeIdx + 1, items.length - 1); syncActive(items); }
				else if (e.key === 'ArrowUp') { e.preventDefault(); activeIdx = Math.max(activeIdx - 1, 0); syncActive(items); }
				else if (e.key === 'Enter' && activeIdx >= 0) { e.preventDefault(); items[activeIdx].click(); }
			});
			function syncActive(items) {
				items.forEach(function (el, i) { el.classList.toggle('is-active', i === activeIdx); if (i === activeIdx) el.focus(); });
			}

			function renderSuggestions(items) {
				activeIdx = -1;
				if (!items || !items.length) {
					listBox.innerHTML = '<li class="search-suggest-empty">Không tìm thấy sản phẩm</li>';
					return;
				}
				var q = input.value.trim().toLowerCase();
				listBox.innerHTML = items.slice(0, 6).map(function (item) {
					var model = escapeHtml(item.model || item.title);
					var idx = model.toLowerCase().indexOf(q);
					if (idx >= 0 && q.length) {
						model = escapeHtml((item.model || item.title).slice(0, idx)) + '<mark>' + escapeHtml((item.model || item.title).slice(idx, idx + q.length)) + '</mark>' + escapeHtml((item.model || item.title).slice(idx + q.length));
					}
					var thumb = item.thumb ? '<img src="' + encodeURI(item.thumb) + '" alt="" loading="lazy">' : '<span class="thumb-ph">' + escapeHtml((item.model || '').slice(0, 2).toUpperCase()) + '</span>';
					var cat = item.category ? '<span>' + escapeHtml(item.category) + '</span>' : '';
					var price = '';
					return '<li role="option"><a class="search-suggest-item" href="' + encodeURI(item.url) + '">' +
						'<span class="search-suggest-thumb">' + thumb + '</span>' +
						'<span class="search-suggest-body"><span class="search-suggest-name">' + model + '</span><span class="search-suggest-meta">' + cat + '</span></span>' +
						price +
						'</a></li>';
				}).join('');
			}
			function escapeHtml(s) {
				return String(s).replace(/[&<>"']/g, function (c) {
					return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
				});
			}
		}
	})();

	/* ---------- Filter: sidebar + chips + AJAX (premium) ---------- */
	(function () {
		var root = document.querySelector('[data-filter-root]');
		var form = document.getElementById('filter-form');
		var grid = document.getElementById('archive-grid');
		var countEl = document.getElementById('result-count');
		var chips = document.getElementById('active-chips');
		var chipsMobile = document.getElementById('active-chips-mobile');
		var pagination = document.getElementById('archive-pagination');
		var emptyEl = document.getElementById('archive-empty');
		var sidebar = document.getElementById('filter-sidebar');
		var toggleBtn = document.querySelector('[data-filter-toggle]');
		if (!root || !form || !grid) return;

		var sortSelect = form.querySelector('select[name="sort"]');
		var debounceTimer = null;

		function collectParams() {
			var params = new URLSearchParams();
			var groups = {};
			form.querySelectorAll('input[type="checkbox"]:checked').forEach(function (cb) {
				var name = cb.name;
				if (!groups[name]) groups[name] = [];
				groups[name].push(cb.value);
			});
			Object.keys(groups).forEach(function (k) { if (groups[k].length) params.set(k, groups[k].join(',')); });
			if (sortSelect && sortSelect.value) params.set('sort', sortSelect.value);
			// Preserve hidden category on taxonomy archive
			var hiddenCat = form.querySelector('input[type="hidden"][name="product_category"]');
			if (hiddenCat && hiddenCat.value) {
				var cur = params.get('product_category');
				if (!cur) params.set('product_category', hiddenCat.value);
			}
			return params;
		}

		function renderChips() {
			var params = collectParams();
			var html = '';
			var labelMap = {};
			form.querySelectorAll('input[type="checkbox"]:checked').forEach(function (cb) {
				var name = cb.name;
				var val = cb.value;
				var label = cb.closest('.filter-check').querySelector('.filter-check-label').textContent.trim();
				html += '<span class="chip" data-chip="' + name + ':' + val + '">' + escapeHtml(label) + ' <button type="button" aria-label="Xóa lọc">&times;</button></span>';
			});
			if (sortSelect && sortSelect.value) {
				var sortLabel = sortSelect.options[sortSelect.selectedIndex].textContent.trim();
				html += '<span class="chip" data-chip="sort:' + sortSelect.value + '">' + escapeHtml(sortLabel) + ' <button type="button" aria-label="Xóa">&times;</button></span>';
			}
			[chips, chipsMobile].forEach(function (c) {
				if (!c) return;
				c.innerHTML = html;
				c.hidden = !html;
			});
		}

		function escapeHtml(s) {
			return String(s).replace(/[&<>"']/g, function (c) { return { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]; });
		}

		function buildCard(item) {
			var price = '';
			var cat = item.category ? '<p class="card-eyebrow">' + escapeHtml(item.category) + '</p>' : '';
			var feat = (item.features && item.features.length) ? '<p class="card-features">' + escapeHtml(item.features.slice(0,3).join(' · ')) + '</p>' : '';
			var thumb = item.thumb_medium || item.thumb;
			var img = thumb ? '<img src="' + encodeURI(thumb) + '" alt="' + escapeHtml(item.model) + '" class="card-image" loading="lazy">' : '<div class="card-image card-image-placeholder"><span>' + escapeHtml(item.model) + '</span></div>';
			return '<article class="product-card" data-model="' + escapeHtml(item.model) + '"><a class="card-link" href="' + encodeURI(item.url) + '"><figure class="card-figure">' + img + '</figure><div class="card-body">' + cat + '<h3 class="card-title">' + escapeHtml(item.model) + '</h3>' + feat + '<p class="card-meta">' + price + '<span class="card-more">Xem chi tiết <span class="arrow">&rarr;</span></span></p></div></a></article>';
		}

		function fetchAndRender() {
			var params = collectParams();
			var qs = params.toString();
			var url = window.MozlexData ? window.MozlexData.restUrl + 'products?' + qs + '&per_page=24' : '';
			if (!url || !window.MozlexData) {
				// Fallback: submit form
				window.location.href = window.location.pathname + (qs ? '?' + qs : '');
				return;
			}
			var archiveMain = document.querySelector('.archive-main');
			archiveMain && archiveMain.classList.add('is-loading');
			fetch(url, { headers: { 'X-WP-Nonce': window.MozlexData.nonce } })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					var items = data.items || data;
					var total = typeof data.total === 'number' ? data.total : items.length;
					if (countEl) countEl.textContent = total + ' sản phẩm';
					if (!items.length) {
						grid.innerHTML = '';
						grid.hidden = true;
						if (emptyEl) emptyEl.hidden = false;
						if (pagination) pagination.hidden = true;
					} else {
						grid.innerHTML = items.map(buildCard).join('');
						grid.hidden = false;
						if (emptyEl) emptyEl.hidden = true;
						if (pagination) pagination.hidden = true;
					}
					renderChips();
					var newUrl = window.location.pathname + (qs ? '?' + qs : '');
					history.replaceState(null, '', newUrl);
				})
				.catch(function () {})
				.finally(function () { archiveMain && archiveMain.classList.remove('is-loading'); });
		}

		function scheduleFetch() {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(fetchAndRender, 280);
		}

		form.addEventListener('change', function (e) {
			if (e.target.matches('input[type="checkbox"], select')) {
				renderChips();
				if (e.target.matches('select')) { fetchAndRender(); } else { scheduleFetch(); }
			}
		});

		// Chips remove
		[chips, chipsMobile].forEach(function (c) {
			if (!c) return;
			c.addEventListener('click', function (e) {
				var btn = e.target.closest('button');
				if (!btn) return;
				var chip = btn.closest('[data-chip]');
				if (!chip) return;
				var parts = chip.getAttribute('data-chip').split(':');
				var name = parts[0], val = parts.slice(1).join(':');
				if (name === 'sort') {
					if (sortSelect) sortSelect.value = '';
				} else {
					var cb = form.querySelector('input[name="' + name + '"][value="' + CSS.escape(val) + '"]');
					if (cb) cb.checked = false;
				}
				renderChips();
				fetchAndRender();
			});
		});

		// Reset all
		document.querySelectorAll('[data-filter-reset]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				form.querySelectorAll('input[type="checkbox"]').forEach(function (cb) { cb.checked = false; });
				if (sortSelect) sortSelect.value = '';
				renderChips();
				fetchAndRender();
			});
		});

		// Mobile toggle
		if (toggleBtn && sidebar) {
			toggleBtn.addEventListener('click', function () {
				var open = sidebar.classList.toggle('is-open');
				toggleBtn.setAttribute('aria-expanded', String(open));
				document.body.style.overflow = open ? 'hidden' : '';
			});
			document.addEventListener('click', function (e) {
				if (sidebar.classList.contains('is-open') && !e.target.closest('#filter-sidebar') && !e.target.closest('[data-filter-toggle]')) {
					sidebar.classList.remove('is-open');
					toggleBtn.setAttribute('aria-expanded', 'false');
					document.body.style.overflow = '';
				}
			});
		}

		renderChips();

		// Non-JS fallback: prevent full submit, use AJAX instead
		form.addEventListener('submit', function (e) { e.preventDefault(); fetchAndRender(); });
	})();

	/* ---------- Consultation wizard ---------- */
	var wizard = document.querySelector('[data-wizard]');
	if (wizard) {
		var steps = Array.prototype.slice.call(wizard.querySelectorAll('.wizard-step'));
		var indicators = Array.prototype.slice.call(wizard.querySelectorAll('[data-step-indicator]'));
		var current = 1;

		function showStep(n) {
			current = n;
			steps.forEach(function (s) {
				var active = Number(s.getAttribute('data-step')) === n;
				s.hidden = !active;
				s.classList.toggle('is-active', active);
			});
			indicators.forEach(function (ind) {
				var num = Number(ind.getAttribute('data-step-indicator'));
				ind.classList.toggle('is-current', num === n);
				ind.classList.toggle('is-done', num < n);
			});
			wizard.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
		}

		wizard.addEventListener('click', function (e) {
			if (e.target.closest('[data-next]')) showStep(Math.min(current + 1, 5));
			if (e.target.closest('[data-prev]')) showStep(current - 1);
		});

		wizard.querySelector('#wizard-form').addEventListener('submit', function (e) {
			e.preventDefault();
			runWizardFilter(new FormData(e.target));
		});

		function runWizardFilter(data) {
			var cardsTpl = document.getElementById('wizard-cards-template');
			var grid = document.getElementById('wizard-grid');
			var results = document.getElementById('wizard-results');
			if (!cardsTpl) return;

			var chosen = {
				door: data.get('door'),
				budget: data.get('budget'),
				color: data.get('color'),
				unlock: data.getAll('unlock[]'),
				thickness: data.get('thickness')
			};

			var all = Array.prototype.slice.call(cardsTpl.content.children);
			var scored = all.map(function (card) {
				var filters = (card.getAttribute('data-filters') || '').split(',').filter(Boolean);
				var score = 0;
				if (chosen.door && (filters.indexOf(chosen.door) >= 0 || chosen.door === 'khác')) score += 3;
				if (chosen.unlock.length) {
					chosen.unlock.forEach(function (u) {
						if (filters.indexOf(u) >= 0) score += 2;
					});
				}
				if (chosen.budget && filters.indexOf(chosen.budget) >= 0) score += 2;
				if (chosen.color && filters.indexOf(chosen.color) >= 0) score += 1;
				return { card: card.cloneNode(true), score: score };
			})
				.filter(function (s) { return s.score > 0; })
				.sort(function (a, b) { return b.score - a.score; })
				.slice(0, 4);

			grid.innerHTML = '';
			scored.forEach(function (s) { grid.appendChild(s.card); });
			results.hidden = scored.length === 0;
			results.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth' });
		}
	}

	/* ---------- Lightbox gallery ---------- */
	var gallery = document.querySelector('[data-lightbox]');
	gallery && gallery.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-lightbox-item]');
		if (!btn) return;
		var items = Array.prototype.slice.call(gallery.querySelectorAll('[data-lightbox-item]'));
		var index = items.indexOf(btn);
		openLightbox(items, index);
	});
	function openLightbox(items, index) {
		var lb = document.createElement('div');
		lb.className = 'lightbox';
		lb.setAttribute('role', 'dialog');
		lb.setAttribute('aria-modal', 'true');
		lb.innerHTML =
			'<img alt="" src="' + encodeURI(items[index].getAttribute('data-full')) + '">' +
			'<p class="lightbox-caption">' + escapeAttr(items[index].getAttribute('data-caption') || '') + '</p>' +
			'<button type="button" class="lightbox-close" aria-label="Đóng">&times;</button>' +
			(items.length > 1
				? '<button type="button" class="lightbox-nav lightbox-prev" aria-label="Ảnh trước">&larr;</button>' +
				  '<button type="button" class="lightbox-nav lightbox-next" aria-label="Ảnh sau">&rarr;</button>'
				: '');
		document.body.appendChild(lb);
		document.body.style.overflow = 'hidden';

		var close = function () { lb.remove(); document.body.style.overflow = ''; };
		lb.querySelector('.lightbox-close').addEventListener('click', close);
		lb.addEventListener('click', function (e) { if (e.target === lb) close(); });

		if (items.length > 1) {
			var goStep = function (dir) {
				index = (index + dir + items.length) % items.length;
				lb.querySelector('img').src = items[index].getAttribute('data-full');
				lb.querySelector('.lightbox-caption').textContent = items[index].getAttribute('data-caption') || '';
			};
			lb.querySelector('.lightbox-prev').addEventListener('click', function () { goStep(-1); });
			lb.querySelector('.lightbox-next').addEventListener('click', function () { goStep(1); });
			lb.tabIndex = -1;
			lb.focus();
			lb.addEventListener('keydown', function (e) {
				if (e.key === 'ArrowLeft') goStep(-1);
				if (e.key === 'ArrowRight') goStep(1);
				if (e.key === 'Escape') close();
			});
		}
	}

	/* ---------- Product gallery slider — mũi tên trái/phải ---------- */
	(function () {
		function initGallery() {
			var slider = document.querySelector('[data-gallery-slider]');
			var dataEl = document.getElementById('gallery-data');
			var mainImg = document.getElementById('gallery-main-img');
			if (!slider || !dataEl || !mainImg) return;
			var data;
			try { data = JSON.parse(dataEl.textContent); } catch (e) { return; }
			if (!data || data.length <= 1) return;
			var idx = 0;
			var prev = slider.querySelector('.gallery-prev');
			var next = slider.querySelector('.gallery-next');
		function show(i) {
			idx = (i + data.length) % data.length;
			// Update src + srcset/sizes để tránh cache srcset cũ giữ ảnh cũ
			mainImg.src = data[idx].src;
			if (data[idx].full) {
				mainImg.srcset = data[idx].src + ' 1x';
				mainImg.removeAttribute('sizes');
			}
			mainImg.alt = data[idx].alt || mainImg.alt;
			// Sync thumbnail active state (cả product-thumbs và product-gallery)
			var thumbs = document.querySelectorAll('[data-lightbox-item]');
			thumbs.forEach(function (b, j) { b.classList.toggle('is-active', j === idx); });
		}
		prev && prev.addEventListener('click', function (e) { e.stopPropagation(); show(idx - 1); });
		next && next.addEventListener('click', function (e) { e.stopPropagation(); show(idx + 1); });
		// Click thumbnail → update main (thay vì mở lightbox ngay)
		var gallery = document.querySelector('[data-gallery]');
		if (gallery) {
			gallery.addEventListener('click', function (e) {
				var btn = e.target.closest('[data-lightbox-item]');
				if (!btn) return;
				e.preventDefault(); e.stopPropagation();
				var items = Array.prototype.slice.call(gallery.querySelectorAll('[data-lightbox-item]'));
				var j = items.indexOf(btn);
				if (j >= 0) show(j);
			}, true);
		}
		// Click main image (không phải nút mũi tên) → mở lightbox tại index hiện tại
		var mainWrap = document.querySelector('[data-gallery-main]');
		if (mainWrap) {
			mainWrap.addEventListener('click', function (e) {
				if (e.target.closest('.gallery-arrow')) return;
				var lbItems = document.querySelectorAll('[data-lightbox-item]');
				if (lbItems.length) openLightbox(Array.prototype.slice.call(lbItems), idx);
			});
		}
		// Keyboard arrows when slider focused
		slider.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') show(idx - 1);
			if (e.key === 'ArrowRight') show(idx + 1);
		});
		slider.tabIndex = 0;
		}
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', initGallery);
		} else {
			initGallery();
		}
	})();

	function escapeAttr(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	/* ---------- Hero Slider — 3s auto, dots/arrows, pause on hover/focus ---------- */
	(function () {
		var slider = document.querySelector('[data-hero-slider]');
		if (!slider) return;
		var slides = slider.querySelectorAll('.hero-slide');
		var dots = slider.querySelectorAll('.hero-dot');
		var prev = slider.querySelector('.hero-prev');
		var next = slider.querySelector('.hero-next');
		if (slides.length <= 1) return;
		var interval = parseInt(slider.getAttribute('data-interval'), 10) || 3000;
		var idx = 0;
		var timer = null;
		var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		function go(n) {
			idx = (n + slides.length) % slides.length;
			slides.forEach(function (s, i) {
				var active = i === idx;
				s.classList.toggle('is-active', active);
				s.setAttribute('aria-hidden', active ? 'false' : 'true');
			});
			dots.forEach(function (d, i) {
				var active = i === idx;
				d.classList.toggle('is-active', active);
				d.setAttribute('aria-selected', String(active));
			});
		}
		function start() {
			if (reduce) return;
			stop();
			timer = setInterval(function () { go(idx + 1); }, interval);
		}
		function stop() { if (timer) { clearInterval(timer); timer = null; } }

		if (prev) prev.addEventListener('click', function () { go(idx - 1); start(); });
		if (next) next.addEventListener('click', function () { go(idx + 1); start(); });
		dots.forEach(function (d) {
			d.addEventListener('click', function () {
				var n = parseInt(d.getAttribute('data-slide'), 10) || 0;
				go(n); start();
			});
		});
		slider.addEventListener('mouseenter', stop);
		slider.addEventListener('mouseleave', start);
		slider.addEventListener('focusin', stop);
		slider.addEventListener('focusout', start);
		// Swipe touch
		var startX = 0;
		slider.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; stop(); }, { passive: true });
		slider.addEventListener('touchend', function (e) {
			var dx = e.changedTouches[0].clientX - startX;
			if (Math.abs(dx) > 40) go(idx + (dx < 0 ? 1 : -1));
			start();
		}, { passive: true });
		document.addEventListener('visibilitychange', function () { document.hidden ? stop() : start(); });
		// Keyboard arrows when slider focused
		slider.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') { e.preventDefault(); go(idx - 1); start(); }
			if (e.key === 'ArrowRight') { e.preventDefault(); go(idx + 1); start(); }
		});
		slider.tabIndex = 0;
		start();
	})();

	/* ---------- Testimonials slider ---------- */
	var testSlider = document.querySelector('.testimonials-slider');
	if (testSlider) {
		var testItems = Array.prototype.slice.call(testSlider.querySelectorAll('.testimonial-item'));
		var testIdx = 0;
		var prevBtn = document.querySelector('.test-prev');
		var nextBtn = document.querySelector('.test-next');

		function showTestimonial(i) {
			testItems.forEach(function (item) { item.classList.remove('active'); });
			testIdx = (i + testItems.length) % testItems.length;
			testItems[testIdx].classList.add('active');
		}

		if (prevBtn) prevBtn.addEventListener('click', function () { showTestimonial(testIdx - 1); });
		if (nextBtn) nextBtn.addEventListener('click', function () { showTestimonial(testIdx + 1); });

		// Auto-slide every 6s
		setInterval(function () { showTestimonial(testIdx + 1); }, 6000);
	}
})();
