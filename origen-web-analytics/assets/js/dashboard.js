(function () {
	'use strict';

	if (typeof wpMetricasDashboard === 'undefined' || typeof Chart === 'undefined') {
		return;
	}

	var config = wpMetricasDashboard;
	var visitsChart = null;
	var clicksChart = null;
	var typesChart = null;
	var countriesChart = null;
	var realtimeInterval = null;

	var els = {
		dateFrom: document.getElementById('metricas-date-from'),
		dateTo: document.getElementById('metricas-date-to'),
		typeFilter: document.getElementById('metricas-type-filter'),
		applyBtn: document.getElementById('metricas-apply-filters'),
		clearBtn: document.getElementById('metricas-clear-filters'),
		loading: document.getElementById('metricas-loading'),
		activeVisitors: document.getElementById('metricas-active-visitors'),
		totalVisits: document.getElementById('metricas-total-visits'),
		totalClicks: document.getElementById('metricas-total-clicks'),
		uniqueSessions: document.getElementById('metricas-unique-sessions'),
		topContent: document.getElementById('metricas-top-content'),
		topButtons: document.getElementById('metricas-top-buttons'),
		pageTimes: document.getElementById('metricas-page-times'),
		topCities: document.getElementById('metricas-top-cities')
	};

	function formatDuration(seconds) {
		seconds = parseInt(seconds, 10) || 0;
		if (seconds < 60) {
			return seconds + ' ' + config.i18n.seconds;
		}
		var mins = Math.floor(seconds / 60);
		var secs = seconds % 60;
		return mins + ' ' + config.i18n.minutes + (secs ? ' ' + secs + 's' : '');
	}

	function showLoading(show) {
		if (els.loading) {
			els.loading.style.display = show ? 'block' : 'none';
		}
	}

	function buildQuery() {
		var params = new URLSearchParams();
		if (els.dateFrom && els.dateFrom.value) {
			params.set('date_from', els.dateFrom.value);
		}
		if (els.dateTo && els.dateTo.value) {
			params.set('date_to', els.dateTo.value);
		}
		if (els.typeFilter && els.typeFilter.value) {
			params.set('type', els.typeFilter.value);
		}
		return params.toString();
	}

	function fetchStats() {
		showLoading(true);
		var url = config.restUrl + '?' + buildQuery();

		return fetch(url, {
			headers: {
				'X-WP-Nonce': config.nonce
			},
			credentials: 'same-origin'
		})
			.then(function (res) {
				if (!res.ok) {
					throw new Error('Error fetching stats');
				}
				return res.json();
			})
			.then(renderStats)
			.catch(function () {
				if (els.topContent) {
					els.topContent.innerHTML = '<tr><td colspan="4">' + config.i18n.noData + '</td></tr>';
				}
			})
			.finally(function () {
				showLoading(false);
			});
	}

	function renderStats(data) {
		if (els.totalVisits) {
			els.totalVisits.textContent = (data.summary && data.summary.total_visits) || 0;
		}
		if (els.totalClicks) {
			els.totalClicks.textContent = (data.summary && data.summary.total_clicks) || 0;
		}
		if (els.uniqueSessions) {
			els.uniqueSessions.textContent = (data.summary && data.summary.unique_sessions) || 0;
		}

		renderVisitsChart(data.visits_by_day || []);
		renderClicksChart(data.clicks_by_day || []);
		renderTypesChart(data.visits_by_type || []);
		renderCountriesChart(data.visits_by_country || []);
		renderTopContent(data.top_content || []);
		renderTopButtons(data.top_buttons || []);
		renderTopCities(data.visits_by_city || []);
		renderPageTimes(data.page_times || []);
	}

	function renderVisitsChart(rows) {
		var canvas = document.getElementById('metricas-visits-chart');
		if (!canvas) {
			return;
		}

		var labels = rows.map(function (r) { return r.date_label; });
		var values = rows.map(function (r) { return parseInt(r.total, 10); });

		if (visitsChart) {
			visitsChart.destroy();
		}

		visitsChart = new Chart(canvas, {
			type: 'line',
			data: {
				labels: labels,
				datasets: [{
					label: config.i18n.visits,
					data: values,
					borderColor: '#6366f1',
					backgroundColor: 'rgba(99, 102, 241, 0.1)',
					fill: true,
					tension: 0.3
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: { legend: { display: false } },
				scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
			}
		});
	}

	function renderClicksChart(rows) {
		var canvas = document.getElementById('metricas-clicks-chart');
		if (!canvas) {
			return;
		}

		var labels = rows.map(function (r) { return r.date_label; });
		var values = rows.map(function (r) { return parseInt(r.total, 10); });

		if (clicksChart) {
			clicksChart.destroy();
		}

		clicksChart = new Chart(canvas, {
			type: 'bar',
			data: {
				labels: labels,
				datasets: [{
					label: config.i18n.clicks,
					data: values,
					backgroundColor: '#10b981'
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: { legend: { display: false } },
				scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
			}
		});
	}

	function renderTypesChart(rows) {
		var canvas = document.getElementById('metricas-types-chart');
		if (!canvas) {
			return;
		}

		var labels = rows.map(function (r) { return r.post_type; });
		var values = rows.map(function (r) { return parseInt(r.total, 10); });

		if (typesChart) {
			typesChart.destroy();
		}

		typesChart = new Chart(canvas, {
			type: 'doughnut',
			data: {
				labels: labels,
				datasets: [{
					data: values,
					backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4']
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false
			}
		});
	}

	function renderCountriesChart(rows) {
		var canvas = document.getElementById('metricas-countries-chart');
		if (!canvas) {
			return;
		}

		var labels = rows.map(function (r) {
			return r.country_name || r.country_code || '—';
		});
		var values = rows.map(function (r) { return parseInt(r.total, 10); });

		if (countriesChart) {
			countriesChart.destroy();
		}

		countriesChart = new Chart(canvas, {
			type: 'bar',
			data: {
				labels: labels,
				datasets: [{
					label: config.i18n.countries,
					data: values,
					backgroundColor: '#0ea5e9'
				}]
			},
			options: {
				indexAxis: 'y',
				responsive: true,
				maintainAspectRatio: false,
				plugins: { legend: { display: false } },
				scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
			}
		});
	}

	function renderTopCities(rows) {
		if (!els.topCities) {
			return;
		}

		if (!rows.length) {
			els.topCities.innerHTML = '<tr><td colspan="4">' + config.i18n.noData + '</td></tr>';
			return;
		}

		els.topCities.innerHTML = rows.map(function (row, i) {
			return '<tr>' +
				'<td>' + (i + 1) + '</td>' +
				'<td>' + escapeHtml(row.city || '—') + '</td>' +
				'<td>' + escapeHtml(row.country_name || row.country_code || '—') + '</td>' +
				'<td><strong>' + parseInt(row.total, 10) + '</strong></td>' +
				'</tr>';
		}).join('');
	}

	function renderTopContent(rows) {
		if (!els.topContent) {
			return;
		}

		if (!rows.length) {
			els.topContent.innerHTML = '<tr><td colspan="4">' + config.i18n.noData + '</td></tr>';
			return;
		}

		els.topContent.innerHTML = rows.map(function (row, i) {
			return '<tr>' +
				'<td>' + (i + 1) + '</td>' +
				'<td>' + escapeHtml(row.post_title || '—') + '</td>' +
				'<td>' + escapeHtml(row.post_type || '') + '</td>' +
				'<td><strong>' + parseInt(row.visits, 10) + '</strong></td>' +
				'</tr>';
		}).join('');
	}

	function renderTopButtons(rows) {
		if (!els.topButtons) {
			return;
		}

		if (!rows.length) {
			els.topButtons.innerHTML = '<tr><td colspan="4">' + config.i18n.noData + '</td></tr>';
			return;
		}

		els.topButtons.innerHTML = rows.map(function (row, i) {
			var text = row.button_text || row.button_url || row.elementor_widget_id || '—';
			return '<tr>' +
				'<td>' + (i + 1) + '</td>' +
				'<td>' + escapeHtml(text) + '</td>' +
				'<td>' + escapeHtml(row.button_url || '—') + '</td>' +
				'<td><strong>' + parseInt(row.clicks, 10) + '</strong></td>' +
				'</tr>';
		}).join('');
	}

	function renderPageTimes(rows) {
		if (!els.pageTimes) {
			return;
		}

		if (!rows.length) {
			els.pageTimes.innerHTML = '<tr><td colspan="5">' + config.i18n.noData + '</td></tr>';
			return;
		}

		els.pageTimes.innerHTML = rows.map(function (row, i) {
			return '<tr>' +
				'<td>' + (i + 1) + '</td>' +
				'<td>' + escapeHtml(row.post_title || '—') + '</td>' +
				'<td>' + escapeHtml(row.post_type || '—') + '</td>' +
				'<td>' + formatDuration(row.avg_seconds) + '</td>' +
				'<td>' + formatDuration(row.total_seconds) + '</td>' +
				'</tr>';
		}).join('');
	}

	function escapeHtml(str) {
		var div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}

	if (els.applyBtn) {
		els.applyBtn.addEventListener('click', fetchStats);
	}

	if (els.clearBtn) {
		els.clearBtn.addEventListener('click', function () {
			if (els.dateFrom && config.defaultDateFrom) {
				els.dateFrom.value = config.defaultDateFrom;
			}
			if (els.dateTo && config.defaultDateTo) {
				els.dateTo.value = config.defaultDateTo;
			}
			if (els.typeFilter) {
				els.typeFilter.value = 'all';
			}
			fetchStats();
		});
	}

	function fetchRealtime() {
		if (!config.realtimeUrl) {
			return;
		}

		fetch(config.realtimeUrl, {
			headers: {
				'X-WP-Nonce': config.nonce
			},
			credentials: 'same-origin'
		})
			.then(function (res) {
				if (!res.ok) {
					throw new Error('Error');
				}
				return res.json();
			})
			.then(function (data) {
				if (els.activeVisitors) {
					els.activeVisitors.textContent = data.active_visitors || 0;
				}
			})
			.catch(function () {
				// Silenciar errores de polling.
			});
	}

	function startRealtimePolling() {
		fetchRealtime();
		realtimeInterval = setInterval(fetchRealtime, config.realtimeInterval || 15000);
	}

	document.addEventListener('visibilitychange', function () {
		if (document.visibilityState === 'visible') {
			fetchRealtime();
		}
	});

	fetchStats();
	startRealtimePolling();
})();
