document.addEventListener('DOMContentLoaded', () => {
    const hotspots = document.querySelectorAll('.e-hotspot');

    hotspots.forEach(hotspot => {
        hotspot.addEventListener('mouseenter', () => {
            hotspot.classList.add('e-hotspot--active');
        });

        hotspot.addEventListener('mouseleave', () => {
            hotspot.classList.remove('e-hotspot--active');
        });
    });
});
