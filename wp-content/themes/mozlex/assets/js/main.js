/**
 * Mozlex front-end behaviors — vanilla JS, defer, không dependency.
 * Modules: header elevate, reveal, drawer/modal, autocomplete,
 * filter autosubmit, wizard, lightbox.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ---------- Product information tabs (links remain usable without JS) ---------- */
	(function () {
		var nav = document.querySelector('[data-product-tabs]');
		if (!nav) return;
		var tabs = Array.from(nav.querySelectorAll('a[href^="#"]'));
		var panels = tabs.map(function (tab) { return document.getElementById(tab.hash.slice(1)); });
		if (panels.some(function (panel) { return !panel; })) return;
		nav.setAttribute('role', 'tablist');
		tabs.forEach(function (tab, index) {
			tab.id = panels[index].id + '-tab';
			tab.setAttribute('role', 'tab');
			tab.setAttribute('aria-controls', panels[index].id);
			panels[index].setAttribute('role', 'tabpanel');
			panels[index].setAttribute('aria-labelledby', tab.id);
			panels[index].tabIndex = 0;
			tab.addEventListener('click', function (event) {
				event.preventDefault();
				activate(index, false);
				history.replaceState(null, '', tab.hash);
			});
			tab.addEventListener('keydown', function (event) {
				var next = index;
				if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
				else if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
				else if (event.key === 'Home') next = 0;
				else if (event.key === 'End') next = tabs.length - 1;
				else if (event.key === ' ') { event.preventDefault(); tab.click(); return; }
				else return;
				event.preventDefault();
				activate(next, true);
				history.replaceState(null, '', tabs[next].hash);
			});
		});
		function activate(index, focus) {
			tabs.forEach(function (tab, i) {
				var selected = i === index;
				tab.setAttribute('aria-selected', String(selected));
				tab.tabIndex = selected ? 0 : -1;
				panels[i].hidden = !selected;
			});
			if (focus) tabs[index].focus();
		}
		function readHash() {
			var index = tabs.findIndex(function (tab) { return tab.hash === window.location.hash; });
			activate(index < 0 ? 0 : index, false);
		}
		readHash();
		window.addEventListener('hashchange', readHash);
	})();

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


	/* ---------- Mobile drawer ---------- */
	var navToggle = document.querySelector('.nav-toggle');
	var mobileDrawer = document.getElementById('mobile-drawer');

	var drawerTimer;
	var setDrawer = function (open) {
		if (!mobileDrawer) return;
		clearTimeout(drawerTimer);
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
				mobileDrawer.querySelector('.drawer-close').focus();
			});
		} else {
			mobileDrawer.classList.remove('is-open');
			drawerTimer = setTimeout(function () { mobileDrawer.hidden = true; }, reduceMotion ? 0 : 280);
			navToggle && navToggle.focus();
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
		if (e.target.closest('.drawer-close') || e.target.classList.contains('drawer-backdrop') || e.target.classList.contains('mobile-drawer')) {
			setDrawer(false);
		}
	});

	// Xử lý chuyển đổi 3 Tabs di động (Menu / Danh mục / Liên hệ)
	if (mobileDrawer) {
		var drawerTabBtns = mobileDrawer.querySelectorAll('.drawer-tab-btn');
		var drawerTabPanels = mobileDrawer.querySelectorAll('.drawer-tab-panel');
		drawerTabBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var targetTab = btn.getAttribute('data-drawer-tab');
				drawerTabBtns.forEach(function (b) {
					var isActive = (b === btn);
					b.classList.toggle('is-active', isActive);
					b.setAttribute('aria-selected', String(isActive));
				});
				drawerTabPanels.forEach(function (panel) {
					var isMatch = (panel.getAttribute('data-drawer-panel') === targetTab);
					panel.hidden = !isMatch;
					panel.classList.toggle('is-active', isMatch);
				});
			});
		});
	}

	function trapFocus(container, e) {
		if (e.key !== 'Tab') return;
		var items = Array.from(container.querySelectorAll('a[href], button, input, select, textarea, [tabindex="0"]')).filter(function (el) { return !el.disabled && el.getClientRects().length; });
		var first = items[0], last = items[items.length - 1];
		if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
		else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
	}
	mobileDrawer && mobileDrawer.addEventListener('keydown', function (e) { trapFocus(mobileDrawer, e); });
	document.querySelectorAll('.drawer-list li').forEach(function (li) {
		var sub = li.querySelector(':scope > ul');
		var link = li.querySelector(':scope > a');
		if (!sub || !link) return;
		sub.hidden = true;
		var button = document.createElement('button');
		button.type = 'button';
		button.className = 'submenu-toggle';
		button.setAttribute('aria-label', 'Mở danh mục ' + link.textContent.trim());
		button.setAttribute('aria-expanded', 'false');
		button.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>';
		button.addEventListener('click', function (evt) {
			evt.preventDefault();
			evt.stopPropagation();
			var open = sub.hidden;
			sub.hidden = !open;
			li.classList.toggle('is-open', open);
			button.setAttribute('aria-expanded', String(open));
		});
		li.insertBefore(button, sub);
	});
	mobileDrawer && mobileDrawer.addEventListener('click', function (e) {
		if (e.target.closest('a') && !e.target.closest('.submenu-toggle')) setDrawer(false);
	});
	window.matchMedia('(max-width: 768px)').addEventListener('change', function (e) {
		if (!e.matches && mobileDrawer && !mobileDrawer.hidden) setDrawer(false);
	});

	/* ---------- Desktop Navigation & Mega Menu Hover Intent ---------- */
	(function () {
		var megaItems = document.querySelectorAll('.primary-nav .has-mega');
		if (!megaItems.length) return;

		function closeAllMega() {
			megaItems.forEach(function (item) {
				if (item._megaTimer) clearTimeout(item._megaTimer);
				item.classList.remove('is-open');
				var link = item.querySelector(':scope > a');
				if (link) link.setAttribute('aria-expanded', 'false');
			});
		}

		megaItems.forEach(function (item) {
			var menu = item.querySelector('.mega-menu');
			var link = item.querySelector(':scope > a');

			function show() {
				if (item._megaTimer) clearTimeout(item._megaTimer);
				item.classList.add('is-open');
				if (link) link.setAttribute('aria-expanded', 'true');
			}

			function hide() {
				if (item._megaTimer) clearTimeout(item._megaTimer);
				item._megaTimer = setTimeout(function () {
					item.classList.remove('is-open');
					if (link) link.setAttribute('aria-expanded', 'false');
				}, 120);
			}

			item.addEventListener('mouseenter', show);
			item.addEventListener('mouseleave', hide);

			if (menu) {
				menu.addEventListener('mouseenter', show);
				menu.addEventListener('mouseleave', hide);
			}

			item.addEventListener('focusin', show);
			item.addEventListener('focusout', function (e) {
				if (!item.contains(e.relatedTarget)) {
					item.classList.remove('is-open');
					if (link) link.setAttribute('aria-expanded', 'false');
				}
			});
		});

		// Immediately close mega menu when mouse enters ANY other navigation item
		var otherNavItems = document.querySelectorAll('.primary-nav .nav-list > li:not(.has-mega)');
		otherNavItems.forEach(function (other) {
			other.addEventListener('mouseenter', function () {
				closeAllMega();
			});
		});

		// Submenu hover intent for .mega-group inside mega menu (e.g. Công nghệ -> Ắc quy...)
		var megaGroups = document.querySelectorAll('.mega-group');
		megaGroups.forEach(function (group) {
			var sub = group.querySelector('.mega-sub');
			var groupTimer = null;

			function openSub() {
				clearTimeout(groupTimer);
				group.classList.add('is-open');
			}

			function closeSub() {
				clearTimeout(groupTimer);
				groupTimer = setTimeout(function () {
					group.classList.remove('is-open');
				}, 150);
			}

			group.addEventListener('mouseenter', openSub);
			group.addEventListener('mouseleave', closeSub);

			if (sub) {
				sub.addEventListener('mouseenter', openSub);
				sub.addEventListener('mouseleave', closeSub);
			}
		});

		// Close menu when clicking outside or pressing Escape
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				closeAllMega();
			}
		});
		document.addEventListener('click', function (e) {
			if (!e.target.closest('.has-mega')) {
				closeAllMega();
			}
		});
	})();

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
				if (!searchPanel.hidden) { setOpen(false); searchToggle.focus(); }
				document.querySelectorAll('.modal:not([hidden])').forEach(function (m) { m.hidden = true; });
				document.body.style.overflow = '';
				var md = document.getElementById('mobile-drawer');
				md && !md.hidden && md.querySelector('.drawer-close').click();
			}
		});

		if (searchForm && window.MozlexData) {
			var input        = searchForm.querySelector('input[type="search"]');
			var clearBtn     = searchForm.querySelector('.search-clear-btn');
			var spinner      = searchForm.querySelector('.search-spinner');
			var suggestPanel = document.getElementById('suggest-panel');
			var catsBox      = document.getElementById('suggest-cats');
			var listBox      = document.getElementById('suggest-box');
			var footerBox    = document.getElementById('suggest-footer');
			var pills        = searchPanel.querySelectorAll('[data-search-cat]');
			var activeCat    = '';
			var searchRequest = 0;
			var searchController;
			var status       = document.getElementById('search-status');
			var timer        = null;
			var activeIdx    = -1;

			function setSearching(loading) {
				if (spinner) spinner.hidden = !loading;
			}

			function updateClearBtn() {
				if (clearBtn) {
					clearBtn.hidden = !input.value.length;
				}
			}

			if (clearBtn) {
				clearBtn.addEventListener('click', function (e) {
					e.preventDefault();
					input.value = '';
					updateClearBtn();
					setSearching(false);
					if (suggestPanel) suggestPanel.hidden = true;
					if (catsBox) { catsBox.hidden = true; catsBox.innerHTML = ''; }
					if (listBox) listBox.innerHTML = '';
					if (footerBox) { footerBox.hidden = true; footerBox.innerHTML = ''; }
					activeIdx = -1;
					input.focus();
				});
			}

			function doFetch() {
				var request = ++searchRequest;
				if (searchController) searchController.abort();
				searchController = new AbortController();
				var q = input.value.trim();
				if (q.length < 2) {
					setSearching(false);
					if (suggestPanel) suggestPanel.hidden = true;
					if (listBox) listBox.innerHTML = '';
					return;
				}
				var url = MozlexData.restUrl + 'products?s=' + encodeURIComponent(q);
				if (activeCat) url += '&product_category=' + encodeURIComponent(activeCat);
				if (status) status.textContent = 'Đang tìm kiếm…';
				setSearching(true);

				fetch(url, { signal: searchController.signal, headers: { 'X-WP-Nonce': MozlexData.nonce } })
					.then(function (r) { if (!r.ok) throw new Error('Search failed'); return r.json(); })
					.then(function (data) {
						if (request !== searchRequest) return;
						setSearching(false);
						renderSuggestions(data, q);
					})
					.catch(function (error) {
						if (error.name === 'AbortError' || request !== searchRequest) return;
						setSearching(false);
						if (suggestPanel) suggestPanel.hidden = false;
						if (listBox) listBox.innerHTML = '<li class="search-suggest-empty">Chưa tải được gợi ý. Nhấn Tìm để xem kết quả.</li>';
						if (status) status.textContent = 'Chưa tải được gợi ý';
					});
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
				var staleCat = searchForm.querySelector('input[name="product_category"]');
				if (!activeCat && staleCat) staleCat.remove();
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
				searchRequest++;
				if (searchController) searchController.abort();
				if (status) status.textContent = '';
				activeIdx = -1;
				updateClearBtn();
				var q = input.value.trim();
				if (q.length < 2) {
					setSearching(false);
					if (suggestPanel) suggestPanel.hidden = true;
					if (listBox) listBox.innerHTML = '';
					return;
				}
				setSearching(true);
				timer = setTimeout(doFetch, 240);
			});

			searchForm.parentElement.addEventListener('keydown', function (e) {
				var focusable = searchPanel.querySelectorAll('.suggest-cat-item, .search-suggest-item, .suggest-view-all');
				if (!focusable.length) return;
				if (e.target !== input && !e.target.closest('#suggest-panel')) return;

				if (e.key === 'ArrowDown') {
					e.preventDefault();
					activeIdx = Math.min(activeIdx + 1, focusable.length - 1);
					syncActive(focusable);
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					activeIdx = Math.max(activeIdx - 1, 0);
					syncActive(focusable);
				} else if (e.key === 'Enter' && activeIdx >= 0 && focusable[activeIdx]) {
					e.preventDefault();
					focusable[activeIdx].click();
				}
			});

			function syncActive(items) {
				items.forEach(function (el, i) {
					el.classList.toggle('is-active', i === activeIdx);
					if (i === activeIdx) {
						el.focus();
						el.scrollIntoView({ block: 'nearest' });
					}
				});
			}

			function mozlexUnaccent(s) {
				return String(s || '')
					.normalize('NFD')
					.replace(/[\u0300-\u036f]/g, '')
					.replace(/[đĐ]/g, function (c) { return c === 'đ' ? 'd' : 'D'; })
					.toLowerCase();
			}

			function highlightMatch(text, query) {
				if (!text || !query) return escapeHtml(text || '');
				var cleanText = String(text);
				var unaccText = mozlexUnaccent(cleanText);
				var unaccQ    = mozlexUnaccent(query.trim());
				if (!unaccQ) return escapeHtml(cleanText);

				var idx = unaccText.indexOf(unaccQ);
				if (idx >= 0) {
					var mLen = query.trim().length;
					return escapeHtml(cleanText.slice(0, idx)) +
						'<mark>' + escapeHtml(cleanText.slice(idx, idx + mLen)) + '</mark>' +
						escapeHtml(cleanText.slice(idx + mLen));
				}
				return escapeHtml(cleanText);
			}

			function renderSuggestions(data, q) {
				activeIdx = -1;
				var items = data.items || (Array.isArray(data) ? data : []);
				var cats  = data.categories || [];
				var total = typeof data.total === 'number' ? data.total : items.length;

				if (suggestPanel) suggestPanel.hidden = false;

				if (!items.length && !cats.length) {
					if (catsBox) { catsBox.hidden = true; catsBox.innerHTML = ''; }
					if (footerBox) { footerBox.hidden = true; footerBox.innerHTML = ''; }
					if (listBox) listBox.innerHTML = '<li class="search-suggest-empty">Không tìm thấy sản phẩm cho “' + escapeHtml(q) + '”</li>';
					if (status) status.textContent = 'Không tìm thấy kết quả';
					return;
				}

				if (status) status.textContent = items.length + ' gợi ý sản phẩm phù hợp';

				// 1. Categories section
				if (catsBox) {
					if (cats.length) {
						catsBox.hidden = false;
						catsBox.innerHTML = '<div class="suggest-section-title">Danh mục liên quan</div><div class="suggest-cat-pills">' +
							cats.map(function (c) {
								return '<a class="suggest-cat-item" href="' + encodeURI(c.url) + '">' +
									'<span>' + escapeHtml(c.name) + '</span>' +
									'<span class="count">(' + (c.count || 0) + ')</span>' +
									'</a>';
							}).join('') +
							'</div>';
					} else {
						catsBox.hidden = true;
						catsBox.innerHTML = '';
					}
				}

				// 2. Products section
				if (listBox) {
					if (items.length) {
						listBox.innerHTML = items.slice(0, 6).map(function (item) {
							var nameHighlighted = highlightMatch(item.title, q);
							var thumb = item.thumb ? '<img src="' + encodeURI(item.thumb) + '" alt="" loading="lazy">' : '<span class="thumb-ph">' + escapeHtml((item.model || item.title || '').slice(0, 2).toUpperCase()) + '</span>';
							var cat = item.category ? '<span>' + escapeHtml(item.category) + '</span>' : '';
							var specsHtml = '';
							if (item.specs && item.specs.length) {
								specsHtml = '<span class="search-suggest-specs">' + item.specs.slice(0, 2).map(function (sp) {
									return '<span class="suggest-spec-tag">' + escapeHtml(sp) + '</span>';
								}).join('') + '</span>';
							}
							return '<li><a class="search-suggest-item" href="' + encodeURI(item.url) + '">' +
								'<span class="search-suggest-thumb">' + thumb + '</span>' +
								'<span class="search-suggest-body">' +
									'<span class="search-suggest-name">' + nameHighlighted + '</span>' +
									'<span class="search-suggest-meta">' + cat + '</span>' +
									specsHtml +
								'</span>' +
								'</a></li>';
						}).join('');
					} else {
						listBox.innerHTML = '';
					}
				}

				// 3. View All footer
				if (footerBox) {
					if (total > 6) {
						footerBox.hidden = false;
						var homeBase = MozlexData.homeUrl || '/';
						var targetUrl = homeBase + (homeBase.slice(-1) === '/' ? '' : '/') + '?s=' + encodeURIComponent(q) + '&post_type=product' + (activeCat ? '&product_category=' + encodeURIComponent(activeCat) : '');
						footerBox.innerHTML = '<a class="suggest-view-all" href="' + encodeURI(targetUrl) + '">Xem tất cả ' + total + ' sản phẩm cho “' + escapeHtml(q) + '” &rarr;</a>';
					} else {
						footerBox.hidden = true;
						footerBox.innerHTML = '';
					}
				}
			}

			function escapeHtml(s) {
				return String(s).replace(/[&<>"']/g, function (c) {
					return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
				});
			}
		}

		// Wire up clear buttons on page-search-form elements
		document.querySelectorAll('.page-search-form').forEach(function (form) {
			var pInput = form.querySelector('input[type="search"]');
			var pClear = form.querySelector('.search-clear-btn');
			if (!pInput || !pClear) return;
			pInput.addEventListener('input', function () {
				pClear.hidden = !pInput.value.trim().length;
			});
			pClear.addEventListener('click', function () {
				pInput.value = '';
				pClear.hidden = true;
				pInput.focus();
			});
		});
	})();

	/* ---------- Filter: sidebar + chips + AJAX + skeleton (luxury) ---------- */
	(function () {
		var root = document.querySelector('[data-filter-root]');
		var form = document.getElementById('filter-form');
		var grid = document.getElementById('archive-grid');
		var countEl = document.getElementById('result-count');
		var chipsContainer = document.getElementById('active-chips');
		var activeFilterBar = document.getElementById('active-filter-bar');
		var pagination = document.getElementById('archive-pagination');
		var emptyEl = document.getElementById('archive-empty');
		var sidebar = document.getElementById('filter-sidebar');
		var toggleBtn = document.querySelector('[data-filter-toggle]');
		var backdrop = document.querySelector('[data-filter-backdrop]');
		var keywordInput = document.getElementById('filter-keyword-input');
		var clearSearchBtn = document.querySelector('[data-clear-search]');
		var badgeCountEl = document.querySelector('[data-filter-badge-count]');
		var drawerCountEl = document.querySelector('[data-filter-drawer-count]');
		var viewModeBtns = document.querySelectorAll('[data-view-mode]');

		if (!root || !form || !grid) return;

		var sortSelect = document.getElementById('product-sort');
		var debounceTimer = null;
		var filterRequest = 0;

		// 1. View mode toggle (Grid 4 vs Grid 2)
		var currentViewMode = localStorage.getItem('mozlex_view_mode') || 'grid-4';
		function setViewMode(mode) {
			currentViewMode = mode;
			localStorage.setItem('mozlex_view_mode', mode);
			grid.classList.remove('grid-4', 'grid-2');
			grid.classList.add(mode);
			viewModeBtns.forEach(function (btn) {
				btn.classList.toggle('is-active', btn.getAttribute('data-view-mode') === mode);
			});
		}
		setViewMode(currentViewMode);
		viewModeBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				setViewMode(this.getAttribute('data-view-mode'));
			});
		});

		// 2. Params Collector
		function collectParams() {
			var params = new URLSearchParams();
			var groups = {};
			form.querySelectorAll('input[type="checkbox"]:checked').forEach(function (cb) {
				var name = cb.name;
				if (!groups[name]) groups[name] = [];
				groups[name].push(cb.value);
			});
			Object.keys(groups).forEach(function (k) {
				if (groups[k].length) params.set(k, groups[k].join(','));
			});

			if (keywordInput && keywordInput.value.trim()) {
				params.set('s', keywordInput.value.trim());
			}

			if (sortSelect && sortSelect.value) {
				params.set('sort', sortSelect.value);
			}

			// Preserve hidden category on taxonomy archive
			var hiddenCat = form.querySelector('input[type="hidden"][name="product_category"]');
			if (hiddenCat && hiddenCat.value) {
				var cur = params.get('product_category');
				if (!cur) params.set('product_category', hiddenCat.value);
			}
			return params;
		}

		// 3. Render Chips & Sync Swatches
		function syncSwatches() {
			form.querySelectorAll('.swatch-item').forEach(function (swatch) {
				var input = swatch.querySelector('input[type="checkbox"]');
				if (input) {
					swatch.classList.toggle('is-active', input.checked);
				}
			});
		}

		function renderChips(totalCount) {
			syncSwatches();
			var params = collectParams();
			var html = '';
			var activeFilterCount = 0;

			// Checkboxes
			form.querySelectorAll('input[type="checkbox"]:checked').forEach(function (cb) {
				var name = cb.name;
				var val = cb.value;
				var label = '';
				var swatch = cb.closest('.swatch-item');
				var checkLabel = cb.closest('.filter-check');

				if (swatch) {
					var nameEl = swatch.querySelector('.swatch-name');
					label = nameEl ? nameEl.textContent.trim() : val;
				} else if (checkLabel) {
					var textEl = checkLabel.querySelector('.filter-check-label');
					label = textEl ? textEl.textContent.trim() : val;
				} else {
					label = val;
				}

				html += '<span class="filter-chip" data-chip="' + name + ':' + val + '">' +
					'<span class="chip-text">' + escapeHtml(label) + '</span>' +
					'<button type="button" class="chip-remove" aria-label="Xóa bộ lọc">&times;</button>' +
				'</span>';
				activeFilterCount++;
			});

			// Keyword search chip
			if (keywordInput && keywordInput.value.trim()) {
				var kw = keywordInput.value.trim();
				html += '<span class="filter-chip is-keyword" data-chip="s:' + escapeHtml(kw) + '">' +
					'<span class="chip-text">Từ khóa: "' + escapeHtml(kw) + '"</span>' +
					'<button type="button" class="chip-remove" aria-label="Xóa từ khóa">&times;</button>' +
				'</span>';
				activeFilterCount++;
			}

			// Sort chip (only if not default newest)
			if (sortSelect && sortSelect.value) {
				var sortLabel = sortSelect.options[sortSelect.selectedIndex].textContent.trim();
				html += '<span class="filter-chip is-sort" data-chip="sort:' + sortSelect.value + '">' +
					'<span class="chip-text">' + escapeHtml(sortLabel) + '</span>' +
					'<button type="button" class="chip-remove" aria-label="Xóa sắp xếp">&times;</button>' +
				'</span>';
			}

			if (chipsContainer) {
				chipsContainer.innerHTML = html;
			}
			if (activeFilterBar) {
				activeFilterBar.hidden = !html;
			}

			// Mobile trigger badge count
			if (badgeCountEl) {
				badgeCountEl.textContent = activeFilterCount;
				badgeCountEl.hidden = activeFilterCount === 0;
			}
			if (drawerCountEl && typeof totalCount === 'number') {
				drawerCountEl.textContent = '(' + totalCount + ')';
			}
		}

		function escapeHtml(s) {
			return String(s).replace(/[&<>"']/g, function (c) {
				return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
			});
		}

		function renderSkeletons() {
			var skels = '';
			for (var i = 0; i < 8; i++) {
				skels += '<article class="product-card skeleton-card">' +
					'<div class="skeleton-figure"></div>' +
					'<div class="skeleton-content">' +
						'<div class="skeleton-bar bar-xs"></div>' +
						'<div class="skeleton-bar bar-title"></div>' +
						'<div class="skeleton-bar bar-meta"></div>' +
					'</div>' +
				'</article>';
			}
			grid.innerHTML = skels;
			grid.hidden = false;
			if (emptyEl) emptyEl.hidden = true;
		}

		function buildCard(item) {
			var cat = item.category ? '<p class="card-eyebrow">' + escapeHtml(item.category) + '</p>' : '';
			var feat = (item.features && item.features.length) ? '<p class="card-features">' + escapeHtml(item.features.slice(0, 3).join(' · ')) + '</p>' : '';
			var thumb = item.thumb_medium || item.thumb;
			var img = thumb ? '<img src="' + encodeURI(thumb) + '" alt="' + escapeHtml(item.model) + '" class="card-image" loading="lazy">' : '<div class="card-image card-image-placeholder"><span>' + escapeHtml(item.model) + '</span></div>';
			return '<article class="product-card" data-model="' + escapeHtml(item.model) + '">' +
				'<a class="card-link" href="' + encodeURI(item.url) + '">' +
					'<figure class="card-figure">' +
						'<span class="card-badge">Chính hãng</span>' +
						img +
					'</figure>' +
					'<div class="card-body">' +
						cat +
						'<h3 class="card-title">' + escapeHtml(item.model) + '</h3>' +
						feat +
						'<p class="card-meta">' +
							'<span class="card-more">Xem chi tiết <span class="arrow">&rarr;</span></span>' +
						'</p>' +
					'</div>' +
				'</a>' +
			'</article>';
		}

		function fetchAndRender() {
			clearTimeout(debounceTimer);
			var request = ++filterRequest;
			var params = collectParams();
			var qs = params.toString();
			var basePath = window.location.pathname.replace(/\/page\/\d+\/?$/, '/');
			var url = window.MozlexData ? window.MozlexData.restUrl + 'products?' + qs + '&per_page=16' : '';

			if (!url || !window.MozlexData) {
				window.location.href = basePath + (qs ? '?' + qs : '');
				return;
			}

			var archiveMain = document.querySelector('.archive-main');
			archiveMain && archiveMain.classList.add('is-loading');
			renderSkeletons();

			fetch(url, { headers: { 'X-WP-Nonce': window.MozlexData.nonce } })
				.then(function (r) {
					if (!r.ok) throw new Error('Filter fetch failed');
					return r.json();
				})
				.then(function (data) {
					if (request !== filterRequest) return;
					var items = data.items || data;
					var total = typeof data.total === 'number' ? data.total : items.length;

					if (countEl) {
						countEl.innerHTML = 'Hiển thị <strong class="count-number">' + total + '</strong> sản phẩm';
					}

					if (!items.length) {
						grid.innerHTML = '';
						grid.hidden = true;
						if (emptyEl) emptyEl.hidden = false;
						if (pagination) pagination.hidden = true;
					} else {
						grid.innerHTML = items.map(buildCard).join('');
						grid.hidden = false;
						if (emptyEl) emptyEl.hidden = true;
					}

					renderChips(total);

					var newUrl = basePath + (qs ? '?' + qs : '');
					if (pagination) {
						pagination.innerHTML = '';
						for (var page = 1; page <= (data.total_pages || 1); page++) {
							var link = document.createElement('a');
							var pageParams = new URLSearchParams(qs);
							pageParams.set('paged', page);
							link.href = basePath + '?' + pageParams;
							link.textContent = page;
							link.className = 'page-numbers';
							link.setAttribute('aria-label', 'Trang ' + page);
							if (page === 1) link.setAttribute('aria-current', 'page');
							pagination.appendChild(link);
						}
						pagination.hidden = !(data.total_pages > 1);
					}
					history.replaceState(null, '', newUrl);
				})
				.catch(function () {
					if (request === filterRequest) {
						window.location.assign(basePath + (qs ? '?' + qs : ''));
					}
				})
				.finally(function () {
					if (request === filterRequest) {
						archiveMain && archiveMain.classList.remove('is-loading');
					}
				});
		}

		function scheduleFetch() {
			filterRequest++;
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(fetchAndRender, 240);
		}

		// Input events
		root.addEventListener('change', function (e) {
			if (e.target.matches('input[type="checkbox"], select')) {
				syncSwatches();
				renderChips();
				if (e.target.matches('select')) {
					fetchAndRender();
				} else {
					scheduleFetch();
				}
			}
		});

		// Keyword search input
		if (keywordInput) {
			keywordInput.addEventListener('input', function () {
				if (clearSearchBtn) {
					clearSearchBtn.hidden = !this.value;
				}
				scheduleFetch();
			});
		}
		if (clearSearchBtn) {
			clearSearchBtn.addEventListener('click', function () {
				if (keywordInput) {
					keywordInput.value = '';
					this.hidden = true;
					fetchAndRender();
				}
			});
		}

		// Remove individual chips
		if (chipsContainer) {
			chipsContainer.addEventListener('click', function (e) {
				var btn = e.target.closest('.chip-remove');
				if (!btn) return;
				var chip = btn.closest('[data-chip]');
				if (!chip) return;
				var parts = chip.getAttribute('data-chip').split(':');
				var name = parts[0];
				var val = parts.slice(1).join(':');

				if (name === 'sort') {
					if (sortSelect) sortSelect.value = '';
				} else if (name === 's') {
					if (keywordInput) {
						keywordInput.value = '';
						if (clearSearchBtn) clearSearchBtn.hidden = true;
					}
				} else {
					var cb = form.querySelector('input[name="' + name + '"][value="' + CSS.escape(val) + '"]');
					if (cb) cb.checked = false;
				}
				syncSwatches();
				renderChips();
				fetchAndRender();
			});
		}

		// Reset all filters
		document.querySelectorAll('[data-filter-reset]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				form.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
					cb.checked = false;
				});
				if (keywordInput) {
					keywordInput.value = '';
					if (clearSearchBtn) clearSearchBtn.hidden = true;
				}
				if (sortSelect) sortSelect.value = '';
				syncSwatches();
				renderChips();
				fetchAndRender();
			});
		});

		// Mobile drawer interactions
		function closeFilters() {
			if (!sidebar) return;
			sidebar.classList.remove('is-open');
			if (backdrop) backdrop.classList.remove('is-open');
			if (toggleBtn) {
				toggleBtn.setAttribute('aria-expanded', 'false');
				toggleBtn.focus();
			}
			sidebar.removeAttribute('role');
			sidebar.removeAttribute('aria-modal');
			document.body.style.overflow = '';
		}

		function openFilters() {
			if (!sidebar) return;
			sidebar.classList.add('is-open');
			if (backdrop) backdrop.classList.add('is-open');
			if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
			document.body.style.overflow = 'hidden';
			sidebar.setAttribute('role', 'dialog');
			sidebar.setAttribute('aria-modal', 'true');
			var closeBtn = sidebar.querySelector('[data-filter-close]');
			if (closeBtn) closeBtn.focus();
		}

		if (toggleBtn && sidebar) {
			toggleBtn.addEventListener('click', function () {
				if (sidebar.classList.contains('is-open')) {
					closeFilters();
				} else {
					openFilters();
				}
			});

			var closeBtn = sidebar.querySelector('[data-filter-close]');
			if (closeBtn) {
				closeBtn.addEventListener('click', closeFilters);
			}

			if (backdrop) {
				backdrop.addEventListener('click', closeFilters);
			}

			var applyBtn = sidebar.querySelector('[data-filter-apply]');
			if (applyBtn) {
				applyBtn.addEventListener('click', function () {
					closeFilters();
					var mainTop = document.querySelector('.archive-main');
					if (mainTop) {
						mainTop.scrollIntoView({ behavior: 'smooth', block: 'start' });
					}
				});
			}

			sidebar.addEventListener('keydown', function (e) {
				if (!sidebar.classList.contains('is-open')) return;
				if (e.key === 'Escape') closeFilters();
			});

			window.matchMedia('(max-width: 991px)').addEventListener('change', function (e) {
				if (!e.matches && sidebar.classList.contains('is-open')) {
					closeFilters();
				}
			});
		}

		// Initial sync
		syncSwatches();
		renderChips();

		// Non-JS fallback
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			fetchAndRender();
		});
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

		var previousFocus = document.activeElement;
		lb.setAttribute('aria-label', 'Ảnh sản phẩm');
		lb.querySelector('img').alt = items[index].getAttribute('data-caption') || '';
		var close = function () { lb.remove(); document.body.style.overflow = ''; previousFocus && previousFocus.focus(); };
		lb.querySelector('.lightbox-close').focus();
		lb.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); else trapFocus(lb, e); });
		lb.querySelector('.lightbox-close').addEventListener('click', close);
		lb.addEventListener('click', function (e) { if (e.target === lb) close(); });

		if (items.length > 1) {
			var goStep = function (dir) {
				index = (index + dir + items.length) % items.length;
				var imgEl = lb.querySelector('img');
				imgEl.classList.remove('is-swap');
				void imgEl.offsetWidth;
				imgEl.src = items[index].getAttribute('data-full');
				imgEl.alt = items[index].getAttribute('data-caption') || '';
				imgEl.classList.add('is-swap');
				lb.querySelector('.lightbox-caption').textContent = items[index].getAttribute('data-caption') || '';
			};
			lb.querySelector('.lightbox-prev').addEventListener('click', function () { goStep(-1); });
			lb.querySelector('.lightbox-next').addEventListener('click', function () { goStep(1); });
			lb.tabIndex = -1;
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
			if (!data || !data.length) return;
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
				if (e.target.closest('.product-arrow')) return;
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
		var paused = reduce;
		var pause = slider.querySelector('[data-hero-pause]');
		function syncPause() { if (pause) { pause.textContent = paused ? 'Phát trình chiếu' : 'Tạm dừng trình chiếu'; pause.setAttribute('aria-pressed', String(paused)); } }
		if (pause) pause.addEventListener('click', function () { paused = !paused; syncPause(); paused ? stop() : start(); });
		syncPause();

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
				d.setAttribute('aria-pressed', String(active));
			});
		}
		function start() {
			if (reduce || paused || document.hidden || slider.contains(document.activeElement)) return;
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
		slider.addEventListener('focusout', function () { setTimeout(start, 0); });
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

		// Advance only on user input; no continuous motion while reading.
	}

	var productActions = document.querySelector('[data-product-actions]');
	var primaryActions = document.querySelector('.product-ctas');
	if (productActions && primaryActions && 'IntersectionObserver' in window) {
		var actionObserver = new IntersectionObserver(function (entries) {
			var show = !entries[0].isIntersecting && entries[0].boundingClientRect.bottom < 0;
			productActions.hidden = !show;
			document.body.classList.toggle('has-product-actions', show);
		});
		actionObserver.observe(primaryActions);
	}

	/* ---------- Social Copy Link Handler ---------- */
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.share-copy');
		if (!btn) return;
		var url = btn.getAttribute('data-copy-url') || window.location.href;
		var textEl = btn.querySelector('.share-copy-text');
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(url).then(function () {
				if (textEl) {
					var orig = textEl.textContent;
					textEl.textContent = '✓ Đã chép!';
					setTimeout(function () { textEl.textContent = orig; }, 2000);
				}
			});
		} else {
			var input = document.createElement('input');
			input.value = url;
			document.body.appendChild(input);
			input.select();
			document.execCommand('copy');
			document.body.removeChild(input);
			if (textEl) {
				var orig = textEl.textContent;
				textEl.textContent = '✓ Đã chép!';
				setTimeout(function () { textEl.textContent = orig; }, 2000);
			}
		}
	});

	/* ---------- Ensure clicking anywhere on product card navigates to product page ---------- */
	document.addEventListener('click', function (e) {
		var card = e.target.closest('.product-card');
		if (!card) return;
		// If clicking an interactive control like button or input, let it handle itself
		if (e.target.closest('button, input, select, textarea, label, [data-prevent-nav]')) return;

		var link = card.querySelector('a.card-link') || card.querySelector('a[href]');
		if (!link || !link.href) return;

		// If click was already on an <a> element itself, browser default will handle it
		var clickedLink = e.target.closest('a');
		if (clickedLink) return;

		if (e.ctrlKey || e.metaKey || e.button === 1) {
			window.open(link.href, '_blank');
		} else {
			window.location.href = link.href;
		}
	});
})();
