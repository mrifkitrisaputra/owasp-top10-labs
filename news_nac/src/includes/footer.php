    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3><i class="fas fa-scroll"></i> Nac News</h3>
                    <p>Your trusted source for technology, science, and world news. We illuminate truth through rigorous journalism.</p>
                    <div class="social-links">
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" title="RSS Feed"><i class="fas fa-rss"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Categories</h3>
                    <ul class="footer-links">
                        <?php
                        $footerCats = getCategories();
                        foreach ($footerCats as $cat):
                        ?>
                        <li><a href="/category.php?slug=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="/page.php?page=about.html">About Us</a></li>
                        <li><a href="/page.php?page=privacy.html">Privacy Policy</a></li>
                        <li><a href="/page.php?page=terms.html">Terms of Service</a></li>
                        <li><a href="/contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Newsletter</h3>
                    <p>Stay updated with our latest news delivered to your inbox.</p>
                    <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');">
                        <input type="email" placeholder="Your email address" required>
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                    <div class="footer-contact">
                        <p><i class="fas fa-map-marker-alt"></i> 1247 Library Avenue, Boston</p>
                        <p><i class="fas fa-envelope"></i> contact@nac-news.com</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Nac News. All rights reserved.</p>
                <p>Powered by Nac CMS v2.1.4</p>
            </div>
        </div>
    </footer>

    <script src="/assets/js/main.js"></script>
</body>
</html>
