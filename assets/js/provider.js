$(document).ready(function() {

    // ==========================================
    // FEATURE 1: Animated Stats Counters
    // ==========================================
    $('.counter').each(function () {
        const $this = $(this);
        const targetValue = parseFloat($this.attr('data-target'));
        const isCurrency = $this.hasClass('currency');
        const isFloat = $this.hasClass('float'); // For the 4.8 rating
        
        $({ Counter: 0 }).animate({ Counter: targetValue }, {
            duration: 1500,
            easing: 'swing',
            step: function (now) {
                if (isFloat) {
                    $this.text(now.toFixed(1));
                } else {
                    let formattedNum = Math.ceil(now).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    $this.text(isCurrency ? 'Rs. ' + formattedNum : formattedNum);
                }
            },
            complete: function() {
                // Ensure exact final number
                if (isFloat) {
                    $this.text(targetValue.toFixed(1));
                } else {
                    let formattedNum = targetValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    $this.text(isCurrency ? 'Rs. ' + formattedNum : formattedNum);
                }
            }
        });
    });



    // ==========================================
    // FEATURE 3: Listing Visibility Toggle
    // ==========================================
    $('.visibility-toggle').on('change', function() {
        const $listingRow = $(this).closest('.listing-item');
        const itemName = $(this).attr('data-item');

        if ($(this).is(':checked')) {
            $listingRow.removeClass('paused');
            showToast(`${itemName} is now live and visible.`);
        } else {
            $listingRow.addClass('paused');
            showToast(`${itemName} has been paused.`);
        }
    });

    // ==========================================
    // FEATURE 4: Interactive Availability Calendar
    // ==========================================
    const $calGrid = $('#availability-grid');
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    
    // Generate the mini calendar days
    days.forEach(day => {
        $calGrid.append(`<div class="cal-day" data-day="${day}">${day}</div>`);
    });

    // Handle blocking/unblocking a day
    $('.cal-day').on('click', function() {
        $(this).toggleClass('blocked');
        const dayName = $(this).attr('data-day');
        
        if ($(this).hasClass('blocked')) {
            showToast(`Rentals blocked for upcoming ${dayName}days`);
        } else {
            showToast(`${dayName}days are now open for booking`);
        }
    });

    // ==========================================
    // TOAST NOTIFICATION SYSTEM
    // ==========================================
    function showToast(message) {
        const $toast = $(`<div class="toast">${message}</div>`);
        $('#toast-container').append($toast);
        $toast.fadeIn(200);

        setTimeout(function() {
            $toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }



});