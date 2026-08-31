(function () {
	'use strict';

	if (typeof wpMetricas === 'undefined') {
		return;
	}

	var config = wpMetricas;
	var sessionStorageKey = 'wp_metricas_visit_' + config.postId;
	var initialized = false;

	/**
	 * Detecta tipo de dispositivo.
	 */
	function getDeviceType() {
		var width = window.innerWidth;
		if (width < 768) {
			return 'mobile';
		}
		if (width < 1024) {
			return 'tablet';
		}
		return 'desktop';
	}

	/**
	 * Envía datos a la API REST.
	 */
	function sendData(endpoint, data) {
		return fetch(config.restUrl + '/' + endpoint, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': config.nonce
			},
			body: JSON.stringify(data),
			keepalive: true,
			credentials: 'same-origin'
		}).catch(function () {
			// Silenciar errores de red en tracking.
		});
	}

	/**
	 * Registra visita (una por sesión por página).
	 */
	function trackVisit() {
		try {
			if (sessionStorage.getItem(sessionStorageKey)) {
				return;
			}
			sessionStorage.setItem(sessionStorageKey, '1');
		} catch (e) {
			// sessionStorage no disponible.
		}

		sendData('visit', {
			post_id: config.postId,
			post_type: config.postType,
			post_title: config.postTitle,
			url: config.url,
			referrer: document.referrer || '',
			session_id: config.sessionId,
			device_type: getDeviceType(),
			is_elementor: config.isElementor,
			has_acf: config.hasAcf
		});
	}

	/**
	 * Obtiene identificador único de un botón.
	 */
	function getButtonId(el) {
		if (el.id) {
			return el.id;
		}
		var widget = el.closest('[data-id]');
		if (widget) {
			return 'elementor-' + widget.getAttribute('data-id');
		}
		var text = (el.textContent || '').trim().substring(0, 50);
		return 'btn-' + text.replace(/\s+/g, '-').toLowerCase();
	}

	/**
	 * Obtiene widget ID de Elementor.
	 */
	function getElementorWidgetId(el) {
		var widget = el.closest('.elementor-widget[data-id]');
		return widget ? widget.getAttribute('data-id') : '';
	}

	/**
	 * Configura tracking de clics en botones.
	 */
	function trackButtons() {
		if (!config.trackButtons) {
			return;
		}

		var selectors = (config.buttonSelectors || []).join(', ');
		if (!selectors) {
			return;
		}

		document.addEventListener('click', function (e) {
			var el = e.target.closest(selectors);
			if (!el) {
				return;
			}

			var href = el.getAttribute('href') || '';
			if (el.tagName === 'INPUT' || el.tagName === 'BUTTON') {
				href = el.getAttribute('formaction') || href;
			}

			sendData('click', {
				post_id: config.postId,
				button_id: getButtonId(el),
				button_text: (el.textContent || el.getAttribute('aria-label') || el.value || '').trim().substring(0, 255),
				button_url: href,
				selector: el.className ? '.' + el.className.split(' ').join('.') : el.tagName.toLowerCase(),
				elementor_widget_id: getElementorWidgetId(el),
				session_id: config.sessionId
			});
		}, true);
	}

	/**
	 * Mide el tiempo total que el visitante permanece en la página.
	 */
	function trackPageTime() {
		if (!config.trackSections) {
			return;
		}

		var accumulated = 0;
		var visibleStart = document.visibilityState === 'visible' ? Date.now() : null;
		var minTime = config.minSectionTime || 2;
		var sent = false;

		function getVisibleDuration() {
			var total = accumulated;
			if (visibleStart) {
				total += Date.now() - visibleStart;
			}
			return Math.floor(total / 1000);
		}

		function pause() {
			if (visibleStart) {
				accumulated += Date.now() - visibleStart;
				visibleStart = null;
			}
		}

		function resume() {
			if (!visibleStart && document.visibilityState === 'visible') {
				visibleStart = Date.now();
			}
		}

		function sendPageTime() {
			if (sent) {
				return;
			}

			var duration = getVisibleDuration();
			pause();

			if (duration < minTime) {
				return;
			}

			sent = true;

			sendData('section', {
				post_id: config.postId,
				section_id: 'page-time',
				section_name: config.postTitle,
				post_type: config.postType,
				selector: config.postType,
				elementor_section_id: '',
				duration_seconds: duration,
				session_id: config.sessionId
			});
		}

		document.addEventListener('visibilitychange', function () {
			if (document.visibilityState === 'hidden') {
				pause();
				sendPageTime();
			} else {
				resume();
			}
		});

		window.addEventListener('pagehide', sendPageTime);
		window.addEventListener('beforeunload', sendPageTime);
	}

	/**
	 * Espera a que Elementor termine de renderizar.
	 */
	function initWhenReady() {
		if (initialized) {
			return;
		}
		initialized = true;
		trackVisit();
		trackButtons();
		trackPageTime();
		startHeartbeat();
	}

	/**
	 * Envía heartbeat para visitantes en tiempo real.
	 */
	function startHeartbeat() {
		var interval = (config.heartbeatInterval || 30) * 1000;

		function ping() {
			if (document.visibilityState !== 'visible') {
				return;
			}

			sendData('heartbeat', {
				session_id: config.sessionId,
				post_id: config.postId,
				url: config.url,
				post_title: config.postTitle
			});
		}

		ping();
		setInterval(ping, interval);

		document.addEventListener('visibilitychange', function () {
			if (document.visibilityState === 'visible') {
				ping();
			}
		});
	}

	if (config.trackElementor && config.isElementor) {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
				setTimeout(initWhenReady, 300);
			});
			// Fallback si los hooks no disparan.
			setTimeout(initWhenReady, 1500);
		} else {
			document.addEventListener('DOMContentLoaded', function () {
				setTimeout(initWhenReady, 500);
			});
		}
	} else {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', initWhenReady);
		} else {
			initWhenReady();
		}
	}
})();
