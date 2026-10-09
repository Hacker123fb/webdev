<?php
/**
 * Common Footer Template
 * Closes the HTML document and includes JavaScript.
 */
if (!isset($base_path)) {
    $base_path = "";
}
?>
    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Library Management System &bull; Semester V Web Development Mini Project</p>
        </div>
    </footer>

    <!-- Core JavaScript & jQuery -->
    <script src="<?php echo htmlspecialchars($base_path); ?>js/jquery.min.js"></script>
    <script src="<?php echo htmlspecialchars($base_path); ?>js/script.js"></script>
</body>
</html>
