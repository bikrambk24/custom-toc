jQuery(document).ready(function($) {
    function scrollToElement(target) {
        var offset = 150;
        var targetPosition = $(target).offset().top - offset;
        $('html, body').animate({
            scrollTop: targetPosition
        }, 500);
    }

    $('.ctoc-h2-link').click(function(e) {
        e.preventDefault();
        var $h2Item = $(this).parent(); // Parent .ctoc-h2 element
        var $h3List = $h2Item.children('.ctoc-h3-list');
        var $toggle = $(this).find('.ctoc-toggle');

        // Remove 'active' class from all links and their parent .ctoc-h2
        $('.ctoc-h2-link').removeClass('active');
        $('.ctoc-h2').removeClass('active');

        // Add 'active' class to the clicked .ctoc-h2-link and its parent .ctoc-h2
        $(this).addClass('active');
        $h2Item.addClass('active'); // Adding 'active' class to the parent .ctoc-h2

        // If a toggle exists, slide the H3 list, else scroll to target
        if ($toggle.length) {
            e.stopPropagation(); // Prevent scrolling if toggle is clicked
            $h3List.slideToggle();
            $toggle.text($toggle.text() === '+' ? '−' : '+');
        } else {
            var target = $(this).attr('href');
            scrollToElement(target);
        }
    });

    $('.ctoc-h3 > a').click(function(e) {
        e.preventDefault();
        $('.ctoc-h2-link, .ctoc-h3 > a').removeClass('active');
        $(this).addClass('active');
        var target = $(this).attr('href');
        scrollToElement(target);
    });

    function updateScrollbar() {
        var $toc = $('.ctoc-wrapper');
        var $list = $('.ctoc-list');
        $list.css('min-height', '');
        var tocHeight = $toc.height();
        var listHeight = $list.outerHeight(true);
        if (listHeight <= tocHeight) {
            $list.css('min-height', tocHeight + 'px');
        }
    }

    updateScrollbar();
    $(window).on('resize', updateScrollbar);

    // Update scrollbar after clicking .ctoc-h2-link
    $('.ctoc-h2-link').on('click', function() {
        setTimeout(updateScrollbar, 400);
    });
});