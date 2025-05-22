<?php
require_once '../config/database.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $author_id = $_POST['author_id'];

    // Handle file uploads
    $cover_image = '';
    $book_file = '';

    // Handle cover image upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['cover_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = '../assets/images/books/' . $new_filename;
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
                $cover_image = $new_filename;
            }
        }
    }

    // Handle book file upload
    if (isset($_FILES['book_file']) && $_FILES['book_file']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx', 'txt'];
        $filename = $_FILES['book_file']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = '../assets/books/' . $new_filename;
            
            if (move_uploaded_file($_FILES['book_file']['tmp_name'], $upload_path)) {
                $book_file = $new_filename;
            }
        }
    }

    if ($cover_image && $book_file) {
        $stmt = $pdo->prepare("
            INSERT INTO books (title, description, price, author_id, file_path, cover_image, category_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title,
            $description,
            $price,
            $author_id,
            $book_file,
            $cover_image,
            $category_id
        ]);
        $success = "Book uploaded successfully!";
    } else {
        $error = "Please upload both cover image and book file.";
    }
}

// Get all writers
$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'writer'");
$writers = $stmt->fetchAll();

// Get all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Upload New Book</h2>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Book Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="author" class="form-label">Author</label>
                            <select class="form-select" id="author" name="author_id" required>
                                <option value="">Select an author</option>
                                <?php foreach($writers as $writer): ?>
                                    <option value="<?php echo $writer['id']; ?>">
                                        <?php echo htmlspecialchars($writer['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Price ($)</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                        </div>

                        <div class="mb-3">
                            <label for="cover_image" class="form-label">Cover Image</label>
                            <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*" required>
                        </div>

                        <div class="mb-3">
                            <label for="book_file" class="form-label">Book File</label>
                            <input type="file" class="form-control" id="book_file" name="book_file" accept=".pdf,.doc,.docx,.txt" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Upload Book</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 