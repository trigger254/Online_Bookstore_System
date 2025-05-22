<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is writer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'writer') {
    header('Location: ../login.php');
    exit();
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    
    // Handle file uploads
    $cover_image = '';
    $book_file = '';
    
    // Upload cover image
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['cover_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = '../assets/images/books/' . $new_filename;
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
                $cover_image = $new_filename;
            }
        }
    }
    
    // Upload book file
    if (isset($_FILES['book_file']) && $_FILES['book_file']['error'] == 0) {
        $allowed = ['pdf', 'epub', 'mobi'];
        $filename = $_FILES['book_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
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
            $_SESSION['user_id'],
            $book_file,
            $cover_image,
            $_POST['category_id']
        ]);
        $success = "Book uploaded successfully!";
    } else {
        $error = "Please upload both cover image and book file.";
    }
}

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Upload New Book</h5>
            </div>
            <div class="card-body">
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
                            <?php
                            $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                            while($category = $stmt->fetch()) {
                                echo "<option value='" . $category['id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price" class="form-label">Price ($)</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_free" name="is_free">
                        <label class="form-check-label" for="is_free">This is a free book</label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cover_image" class="form-label">Cover Image</label>
                        <input type="file" class="form-control" id="cover_image" name="cover_image" 
                               accept="image/*" required>
                        <div class="form-text">Accepted formats: JPG, JPEG, PNG, GIF</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="book_file" class="form-label">Book File</label>
                        <input type="file" class="form-control" id="book_file" name="book_file" 
                               accept=".pdf,.epub,.mobi" required>
                        <div class="form-text">Accepted formats: PDF, EPUB, MOBI</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Upload Book</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 