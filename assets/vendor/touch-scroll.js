/*
 * touch-scroll.js -- drag-to-scroll (Utterscroll).
 *
 * Gated on the TOUCH_SCROLL setting, published as window.OrykGui.settings by
 * the inline bootstrap in Oryk_gui::getBootstrapScript().
 *
 * This file is injected into <head> ahead of FreePBX's own bundles, so on load
 * neither window.jQuery nor document.body is guaranteed to exist yet. Boot is
 * deferred until both do.
 */
(function () {
	"use strict";

	function init($) {
		if (!window.debiki) window.debiki = {};

		window.debiki.Utterscroll = (function () {
			let enabled = false;
			let startPos, lastPos;
			let $elemToScroll;
			const scrollstoppers =
				"a, area, button, input, select, textarea, .no-scroll, .noscroll, .MuiSlider-root, .MuiDialog-root, .picker-item";

			// Fills the viewport, so a click past its edges is on browser
			// chrome (a scrollbar) rather than the page. Safe to create now:
			// init() does not run until <body> exists.
			const $viewportGhost = $("<div>", {
				css: {
					width: "100%",
					height: "100%",
					position: "fixed",
					top: 0,
					left: 0,
					zIndex: -999,
				},
			}).appendTo("body");

			function findScrollable($el) {
				return $el
					.closest("*")
					.filter(function () {
						const $t = $(this);
						const y = $t.css("overflow-y"),
							x = $t.css("overflow-x");
						return /(auto|scroll)/.test(y + x);
					})
					.add(window)
					.first();
			}

			$(document).on("mousedown", function (event) {
				if (!enabled || event.which !== 1) return;

				const $target = $(event.target);
				if ($target.closest(scrollstoppers).length) return;

				const ghostOffset = $viewportGhost.offset();
				if (!ghostOffset) return;

				if (
					event.pageX > ghostOffset.left + $viewportGhost.width() ||
					event.pageY > ghostOffset.top + $viewportGhost.height()
				)
					return;

				$elemToScroll = findScrollable($target);
				startPos = { x: event.clientX, y: event.clientY };
				lastPos = { x: event.clientX, y: event.clientY };

				$(document).on("mousemove", doScroll);
				$(document).on("mouseup", stopScroll);
				$("body").css("cursor", "grabbing");

				return false;
			});

			function doScroll(event) {
				const dx = event.clientX - lastPos.x;
				const dy = event.clientY - lastPos.y;

				$elemToScroll.scrollLeft($elemToScroll.scrollLeft() - dx);
				$elemToScroll.scrollTop($elemToScroll.scrollTop() - dy);

				lastPos = { x: event.clientX, y: event.clientY };
				return false;
			}

			function stopScroll() {
				$(document).off("mousemove", doScroll);
				$(document).off("mouseup", stopScroll);
				$("body").css("cursor", "");
				startPos = lastPos = $elemToScroll = null;
				return false;
			}

			// Touchscreens already drag-scroll natively; taking over mousedown
			// there fights the browser instead of helping.
			function isTouchDevice() {
				return "ontouchstart" in window || navigator.maxTouchPoints > 0;
			}

			// Idempotent, so it is safe to call on every settings change.
			function setEnabled(on) {
				enabled = !!on && !isTouchDevice();

				// Drop any drag in progress when switching off.
				if (!enabled) stopScroll();

				document.documentElement.style.cursor = enabled ? "grab" : "";

				return enabled;
			}

			return {
				setEnabled: setEnabled,
				enable: function () {
					return setEnabled(true);
				},
				disable: function () {
					return setEnabled(false);
				},
				isEnabled: function () {
					return enabled;
				},
				isScrolling: function () {
					return !!startPos;
				},
				isSupported: function () {
					return !isTouchDevice();
				},
			};
		})();

		// Default on if the bootstrap has not published settings for some
		// reason -- matches the declared default in Oryk_gui::$sets.
		const settings = (window.OrykGui && window.OrykGui.settings) || {};
		const wanted = "TOUCH_SCROLL" in settings ? settings.TOUCH_SCROLL : true;

		window.debiki.Utterscroll.setEnabled(wanted);
	}

	// Wait for jQuery and <body>, whichever arrives last.
	let booted = false;

	function attempt() {
		if (booted) return true;
		if (!window.jQuery || !document.body) return false;

		booted = true;
		init(window.jQuery);
		return true;
	}

	if (!attempt()) {
		const poll = setInterval(function () {
			if (attempt()) clearInterval(poll);
		}, 50);

		document.addEventListener("DOMContentLoaded", attempt);

		// Give up rather than polling forever if jQuery never shows up.
		setTimeout(function () {
			clearInterval(poll);
		}, 15000);
	}
})();
