<header>
    <link rel="stylesheet" href="style.css">
    <div class="header-left">
        <img src="images/logo.png" alt="Logo" class="logo">
        <span class="agency-name">Lara's Flat Rent</span>
        <a href="about.php" class="about-link">🗂 About Us</a>
        <a href="contact.php" class="about-link">📬 Contact Us</a>
    </div>

    <div class="header-right">
        <?php if (isset($_SESSION['user'])): ?>
           <div class="user-card <?= htmlspecialchars($_SESSION['user']['role']) ?>">
               <?php if (isset($_SESSION['user']['id'], $_SESSION['user']['role'])): ?>
                   <a href="usercard.php?id=<?= urlencode($_SESSION['user']['id']) ?>&role=<?= urlencode($_SESSION['user']['role']) ?>">
                       <img src="<?= htmlspecialchars($_SESSION['user']['photo'] ?? 'images/boy.png') ?>" alt="User Photo" class="user-photo">
                   </a>
               <?php else: ?>
                   <img src="images/boy.png" alt="Default User Photo" class="user-photo">
               <?php endif; ?>
               <span class="user-name"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Guest') ?></span>
           </div>

            <?php if ($_SESSION['user']['role'] == 'customer'): ?>
                <a href="basket.php" class="header-link">🛒 Basket</a>
            <?php endif; ?>

            <a href="logout.php" class="header-link">🔓 Logout</a>
        <?php else: ?>
            <a href="login.php" class="header-link">🔐 Login</a>
            <a href="register.php" class="header-link">➕👤 Sign Up</a>
        <?php endif; ?>
    </div>
</header>
