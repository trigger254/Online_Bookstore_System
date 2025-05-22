<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is reader
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reader') {
    header('Location: ../login.php');
    exit();
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$query = "SELECT b.*, u.username as author_name 
          FROM books b 
          JOIN users u ON b.author_id = u.id 
          WHERE 1=1";

$params = [];

if ($search) {
    $query .= " AND (b.title LIKE ? OR b.description LIKE ? OR u.username LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($filter === 'free') {
    $query .= " AND b.is_free = 1";
} elseif ($filter === 'paid') {
    $query .= " AND b.is_free = 0";
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY b.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY b.price DESC";
        break;
    case 'oldest':
        $query .= " ORDER BY b.created_at ASC";
        break;
    default:
        $query .= " ORDER BY b.created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <form action="" method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" 
                   placeholder="Search books..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
    <div class="col-md-4">
        <form action="" method="GET" class="d-flex">
            <select name="category" class="form-select me-2">
                <option value="">All Categories</option>
                <?php
                $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                while($category = $stmt->fetch()) {
                    $selected = (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : '';
                    echo "<option value='" . $category['id'] . "' $selected>" . 
                         htmlspecialchars($category['name']) . "</option>";
                }
                ?>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<div class="row">
    <?php foreach ($books as $book): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <?php if($book['cover_image']): ?>
                    <img src="../assets/images/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
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
                        <a href="view_book.php?id=<?php echo $book['id']; ?>" 
                           class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($books)): ?>
    <div class="text-center py-5">
        <h3>No books found</h3>
        <p>Try adjusting your search criteria</p>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?> 