/* Админка: запросы к actions.php, плашки, модалки, транслит слага.
   Никаких браузерных alert/confirm — всё своё, чтобы выглядело одинаково везде. */

(function () {
	"use strict";

	var CSRF = document.body.dataset.csrf || "";

	// ===== запрос к actions.php: form-urlencoded, csrf добавляется сам =====
	function api(data, done) {
		var body = new URLSearchParams(data);
		body.set("csrf", CSRF);
		fetch("/admin/php/actions.php", { method: "POST", body: body })
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (!json.ok && json.error) { toast(json.error, "error"); }
				done(json);
			})
			.catch(function () {
				// сеть упала или сервер ответил не-JSON — говорим по-человечески
				toast("Не получилось связаться с сервером. Проверьте интернет и попробуйте ещё раз.", "error");
				done({ ok: false });
			});
	}

	// ===== плашки =====
	function toast(text, type) {
		var box = document.getElementById("toasts");
		var el = document.createElement("div");
		el.className = "toast" + (type ? " toast--" + type : "");
		el.textContent = text;
		box.appendChild(el);
		setTimeout(function () { el.remove(); }, type === "error" ? 6000 : 2500);
	}

	// ===== выпадающее меню в шапке =====
	// клик по кнопке открывает своё меню и закрывает соседние; клик мимо — закрывает всё
	document.addEventListener("click", function (e) {
		var btn = e.target.closest(".js-drop");
		document.querySelectorAll(".topbar__drop.is-open").forEach(function (d) {
			if (!btn || d !== btn.parentNode) { d.classList.remove("is-open"); }
		});
		if (btn) { btn.parentNode.classList.toggle("is-open"); }
	});

	document.addEventListener("keydown", function (e) {
		if (e.key !== "Escape") { return; }
		document.querySelectorAll(".topbar__drop.is-open").forEach(function (d) { d.classList.remove("is-open"); });
	});

	// ===== модалки =====
	function modalOpen(id) { document.getElementById(id).classList.add("is-open"); }
	function modalClose(el) { el.closest(".modal").classList.remove("is-open"); }

	document.addEventListener("click", function (e) {
		var closer = e.target.closest("[data-modal-close]");
		if (closer) { modalClose(closer); }
	});

	// подтверждение: заголовок, html-текст, подпись кнопки, колбэк по «да»
	var confirmYes = null;
	function confirmShow(title, html, button, onYes) {
		document.getElementById("confirm-title").textContent = title;
		document.getElementById("confirm-text").innerHTML = html;
		document.getElementById("confirm-yes").textContent = button;
		confirmYes = onYes;
		modalOpen("confirm");
	}

	var yesBtn = document.getElementById("confirm-yes");
	if (yesBtn) {
		yesBtn.addEventListener("click", function () {
			modalClose(yesBtn);
			if (confirmYes) { confirmYes(); confirmYes = null; }
		});
	}

	// ===== вход/выход =====
	var loginForm = document.getElementById("login-form");
	if (loginForm) {
		loginForm.addEventListener("submit", function (e) {
			e.preventDefault();
			api({
				action: "login",
				user: loginForm.user.value,
				pass: loginForm.pass.value
			}, function (json) {
				if (json.ok) { location.href = "/admin/"; }
			});
		});
	}

	var logout = document.getElementById("logout");
	if (logout) {
		logout.addEventListener("click", function () {
			api({ action: "logout" }, function () { location.href = "/admin/"; });
		});
	}

	// ===== список услуг =====

	// галочки «видима»/«в футере» — сохраняются сразу
	document.querySelectorAll(".js-toggle").forEach(function (cb) {
		cb.addEventListener("change", function () {
			var tr = cb.closest("tr");
			api({
				action: "svc_toggle",
				slug: tr.dataset.slug,
				field: cb.dataset.field,
				value: cb.checked ? 1 : ""
			}, function (json) {
				if (!json.ok) { cb.checked = !cb.checked; return; } // откат при ошибке
				if (cb.dataset.field === "visible") { tr.classList.toggle("is-hidden", !cb.checked); }
				toast("Сохранено", "ok");
			});
		});
		// стартовая подсветка скрытых
		if (cb.dataset.field === "visible" && !cb.checked) {
			cb.closest("tr").classList.add("is-hidden");
		}
	});

	// стрелки ↑↓ — двигаем строку сразу, без перезагрузки
	document.querySelectorAll(".js-move").forEach(function (btn) {
		btn.addEventListener("click", function () {
			var tr = btn.closest("tr");
			api({ action: "svc_move", slug: tr.dataset.slug, dir: btn.dataset.dir }, function (json) {
				if (!json.ok || json.noop) { return; }
				var other = btn.dataset.dir === "up" ? tr.previousElementSibling : tr.nextElementSibling;
				if (other) {
					if (btn.dataset.dir === "up") { tr.parentNode.insertBefore(tr, other); }
					else { tr.parentNode.insertBefore(other, tr); }
				}
				toast("Порядок сохранён", "ok");
			});
		});
	});

	// удаление: сначала спрашиваем сервер, что заденет, потом подтверждаем словами
	document.querySelectorAll(".js-delete").forEach(function (btn) {
		btn.addEventListener("click", function () {
			var tr = btn.closest("tr");
			var slug = tr.dataset.slug;
			api({ action: "svc_delete", slug: slug, dry: 1 }, function (json) {
				if (!json.ok) { return; }
				var html = "Услуга <b>«" + esc(json.name) + "»</b> переместится в корзину:" +
					"<ul>" +
					"<li>страница /" + esc(slug) + "/ перестанет открываться;</li>" +
					"<li>пункт исчезнет из меню сайта;</li>" +
					(json.slider ? "<li>карточка в слайдере главной останется, но перестанет вести на эту услугу;</li>" : "") +
					(json.quiz ? "<li>из формы-опроса уберётся привязка к этой услуге;</li>" : "") +
					"</ul>" +
					"Услугу можно будет восстановить из корзины.";
				confirmShow("Убрать услугу в корзину?", html, "Убрать в корзину", function () {
					api({ action: "svc_delete", slug: slug }, function (json2) {
						if (!json2.ok) { return; }
						tr.remove();
						toast("Услуга перемещена в корзину", "ok");
					});
				});
			});
		});
	});

	// восстановление из корзины
	document.querySelectorAll(".js-restore").forEach(function (btn) {
		btn.addEventListener("click", function () {
			var slug = btn.dataset.slug;
			var html = "Услуга <b>«" + esc(btn.dataset.name) + "»</b> вернётся на сайт: " +
				"страница снова начнёт открываться, пункт появится в меню.<br><br>" +
				"Ссылки из слайдера главной и формы-опроса, если они были, " +
				"автоматически не вернутся — их нужно будет проставить заново.";
			confirmShow("Восстановить услугу?", html, "Восстановить", function () {
				api({ action: "svc_restore", slug: slug }, function (json) {
					if (!json.ok) { return; }
					btn.closest("tr").remove();
					toast("Услуга восстановлена", "ok");
				});
			});
		});
	});

	// ===== добавление услуги =====
	var addOpen = document.getElementById("svc-add-open");
	var addForm = document.getElementById("svc-add-form");
	if (addOpen && addForm) {
		addOpen.addEventListener("click", function () { modalOpen("svc-add"); addForm.name.focus(); });

		// слаг заполняется из названия, пока его не начали править руками
		var slugTouched = false;
		addForm.slug.addEventListener("input", function () { slugTouched = true; });
		addForm.name.addEventListener("input", function () {
			if (!slugTouched) { addForm.slug.value = translit(addForm.name.value); }
		});

		addForm.addEventListener("submit", function (e) {
			e.preventDefault();
			// свои подсветки полей вместо браузерных пузырей
			var bad = false;
			[addForm.name, addForm.slug].forEach(function (input) {
				var empty = input.value.trim() === "";
				input.classList.toggle("is-error", empty);
				if (empty) { bad = true; }
			});
			if (bad) { toast("Заполните название и адрес страницы", "error"); return; }

			api({
				action: "svc_add",
				name: addForm.name.value.trim(),
				slug: addForm.slug.value.trim(),
				group: addForm.group.value
			}, function (json) {
				if (!json.ok) { return; }
				// перезагрузка: строка встанет в свою группу на своё место
				location.href = "/admin/?added=" + encodeURIComponent(json.slug);
			});
		});
	}

	// после добавления показываем плашку (страница перезагружалась)
	var added = new URLSearchParams(location.search).get("added");
	if (added) {
		toast("Услуга добавлена и уже видна в меню сайта", "ok");
		history.replaceState(null, "", "/admin/");
	}

	// ===== транслит для слага: таблица как в существующих адресах =====
	function translit(s) {
		var map = {
			"а": "a", "б": "b", "в": "v", "г": "g", "д": "d", "е": "e", "ё": "e",
			"ж": "zh", "з": "z", "и": "i", "й": "y", "к": "k", "л": "l", "м": "m",
			"н": "n", "о": "o", "п": "p", "р": "r", "с": "s", "т": "t", "у": "u",
			"ф": "f", "х": "h", "ц": "ts", "ч": "ch", "ш": "sh", "щ": "shch",
			"ъ": "", "ы": "y", "ь": "", "э": "e", "ю": "yu", "я": "ya"
		};
		return s.toLowerCase().split("").map(function (ch) {
			if (map.hasOwnProperty(ch)) { return map[ch]; }
			if (/[a-z0-9]/.test(ch)) { return ch; }
			return "-";
		}).join("").replace(/-+/g, "-").replace(/^-|-$/g, "");
	}

	// экранирование текста, который вставляем в html модалок
	function esc(s) {
		var d = document.createElement("div");
		d.textContent = s;
		return d.innerHTML;
	}
})();

/* ===== редактор страницы услуги ===== */
(function () {
	"use strict";

	var CSRF = document.body.dataset.csrf || "";
	var slugInput = document.getElementById("edit-slug");
	if (!slugInput) { return; } // не на странице редактора
	var SLUG = slugInput.value;

	function toast(text, type) {
		var box = document.getElementById("toasts");
		var el = document.createElement("div");
		el.className = "toast" + (type ? " toast--" + type : "");
		el.textContent = text;
		box.appendChild(el);
		setTimeout(function () { el.remove(); }, type === "error" ? 6000 : 2500);
	}

	// ===== вкладки =====
	var tabBtns = document.querySelectorAll(".tabs__btn");
	tabBtns.forEach(function (btn) {
		btn.addEventListener("click", function () {
			tabBtns.forEach(function (b) { b.classList.remove("is-active"); });
			btn.classList.add("is-active");
			document.querySelectorAll(".tab").forEach(function (t) {
				t.classList.toggle("is-active", t.dataset.tab === btn.dataset.tab);
			});
		});
	});

	// ===== несохранённые правки: точка на вкладке + предупреждение при уходе =====
	var dirty = {};

	function markDirty(tab) {
		dirty[tab] = true;
		tabBtns.forEach(function (b) {
			if (b.dataset.tab === tab) { b.classList.add("is-dirty"); }
		});
	}

	function markClean(tab) {
		delete dirty[tab];
		tabBtns.forEach(function (b) {
			if (b.dataset.tab === tab) { b.classList.remove("is-dirty"); }
		});
	}

	document.querySelectorAll(".js-tab-form").forEach(function (form) {
		form.addEventListener("input", function () { markDirty(form.dataset.tab); });
		form.addEventListener("change", function () { markDirty(form.dataset.tab); });
	});

	// закрытие вкладки браузера с правками — тут без штатного диалога не обойтись
	window.addEventListener("beforeunload", function (e) {
		if (Object.keys(dirty).length) { e.preventDefault(); }
	});

	// ===== сохранение вкладок =====
	document.querySelectorAll(".js-tab-form").forEach(function (form) {
		form.addEventListener("submit", function (e) {
			e.preventDefault();
			var body = new FormData(form);
			body.set("action", form.dataset.action);
			body.set("slug", SLUG);
			body.set("csrf", CSRF);

			// цены: собираем структуру из вёрстки в одно JSON-поле.
			// по id формы, не по экшену — форма общая для услуги и прайса главной
			if (form.id === "prices-form") {
				body.set("prices_json", JSON.stringify(collectPrices()));
			}
			// слайдер главной: карточки собираются своим модулем
			if (form.id === "slider-form") {
				body.set("slider_json", JSON.stringify(window.collectSlider()));
			}
			// примеры работ: карточки собираются своим модулем
			if (form.id === "works-form") {
				body.set("works_json", JSON.stringify(window.collectWorks()));
			}

			fetch("/admin/php/actions.php", { method: "POST", body: body })
				.then(function (r) { return r.json(); })
				.then(function (json) {
					if (!json.ok) { toast(json.error || "Не получилось сохранить", "error"); return; }
					// тексты: показываем то, что реально легло на диск после чистки тегов
					if (json.intro_html !== undefined) {
						form.intro_html.value = json.intro_html;
						form.intro_html.dispatchEvent(new CustomEvent("editor:refresh"));
					}
					if (json.seo_html !== undefined) {
						form.seo_html.value = json.seo_html;
						form.seo_html.dispatchEvent(new CustomEvent("editor:refresh"));
					}
					// доступ: пароли в полях после сохранения не держим
					if (form.id === "settings-auth-form") {
						form.querySelectorAll("input[type=\"password\"]").forEach(function (i) { i.value = ""; });
					}
					markClean(form.dataset.tab);
					toast("Сохранено", "ok");
				})
				.catch(function () {
					toast("Не получилось связаться с сервером. Проверьте интернет и попробуйте ещё раз.", "error");
				});
		});
	});

	// ===== цены: подразделы и строки =====
	var groupsBox = document.getElementById("prices-groups");

	function collectPrices() {
		var groups = [];
		groupsBox.querySelectorAll(".js-pgroup").forEach(function (g) {
			var items = [];
			g.querySelectorAll(".js-prow").forEach(function (r) {
				items.push({
					name: r.querySelector(".prow__name").value,
					price: r.querySelector(".prow__price").value
				});
			});
			groups.push({
				visible: g.querySelector(".pgroup__visible").checked,
				title: g.querySelector(".pgroup__title").value,
				items: items
			});
		});
		return {
			visible: document.getElementById("prices-visible").checked,
			groups: groups
		};
	}

	function clone(tplId) {
		return document.getElementById(tplId).content.firstElementChild.cloneNode(true);
	}

	// делегирование: кнопки живут и в строках, добавленных после загрузки
	var pricesForm = document.getElementById("prices-form");
	if (pricesForm) {
		pricesForm.addEventListener("click", function (e) {
			var t = e.target;

			if (t.classList.contains("js-pr-add")) {
				t.closest(".js-pgroup").querySelector(".pgroup__items").appendChild(clone("tpl-prow"));
				markDirty("prices");
			}
			if (t.classList.contains("js-pr-del")) {
				t.closest(".js-prow").remove();
				markDirty("prices");
			}
			if (t.classList.contains("js-pr-move")) {
				moveEl(t.closest(".js-prow"), t.dataset.dir);
				markDirty("prices");
			}
			if (t.classList.contains("js-pg-del")) {
				t.closest(".js-pgroup").remove();
				markDirty("prices");
			}
			if (t.classList.contains("js-pg-move")) {
				moveEl(t.closest(".js-pgroup"), t.dataset.dir);
				markDirty("prices");
			}
		});

		// потушенный подраздел приглушаем сразу, не дожидаясь сохранения
		pricesForm.addEventListener("change", function (e) {
			if (e.target.classList.contains("pgroup__visible")) {
				e.target.closest(".js-pgroup").classList.toggle("is-off", !e.target.checked);
			}
		});
		pricesForm.querySelectorAll(".pgroup__visible").forEach(function (cb) {
			cb.closest(".js-pgroup").classList.toggle("is-off", !cb.checked);
		});

		document.getElementById("pg-add").addEventListener("click", function () {
			groupsBox.appendChild(clone("tpl-pgroup"));
			markDirty("prices");
		});
	}

	// ===== FAQ: карточки вопросов =====
	var faqForm = document.getElementById("faq-form");
	if (faqForm) {
		faqForm.addEventListener("click", function (e) {
			var t = e.target;
			if (t.classList.contains("js-fq-del")) {
				t.closest(".js-fitem").remove();
				markDirty("faq");
			}
			if (t.classList.contains("js-fq-move")) {
				moveEl(t.closest(".js-fitem"), t.dataset.dir);
				markDirty("faq");
			}
		});

		document.getElementById("fq-add").addEventListener("click", function () {
			document.getElementById("faq-items").appendChild(clone("tpl-fitem"));
			markDirty("faq");
		});

		// перед отправкой перекладываем поля карточек в параллельные массивы q[]/a[]
		faqForm.addEventListener("submit", function () {
			faqForm.querySelectorAll("input[name='q[]'], textarea[name='a[]']").forEach(function (el) { el.remove(); });
			faqForm.querySelectorAll(".js-fitem").forEach(function (f) {
				addHidden(faqForm, "q[]", f.querySelector(".fitem__q").value);
				addHidden(faqForm, "a[]", f.querySelector(".fitem__a").value);
			});
		}, true); // capture: раньше общего обработчика сохранения
	}

	function addHidden(form, name, value) {
		var el = document.createElement("input");
		el.type = "hidden";
		el.name = name;
		el.value = value;
		form.appendChild(el);
	}

	// сдвиг элемента вверх/вниз среди соседей того же класса
	function moveEl(el, dir) {
		if (dir === "up" && el.previousElementSibling) {
			el.parentNode.insertBefore(el, el.previousElementSibling);
		}
		if (dir === "down" && el.nextElementSibling) {
			el.parentNode.insertBefore(el.nextElementSibling, el);
		}
	}
})();

/* ===== WYSIWYG: свой минимум вокруг textarea.js-editor =====
   Два режима: визуальный (contenteditable) и HTML (сама textarea).
   Источник правды при отправке — textarea; визуальный режим синхронизирует её. */
(function () {
	"use strict";

	var areas = document.querySelectorAll("textarea.js-editor");
	if (!areas.length) { return; }

	// Enter в contenteditable должен давать <p>, а не <div>
	try { document.execCommand("defaultParagraphSeparator", false, "p"); } catch (e) {}

	// белый список — тот же, что на сервере; чистим и при загрузке, и при вставке
	var ALLOWED = { P: 1, H2: 1, H3: 1, STRONG: 1, EM: 1, B: 1, I: 1, UL: 1, OL: 1, LI: 1, BR: 1, A: 1 };

	function cleanHtml(html) {
		var doc = new DOMParser().parseFromString("<div>" + html + "</div>", "text/html");
		var out = document.createElement("div");
		cleanInto(doc.body.firstChild, out);
		return out.innerHTML;
	}

	// рекурсивный перенос узлов: разрешённые теги пересоздаём голыми,
	// запрещённые контейнеры разворачиваем, script/style выбрасываем целиком
	function cleanInto(src, dst) {
		for (var node = src.firstChild; node; node = node.nextSibling) {
			if (node.nodeType === 3) {
				dst.appendChild(document.createTextNode(node.nodeValue));
				continue;
			}
			if (node.nodeType !== 1) { continue; }
			var tag = node.tagName;
			if (tag === "SCRIPT" || tag === "STYLE") { continue; }

			if (ALLOWED[tag]) {
				var el = document.createElement(tag);
				// у ссылок оставляем только href с безопасной схемой
				if (tag === "A") {
					var href = (node.getAttribute("href") || "").trim();
					if (/^(https?:\/\/|mailto:|tel:|\/)/i.test(href)) { el.setAttribute("href", href); }
				}
				cleanInto(node, el);
				dst.appendChild(el);
			} else {
				cleanInto(node, dst); // div/span и прочее — разворачиваем содержимое
			}
		}
	}

	// переносы между блоками — чтобы HTML-режим читался глазами
	function prettyHtml(html) {
		return html
			.replace(/<\/(p|h2|h3|ul|ol)>\s*/gi, "</$1>\n\n")
			.replace(/<li>/gi, "\t<li>")
			.replace(/\s*<\/li>/gi, "</li>\n")
			.replace(/<(ul|ol)>\s*/gi, "<$1>\n")
			.trim();
	}

	areas.forEach(function (ta) { initEditor(ta); });

	function initEditor(ta) {
		// каркас: переключатель режимов + панель + contenteditable
		var wrap = document.createElement("div");
		wrap.className = "editor";
		wrap.innerHTML =
			'<div class="editor__top">' +
				'<div class="editor__bar">' +
					'<button type="button" data-block="p" title="Обычный абзац">Абзац</button>' +
					'<button type="button" data-block="h2" title="Заголовок раздела">H2</button>' +
					'<button type="button" data-block="h3" title="Подзаголовок">H3</button>' +
					'<span class="editor__sep"></span>' +
					'<button type="button" data-cmd="bold" title="Жирный"><b>Ж</b></button>' +
					'<button type="button" data-cmd="italic" title="Курсив"><i>К</i></button>' +
					'<span class="editor__sep"></span>' +
					'<button type="button" data-cmd="insertUnorderedList" title="Маркированный список">•&nbsp;список</button>' +
					'<button type="button" data-cmd="insertOrderedList" title="Нумерованный список">1.&nbsp;список</button>' +
					'<span class="editor__sep"></span>' +
					'<button type="button" data-link="add" title="Превратить выделенное в ссылку">Ссылка</button>' +
					'<button type="button" data-link="del" title="Убрать ссылку">Убрать</button>' +
				'</div>' +
				'<div class="editor__mode">' +
					'<button type="button" class="is-active" data-mode="visual">Визуально</button>' +
					'<button type="button" data-mode="html">HTML</button>' +
				'</div>' +
			'</div>' +
			'<div class="editor__area" contenteditable="true"></div>' +
			'<div class="editor__linkbox" hidden>' +
				'<input type="text" placeholder="Адрес ссылки: https://… или /stranitsa/">' +
				'<button type="button" data-linkbox="ok" class="btn btn--small btn--primary">Ок</button>' +
				'<button type="button" data-linkbox="cancel" class="btn btn--small">Отмена</button>' +
			'</div>';

		ta.parentNode.insertBefore(wrap, ta);
		var area = wrap.querySelector(".editor__area");
		var linkbox = wrap.querySelector(".editor__linkbox");
		var linkInput = linkbox.querySelector("input");
		var visual = true;
		var savedRange = null;

		area.innerHTML = cleanHtml(ta.value);

		// правки в визуальном режиме сразу уходят в textarea — форма всегда актуальна
		area.addEventListener("input", function () { ta.value = prettyHtml(cleanArea()); });

		function cleanArea() { return cleanHtml(area.innerHTML); }

		// вставка из буфера: чистим тот же белый список — мусор из Word не доезжает
		area.addEventListener("paste", function (e) {
			e.preventDefault();
			var html = e.clipboardData.getData("text/html");
			var text = e.clipboardData.getData("text/plain");
			var insert = html ? cleanHtml(html) : text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/\n/g, "<br>");
			document.execCommand("insertHTML", false, insert);
		});

		// панель форматирования
		wrap.querySelector(".editor__bar").addEventListener("click", function (e) {
			var btn = e.target.closest("button");
			if (!btn || !visual) { return; }
			area.focus();

			if (btn.dataset.cmd) { document.execCommand(btn.dataset.cmd, false, null); }
			if (btn.dataset.block) { document.execCommand("formatBlock", false, btn.dataset.block); }

			if (btn.dataset.link === "add") {
				// запоминаем выделение: пока вводится адрес, оно теряется
				var sel = window.getSelection();
				if (!sel.rangeCount || sel.isCollapsed) { return; }
				savedRange = sel.getRangeAt(0).cloneRange();
				linkbox.hidden = false;
				linkInput.value = "";
				linkInput.focus();
				return;
			}
			if (btn.dataset.link === "del") { document.execCommand("unlink", false, null); }

			ta.value = prettyHtml(cleanArea());
		});

		// мини-диалог адреса ссылки — вместо браузерного prompt
		linkbox.addEventListener("click", function (e) {
			var btn = e.target.closest("button");
			if (!btn) { return; }
			if (btn.dataset.linkbox === "ok" && savedRange) {
				var url = linkInput.value.trim();
				if (/^(https?:\/\/|mailto:|tel:|\/)/i.test(url)) {
					var sel = window.getSelection();
					sel.removeAllRanges();
					sel.addRange(savedRange);
					document.execCommand("createLink", false, url);
					ta.value = prettyHtml(cleanArea());
				}
			}
			linkbox.hidden = true;
			savedRange = null;
		});
		linkInput.addEventListener("keydown", function (e) {
			if (e.key === "Enter") {
				e.preventDefault();
				linkbox.querySelector("[data-linkbox='ok']").click();
			}
		});

		// переключение режимов: HTML-режим показывает саму textarea
		wrap.querySelector(".editor__mode").addEventListener("click", function (e) {
			var btn = e.target.closest("button");
			if (!btn) { return; }
			var toVisual = btn.dataset.mode === "visual";
			if (toVisual === visual) { return; }
			visual = toVisual;

			if (visual) {
				area.innerHTML = cleanHtml(ta.value); // правки руками — через ту же чистку
			} else {
				ta.value = prettyHtml(cleanArea());
			}
			wrap.classList.toggle("is-html", !visual);
			wrap.querySelectorAll(".editor__mode button").forEach(function (b) {
				b.classList.toggle("is-active", b.dataset.mode === (visual ? "visual" : "html"));
			});
		});

		// после сохранения сервер возвращает вычищенный текст в textarea —
		// подтягиваем его и в визуальную область
		ta.addEventListener("editor:refresh", function () {
			if (visual) { area.innerHTML = cleanHtml(ta.value); }
		});
	}
})();

/* ===== слайдер главной: карточки с фото ===== */
(function () {
	"use strict";

	var form = document.getElementById("slider-form");
	if (!form) { return; }

	var CSRF = document.body.dataset.csrf || "";
	var cardsBox = document.getElementById("slider-cards");

	function toast(text, type) {
		var box = document.getElementById("toasts");
		var el = document.createElement("div");
		el.className = "toast" + (type ? " toast--" + type : "");
		el.textContent = text;
		box.appendChild(el);
		setTimeout(function () { el.remove(); }, type === "error" ? 6000 : 2500);
	}

	// сборка формы в структуру для сохранения; дергается общим обработчиком
	window.collectSlider = function () {
		var cards = [];
		cardsBox.querySelectorAll(".js-scard").forEach(function (c) {
			cards.push({
				image: c.querySelector(".scard__image").value,
				title: c.querySelector(".scard__title").value,
				subtitle: c.querySelector(".scard__subtitle").value,
				items: c.querySelector(".scard__items").value.split("\n"),
				from: c.querySelector(".scard__from").value,
				slug: c.querySelector(".scard__slug").value
			});
		});
		return {
			visible: document.getElementById("slider-visible").checked,
			title: form.title.value,
			cards: cards
		};
	};

	function markDirtySlider() {
		// точки-вкладки на этой странице нет, но beforeunload из модуля редактора
		// живёт от form input/change — они здесь и так всплывают
	}

	form.addEventListener("click", function (e) {
		var t = e.target;

		if (t.classList.contains("js-sc-del")) {
			t.closest(".js-scard").remove();
			form.dispatchEvent(new Event("change", { bubbles: false }));
		}
		if (t.classList.contains("js-sc-move")) {
			var el = t.closest(".js-scard");
			if (t.dataset.dir === "up" && el.previousElementSibling) {
				el.parentNode.insertBefore(el, el.previousElementSibling);
			}
			if (t.dataset.dir === "down" && el.nextElementSibling) {
				el.parentNode.insertBefore(el.nextElementSibling, el);
			}
			form.dispatchEvent(new Event("change", { bubbles: false }));
		}
		// «Заменить фото» открывает скрытый input file своей карточки
		if (t.classList.contains("js-sc-photo")) {
			t.closest(".scard__photo").querySelector(".scard__file").click();
		}
	});

	document.getElementById("sc-add").addEventListener("click", function () {
		cardsBox.appendChild(document.getElementById("tpl-scard").content.firstElementChild.cloneNode(true));
	});

	// выбор файла → сразу загрузка на сервер → превью и путь в карточке
	form.addEventListener("change", function (e) {
		if (!e.target.classList || !e.target.classList.contains("scard__file")) { return; }
		var input = e.target;
		var file = input.files[0];
		if (!file) { return; }
		if (file.size > 10 * 1024 * 1024) {
			toast("Файл слишком большой — загрузите фото до 10 МБ", "error");
			input.value = "";
			return;
		}

		var photo = input.closest(".scard__photo");
		var btn = photo.querySelector(".js-sc-photo");
		btn.disabled = true;
		btn.textContent = "Загружается…";

		var body = new FormData();
		body.set("action", "home_slider_upload");
		body.set("csrf", CSRF);
		body.set("file", file);

		fetch("/admin/php/actions.php", { method: "POST", body: body })
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (!json.ok) { toast(json.error || "Не получилось загрузить фото", "error"); return; }
				photo.querySelector(".scard__image").value = json.path;
				var img = photo.querySelector(".scard__img");
				img.src = json.path;
				img.classList.remove("is-empty");
				toast("Фото загружено — не забудьте сохранить", "ok");
			})
			.catch(function () {
				toast("Не получилось связаться с сервером. Проверьте интернет и попробуйте ещё раз.", "error");
			})
			.finally(function () {
				btn.disabled = false;
				btn.textContent = "Заменить фото";
				input.value = "";
			});
	});
})();

/* ===== примеры работ: карточки «до/после» ===== */
(function () {
	"use strict";

	var form = document.getElementById("works-form");
	if (!form) { return; }

	var CSRF = document.body.dataset.csrf || "";
	var cardsBox = document.getElementById("works-cards");

	function toast(text, type) {
		var box = document.getElementById("toasts");
		var el = document.createElement("div");
		el.className = "toast" + (type ? " toast--" + type : "");
		el.textContent = text;
		box.appendChild(el);
		setTimeout(function () { el.remove(); }, type === "error" ? 6000 : 2500);
	}

	// путь фото из нужного слота (до/после) карточки
	function photoPath(card, role) {
		return card.querySelector('[data-role="' + role + '"] .wcard__image').value;
	}

	// сборка формы в структуру для сохранения; дергается общим обработчиком
	window.collectWorks = function () {
		var items = [];
		cardsBox.querySelectorAll(".js-wcard").forEach(function (c) {
			items.push({
				text: c.querySelector(".wcard__text").value,
				year: c.querySelector(".wcard__year").value,
				location: c.querySelector(".wcard__location").value,
				slug: c.querySelector(".wcard__slug").value,
				before: photoPath(c, "before"),
				after: photoPath(c, "after")
			});
		});
		return {
			visible: document.getElementById("works-visible").checked,
			title: form.title.value,
			items: items
		};
	};

	form.addEventListener("click", function (e) {
		var t = e.target;

		if (t.classList.contains("js-wc-del")) {
			t.closest(".js-wcard").remove();
			form.dispatchEvent(new Event("change", { bubbles: false }));
		}
		if (t.classList.contains("js-wc-move")) {
			var el = t.closest(".js-wcard");
			if (t.dataset.dir === "up" && el.previousElementSibling) {
				el.parentNode.insertBefore(el, el.previousElementSibling);
			}
			if (t.dataset.dir === "down" && el.nextElementSibling) {
				el.parentNode.insertBefore(el.nextElementSibling, el);
			}
			form.dispatchEvent(new Event("change", { bubbles: false }));
		}
		// «Загрузить/Заменить» открывает скрытый input file своего слота
		if (t.classList.contains("js-wc-photo")) {
			t.closest(".wcard__photo").querySelector(".wcard__file").click();
		}
	});

	document.getElementById("wc-add").addEventListener("click", function () {
		cardsBox.appendChild(document.getElementById("tpl-wcard").content.firstElementChild.cloneNode(true));
	});

	// выбор файла → сразу загрузка на сервер → превью и путь в слоте
	form.addEventListener("change", function (e) {
		if (!e.target.classList || !e.target.classList.contains("wcard__file")) { return; }
		var input = e.target;
		var file = input.files[0];
		if (!file) { return; }
		if (file.size > 10 * 1024 * 1024) {
			toast("Файл слишком большой — загрузите фото до 10 МБ", "error");
			input.value = "";
			return;
		}

		var photo = input.closest(".wcard__photo");
		var btn = photo.querySelector(".js-wc-photo");
		btn.disabled = true;
		btn.textContent = "Загружается…";

		var body = new FormData();
		body.set("action", "works_upload");
		body.set("csrf", CSRF);
		body.set("file", file);

		fetch("/admin/php/actions.php", { method: "POST", body: body })
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (!json.ok) { toast(json.error || "Не получилось загрузить фото", "error"); return; }
				photo.querySelector(".wcard__image").value = json.path;
				var img = photo.querySelector(".wcard__img");
				img.src = json.path;
				img.classList.remove("is-empty");
				toast("Фото загружено — не забудьте сохранить", "ok");
			})
			.catch(function () {
				toast("Не получилось связаться с сервером. Проверьте интернет и попробуйте ещё раз.", "error");
			})
			.finally(function () {
				btn.disabled = false;
				btn.textContent = "Заменить";
				input.value = "";
			});
	});
})();


/* ===== редактор услуги, вкладка «Герой»: картинки услуги ===== */
(function () {
	"use strict";

	var form = document.getElementById("hero-form");
	if (!form) { return; }

	var CSRF = document.body.dataset.csrf || "";

	function toast(text, type) {
		var box = document.getElementById("toasts");
		var el = document.createElement("div");
		el.className = "toast" + (type ? " toast--" + type : "");
		el.textContent = text;
		box.appendChild(el);
		setTimeout(function () { el.remove(); }, type === "error" ? 6000 : 2500);
	}

	form.addEventListener("click", function (e) {
		var t = e.target;
		// «Загрузить/Заменить» → открыть скрытый input file своего слота
		if (t.classList.contains("js-himg-photo")) {
			t.closest(".js-himg").querySelector(".himg__file").click();
		}
		// «Убрать» → сброс пути (услуга вернётся к дефолту)
		if (t.classList.contains("js-himg-clear")) {
			var slot = t.closest(".js-himg");
			slot.querySelector(".himg__path").value = "";
			var img = slot.querySelector(".himg__img");
			img.removeAttribute("src");
			img.classList.add("is-empty");
			form.dispatchEvent(new Event("change", { bubbles: false }));
		}
	});

	// выбор файла → загрузка на сервер → превью и путь в слоте
	form.addEventListener("change", function (e) {
		if (!e.target.classList || !e.target.classList.contains("himg__file")) { return; }
		var input = e.target;
		var file = input.files[0];
		if (!file) { return; }
		if (file.size > 10 * 1024 * 1024) {
			toast("Файл слишком большой — загрузите фото до 10 МБ", "error");
			input.value = "";
			return;
		}

		var slot = input.closest(".js-himg");
		var btn = slot.querySelector(".js-himg-photo");
		btn.disabled = true;
		btn.textContent = "Загружается…";

		var body = new FormData();
		body.set("action", "svc_image_upload");
		body.set("csrf", CSRF);
		body.set("file", file);

		fetch("/admin/php/actions.php", { method: "POST", body: body })
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (!json.ok) { toast(json.error || "Не получилось загрузить фото", "error"); return; }
				slot.querySelector(".himg__path").value = json.path;
				var img = slot.querySelector(".himg__img");
				img.src = json.path;
				img.classList.remove("is-empty");
				toast("Фото загружено — не забудьте сохранить", "ok");
			})
			.catch(function () {
				toast("Не получилось связаться с сервером. Проверьте интернет и попробуйте ещё раз.", "error");
			})
			.finally(function () {
				btn.disabled = false;
				btn.textContent = "Заменить";
				input.value = "";
			});
	});
})();

/* ===== настройки: список телефонов ===== */
(function () {
	"use strict";

	var list = document.getElementById("phones-list");
	if (!list) { return; } // не на странице настроек

	var addBtn = document.getElementById("phone-add");

	// новая строка — копия первой с очищенными полями
	addBtn.addEventListener("click", function () {
		var row = list.querySelector(".js-phone-row").cloneNode(true);
		row.querySelectorAll("input").forEach(function (i) { i.value = ""; });
		list.appendChild(row);
		row.querySelector("input").focus();
		list.dispatchEvent(new Event("input", { bubbles: true })); // пометить вкладку правленой
	});

	// удаление: последнюю строку не убираем, просто чистим — телефон нужен хотя бы один
	list.addEventListener("click", function (e) {
		var btn = e.target.closest(".js-phone-del");
		if (!btn) { return; }
		var rows = list.querySelectorAll(".js-phone-row");
		if (rows.length > 1) {
			btn.closest(".js-phone-row").remove();
		} else {
			rows[0].querySelectorAll("input").forEach(function (i) { i.value = ""; });
		}
		list.dispatchEvent(new Event("input", { bubbles: true }));
	});
})();