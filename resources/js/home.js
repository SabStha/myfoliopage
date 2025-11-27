document.addEventListener('DOMContentLoaded', function() {
    // Typed.js Effect
    if (document.getElementById('typed-output')) {
        var typed = new Typed('#typed-output', {
            strings: [
                "Developer",
                "Designer",
                "Creator",
                "Problem Solver"
            ],
            typeSpeed: 50,
            backSpeed: 30,
            backDelay: 2000,
            loop: true
        });
    }

    // Profile Image Rotation
    const profileImages = window.profileImages || [];
    let currentImageIndex = 0;
    const profileImageElement = document.getElementById('profile-image');

    if (profileImages.length > 1 && profileImageElement) {
        setInterval(() => {
            currentImageIndex = (currentImageIndex + 1) % profileImages.length;
            profileImageElement.style.opacity = '0';
            
            setTimeout(() => {
                profileImageElement.src = profileImages[currentImageIndex];
                profileImageElement.style.opacity = '1';
            }, 500);
        }, 5000);
    }
});
