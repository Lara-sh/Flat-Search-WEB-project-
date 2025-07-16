<?php
$role = $_SESSION['user']['role'] ?? null;
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
    <?php if ($role === 'customer'): ?>
        <a href="search.php" class="nav-link <?= $currentPage === 'search.php' ? 'active' : '' ?>">🏠 Home</a>
        <a href="search.php" class="nav-link <?= $currentPage === 'search.php' ? 'active' : '' ?>">🔎 Flat Search</a>
        <a href="view_rented.php" class="nav-link <?= $currentPage === 'view_rented.php' ? 'active' : '' ?>">🏡 View Rented Flats</a>
        <a href="view_messages.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">💬 View Messages</a>
        <a href="about.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">🏢🌟 About Us</a>
        <a href="login.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">🔐 Login</a>
        <a href="logout.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">🔓 Logout</a>
        <a href="login.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">➕👤 Sign Up</a>
    <?php elseif ($role === 'owner'): ?>
        <a href="search.php" class="nav-link <?= $currentPage === 'search.php' ? 'active' : '' ?>">🏠 Home</a>
         <a href="search.php" class="nav-link <?= $currentPage === 'search.php' ? 'active' : '' ?>">🔎 Flat Search</a>
        <a href="offer_flat.php" class="nav-link <?= $currentPage === 'offer_flat.php' ? 'active' : '' ?>">📢 Offer Flat for Rent</a>
        <a href="view_messages.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">💬 View Messages</a>
        <a href="about.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">🏢🌟 About Us</a>
        <a href="login.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">🔐 Login</a>
        <a href="logout.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">🔓 Logout</a>
        <a href="login.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">➕👤 Sign Up</a>
       
    <?php elseif ($role === 'manager'): ?>
        <a href="search.php" class="nav-link <?= $currentPage === 'search.php' ? 'active' : '' ?>">🏠 Home</a>
         <a href="search.php" class="nav-link <?= $currentPage === 'search.php' ? 'active' : '' ?>">🔎 Flat Search</a>
        <a href="flats_inquire.php" class="nav-link <?= $currentPage === 'flats_inquire.php' ? 'active' : '' ?>">❓ Flats Inquire</a>
        <a href="view_messages.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">💬 View Messages</a>
        <a href="about.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">🏢🌟 About Us</a>
        <a href="login.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">🔐 Login</a>
        <a href="logout.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">🔓 Logout</a>
        <a href="login.php" class="nav-link <?= $currentPage === 'view_messages.php' ? 'active' : '' ?>">➕👤 Sign Up</a>

    <?php else: ?>
        <a href="login.php" class="nav-link <?= $currentPage === 'login.php' ? 'active' : '' ?>">🔐 Login</a>
    <?php endif; ?>
</nav>