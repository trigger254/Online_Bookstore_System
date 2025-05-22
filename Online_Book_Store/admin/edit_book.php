<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get book ID from URL
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get book details
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: manage_books.php');
    exit();
}

// Get all writers for the dropdown
$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'writer' ORDER BY username");
$writers = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $author_id = $_POST['author_id'];
    $price = floatval($_POST['price']);
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    
    $errors = [];
    
    // Validate inputs
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    
    if (!$is_free && $price <= 0) {
        $errors[] = "Price must be greater than 0 for paid books";
    }
    
    // Handle file uploads
    $cover_image = $book['cover_image'];
    $file_path = $book['file_path'];
    
    if (empty($errors)) {
        // Handle cover image upload
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['cover_image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $new_cover_image = uniqid() . '.' . $file_extension;
                $upload_path = '../assets/images/books/' . $new_cover_image;
                
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
                    // Delete old cover image if exists
                    if ($book['cover_image'] && file_exists('../assets/images/books/' . $book['cover_image'])) {
                        unlink('../assets/images/books/' . $book['cover_image']);
                    }
                    $cover_image = $new_cover_image;
                } else {
                    $errors[] = "Failed to upload cover image";
                }
            } else {
                $errors[] = "Invalid cover image format. Allowed formats: JPG, PNG, GIF";
            }
        }
        
        // Handle book file upload
        if (isset($_FILES['book_file']) && $_FILES['book_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'application/epub+zip', 'application/x-mobipocket-ebook'];
            $file_type = $_FILES['book_file']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['book_file']['name'], PATHINFO_EXTENSION);
                $new_file_path = uniqid() . '.' . $file_extension;
                $upload_path = '../assets/books/' . $new_file_path;
                
                if (move_uploaded_file($_FILES['book_file']['tmp_name'], $upload_path)) {
                    // Delete old book file if exists
                    if ($book['file_path'] && file_exists('../assets/books/' . $book['file_path'])) {
                        unlink('../assets/books/' . $book['file_path']);
                    }
                    $file_path = $new_file_path;
                } else {
                    $errors[] = "Failed to upload book file";
                }
            } else {
                $errors[] = "Invalid book file format. Allowed formats: PDF, EPUB, MOBI";
            }
        }
        
        // Update book in database if no errors
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE books SET 
                                     title = ?, 
                                     description = ?, 
                                     author_id = ?, 
                                     price = ?, 
                                     is_free = ?, 
                                     cover_image = ?, 
                                     file_path = ? 
                                     WHERE id = ?");
                $stmt->execute([$title, $description, $author_id, $price, $is_free, $cover_image, $file_path, $book_id]);
                
                header('Location: manage_books.php');
                exit();
            } catch (PDOException $e) {
                $errors[] = "Error updating book. Please try again.";
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Book</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Book Title</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($book['title']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="4" required><?php echo htmlspecialchars($book['description']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="author_id" class="form-label">Author</label>
                            <select class="form-select" id="author_id" name="author_id" required>
                                <option value="">Select Author</option>
                                <?php foreach ($writers as $writer): ?>
                                    <option value="<?php echo $writer['id']; ?>" 
                                            <?php echo $writer['id'] == $book['author_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($writer['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (KSh)</label>
                            <input type="number" class="form-control" id="price" name="price" 
                                   step="0.01" min="0" value="<?php echo $book['price']; ?>" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_free" name="is_free"
                                   <?php echo $book['is_free'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_free">This is a free book</label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cover_image" class="form-label">Cover Image</label>
                            <?php if ($book['cover_image']): ?>
                                <div class="mb-2">
                                    <img src="../assets/images/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                         alt="Current cover" style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*">
                            <div class="form-text">Leave empty to keep current image. Allowed formats: JPG, PNG, GIF</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="book_file" class="form-label">Book File</label>
                            <?php if ($book['file_path']): ?>
                                <div class="mb-2">
                                    <a href="../assets/books/<?php echo htmlspecialchars($book['file_path']); ?>" 
                                       class="btn btn-sm btn-secondary" target="_blank">
                                        View Current File
                                    </a>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="book_file" name="book_file">
                            <div class="form-text">Leave empty to keep current file. Allowed formats: PDF, EPUB, MOBI</div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="manage_books.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Book</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('is_free').addEventListener('change', function() {
    const priceInput = document.getElementById('price');
    if (this.checked) {
        priceInput.value = '0';
        priceInput.disabled = true;
    } else {
        priceInput.disabled = false;
    }
});

// Initialize price input state based on is_free checkbox
document.addEventListener('DOMContentLoaded', function() {
    const isFreeCheckbox = document.getElementById('is_free');
    const priceInput = document.getElementById('price');
    if (isFreeCheckbox.checked) {
        priceInput.value = '0';
        priceInput.disabled = true;
    }
});
</script>

<?php include '../includes/footer.php'; ?> 