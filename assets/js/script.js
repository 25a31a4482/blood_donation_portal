function validateRegister() {
  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  if (!name || !email || password.length < 6) {
    alert('Please complete the registration form with a valid name, email, and password of at least 6 characters.');
    return false;
  }
  return true;
}

function validateLogin() {
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  if (!email || password.length < 6) {
    alert('Please enter a registered email and password.');
    return false;
  }
  return true;
}

function validateDonor() {
  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const phone = document.getElementById('phone').value.trim();
  const bloodGroup = document.getElementById('blood_group').value;
  const city = document.getElementById('city').value.trim();
  if (!name || !email || !phone || !bloodGroup || !city) {
    alert('Please fill out all donor fields before submitting.');
    return false;
  }
  if (phone.length < 10) {
    alert('Please enter a valid phone number with at least 10 digits.');
    return false;
  }
  return true;
}

function validateRequest() {
  const name = document.getElementById('name').value.trim();
  const phone = document.getElementById('phone').value.trim();
  const bloodGroup = document.getElementById('blood_group').value;
  const city = document.getElementById('city').value.trim();
  if (!name || !phone || !bloodGroup || !city) {
    alert('Please fill in all required fields before submitting your blood request.');
    return false;
  }
  if (phone.length < 10) {
    alert('Please enter a valid phone number with at least 10 digits.');
    return false;
  }
  return true;
}

function toggleNavigation() {
  const nav = document.querySelector('.site-nav');
  if (nav) {
    nav.classList.toggle('open');
  }
}

window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  if (params.get('registered') === '1') {
    alert('Registration successful. Please login to continue.');
  }
  if (params.get('success') === '1') {
    alert('Donor registration successful. Thank you for helping save lives.');
  }

  const toggleButton = document.querySelector('.nav-toggle');
  if (toggleButton) {
    toggleButton.addEventListener('click', toggleNavigation);
  }
});
