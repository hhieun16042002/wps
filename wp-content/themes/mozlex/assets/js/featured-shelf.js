/* Kệ 3D khối nổi bật — cảm hứng AshenPress, dữ liệu sản phẩm thật của shop.
 * Three.js r181 (importmap trong template part). Thất bại ở bất kỳ bước nào
 * → giữ nguyên lưới product-card (fallback), không bao giờ để trống section.
 */
import * as THREE from 'three';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

(function () {
	var mount = document.getElementById('mozlex-shelf');
	var dataEl = document.getElementById('mozlex-shelf-data');
	var tip = document.getElementById('mozlex-shelf-tip');
	var panel = document.getElementById('mozlex-shelf-preview');
	if (!mount || !dataEl) return;

	var items = null;
	try { items = JSON.parse(dataEl.textContent || '[]'); } catch (e) { items = null; }
	if (!Array.isArray(items) || !items.length) return;

	var grid = mount.closest('section')
		? mount.closest('section').querySelector('.product-grid')
		: null;

	function fail() {
		/* Giữ lưới hiện tại, ẩn mount. */
		mount.setAttribute('hidden', '');
		if (grid) grid.classList.remove('is-hidden');
	}

	/* Kệ 3D cần chiều ngang tối thiểu mới đủ chỗ trưng — màn hẹp (<640px)
	 * giữ lưới 2 cột vốn đã đẹp, không cố nhồi kệ tí hon. */
	var MIN_WIDTH = 640;
	var started = false;
	function viewWidth() {
		/* Mount đang hidden nên clientWidth = 0 — đo theo khung cha đang hiện. */
		var shell = mount.parentElement;
		return (shell && shell.clientWidth) || window.innerWidth || 1024;
	}
	function maybeStart() {
		if (started) return;
		if (viewWidth() < MIN_WIDTH) return; /* giữ lưới, thử lại khi xoay ngang */
		started = true;
		boot();
	}
	window.addEventListener('resize', maybeStart);
	window.addEventListener('orientationchange', maybeStart);
	maybeStart();

	function boot() {
	var canvas = document.getElementById('mozlex-shelf-canvas');
	if (!canvas) { fail(); return; }

	var renderer = null;
	try {
		renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true });
	} catch (e) { fail(); return; }
	if (!renderer.getContext()) { fail(); return; }

	var reduced = window.matchMedia
		? window.matchMedia('(prefers-reduced-motion: reduce)').matches
		: false;

	/* ---------- dữ liệu ---------- */
	var BOOK_W = 1.04, BOOK_H = 1.58, BOOK_D = 0.56, GAP = 0.26, ROW_H = 2.2;
	var n = items.length;
	var perShelf = n <= 4 ? n : (n <= 12 ? Math.ceil(n / 2) : Math.ceil(n / 3));
	var shelves = [];
	for (var s = 0; s < n; s += perShelf) shelves.push(items.slice(s, s + perShelf));

	/* ---------- scene ---------- */
	var BG = new THREE.Color(0xefe4d2);
	var scene = new THREE.Scene();
	scene.background = BG;
	scene.fog = new THREE.Fog(BG, 14, 26);

	var camera = new THREE.PerspectiveCamera(32, 1, 0.1, 100);

	try {
		var pmrem = new THREE.PMREMGenerator(renderer);
		scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
	} catch (e) { /* thiếu env vẫn render tốt */ }

	renderer.shadowMap.enabled = true;
	renderer.shadowMap.type = THREE.PCFSoftShadowMap;
	renderer.toneMapping = THREE.ACESFilmicToneMapping;
	renderer.toneMappingExposure = 1.05;

	scene.add(new THREE.HemisphereLight(0xfff4e0, 0x8a6b4a, 0.55));
	var key = new THREE.DirectionalLight(0xffe9c4, 1.7);
	key.position.set(4.5, 8, 6.5);
	key.castShadow = true;
	key.shadow.mapSize.set(2048, 2048);
	key.shadow.camera.left = -8; key.shadow.camera.right = 8;
	key.shadow.camera.top = 8; key.shadow.camera.bottom = -4;
	key.shadow.camera.far = 30;
	key.shadow.bias = -0.0004;
	scene.add(key);
	var fill = new THREE.DirectionalLight(0xdfe8ff, 0.45);
	fill.position.set(-5, 3, 4);
	scene.add(fill);

	/* ---------- vân gỗ vẽ tay (không asset ngoài) ---------- */
	function woodTexture(w, h, base, dark) {
		var c = document.createElement('canvas');
		c.width = w; c.height = h;
		var g = c.getContext('2d');
		g.fillStyle = base; g.fillRect(0, 0, w, h);
		var i, y;
		for (i = 0; i < 46; i++) {
			y = Math.random() * h;
			g.strokeStyle = 'rgba(' + dark + ',' + (0.05 + Math.random() * 0.12) + ')';
			g.lineWidth = 0.6 + Math.random() * 2.2;
			g.beginPath();
			for (var x = 0; x <= w; x += 16) {
				g.lineTo(x, y + Math.sin(x * 0.02 + i) * 3 + (Math.random() - 0.5) * 2);
			}
			g.stroke();
		}
		for (i = 0; i < 7; i++) {
			var kx = Math.random() * w, ky = Math.random() * h;
			var r = 3 + Math.random() * 7;
			var rg = g.createRadialGradient(kx, ky, 1, kx, ky, r);
			rg.addColorStop(0, 'rgba(' + dark + ',0.35)');
			rg.addColorStop(1, 'rgba(' + dark + ',0)');
			g.fillStyle = rg;
			g.beginPath(); g.arc(kx, ky, r, 0, 7); g.fill();
		}
		var t = new THREE.CanvasTexture(c);
		t.colorSpace = THREE.SRGBColorSpace;
		t.wrapS = t.wrapT = THREE.RepeatWrapping;
		return t;
	}

	/* ---------- bìa sản phẩm ---------- */
	function drawCover(item, img) {
		var W = 512, H = 768;
		var c = document.createElement('canvas');
		c.width = W; c.height = H;
		var g = c.getContext('2d');
		if (img) {
			var ir = img.width / img.height, cr = W / H, sw, sh, sx, sy;
			if (ir > cr) { sh = img.height; sw = sh * cr; sx = (img.width - sw) / 2; sy = 0; }
			else { sw = img.width; sh = sw / cr; sx = 0; sy = (img.height - sh) / 2; }
			g.drawImage(img, sx, sy, sw, sh, 0, 0, W, H);
		} else {
			var grad = g.createLinearGradient(0, 0, 0, H);
			grad.addColorStop(0, '#2b2118'); grad.addColorStop(1, '#171008');
			g.fillStyle = grad; g.fillRect(0, 0, W, H);
			g.fillStyle = '#e8d5a8';
			g.font = '600 44px Georgia, serif';
			g.textAlign = 'center';
			wrapText(g, item.title || '', W / 2, H / 2 - 20, W - 80, 54);
		}
		/* phủ sáng + khung vàng mảnh kiểu sách vải */
		var sheen = g.createLinearGradient(0, 0, W, H);
		sheen.addColorStop(0, 'rgba(255,250,235,0.20)');
		sheen.addColorStop(0.35, 'rgba(255,250,235,0)');
		sheen.addColorStop(1, 'rgba(30,18,6,0.28)');
		g.fillStyle = sheen; g.fillRect(0, 0, W, H);
		g.strokeStyle = 'rgba(232,213,168,0.85)'; g.lineWidth = 6;
		g.strokeRect(22, 22, W - 44, H - 44);
		if (img && item.title) {
			g.fillStyle = 'rgba(12,8,4,0.72)';
			g.fillRect(22, H - 150, W - 44, 128);
			g.fillStyle = '#f3e6c8';
			g.font = '700 40px Georgia, serif';
			g.textAlign = 'center';
			wrapText(g, item.title, W / 2, H - 96, W - 110, 46);
		}
		var t = new THREE.CanvasTexture(c);
		t.colorSpace = THREE.SRGBColorSpace;
		t.anisotropy = 4;
		return t;
	}
	function wrapText(g, text, x, y, maxW, lh) {
		var words = String(text).split(/\s+/), lines = [], line = '';
		words.forEach(function (w) {
			var t = line ? line + ' ' + w : w;
			if (g.measureText(t).width > maxW && line) { lines.push(line); line = w; }
			else line = t;
		});
		lines.push(line);
		lines = lines.slice(0, 3);
		var y0 = y - ((lines.length - 1) * lh) / 2;
		lines.forEach(function (l, i) { g.fillText(l, x, y0 + i * lh); });
	}
	function spineTexture(title) {
		var c = document.createElement('canvas');
		c.width = 128; c.height = 768;
		var g = c.getContext('2d');
		g.fillStyle = '#221810'; g.fillRect(0, 0, 128, 768);
		g.fillStyle = 'rgba(232,213,168,0.25)'; g.fillRect(10, 0, 3, 768); g.fillRect(115, 0, 3, 768);
		g.save();
		g.translate(64, 384); g.rotate(-Math.PI / 2);
		g.fillStyle = '#e8d5a8';
		g.font = '600 44px Georgia, serif';
		g.textAlign = 'center'; g.textBaseline = 'middle';
		var t = String(title || '');
		if (g.measureText(t).width > 660) {
			while (t.length > 4 && g.measureText(t + '…').width > 660) t = t.slice(0, -1);
			t += '…';
		}
		g.fillText(t, 0, 0);
		g.restore();
		var tex = new THREE.CanvasTexture(c);
		tex.colorSpace = THREE.SRGBColorSpace;
		return tex;
	}
	function pagesTexture() {
		var c = document.createElement('canvas');
		c.width = 64; c.height = 256;
		var g = c.getContext('2d');
		g.fillStyle = '#ece1c8'; g.fillRect(0, 0, 64, 256);
		g.strokeStyle = 'rgba(120,95,60,0.35)'; g.lineWidth = 1;
		for (var y = 4; y < 256; y += 4) { g.beginPath(); g.moveTo(0, y); g.lineTo(64, y); g.stroke(); }
		var t = new THREE.CanvasTexture(c);
		t.colorSpace = THREE.SRGBColorSpace;
		return t;
	}

	function loadImage(url, timeout) {
		return new Promise(function (resolve) {
			if (!url) { resolve(null); return; }
			var done = false;
			var timer = setTimeout(function () { if (!done) { done = true; resolve(null); } }, timeout || 8000);
			var img = new Image();
			img.crossOrigin = 'anonymous';
			img.onload = function () { if (!done) { done = true; clearTimeout(timer); resolve(img); } };
			img.onerror = function () { if (!done) { done = true; clearTimeout(timer); resolve(null); } };
			img.src = url;
		});
	}

	/* ---------- dựng kệ ---------- */
	var rig = new THREE.Group();
	scene.add(rig);

	var wood = woodTexture(512, 256, '#8a5a2e', '40,22,8');
	var woodMat = new THREE.MeshStandardMaterial({ map: wood, roughness: 0.5, metalness: 0.05, envMapIntensity: 0.7 });
	var darkWood = new THREE.MeshStandardMaterial({ color: 0x5d3a1a, roughness: 0.6, envMapIntensity: 0.5 });

	var shelfW = perShelf * (BOOK_W + GAP) + 0.7;
	var totalH = shelves.length * ROW_H;
	var books = [];

	function plank(w, h, d, x, y, z, mat) {
		var m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mat || woodMat);
		m.position.set(x, y, z);
		m.castShadow = true; m.receiveShadow = true;
		rig.add(m);
		return m;
	}

	shelves.forEach(function (row, ri) {
		var yBase = totalH / 2 - ri * ROW_H - ROW_H / 2;
		plank(shelfW, 0.12, 1.0, 0, yBase - BOOK_H / 2 - 0.06, 0);
		row.forEach(function (item, bi) {
			var b = new THREE.Group();
			var x0 = -((row.length * (BOOK_W + GAP) - GAP) / 2) + bi * (BOOK_W + GAP) + BOOK_W / 2;
			b.position.set(x0, yBase, 0);
			b.userData = { item: item, x0: x0, y0: yBase, lift: 0, liftTarget: 0, phase: Math.random() * 6.28 };

			var coverTex = drawCover(item, item._img || null);
			var coverMat = new THREE.MeshStandardMaterial({ map: coverTex, roughness: 0.55, envMapIntensity: 0.5, emissive: 0x000000 });
			var spineMat = new THREE.MeshStandardMaterial({ map: spineTexture(item.title), roughness: 0.6 });
			var pagesMat = new THREE.MeshStandardMaterial({ map: pagesTexture(), roughness: 0.85 });
			var backMat = new THREE.MeshStandardMaterial({ color: 0x2a2016, roughness: 0.7 });

			/* BoxGeometry: +x pages, -x spine, +z cover */
			var mesh = new THREE.Mesh(
				new THREE.BoxGeometry(BOOK_W, BOOK_H, BOOK_D),
				[pagesMat, spineMat, pagesMat, pagesMat, coverMat, backMat]
			);
			mesh.castShadow = true; mesh.receiveShadow = true;
			mesh.userData.book = b;
			b.add(mesh);
			b.userData.mesh = mesh;
			b.userData.mats = [coverMat, spineMat, pagesMat, backMat];
			rig.add(b);
			books.push(b);
		});
	});

	/* khung tủ: 2 hồi + nóc */
	var sideH = totalH + 0.5;
	plank(0.14, sideH, 1.0, -(shelfW / 2 + 0.07), 0, 0, darkWood);
	plank(0.14, sideH, 1.0, shelfW / 2 + 0.07, 0, 0, darkWood);
	plank(shelfW + 0.28, 0.16, 1.0, 0, totalH / 2 + 0.2, 0, darkWood);

	/* sàn hứng bóng */
	var ground = new THREE.Mesh(
		new THREE.PlaneGeometry(40, 40),
		new THREE.MeshStandardMaterial({ color: 0xe4d5b8, roughness: 0.95 })
	);
	ground.rotation.x = -Math.PI / 2;
	ground.position.y = -totalH / 2 - BOOK_H / 2 - 0.12;
	ground.receiveShadow = true;
	scene.add(ground);

	/* ---------- tương tác ---------- */
	var ray = new THREE.Raycaster();
	var ptr = new THREE.Vector2(-2, -2);
	var hovered = null;
	var dragging = false, dragMoved = 0, lastX = 0;
	var rotTarget = 0, downX = 0, downY = 0;
	var intro = reduced ? 1 : 0;

	function setTip(b) {
		if (!b || selected) { tip.setAttribute('hidden', ''); return; }
		var it = b.userData.item;
		var html = '';
		if (it.cat) html += '<span class="tip-cat">' + escapeHtml(it.cat) + '</span>';
		html += '<span class="tip-title">' + escapeHtml(it.title) + '</span>';
		if (it.price) html += '<span class="tip-price">' + escapeHtml(it.price) + '</span>';
		html += '<span class="tip-go">Xem chi tiết →</span>';
		tip.innerHTML = html;
		tip.removeAttribute('hidden');
	}
	function escapeHtml(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}
	/* ---------- xem nhanh: bấm 1 cái mở panel, bấm nữa vào trang ---------- */
	var selected = null;
	function paint(b) {
		var glow = b === selected ? 0x3a2408 : (b === hovered ? 0x2a1c08 : 0x000000);
		b.userData.mesh.material[4].emissive.setHex(glow);
	}
	function selectBook(b) {
		if (selected && selected !== b) {
			selected.userData.liftTarget = 0;
			paint(selected);
		}
		selected = b;
		b.userData.liftTarget = 0.15;
		paint(b);
		setTip(null);
		fillPanel(b.userData.item);
		if (panel) panel.removeAttribute('hidden');
	}
	function deselect() {
		if (!selected) return;
		var b = selected;
		selected = null;
		b.userData.liftTarget = (b === hovered) ? 0.15 : 0;
		paint(b);
		if (panel) panel.setAttribute('hidden', '');
	}
	function fillPanel(it) {
		if (!panel) return;
		var img = document.getElementById('mozlex-shelf-pimg');
		var cat = document.getElementById('mozlex-shelf-pcat');
		var title = document.getElementById('mozlex-shelf-ptitle');
		var price = document.getElementById('mozlex-shelf-pprice');
		var specs = document.getElementById('mozlex-shelf-pspecs');
		var feats = document.getElementById('mozlex-shelf-pfeatures');
		var cta = document.getElementById('mozlex-shelf-pcta');
		if (img) {
			if (it.img) { img.src = it.img; img.alt = it.title || ''; img.style.display = ''; }
			else { img.removeAttribute('src'); img.style.display = 'none'; }
		}
		if (cat) { cat.textContent = it.cat || ''; cat.style.display = it.cat ? '' : 'none'; }
		if (title) title.textContent = it.title || '';
		if (price) { price.textContent = it.price || ''; price.style.display = it.price ? '' : 'none'; }
		if (specs) {
			specs.innerHTML = '';
			(it.specs || []).slice(0, 5).forEach(function (row) {
				var label = Array.isArray(row) ? row[0] : '', val = Array.isArray(row) ? row[1] : row;
				if (!label && !val) return;
				var div = document.createElement('div');
				var dt = document.createElement('dt'); dt.textContent = label;
				var dd = document.createElement('dd'); dd.textContent = val;
				div.appendChild(dt); div.appendChild(dd);
				specs.appendChild(div);
			});
			specs.style.display = specs.children.length ? '' : 'none';
		}
		if (feats) {
			var f = (it.features || []).join(' · ');
			feats.textContent = f; feats.style.display = f ? '' : 'none';
		}
		if (cta && it.url) cta.href = it.url;
	}
	var closeBtn = document.getElementById('mozlex-shelf-close');
	if (closeBtn) closeBtn.addEventListener('click', function () { deselect(); });
	document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') deselect(); });
	function pick(ev) {
		var r = canvas.getBoundingClientRect();
		ptr.x = ((ev.clientX - r.left) / r.width) * 2 - 1;
		ptr.y = -((ev.clientY - r.top) / r.height) * 2 + 1;
		ray.setFromCamera(ptr, camera);
		var hits = ray.intersectObjects(books.map(function (b) { return b.userData.mesh; }), false);
		return hits.length ? hits[0].object.userData.book : null;
	}
	function placeTip(b) {
		var v = new THREE.Vector3();
		b.updateWorldMatrix(true, false);
		v.setFromMatrixPosition(b.matrixWorld);
		v.y += BOOK_H / 2 + 0.25;
		v.project(camera);
		var r = canvas.getBoundingClientRect();
		tip.style.left = ((v.x * 0.5 + 0.5) * r.width) + 'px';
		tip.style.top = ((-v.y * 0.5 + 0.5) * r.height) + 'px';
	}

	canvas.addEventListener('pointermove', function (ev) {
		if (dragging) {
			var dx = ev.clientX - lastX;
			lastX = ev.clientX;
			dragMoved += Math.abs(dx);
			rotTarget = Math.max(-0.34, Math.min(0.34, rotTarget + dx * 0.0035));
			return;
		}
		var b = pick(ev);
		if (b !== hovered) {
			if (hovered && hovered !== selected) {
				hovered.userData.liftTarget = 0;
				paint(hovered);
			}
			hovered = b;
			if (hovered) {
				hovered.userData.liftTarget = 0.15;
				paint(hovered);
				setTip(hovered);
				placeTip(hovered);
			} else {
				setTip(null);
			}
			canvas.classList.toggle('is-hover', !!hovered);
		} else if (hovered) {
			placeTip(hovered);
		}
	});
	canvas.addEventListener('pointerdown', function (ev) {
		dragging = true; dragMoved = 0; lastX = ev.clientX; downX = ev.clientX; downY = ev.clientY;
		canvas.classList.add('is-drag');
		try { canvas.setPointerCapture(ev.pointerId); } catch (e) {}
	});
	canvas.addEventListener('pointerup', function (ev) {
		canvas.classList.remove('is-drag');
		var wasDrag = dragMoved > 6 || Math.hypot(ev.clientX - downX, ev.clientY - downY) > 6;
		dragging = false;
		if (!wasDrag) {
			var b = pick(ev);
			if (b) {
				if (b.userData && b.userData.item && b.userData.item.url) {
					window.location.href = b.userData.item.url;
				} else {
					selectBook(b);
				}
			} else {
				deselect();
			}
		}
	});
	canvas.addEventListener('pointerleave', function () {
		dragging = false;
		canvas.classList.remove('is-drag');
		if (hovered && hovered !== selected) {
			hovered.userData.liftTarget = 0;
			paint(hovered);
			hovered = null;
		} else if (hovered) {
			hovered = null;
		}
		setTip(null);
		canvas.classList.remove('is-hover');
	});

	/* ---------- camera vừa kệ ---------- */
	function fit() {
		var w = mount.clientWidth || 1, h = mount.clientHeight || 1;
		renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
		renderer.setSize(w, h, false);
		camera.aspect = w / h;
		var vFit = (totalH / 2 + 0.45) / Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
		var hFit = (shelfW / 2 + 0.4) / (Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)) * camera.aspect);
		var dist = Math.max(vFit, hFit);
		camera.position.set(0, 0.5, dist);
		camera.lookAt(0, 0, 0);
		camera.updateProjectionMatrix();
	}

	/* ---------- vòng lặp ---------- */
	var running = true, visible = true;
	function frame() {
		if (!running || !visible) return;
		requestAnimationFrame(frame);
		if (!reduced && intro < 1) intro = Math.min(1, intro + 0.05);
		var e = reduced ? 1 : (1 - Math.pow(1 - intro, 3));
		books.forEach(function (b) {
			var u = b.userData;
			u.lift += (u.liftTarget - u.lift) * 0.18;
			var drop = reduced ? 0 : (1 - e) * -0.8;
			b.position.y = u.y0 + u.lift + drop;
			b.rotation.x = u.lift ? -0.07 : 0;
		});
		rig.rotation.y += (rotTarget - rig.rotation.y) * 0.12;
		if (hovered) placeTip(hovered);
		renderer.render(scene, camera);
	}
	function setVisible(v) {
		if (v === visible) return;
		visible = v;
		if (visible) frame();
	}
	if ('IntersectionObserver' in window) {
		new IntersectionObserver(function (en) { setVisible(!!(en[0] && en[0].isIntersecting)); }, { rootMargin: '60px' }).observe(mount);
	}
	document.addEventListener('visibilitychange', function () { setVisible(!document.hidden); });
	if ('ResizeObserver' in window) {
		new ResizeObserver(function () { fit(); }).observe(mount);
	} else {
		window.addEventListener('resize', fit);
	}

	/* ---------- khởi động: preload ảnh rồi dựng ---------- */
	Promise.all(items.map(function (it) { return loadImage(it.img, 8000); })).then(function (imgs) {
		imgs.forEach(function (img, i) {
			if (!img) return;
			var b = books[i];
			var tex = drawCover(items[i], img);
			var old = b.userData.mesh.material[4].map;
			b.userData.mesh.material[4].map = tex;
			b.userData.mesh.material[4].needsUpdate = true;
			if (old) old.dispose();
		});
		mount.removeAttribute('hidden');
		mount.setAttribute('data-ready', '1');
		if (grid) grid.classList.add('is-hidden');
		fit();
		frame();
	}).catch(function () { fail(); });
	} /* end boot() */
})();
