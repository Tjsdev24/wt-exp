$(document).ready(function() {
    
    // Handle Signup Form
    $('#signupForm').on('submit', function(e) {
        e.preventDefault(); // Stop default form submission
        
        $.ajax({
            url: 'backend/signup_process.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.trim() === "success") {
                    $('#signupStatus').css('color', '#00ffaa').text('Account created! Redirecting...').fadeIn();
                    setTimeout(function() { window.location.href = "login.php"; }, 2000);
                } else {
                    $('#signupStatus').css('color', '#ff003c').text(response).fadeIn();
                }
            }
        });
    });

    // Handle Login Form
    $('#loginForm').on('submit', function(e) {
        e.preventDefault(); // Stop default form submission
        
        $.ajax({
            url: 'backend/login_process.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                let res = response.trim();
                if(res.startsWith("success_")) {
                    let role = res.split("_")[1];
                    $('#loginStatus').css('color', '#00ffaa').text('Access granted. Redirecting...').fadeIn();
                    
                    setTimeout(function() { 
                        if (role === 'admin') {
                            window.location.href = "admin.php";
                        } else if (role === 'provider') {
                            window.location.href = "provider-dashboard.php";
                        } else {
                            window.location.href = "explore.php";
                        }
                    }, 1500);
                } else {
                    $('#loginStatus').css('color', '#ff003c').text(response).fadeIn();
                }
            }
        });
    });
});