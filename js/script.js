/**
 * Library Management System - Client Script
 * Semester V Web Development Mini Project
 */

document.addEventListener("DOMContentLoaded", function () {
    // Generic confirmation handler for action buttons with data-confirm
    document.querySelectorAll("[data-confirm]").forEach(function (el) {
        el.addEventListener("click", function (e) {
            if (!confirm(el.getAttribute("data-confirm") || "Are you sure?")) {
                e.preventDefault();
            }
        });
    });
});
