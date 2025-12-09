// Test if script loads
alert('login.js loaded');

// ===============================
// OVERLAY PANEL TOGGLE
const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const container = document.getElementById('container');

signUpButton.addEventListener('click', () => {
    container.classList.add("right-panel-active");
});

signInButton.addEventListener('click', () => {
    container.classList.remove("right-panel-active");
});

// ===============================
// SIGNUP FORM SUBMIT
// ===============================
document.getElementById('signupForm').addEventListener('submit', async function(e) {
    e.preventDefault(); // Prevent form submission

    const formData = new FormData(this);

    try {
        const response = await fetch('signup.php', {
            method: 'POST',
            body: formData
        });

        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        // Safely parse JSON
        let data;
        try {
            data = await response.json();
        } catch (jsonError) {
            throw new Error('Invalid JSON response from server');
        }

        // Handle server response
        if (data.status === 'success') {
            alert(data.message);
            console.log('User ID:', data.user_id);
            console.log('QR code path:', data.qr_code);
            // Optionally redirect or reset form
            // window.location.href = 'login.html';
        } else {
            alert('Error: ' + data.message);
        }

    } catch (error) {
        // Catch network errors or JSON parsing errors
        console.error('Signup failed:', error);
        alert('Signup failed. Please try again later.');
    }
});

// ===============================
// LOGIN FORM SUBMIT
// ===============================
const loginForm = document.getElementById('loginForm');
loginForm.addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);

    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);

        if(data.status === 'success'){
            if(data.role === 'admin'){
                window.location.href = '/WEB_DEV/Event_Planner/index.php';
            } else {
                window.location.href = '/WEB_DEV/Event_Planner/user/user_dashboard.php';
            }
        }
    })
    .catch(err => console.error('Login Error:', err));
});
