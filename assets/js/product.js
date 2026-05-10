$(document).ready(function() {

    // ==========================================
    // FEATURE 2: Interactive Image Gallery
    // ==========================================
    $('.thumb-img').on('click', function() {
        // Manage active state on thumbnails
        $('.thumb-img').removeClass('active');
        $(this).addClass('active');

        // Fetch the large image URL from data attribute
        const newSrc = $(this).attr('data-large');
        
        // Add a quick fade effect for smooth transition
        const $mainImg = $('#main-image');
        $mainImg.css('opacity', 0.5);
        
        setTimeout(() => {
            $mainImg.attr('src', newSrc).css('opacity', 1);
        }, 150);
    });

    // ==========================================
    // FEATURE 3: Smart Date Constraints
    // ==========================================
    // Format date to YYYY-MM-DD for the input fields
    const today = new Date().toISOString().split('T')[0];
    
    const $startDate = $('#start-date');
    const $endDate = $('#end-date');

    // Prevent selecting dates in the past
    $startDate.attr('min', today);

    // Update the end date's minimum value based on start date
    $startDate.on('change', function() {
        const selectedStart = $(this).val();
        $endDate.attr('min', selectedStart);
        
        // If end date is now before start date, clear it
        if ($endDate.val() && $endDate.val() < selectedStart) {
            $endDate.val('');
            $('.total-breakdown').slideUp(200);
        } else {
            calculatePrice();
        }
    });

    $endDate.on('change', function() {
        calculatePrice();
    });

    // ==========================================
    // FEATURE 1: Dynamic Price Calculator
    // ==========================================
    const dailyRate = parseInt($('#daily-rate').text(), 10);
    const serviceFee = 5; // Flat Rs. 5 service fee

    function calculatePrice() {
        const start = $startDate.val();
        const end = $endDate.val();

        // Only calculate if both dates are present
        if (start && end) {
            const startDateObj = new Date(start);
            const endDateObj = new Date(end);
            
            // Calculate difference in time, then convert to days (inclusive)
            const timeDiff = endDateObj.getTime() - startDateObj.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;

            const subtotal = daysDiff * dailyRate;
            const total = subtotal + serviceFee;

            // Update DOM elements
            $('#days-calc').text(`Rs. ${dailyRate} x ${daysDiff} day${daysDiff > 1 ? 's' : ''}`);
            $('#subtotal-calc').text(`Rs. ${subtotal}`);
            $('#total-calc').text(`Rs. ${total}`);

            // Reveal the breakdown smoothly
            $('.total-breakdown').slideDown(300);
        } else {
            $('.total-breakdown').slideUp(200);
        }
    }

    // ==========================================
    // FEATURE 4: Form Validation & Toast System
    // ==========================================
    $('#book-btn').on('click', function(e) {
        e.preventDefault();

        // Check if dates are selected
        if (!$startDate.val() || !$endDate.val()) {
            showToast('Please select your start and end dates.', 'error');
            
            // Add a brief shake effect to the inputs to draw attention
            $('.date-pickers').css('transform', 'translateX(5px)');
            setTimeout(() => $('.date-pickers').css('transform', 'translateX(-5px)'), 100);
            setTimeout(() => $('.date-pickers').css('transform', 'translateX(5px)'), 200);
            setTimeout(() => $('.date-pickers').css('transform', 'translateX(0)'), 300);
            return;
        }

        
        const productId = $('#product-title').data('id');
        const totalText = $('#total-calc').text().replace('Rs. ', '');

        $.ajax({
            url: 'backend/book_product.php',
            type: 'POST',
            data: {
                product_id: productId,
                start_date: $startDate.val(),
                end_date: $endDate.val(),
                total_price: totalText
            },
            success: function(response) {
                let res = typeof response === 'string' ? JSON.parse(response) : response;
                if (res.status === 'success') {
                    showToast('Booking request sent successfully!', 'success');
                    $('#book-btn')
                        .text('Request Sent')
                        .css({
                            'background-color': '#10b981', // Green
                            'pointer-events': 'none',
                            'opacity': '0.9'
                        });
                } else {
                    showToast(res.message, 'error');
                }
            },
            error: function() {
                showToast('An error occurred while booking.', 'error');
            }
        });

    });

    // Reusable Toast Function
    function showToast(message, type = 'default') {
        const $toast = $(`<div class="toast ${type}">${message}</div>`);
        $('#toast-container').append($toast);
        $toast.fadeIn(200);

        setTimeout(function() {
            $toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3500);
    }

});