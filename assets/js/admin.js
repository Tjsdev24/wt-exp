$(document).ready(function() {
    
    // ==========================================
    // FEATURE 1: Animated Number Counters
    // ==========================================
    $('.counter').each(function () {
        const $this = $(this);
        const targetValue = parseInt($this.attr('data-target'), 10);
        
        // Animate from 0 to the target value
        $({ Counter: 0 }).animate({ Counter: targetValue }, {
            duration: 1500,
            easing: 'swing',
            step: function (now) {
                // Add commas for thousands using regex
                $this.text(Math.ceil(now).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            }
        });
    });

    // ==========================================
    // FEATURE 2: Listing Status Filter
    // ==========================================
    $('.filter-btn').on('click', function() {
        // Manage active state of buttons
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');

        const filterValue = $(this).attr('data-filter');

        if (filterValue === 'all') {
            // Show all rows
            $('.table-row').fadeIn(300);
        } else {
            // Hide rows that don't match, show ones that do
            $('.table-row').each(function() {
                if ($(this).attr('data-status') === filterValue) {
                    $(this).fadeIn(300);
                } else {
                    $(this).hide();
                }
            });
        }
    });

    // ==========================================
    // FEATURE 3: Dismissible Report Cards
    // ==========================================
    $('.dismiss-report').on('click', function(e) {
        e.preventDefault();
        
        const $card = $(this).closest('.report-card');
        const reportTitle = $card.find('h4').text();

        // Animate the card out of view
        $card.slideUp(300, function() {
            $(this).remove(); // Remove from DOM entirely
            
            // Check if there are no more reports
            if ($('.report-card').length === 0) {
                $card.parent().append('<p style="color: var(--text-muted); margin-top:1rem;">All caught up! No active reports.</p>');
            }
        });

        // Trigger a toast notification
        showToast(`Report resolved: ${reportTitle}`);
    });

    // ==========================================
    // FEATURE 4: Toast Notification System
    // ==========================================
    function showToast(message) {
        // Create the toast element
        const $toast = $(`<div class="toast">${message}</div>`);
        
        // Append to container and show
        $('#toast-container').append($toast);
        $toast.fadeIn(200);

        // Remove toast after 3 seconds
        setTimeout(function() {
            $toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Attach toast to the main review button for demonstration
    $('#review-btn').on('click', function(e) {
        e.preventDefault();
        showToast("Fetching pending items queue...");
    });

    // ==========================================
    // FEATURE 5: Provider Approval Actions
    // ==========================================
    $('.approve-user, .reject-user').on('click', function() {
        const userId = $(this).attr('data-id');
        const action = $(this).hasClass('approve-user') ? 'approve' : 'reject';
        const $row = $(this).closest('.table-row');

        $.ajax({
            url: 'backend/update_user_status.php',
            type: 'POST',
            data: { user_id: userId, action: action },
            success: function(res) {
                if (res.status === 'success') {
                    showToast(res.message);
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        if ($('#pending-providers-list .table-row').length === 0) {
                            $('#pending-providers-list').append('<p style="color: var(--text-muted); padding: 1rem;">No providers waiting for approval.</p>');
                        }
                    });
                } else {
                    alert(res.message);
                }
            }
        });
    });

});