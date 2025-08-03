(function ($) {
	if (!$ || typeof $ !== "function") return;

	if (!window.debiki) window.debiki = {};
	if (!debiki.Utterscroll) debiki.Utterscroll = {};

	debiki.Utterscroll = (function () {
		let enabled = false;
		let startPos, lastPos;
		let $elemToScroll;
		const scrollstoppers =
			"a, area, button, input, select, textarea, .no-scroll, .noscroll, .MuiSlider-root, .MuiDialog-root, .picker-item";

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

		return {
			enable: function () {
				enabled = true;
			},
			disable: function () {
				enabled = false;
			},
			isEnabled: function () {
				return enabled;
			},
			isScrolling: function () {
				return !!startPos;
			},
		};
	})();

	if (!("ontouchstart" in window || navigator.maxTouchPoints > 0)) {
		debiki.Utterscroll.enable();

		document.documentElement.style.cursor = "grab";
	}
})(window.jQuery);
