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

			// имя обязательно, если поле есть в форме
			if (name && name.value.trim() === "") {
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
});
