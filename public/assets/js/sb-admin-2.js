(function($) {
  "use strict"; // Start of use strict

  // Toggle the side navigation
  $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // Close any open menu accordions when window is resized below 768px
  $(window).resize(function() {
    if ($(window).width() < 768) {
      $('.sidebar .collapse').collapse('hide');
    };
    
    // Toggle the side navigation when window is resized below 480px
    if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
      $("body").addClass("sidebar-toggled");
      $(".sidebar").addClass("toggled");
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
  $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
    if ($(window).width() > 768) {
      var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;
      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
      e.preventDefault();
    }
  });

  // Scroll to top button appear
  $(document).on('scroll', function() {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $('.scroll-to-top').fadeIn();
    } else {
      $('.scroll-to-top').fadeOut();
    }
  });

  // Smooth scrolling using jQuery easing
  $(document).on('click', 'a.scroll-to-top', function(e) {
    var $anchor = $(this);
    $('html, body').stop().animate({
      scrollTop: ($($anchor.attr('href')).offset().top)
    }, 1000, 'easeInOutExpo');
    e.preventDefault();
  });

  // Collapsed sidebar-modern flyout menus
  var $sidebarModern = $('#accordionSidebar.sidebar-modern');
  if ($sidebarModern.length) {
    function sidebarModernCollapsed() {
      return $sidebarModern.hasClass('toggled');
    }

    function closeSidebarFlyouts() {
      if (!sidebarModernCollapsed()) {
        return;
      }
      $sidebarModern.find('.collapse.show').collapse('hide');
    }

    function fixSidebarFlyout($panel) {
      if (!sidebarModernCollapsed() || !$panel.length) {
        return;
      }
      $panel.each(function () {
        this.style.setProperty('height', 'auto', 'important');
        this.style.overflow = 'visible';
      });
    }

    if (sidebarModernCollapsed()) {
      closeSidebarFlyouts();
    }

    $('#sidebarToggle, #sidebarToggleTop').on('click', function () {
      window.setTimeout(function () {
        if (sidebarModernCollapsed()) {
          closeSidebarFlyouts();
        }
      }, 0);
    });

    $sidebarModern.on('show.bs.collapse', '.collapse', function () {
      if (!sidebarModernCollapsed()) {
        return;
      }
      $sidebarModern.find('.collapse.show').not(this).collapse('hide');
    });

    $sidebarModern.on('shown.bs.collapse', '.collapse', function () {
      fixSidebarFlyout($(this));
    });

    $(document).on('click', function (event) {
      if (!sidebarModernCollapsed()) {
        return;
      }
      if ($(event.target).closest('#accordionSidebar').length) {
        return;
      }
      closeSidebarFlyouts();
    });
  }

})(jQuery); // End of use strict
