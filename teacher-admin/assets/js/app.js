$(function() {
	"use strict";

	// Search bar toggling
	$(".mobile-search-icon").on("click", function() {
		$(".search-bar").addClass("full-search-bar");
	});
	$(".search-close").on("click", function() {
		$(".search-bar").removeClass("full-search-bar");
	});

	// Function to toggle sidebar reliably on all screen sizes
	function toggleSidebar() {
		if ($(".wrapper").hasClass("toggled")) {
			$(".wrapper").removeClass("toggled");
			$(".sidebar-wrapper").off("mouseenter mouseleave");
		} else {
			$(".wrapper").addClass("toggled");
			if ($(window).width() > 1024) {
				$(".sidebar-wrapper").on("mouseenter", function() {
					$(".wrapper").addClass("sidebar-hovered");
				}).on("mouseleave", function() {
					$(".wrapper").removeClass("sidebar-hovered");
				});
			}
		}
	}

	function closeSidebarMobile() {
		if ($(window).width() <= 1024) {
			$(".wrapper").removeClass("toggled");
		}
	}

	// Topbar hamburger menu click
	$(".mobile-toggle-menu").on("click", function(e) {
		e.stopPropagation();
		toggleSidebar();
	});

	// Sidebar header toggle / close icon
	$(".sidebar-header .toggle-icon").on("click", function(e) {
		e.stopPropagation();
		toggleSidebar();
	});

	// Overlay backdrop click or tap to close
	$(".overlay").on("click touchstart", function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(".wrapper").removeClass("toggled");
	});

	// Auto-close sidebar on mobile when navigating (clicking any non-dropdown link)
	$(".sidebar-wrapper .metismenu a:not(.has-arrow)").on("click", function() {
		closeSidebarMobile();
	});

	// Auto-close sidebar on mobile when clicking brand logo
	$(".sidebar-header a").on("click", function() {
		closeSidebarMobile();
	});

	// Close on Escape key
	$(document).on("keydown", function(e) {
		if (e.key === "Escape" && $(".wrapper").hasClass("toggled") && $(window).width() <= 1024) {
			$(".wrapper").removeClass("toggled");
		}
	});

	// Handle window resize cleanly: reset toggled when crossing into desktop
	$(window).on("resize", function() {
		if ($(window).width() > 1024) {
			if (!$(".wrapper").hasClass("sidebar-hovered")) {
				$(".wrapper").removeClass("toggled");
				$(".sidebar-wrapper").off("mouseenter mouseleave");
			}
		}
	});

	// Scroll to top button
	$(window).on("scroll", function() {
		$(this).scrollTop() > 300 ? $(".back-to-top").fadeIn() : $(".back-to-top").fadeOut();
	});
	$(".back-to-top").on("click", function() {
		$("html, body").animate({
			scrollTop: 0
		}, 600);
		return false;
	});

	// Initialize MetisMenu
	$("#menu").metisMenu();

});
