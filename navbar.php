<nav>
    <div class="nav-logo">
        <a href="#" id="title">
            <img src="./img/logo.png" alt="Logo Green Mart"/>
        </a>
    </div>

    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="index.php#novita">News</a></li>
        <li><a href="index.php#categoria">Categories</a></li>
        <li><a href="index.php#contact">Contact</a></li>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="sign-up.php">Sign up</a></li>
        <?php else: ?>
            <li><a href="userProfile.php"><img class="nav-user-icon" src="./img/user.png" alt="User Icon"
                                               style="width: 24px; height: 24px; border-radius: 50%; background: #fff"/></a>
            </li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="logout.php">Logout</a></li>
        <?php endif; ?>
    </ul>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST" action="tracking.php" class="search-box">
            <input type="search" name="code" id="search" placeholder="Find your package"/>
            <i class="ri-search-line"></i>
        </form>
    <?php endif; ?>
</nav>