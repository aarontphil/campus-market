</main>

<!-- ============ Footer ============ -->
<footer class="site-footer mt-auto">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4">
                <h5><i class="fas fa-store me-2"></i>Campus<strong>Market</strong></h5>
                <p class="text-secondary small">A dedicated campus marketplace where students can list second-hand products, browse available items, search by category, and negotiate prices.</p>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="footer-heading">Navigate</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="/campus-marketplace/">Home</a></li>
                    <li><a href="/campus-marketplace/browse.php">Browse</a></li>
                    <li><a href="/campus-marketplace/dashboard.php">Dashboard</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-4">
                <h6 class="footer-heading">Account</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="/campus-marketplace/my-listings.php">My Listings</a></li>
                    <li><a href="/campus-marketplace/my-offers.php">My Offers</a></li>
                    <li><a href="/campus-marketplace/profile.php">Profile</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-4">
                <h6 class="footer-heading">Categories</h6>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-secondary bg-opacity-25 text-light">Textbooks</span>
                    <span class="badge bg-secondary bg-opacity-25 text-light">Electronics</span>
                    <span class="badge bg-secondary bg-opacity-25 text-light">Hostel</span>
                    <span class="badge bg-secondary bg-opacity-25 text-light">Furniture</span>
                    <span class="badge bg-secondary bg-opacity-25 text-light">Clothing</span>
                    <span class="badge bg-secondary bg-opacity-25 text-light">Sports</span>
                </div>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="text-center text-secondary small">
            &copy; <?= date('Y') ?> Campus Marketplace &mdash; Web Programming Project. Built with <i class="fas fa-heart text-danger"></i> by Students.
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- App JS -->
<script>
    const BASE = '/campus-marketplace/';
    const CSRF = '<?= csrfToken() ?>';
</script>
<script src="/campus-marketplace/assets/js/app.js"></script>
</body>
</html>
