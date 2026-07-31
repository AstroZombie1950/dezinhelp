// интерактив главной: меню, подменю, FAQ, маски/валидация форм, карусель отзывов

document.addEventListener("DOMContentLoaded", function () {
	var header = document.querySelector(".header");
	var burger = document.querySelector(".header__burger");

	// мобильное меню: панель во весь экран, поэтому фон под ней не должен скроллиться
	if (burger) {
		function closeNav() {
			header.classList.remove("is-nav-open");
			document.body.classList.remove("modal-open");
		}

		burger.addEventListener("click", function () {
			var open = header.classList.toggle("is-nav-open");
			document.body.classList.toggle("modal-open", open);
		});

		// переход по пункту меню закрывает панель — иначе она остаётся поверх страницы
		document.querySelectorAll(".nav__link, .nav__submenu-link").forEach(function (link) {
			link.addEventListener("click", closeNav);
		});

		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape" && header.classList.contains("is-nav-open")) { closeNav(); }
		});
	}

	// выпадающее подменю "Обработки"
	document.querySelectorAll(".nav__item--dropdown").forEach(function (item) {
		var toggle = item.querySelector(".nav__toggle");
		toggle.addEventListener("click", function () {
			var isOpen = item.classList.contains("is-open");
			document.querySelectorAll(".nav__item--dropdown").forEach(function (i) {
				i.classList.remove("is-open");
			});
			if (!isOpen) {
				item.classList.add("is-open");
			}
		});
	});

	document.addEventListener("click", function (e) {
		if (!e.target.closest(".nav__item--dropdown")) {
			document.querySelectorAll(".nav__item--dropdown").forEach(function (i) {
				i.classList.remove("is-open");
			});
		}
	});

	// ===== якоря: прокрутка ровно к началу блока =====
	// Штатного scroll-padding-top мало: пока идёт плавная прокрутка, выше цели
	// догружаются картинки и раскрываются блоки — высота меняется, и страница
	// останавливается мимо. Поэтому целимся сами и после остановки поправляем.
	function anchorOffset() {
		// липкая шапка перекрывает верх блока — вычитаем её реальную высоту
		var h = header ? header.getBoundingClientRect().height : 0;
		return h + 12;
	}

	// мгновенный переход: scroll-behavior: smooth в css перебивает behavior "auto",
	// поэтому на время прыжка гасим его на самом html
	function jumpTo(top) {
		var root = document.documentElement;
		var prev = root.style.scrollBehavior;
		root.style.scrollBehavior = "auto";
		window.scrollTo(0, top < 0 ? 0 : top);
		root.style.scrollBehavior = prev;
	}

	var noMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

	function scrollToAnchor(target, smooth) {
		var top = window.pageYOffset + target.getBoundingClientRect().top - anchorOffset();
		if (top < 0) { top = 0; }

		if (!smooth || noMotion) { jumpTo(top); aim(target); return; }

		// ждём тишины в 140 мс — значит анимация закончилась — и добираем разницу
		var settle = null;
		var guard = setTimeout(finish, 600);   // страница могла не сдвинуться вовсе
		function finish() {
			clearTimeout(settle);
			clearTimeout(guard);
			window.removeEventListener("scroll", onScroll);
			aim(target);
		}
		function onScroll() {
			clearTimeout(guard);
			clearTimeout(settle);
			settle = setTimeout(finish, 140);
		}
		window.addEventListener("scroll", onScroll, { passive: true });
		window.scrollTo({ top: top, behavior: "smooth" });
	}

	// доводка после остановки: пока шла прокрутка, ниже догружались картинки —
	// документ подрос, цель уехала, а первый прицел мог упереться в конец страницы.
	// Поправляем несколько раз подряд, пока позиция не перестанет меняться.
	function aim(target) {
		var tries = 0;
		(function fix() {
			var delta = target.getBoundingClientRect().top - anchorOffset();
			if (Math.abs(delta) > 2) { jumpTo(window.pageYOffset + delta); }
			if (++tries < 8) { setTimeout(fix, 150); }
		})();
	}

	// свой это адрес и та же самая страница — иначе пусть браузер уходит по ссылке
	function samePage(url) {
		if (url.host !== location.host) { return false; }
		var a = url.pathname.replace(/index\.php$/, "");
		var b = location.pathname.replace(/index\.php$/, "");
		return a === b;
	}

	document.addEventListener("click", function (e) {
		var link = e.target.closest ? e.target.closest('a[href*="#"]') : null;
		if (!link || link.classList.contains("js-order-open")) { return; }

		var url;
		try { url = new URL(link.getAttribute("href"), location.href); } catch (err) { return; }
		if (!samePage(url) || url.hash.length < 2) { return; }

		var target = document.getElementById(decodeURIComponent(url.hash.slice(1)));
		if (!target) { return; }

		e.preventDefault();
		if (history.replaceState) { history.pushState(null, "", url.hash); }
		// панель меню закрывается в своём обработчике выше по всплытию и она fixed,
		// поэтому на позицию цели не влияет — считаем сразу
		scrollToAnchor(target, true);
	});

	// приход с якорем из адресной строки (например «/#methods» со страницы услуги):
	// браузер прыгает до загрузки картинок, поэтому после load повторяем прицел
	if (location.hash.length > 1) {
		var landing = document.getElementById(decodeURIComponent(location.hash.slice(1)));
		if (landing) {
			window.addEventListener("load", function () {
				setTimeout(function () { scrollToAnchor(landing, false); }, 60);
			});
		}
	}

	// аккордеон FAQ
	document.querySelectorAll(".faq-item__question").forEach(function (btn) {
		btn.addEventListener("click", function () {
			btn.closest(".faq-item").classList.toggle("is-open");
		});
	});

	// ===== маска телефона +7 (###) ###-##-## =====
	function formatPhone(value) {
		var digits = value.replace(/\D/g, "");
		if (digits[0] === "8") { digits = "7" + digits.slice(1); }
		if (digits[0] !== "7") { digits = "7" + digits; }
		digits = digits.slice(0, 11);
		var res = "+7";
		if (digits.length > 1) { res += " (" + digits.slice(1, 4); }
		if (digits.length >= 4) { res += ") " + digits.slice(4, 7); }
		if (digits.length >= 7) { res += "-" + digits.slice(7, 9); }
		if (digits.length >= 9) { res += "-" + digits.slice(9, 11); }
		return res;
	}

	document.querySelectorAll("input[type=tel]").forEach(function (input) {
		input.addEventListener("input", function () {
			input.value = formatPhone(input.value);
		});
		input.addEventListener("focus", function () {
			if (!input.value) { input.value = "+7 ("; }
		});
		input.addEventListener("blur", function () {
			if (input.value === "+7 (" || input.value === "+7") { input.value = ""; }
		});
	});

	// ===== маска имени: кириллица, латиница, пробелы =====
	document.querySelectorAll("input[name=name]").forEach(function (input) {
		input.addEventListener("input", function () {
			input.value = input.value.replace(/[^A-Za-zА-Яа-яЁё\s]/g, "");
		});
	});

	// ===== отправка форм в Telegram (через order.php) =====
	function setStatus(el, text, type) {
		if (!el) { return; }
		el.textContent = text;
		el.className = "form-status" + (type ? " is-" + type : "");
	}

	document.querySelectorAll(".js-form").forEach(function (form) {
		// отключаем нативные всплывающие подсказки браузера — валидируем сами
		form.setAttribute("novalidate", "novalidate");
		form.addEventListener("submit", function (e) {
			e.preventDefault();
			var name = form.querySelector("input[name=name]");
			var phone = form.querySelector("input[type=tel]");
			var status = form.querySelector(".form-status");
			var digits = phone ? phone.value.replace(/\D/g, "") : "";

			// имя обязательно, если поле есть и форма не помечена как name_optional
			var nameOptional = form.querySelector("input[name=name_optional]");
			if (name && !nameOptional && name.value.trim() === "") {
				setStatus(status, "Введите ваше имя", "error");
				name.focus();
				return;
			}

			if (digits.length !== 11) {
				setStatus(status, "Введите корректный телефон", "error");
				if (phone) { phone.focus(); }
				return;
			}

			// согласие на обработку данных — есть только там, где его требует макет
			var consent = form.querySelector("input[name=consent]");
			if (consent && !consent.checked) {
				setStatus(status, "Подтвердите согласие на обработку данных", "error");
				return;
			}

			var btn = form.querySelector("button[type=submit]");
			if (btn) { btn.disabled = true; }
			setStatus(status, "Отправляем…", "");

			fetch(form.action, { method: "POST", body: new FormData(form) })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (res.ok) {
						setStatus(status, "Заявка отправлена! Мы перезвоним вам.", "success");
						form.reset();
						// квизу нужно вернуться на первый экран — слушает это событие
						form.dispatchEvent(new CustomEvent("form:sent"));
					} else {
						setStatus(status, "Не удалось отправить. Позвоните нам, пожалуйста.", "error");
					}
				})
				.catch(function () {
					setStatus(status, "Ошибка сети. Попробуйте позже.", "error");
				})
				.finally(function () {
					if (btn) { btn.disabled = false; }
				});
		});
	});

	// ===== карусель отзывов =====
	var track = document.querySelector(".reviews__track");
	if (track) {
		var carousel = track.closest(".reviews__carousel");
		var step = function () {
			var card = track.querySelector(".review-card");
			if (!card) { return track.clientWidth; }
			var gap = parseInt(getComputedStyle(track).columnGap || getComputedStyle(track).gap || "20", 10);
			return card.offsetWidth + (isNaN(gap) ? 20 : gap);
		};

		carousel.querySelectorAll(".reviews__btn").forEach(function (btn) {
			btn.addEventListener("click", function () {
				var dir = btn.dataset.dir === "prev" ? -1 : 1;
				track.scrollBy({ left: dir * step(), behavior: "smooth" });
			});
		});

		// автопрокрутка с зацикливанием, пауза при наведении/касании
		var timer = null;
		function play() {
			timer = setInterval(function () {
				var maxScroll = track.scrollWidth - track.clientWidth - 4;
				if (track.scrollLeft >= maxScroll) {
					track.scrollTo({ left: 0, behavior: "smooth" });
				} else {
					track.scrollBy({ left: step(), behavior: "smooth" });
				}
			}, 5000);
		}
		function stop() { if (timer) { clearInterval(timer); timer = null; } }

		carousel.addEventListener("mouseenter", stop);
		carousel.addEventListener("mouseleave", play);
		track.addEventListener("touchstart", stop, { passive: true });
		play();
	}

	// ===== поп-ап заявки (открывается со всех .js-order-open) =====
	var orderModal = document.getElementById("order-modal");
	if (orderModal) {
		function openModal() {
			orderModal.classList.add("is-open");
			orderModal.setAttribute("aria-hidden", "false");
			document.body.classList.add("modal-open");
			// фокус на первое поле для удобства
			var first = orderModal.querySelector("input[name=name]");
			if (first) { first.focus(); }
		}
		function closeModal() {
			orderModal.classList.remove("is-open");
			orderModal.setAttribute("aria-hidden", "true");
			document.body.classList.remove("modal-open");
		}

		// открытие: любая кнопка-триггер
		document.querySelectorAll(".js-order-open").forEach(function (el) {
			el.addEventListener("click", function (e) {
				e.preventDefault();
				openModal();
			});
		});

		// закрытие: крестик и клик по затемнению
		orderModal.querySelectorAll("[data-modal-close]").forEach(function (el) {
			el.addEventListener("click", closeModal);
		});

		// закрытие по Esc
		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape" && orderModal.classList.contains("is-open")) {
				closeModal();
			}
		});
	}

	// ===== бесшовные карусели: клоны по краям, скролл целыми карточками =====
	// одна механика на услуги, сертификаты, галерею и примеры работ.
	// число клонов и реальных карточек приходит с разметки: data-clone / data-real
	function initCarousel(trackSelector, cardSelector, btnSelector) {
		var track = document.querySelector(trackSelector);
		if (!track) { return; }
		var carousel = track.parentElement;
		var CLONE = parseInt(track.dataset.clone, 10); // клонов с каждой стороны
		var REAL = parseInt(track.dataset.real, 10);   // реальных карточек

		function step() {
			var card = track.querySelector(cardSelector);
			if (!card) { return track.clientWidth; }
			var gap = parseInt(getComputedStyle(track).columnGap || getComputedStyle(track).gap || "20", 10);
			return card.offsetWidth + (isNaN(gap) ? 20 : gap);
		}

		// стартуем на первой настоящей карточке (после клонов слева), без анимации
		function setPosition(index) {
			track.style.scrollBehavior = "auto";
			track.scrollLeft = index * step();
			track.style.scrollBehavior = "";
		}
		setPosition(CLONE);
		window.addEventListener("resize", function () { setPosition(CLONE); });

		// после остановки скролла — если заехали в зону клонов, бесшовно телепортируем на реальную позицию
		var settleTimer = null;
		track.addEventListener("scroll", function () {
			clearTimeout(settleTimer);
			settleTimer = setTimeout(function () {
				var index = Math.round(track.scrollLeft / step());
				if (index < CLONE) {
					setPosition(index + REAL);
				} else if (index >= CLONE + REAL) {
					setPosition(index - REAL);
				}
			}, 120);
		}, { passive: true });

		carousel.querySelectorAll(btnSelector).forEach(function (btn) {
			btn.addEventListener("click", function () {
				var dir = btn.dataset.dir === "prev" ? -1 : 1;
				track.scrollBy({ left: dir * step(), behavior: "smooth" });
			});
		});
	}

	initCarousel(".js-srv-track", ".srv-card", ".srv__btn");     // слайдер услуг
	initCarousel(".js-certs-track", ".cert-card", ".certs__btn"); // сертификаты
	initCarousel(".js-gal-track", ".gal__item", ".gal__btn");     // объекты санобработки
	initCarousel(".js-works-track", ".work-card", ".works__btn"); // примеры «до/после»

	// ===== лайтбокс сертификатов: увеличенный просмотр по клику =====
	var certLightbox = document.getElementById("cert-lightbox");
	if (certLightbox) {
		var certLightboxImg = certLightbox.querySelector(".lightbox__img");

		function openCertLightbox(num) {
			certLightboxImg.src = "/source/img/certificate/cert_" + num + "_full.webp";
			certLightboxImg.alt = "Сертификат №" + num;
			certLightbox.classList.add("is-open");
			certLightbox.setAttribute("aria-hidden", "false");
			document.body.classList.add("modal-open");
		}
		function closeCertLightbox() {
			certLightbox.classList.remove("is-open");
			certLightbox.setAttribute("aria-hidden", "true");
			document.body.classList.remove("modal-open");
			certLightboxImg.src = "";
		}

		document.querySelectorAll(".js-cert-open").forEach(function (btn) {
			btn.addEventListener("click", function () {
				openCertLightbox(btn.dataset.cert);
			});
		});

		certLightbox.querySelectorAll("[data-modal-close]").forEach(function (el) {
			el.addEventListener("click", closeCertLightbox);
		});

		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape" && certLightbox.classList.contains("is-open")) {
				closeCertLightbox();
			}
		});
	}

	// ===== кнопка «наверх»: показ после первого экрана + плавный скролл =====
	var toTop = document.querySelector(".fab--top");
	if (toTop) {
		function toggleTop() {
			if (window.scrollY > window.innerHeight) {
				toTop.classList.add("is-visible");
			} else {
				toTop.classList.remove("is-visible");
			}
		}
		window.addEventListener("scroll", toggleTop, { passive: true });
		toggleTop();

		toTop.addEventListener("click", function () {
			window.scrollTo({ top: 0, behavior: "smooth" });
		});
	}

	// ===== список городов: разворачивание свёрнутой части =====
	// Обрезку по высоте делает css (только узкие экраны), здесь только переключение.
	var citiesBox = document.querySelector(".js-cities");
	var citiesToggle = citiesBox ? citiesBox.querySelector(".js-cities-toggle") : null;
	if (citiesToggle) {
		citiesToggle.addEventListener("click", function () {
			var open = citiesBox.classList.toggle("is-open");
			citiesToggle.textContent = open ? citiesToggle.dataset.less : citiesToggle.dataset.more;
			citiesToggle.setAttribute("aria-expanded", open ? "true" : "false");
			// свернули из середины списка — возвращаем к заголовку блока
			if (!open) {
				var section = citiesBox.closest(".section");
				if (section && section.getBoundingClientRect().top < 0) { scrollToAnchor(section, false); }
			}
		});
	}

	// ===== основной текст услуги: сворачивание с анимацией =====
	// max-height нельзя анимировать от auto, поэтому подставляем реальную высоту содержимого
	var collapse = document.querySelector(".js-collapse");
	var collapseBtn = document.querySelector(".js-collapse-toggle");
	if (collapse && collapseBtn) {
		var open = false;

		function collapseApply() {
			collapse.style.maxHeight = open ? collapse.scrollHeight + "px" : "0px";
			collapse.classList.toggle("is-open", open);
			collapseBtn.textContent = open ? collapseBtn.dataset.less : collapseBtn.dataset.more;
			collapseBtn.setAttribute("aria-expanded", open ? "true" : "false");
		}
		collapseApply();

		collapseBtn.addEventListener("click", function () {
			open = !open;
			collapseApply();
		});

		// после раскрытия высота фиксирована в пикселях — при ресайзе пересчитываем
		window.addEventListener("resize", function () {
			if (open) { collapse.style.maxHeight = collapse.scrollHeight + "px"; }
		});

		// картинки внутри догружаются и меняют высоту — снимаем ограничение после раскрытия
		collapse.addEventListener("transitionend", function (e) {
			if (e.propertyName === "max-height" && open) { collapse.style.maxHeight = "none"; }
		});
	}

	// ===== квиз-заявка: переключение экранов, прогресс, блокировка «Далее» =====
	// отправку делает общий обработчик .js-form — здесь только навигация
	var quiz = document.querySelector(".js-quiz");
	if (quiz) {
		var quizSteps = quiz.querySelectorAll(".quiz__step");
		var quizBack = quiz.querySelector(".js-quiz-back");
		var quizNext = quiz.querySelector(".js-quiz-next");
		var quizLabel = quiz.querySelector(".js-quiz-label");
		var quizPercent = quiz.querySelector(".js-quiz-percent");
		var quizFill = quiz.querySelector(".js-quiz-fill");
		var quizFinalLabel = quiz.querySelector(".js-quiz-final-label").textContent;
		var quizStartLabel = quizLabel.textContent;
		var quizIndex = 0;

		// на финале «Далее» не нужна, там своя кнопка отправки
		function quizIsFinal(i) {
			return quizSteps[i].classList.contains("quiz__step--final");
		}

		// пустой шаг дальше не пускает: и радио, и чекбоксы считаем одинаково
		function quizAnswered(i) {
			return quizSteps[i].querySelector("input:checked") !== null;
		}

		function quizRender() {
			quizSteps.forEach(function (step, i) {
				step.classList.toggle("is-active", i === quizIndex);
			});

			var percent = quizSteps[quizIndex].dataset.percent;
			quizPercent.textContent = percent + "%";
			quizFill.style.width = percent + "%";

			// на финале счётчик процентов заменяем подписью «остался последний шаг»
			var final = quizIsFinal(quizIndex);
			quizLabel.textContent = final ? quizFinalLabel : quizStartLabel;
			quizPercent.hidden = final;

			quizBack.classList.toggle("is-visible", quizIndex > 0);
			quizNext.classList.toggle("is-hidden", final);
			quizNext.disabled = !final && !quizAnswered(quizIndex);
		}

		// выбор варианта разблокирует «Далее» сразу, без перерисовки экрана
		quiz.addEventListener("change", function () {
			if (!quizIsFinal(quizIndex)) {
				quizNext.disabled = !quizAnswered(quizIndex);
			}
		});

		quizNext.addEventListener("click", function () {
			if (quizIndex < quizSteps.length - 1) {
				quizIndex++;
				quizRender();
			}
		});

		quizBack.addEventListener("click", function () {
			if (quizIndex > 0) {
				quizIndex--;
				quizRender();
			}
		});

		// отправили — даём прочитать ответ и возвращаем на первый экран.
		// reset() уже отработал в обработчике формы: предвыбранные варианты
		// проставлены атрибутом checked и переживают сброс
		quiz.addEventListener("form:sent", function () {
			setTimeout(function () {
				var status = quiz.querySelector(".form-status");
				if (status) {
					status.textContent = "";
					status.className = "form-status";
				}
				quizIndex = 0;
				quizRender();
			}, 3000);
		});

		quizRender();
	}

	// ===== счётчики блока «цифры»: анимация от нуля при попадании в вьюпорт =====
	var statNums = document.querySelectorAll(".stats__num");
	if (statNums.length) {
		var statsReduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

		// разбор data-count: одиночное «1812» или диапазон «4–12»
		function parseCount(raw) {
			var m = raw.match(/^(\d+)\s*[–-]\s*(\d+)$/);
			if (m) { return { range: true, a: +m[1], b: +m[2], sep: raw.indexOf("–") > -1 ? "–" : "-" }; }
			return { range: false, a: +raw };
		}

		// один прогон: от 0 к финалу за dur, замедление к концу (easeOutCubic)
		function runCount(el) {
			var c = parseCount(el.dataset.count);
			if (statsReduce) { el.textContent = el.dataset.count; return; } // без анимации
			var dur = 1600, start = null;
			function frame(t) {
				if (start === null) { start = t; }
				var p = Math.min((t - start) / dur, 1);
				var e = 1 - Math.pow(1 - p, 3);
				// диапазон крутим обоими концами сразу
				el.textContent = c.range
					? Math.round(c.a * e) + c.sep + Math.round(c.b * e)
					: Math.round(c.a * e);
				if (p < 1) { requestAnimationFrame(frame); }
			}
			requestAnimationFrame(frame);
		}

		// запуск один раз, когда число показалось на экране
		var statsSeen = new WeakSet();
		var statsIO = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting && !statsSeen.has(en.target)) {
					statsSeen.add(en.target);
					runCount(en.target);
					statsIO.unobserve(en.target);
				}
			});
		}, { threshold: 0.4 });

		statNums.forEach(function (el) { statsIO.observe(el); });
	}

	// ===== город посетителя: определение, подтверждение, выбор из списка =====
	// Порядок: спрашиваем геолокацию браузера, при отказе определяем по IP (DaData).
	// Город из нашего реестра — уводим на его поддомен, чужой — остаёмся на Москве.
	// Дальше поп-ап «Нужна обработка в …?»: «Да» — остаться, «Нет» — список городов.
	var geoAsk = document.getElementById("geo-ask");
	var cityPicker = document.getElementById("city-picker");
	if (geoAsk && cityPicker) {
		var citySlug = geoAsk.dataset.slug;
		var cityBase = geoAsk.dataset.base || "";
		var CITY_COOKIE = "dh_city";       // что определили — второй раз сервисы не дёргаем
		var CITY_OK_COOKIE = "dh_city_ok"; // город подтверждён посетителем

		// куку ставим на весь домен: поддомены должны видеть ответ друг друга
		function cookieDomain() {
			if (cityBase.indexOf(".") === -1) { return ""; }  // localhost
			if (/^[\d.]+$/.test(cityBase)) { return ""; }     // IP вместо домена
			return "; domain=." + cityBase;
		}

		function cookieGet(name) {
			var m = document.cookie.match(new RegExp("(?:^|; )" + name + "=([^;]*)"));
			return m ? decodeURIComponent(m[1]) : "";
		}

		function cookieSet(name, value, days) {
			document.cookie = name + "=" + encodeURIComponent(value) + "; path=/; max-age="
				+ (days * 86400) + cookieDomain() + "; samesite=lax";
		}

		// --- поп-ап подтверждения ---
		function askShow() {
			if (cookieGet(CITY_OK_COOKIE)) { return; }
			geoAsk.hidden = false;
			setTimeout(function () { geoAsk.classList.add("is-open"); }, 20);
		}

		function askHide() {
			geoAsk.classList.remove("is-open");
			setTimeout(function () { geoAsk.hidden = true; }, 250);
		}

		geoAsk.querySelector(".js-geo-yes").addEventListener("click", function () {
			cookieSet(CITY_OK_COOKIE, "1", 180);
			cookieSet(CITY_COOKIE, citySlug, 180);
			askHide();
		});

		geoAsk.querySelector(".js-geo-no").addEventListener("click", function () {
			askHide();
			pickerOpen();
		});

		// крестик — вопрос не задаём до конца сессии, но выбор не запоминаем
		geoAsk.querySelector("[data-geo-close]").addEventListener("click", function () {
			document.cookie = CITY_OK_COOKIE + "=1; path=/" + cookieDomain() + "; samesite=lax";
			askHide();
		});

		// --- список городов ---
		var citySearch = cityPicker.querySelector(".js-city-search");
		var cityItems = cityPicker.querySelectorAll(".city-picker__item");
		var cityEmpty = cityPicker.querySelector(".js-city-empty");

		function pickerOpen() {
			cityPicker.classList.add("is-open");
			cityPicker.setAttribute("aria-hidden", "false");
			document.body.classList.add("modal-open");
			// на десктопе сразу в поиск, на тач-экранах фокус выдёргивает клавиатуру
			if (window.innerWidth > 768) { citySearch.focus(); }
		}

		function pickerClose() {
			cityPicker.classList.remove("is-open");
			cityPicker.setAttribute("aria-hidden", "true");
			document.body.classList.remove("modal-open");
		}

		cityPicker.querySelectorAll("[data-picker-close]").forEach(function (el) {
			el.addEventListener("click", pickerClose);
		});

		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape" && cityPicker.classList.contains("is-open")) { pickerClose(); }
		});

		// поиск по списку: без учёта регистра и «ё», совпадение с любого места названия
		function cityNorm(s) {
			return s.toLowerCase().replace(/ё/g, "е").trim();
		}

		citySearch.addEventListener("input", function () {
			var q = cityNorm(citySearch.value);
			var shown = 0;
			cityItems.forEach(function (item) {
				var hit = q === "" || cityNorm(item.dataset.name).indexOf(q) !== -1;
				item.hidden = !hit;
				if (hit) { shown++; }
			});
			cityEmpty.hidden = shown > 0;
		});

		// выбор города вручную — и в поп-апе, и в списке на главной: запоминаем и не переспрашиваем
		document.querySelectorAll(".js-city-choose, .cities__link").forEach(function (link) {
			link.addEventListener("click", function () {
				cookieSet(CITY_OK_COOKIE, "1", 180);
				cookieSet(CITY_COOKIE, link.dataset.slug || "", 180);
			});
		});

		// --- определение города ---
		function detectSend(query) {
			fetch("/source/php/detect.php" + query, { headers: { "Accept": "application/json" } })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					cookieSet(CITY_COOKIE, res.slug || citySlug, 30);
					// определился другой город — открываем его версию тем же путём
					if (res.ok && res.slug && res.slug !== citySlug && res.origin) {
						location.replace(res.origin + location.pathname + location.search);
						return;
					}
					askShow();
				})
				.catch(function () { askShow(); });
		}

		function detect() {
			if (!navigator.geolocation) { detectSend(""); return; }
			navigator.geolocation.getCurrentPosition(
				function (pos) {
					detectSend("?lat=" + pos.coords.latitude + "&lon=" + pos.coords.longitude);
				},
				function () { detectSend(""); },  // отказ, таймаут, http — определяем по IP
				{ timeout: 8000, maximumAge: 600000 }
			);
		}

		// определяем один раз на посетителя, дальше только подтверждение
		if (cookieGet(CITY_COOKIE)) {
			askShow();
		} else {
			detect();
		}
	}
});