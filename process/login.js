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
const signupForm = document.getElementById('signupForm');
signupForm.addEventListener('submit', function(e){
    e.preventDefault();
    alert('Signup form submitted');
    const formData = new FormData(this);

    fetch('signup.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        alert('Response status: ' + res.status);
        return res.json();
    })
    .then(data => {
        alert('Response data: ' + JSON.stringify(data));
        alert(data.message);
        if(data.status === 'success'){
            signupForm.reset();
        }
    })
    .catch(err => {
        alert('Signup Error: ' + err);
        alert('An error occurred during signup. Check console for details.');
    });
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
                window.location.href = '../index.php';
            } else {
                window.location.href = '../users/user_dashboard.php';
            }
        }
    })
    .catch(err => console.error('Login Error:', err));
});
