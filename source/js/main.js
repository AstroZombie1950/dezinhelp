// интерактив главной: меню, подменю, FAQ, маски/валидация форм, карусель отзывов

document.addEventListener("DOMContentLoaded", function () {
	var header = document.querySelector(".header");
	var burger = document.querySelector(".header__burger");

	// мобильное меню
	if (burger) {
		burger.addEventListener("click", function () {
			header.classList.toggle("is-nav-open");
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

			var btn = form.querySelector("button[type=submit]");
			if (btn) { btn.disabled = true; }
			setStatus(status, "Отправляем…", "");

			fetch(form.action, { method: "POST", body: new FormData(form) })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (res.ok) {
						setStatus(status, "Заявка отправлена! Мы перезвоним вам.", "success");
						form.reset();
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

	// ===== слайдер услуг: скролл + клоны по краям, механика как у сертификатов =====
	var srvTrack = document.querySelector(".js-srv-track");
	if (srvTrack) {
		var srvCarousel = srvTrack.closest(".srv__carousel");
		var SRV_CLONE = parseInt(srvTrack.dataset.clone, 10); // клонов с каждой стороны
		var SRV_REAL = parseInt(srvTrack.dataset.real, 10);   // реальных карточек

		function srvStep() {
			var card = srvTrack.querySelector(".srv-card");
			if (!card) { return srvTrack.clientWidth; }
			var gap = parseInt(getComputedStyle(srvTrack).columnGap || getComputedStyle(srvTrack).gap || "20", 10);
			return card.offsetWidth + (isNaN(gap) ? 20 : gap);
		}

		// стартуем на первой настоящей карточке (после клонов слева), без анимации
		function srvSetPosition(index) {
			srvTrack.style.scrollBehavior = "auto";
			srvTrack.scrollLeft = index * srvStep();
			srvTrack.style.scrollBehavior = "";
		}
		srvSetPosition(SRV_CLONE);
		window.addEventListener("resize", function () { srvSetPosition(SRV_CLONE); });

		// после остановки скролла — если заехали в зону клонов, бесшовно телепортируем на реальную позицию
		var srvSettleTimer = null;
		srvTrack.addEventListener("scroll", function () {
			clearTimeout(srvSettleTimer);
			srvSettleTimer = setTimeout(function () {
				var index = Math.round(srvTrack.scrollLeft / srvStep());
				if (index < SRV_CLONE) {
					srvSetPosition(index + SRV_REAL);
				} else if (index >= SRV_CLONE + SRV_REAL) {
					srvSetPosition(index - SRV_REAL);
				}
			}, 120);
		}, { passive: true });

		srvCarousel.querySelectorAll(".srv__btn").forEach(function (btn) {
			btn.addEventListener("click", function () {
				var dir = btn.dataset.dir === "prev" ? -1 : 1;
				srvTrack.scrollBy({ left: dir * srvStep(), behavior: "smooth" });
			});
		});
	}

	// ===== карусель сертификатов: скролл + клоны по краям для бесшовной прокрутки =====
	var certsTrack = document.querySelector(".js-certs-track");
	if (certsTrack) {
		var certsCarousel = certsTrack.closest(".certs__carousel");
		var CERTS_CLONE = 4; // клонов с каждой стороны
		var CERTS_REAL = 6;  // реальных сертификатов

		function certStep() {
			var card = certsTrack.querySelector(".cert-card");
			if (!card) { return certsTrack.clientWidth; }
			var gap = parseInt(getComputedStyle(certsTrack).columnGap || getComputedStyle(certsTrack).gap || "20", 10);
			return card.offsetWidth + (isNaN(gap) ? 20 : gap);
		}

		// стартуем на первой настоящей карточке (после клонов слева), без анимации
		function certsSetPosition(index) {
			certsTrack.style.scrollBehavior = "auto";
			certsTrack.scrollLeft = index * certStep();
			certsTrack.style.scrollBehavior = "";
		}
		certsSetPosition(CERTS_CLONE);
		window.addEventListener("resize", function () { certsSetPosition(CERTS_CLONE); });

		// после остановки скролла — если заехали в зону клонов, бесшовно телепортируем на реальную позицию
		var certsSettleTimer = null;
		certsTrack.addEventListener("scroll", function () {
			clearTimeout(certsSettleTimer);
			certsSettleTimer = setTimeout(function () {
				var step = certStep();
				var index = Math.round(certsTrack.scrollLeft / step);
				if (index < CERTS_CLONE) {
					certsSetPosition(index + CERTS_REAL);
				} else if (index >= CERTS_CLONE + CERTS_REAL) {
					certsSetPosition(index - CERTS_REAL);
				}
			}, 120);
		}, { passive: true });

		certsCarousel.querySelectorAll(".certs__btn").forEach(function (btn) {
			btn.addEventListener("click", function () {
				var dir = btn.dataset.dir === "prev" ? -1 : 1;
				certsTrack.scrollBy({ left: dir * certStep(), behavior: "smooth" });
			});
		});
	}

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

	// ===== объекты санобработки: слайдер (та же схема, что у услуг — клоны по краям) =====
	var galTrack = document.querySelector(".js-gal-track");
	if (galTrack) {
		var galCarousel = galTrack.closest(".gal__carousel");
		var GAL_CLONE = parseInt(galTrack.dataset.clone, 10); // клонов с каждой стороны
		var GAL_REAL = parseInt(galTrack.dataset.real, 10);   // реальных фото

		function galStep() {
			var item = galTrack.querySelector(".gal__item");
			if (!item) { return galTrack.clientWidth; }
			var gap = parseInt(getComputedStyle(galTrack).columnGap || getComputedStyle(galTrack).gap || "20", 10);
			return item.offsetWidth + (isNaN(gap) ? 20 : gap);
		}

		// стартуем на первом настоящем фото, без анимации
		function galSetPosition(index) {
			galTrack.style.scrollBehavior = "auto";
			galTrack.scrollLeft = index * galStep();
			galTrack.style.scrollBehavior = "";
		}
		galSetPosition(GAL_CLONE);
		window.addEventListener("resize", function () { galSetPosition(GAL_CLONE); });

		// заехали в клоны — бесшовно телепортируем на реальную позицию
		var galSettleTimer = null;
		galTrack.addEventListener("scroll", function () {
			clearTimeout(galSettleTimer);
			galSettleTimer = setTimeout(function () {
				var index = Math.round(galTrack.scrollLeft / galStep());
				if (index < GAL_CLONE) {
					galSetPosition(index + GAL_REAL);
				} else if (index >= GAL_CLONE + GAL_REAL) {
					galSetPosition(index - GAL_REAL);
				}
			}, 120);
		}, { passive: true });

		galCarousel.querySelectorAll(".gal__btn").forEach(function (btn) {
			btn.addEventListener("click", function () {
				var dir = btn.dataset.dir === "prev" ? -1 : 1;
				galTrack.scrollBy({ left: dir * galStep(), behavior: "smooth" });
			});
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
});
