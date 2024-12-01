/**
 * PerformancePlus Public JavaScript
 * This file contains JavaScript for public-facing interactions of the plugin.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Notification close button functionality
    const notifications = document.querySelectorAll('.performanceplus-notification');
    notifications.forEach(notification => {
        const closeButton = document.createElement('span');
        closeButton.innerText = '×';
        closeButton.style.cursor = 'pointer';
        closeButton.style.marginLeft = '10px';
        closeButton.style.fontWeight = 'bold';
        closeButton.onclick = function () {
            notification.style.display = 'none';
        };
        notification.appendChild(closeButton);
    });
});
