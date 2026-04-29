document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const mainContainer = document.querySelector('.body');

    function triggerGoogleTilt() {
        // Prevent overlapping animations
        if (!mainContainer.classList.contains('animate-67')) {
            mainContainer.classList.add('animate-67');
            
            // Remove after 1.8s so it can be triggered again
            setTimeout(() => {
                mainContainer.classList.remove('animate-67');
            }, 1800);
        }
    }

    // Trigger on typing exactly '67'
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (this.value === '67') {
                triggerGoogleTilt();
            }
        });
    }

    // Trigger on load if an order number ends in 67
    const firstColumnCells = document.querySelectorAll('table td:first-child');
    let has67 = false;
    firstColumnCells.forEach(cell => {
        if (cell.textContent.trim().endsWith('67')) {
            has67 = true;
        }
    });

    if (has67) {
        triggerGoogleTilt();
    }
});