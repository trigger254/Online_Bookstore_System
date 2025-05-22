<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is writer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'writer') {
    header('Location: ../login.php');
    exit();
}

// Get book ID from URL
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get book details
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND author_id = ?");
$stmt->execute([$book_id, $_SESSION['user_id']]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    
    // Handle cover image upload
    $cover_image = $book['cover_image']; // Keep existing cover by default
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['cover_image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = '../assets/images/books/' . $new_filename;
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
                // Delete old cover image if exists
                if ($book['cover_image'] && file_exists('../assets/images/books/' . $book['cover_image'])) {
                    unlink('../assets/images/books/' . $book['cover_image']);
                }
                $cover_image = $new_filename;
            }
        }
    }
    
    // Handle book file upload
    $file_path = $book['file_path']; // Keep existing file by default
    if (isset($_FILES['book_file']) && $_FILES['book_file']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'application/epub+zip', 'application/x-mobipocket-ebook'];
        $file_type = $_FILES['book_file']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['book_file']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = '../assets/books/' . $new_filename;
            
            if (move_uploaded_file($_FILES['book_file']['tmp_name'], $upload_path)) {
                // Delete old book file if exists
                if ($book['file_path'] && file_exists('../assets/books/' . $book['file_path'])) {
                    unlink('../assets/books/' . $book['file_path']);
                }
                $file_path = $new_filename;
            }
        }
    }
    
    // Update book in database
    $stmt = $pdo->prepare("UPDATE books SET 
                            title = ?, 
                            description = ?, 
                            price = ?, 
                            is_free = ?,
                            cover_image = ?,
                            file_path = ?
                          WHERE id = ? AND author_id = ?");
    
    if ($stmt->execute([$title, $description, $price, $is_free, $cover_image, $file_path, $book_id, $_SESSION['user_id']])) {
        header('Location: dashboard.php?success=1');
        exit();
    } else {
        $error = "Failed to update book. Please try again.";
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>Edit Book</h2>
            <p class="text-muted">Update your book details</p>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Book Title</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($book['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="5" required><?php echo htmlspecialchars($book['description']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Price (KSh)</label>
                            <input type="number" class="form-control" id="price" name="price" 
                                   value="<?php echo $book['price']; ?>" step="0.01" min="0" required>
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
                            <input type="file" class="form-control" id="cover_image" name="cover_image" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif">
                            <small class="text-muted">Leave empty to keep current cover image</small>
                        </div>

                        <div class="mb-3">
                            <label for="book_file" class="form-label">Book File</label>
                            <input type="file" class="form-control" id="book_file" name="book_file" 
                                   accept=".pdf,.epub,.mobi">
                            <small class="text-muted">Leave empty to keep current book file</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Book</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 