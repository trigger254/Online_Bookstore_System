<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

// Get featured books
$stmt = $pdo->query("SELECT b.*, u.username as author_name 
                     FROM books b 
                     JOIN users u ON b.author_id = u.id 
                     ORDER BY b.created_at DESC 
                     LIMIT 6");
$featured_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Book Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
        }
        
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            padding: 0.5rem 2rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-img-top {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            height: 200px;
            object-fit: cover;
        }
        
        .card-title {
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .price-tag {
            background-color: var(--accent-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book-reader me-2"></i>Online Book Store
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Admin Dashboard</a>
                            </li>
                        <?php elseif($_SESSION['role'] === 'reader'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="reader/dashboard.php">Reader Dashboard</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="writer/dashboard.php">Writer Dashboard</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container text-center">
            <h1 class="hero-title">Welcome to Online Book Store</h1>
            <p class="hero-subtitle">Discover amazing books from talented writers</p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="mt-4">
                    <a href="register.php" class="btn btn-primary btn-lg me-3">Register Now</a>
                    <a href="login.php" class="btn btn-outline-primary btn-lg">Login</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="container">
        <div class="row mb-5">
            <div class="col-md-6">
                <h1 class="display-4">Welcome to Online Book Store</h1>
                <p class="lead">Discover amazing books from talented authors around the world.</p>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <div class="mt-4">
                        <a href="register.php" class="btn btn-primary btn-lg me-3">Register Now</a>
                        <a href="login.php" class="btn btn-outline-primary btn-lg">Login</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <div class="row g-4">
                    <div class="col-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-book-reader fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">For Readers</h5>
                                <p class="card-text">Browse and purchase books from talented authors.</p>
                                <?php if(!isset($_SESSION['user_id'])): ?>
                                    <a href="register.php?role=reader" class="btn btn-primary">Join as Reader</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-pen-fancy fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">For Writers</h5>
                                <p class="card-text">Publish and sell your books to readers worldwide.</p>
                                <?php if(!isset($_SESSION['user_id'])): ?>
                                    <a href="register.php?role=writer" class="btn btn-primary">Join as Writer</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="section-title">Featured Books</h2>
        <div class="row g-4">
            <?php
            // Fetch featured books
            $stmt = $pdo->query("
                SELECT b.*, u.username as author_name 
                FROM books b 
                JOIN users u ON b.author_id = u.id 
                ORDER BY b.created_at DESC 
                LIMIT 6
            ");
            $featured_books = $stmt->fetchAll();

            if($featured_books): 
                foreach($featured_books as $book): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <?php if($book['cover_image']): ?>
                                <img src="assets/images/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                                     style="height: 300px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 300px;">
                                    <i class="fas fa-book fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="card-text text-muted">By <?php echo htmlspecialchars($book['author_name']); ?></p>
                                <p class="card-text"><?php echo substr(htmlspecialchars($book['description']), 0, 100) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0">$<?php echo number_format($book['price'], 2); ?></span>
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <a href="reader/view_book.php?id=<?php echo $book['id']; ?>" 
                                           class="btn btn-primary">View Details</a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-primary">Login to View</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach;
            else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No featured books available at the moment.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Online Book Store. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 