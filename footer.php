        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Task Management System. All rights reserved.</p>
            <p>ITCC1023 - Web Systems and Technologies I</p>
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <p style="font-size: 12px; margin-top: 5px; color: #a0aec0;">
                    Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?>
                    (<?php echo ucfirst($_SESSION['role']); ?>)
                </p>
            <?php endif; ?>
        </footer>
        </div>
        </body>

        </html>