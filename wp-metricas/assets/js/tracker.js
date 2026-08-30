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
	 * Gestor de tiempo por sección con Intersection Observer.
	 */
	function trackSections() {
		if (!config.trackSections || !('IntersectionObserver' in window)) {
			return;
		}

		var selectors = (config.sectionSelectors || []).join(', ');
		if (!selectors) {
			return;
		}

		var sections = document.querySelectorAll(selectors);
		if (!sections.length) {
			return;
		}

		var activeSections = {};
		var minTime = config.minSectionTime || 2;

		function getSectionId(el, index) {
			if (el.id) {
				return el.id;
			}
			var elementorId = el.getAttribute('data-id');
			if (elementorId) {
				return 'elementor-section-' + elementorId;
			}
			var name = el.getAttribute('data-metricas-section') || el.getAttribute('data-settings');
			if (name) {
				try {
					var parsed = JSON.parse(name);
					if (parsed && parsed._element_id) {
						return parsed._element_id;
					}
				} catch (err) {
					return 'section-' + name.substring(0, 30);
				}
			}
			return 'section-' + index;
		}

		function getSectionName(el) {
			var heading = el.querySelector('h1, h2, h3, .elementor-heading-title');
			if (heading && heading.textContent) {
				return heading.textContent.trim().substring(0, 100);
			}
			var aria = el.getAttribute('aria-label');
			if (aria) {
				return aria.substring(0, 100);
			}
			return getSectionId(el, 0);
		}

		function getElementorSectionId(el) {
			var section = el.classList.contains('elementor-section')
				? el
				: el.closest('.elementor-section[data-id]');
			return section ? section.getAttribute('data-id') || '' : '';
		}

		function flushSection(sectionKey) {
			var data = activeSections[sectionKey];
			if (!data || !data.visibleSince) {
				return;
			}

			var duration = Math.floor((Date.now() - data.visibleSince) / 1000);
			delete data.visibleSince;

			if (duration < minTime) {
				return;
			}

			sendData('section', {
				post_id: config.postId,
				section_id: data.id,
				section_name: data.name,
				selector: data.selector,
				elementor_section_id: data.elementorId,
				duration_seconds: duration,
				session_id: config.sessionId
			});
		}

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					var el = entry.target;
					var key = el.dataset.metricasKey;

					if (entry.isIntersecting && entry.intersectionRatio >= 0.25) {
						if (!activeSections[key].visibleSince) {
							activeSections[key].visibleSince = Date.now();
						}
					} else if (activeSections[key] && activeSections[key].visibleSince) {
						flushSection(key);
					}
				});
			},
			{ threshold: [0, 0.25, 0.5, 0.75, 1] }
		);

		sections.forEach(function (el, index) {
			var key = 's-' + index;
			el.dataset.metricasKey = key;

			activeSections[key] = {
				id: getSectionId(el, index),
				name: getSectionName(el),
				selector: el.className ? '.' + el.className.split(' ')[0] : 'section',
				elementorId: getElementorSectionId(el),
				el: el
			};

			observer.observe(el);
		});

		// Enviar tiempo acumulado al salir.
		function flushAll() {
			Object.keys(activeSections).forEach(flushSection);
		}

		document.addEventListener('visibilitychange', function () {
			if (document.visibilityState === 'hidden') {
				flushAll();
			}
		});

		window.addEventListener('pagehide', flushAll);
		window.addEventListener('beforeunload', flushAll);
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
		trackSections();
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
