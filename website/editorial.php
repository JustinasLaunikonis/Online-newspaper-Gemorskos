<?php
    session_start();
    $allowedRoles = ['Editor in Chief', 'Editor'];
    $userRole = $_SESSION['user_role'] ?? '';
    
    if (!in_array($userRole, $allowedRoles)) {
        header('Location: welcome.php');
        exit();
    }
    
    require_once '../components/layout.php';

    try {
        $dbHandler = new PDO("mysql:host=mysql;dbname=gemorskos;charset=utf8", "root", "qwerty");
        $dbHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $exception) {
        die("Connection error: " . $exception->getMessage());
    }

    $errors = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_article'])) {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? '';
        $author_id = $_SESSION['user_id'] ?? 0;

        if (empty($title)) { $errors['title'] = 'Article title is required.'; }
        if (empty($content)) { $errors['content'] = 'Article content is required.'; }
        if (empty($status)) { $errors['status'] = 'Please select a status.'; }

        if (empty($errors)) {
            try {
                $stmt = $dbHandler->prepare("INSERT INTO articles (title, content, author_id, status, submission_date) VALUES (:title, :content, :author_id, :status, NOW())");
                $stmt->execute([
                    'title' => $title,
                    'content' => $content,
                    'author_id' => $author_id,
                    'status' => $status
                ]);
                header('Location: editorial.php?success=submitted');
                exit();
            } catch(PDOException $e) {
                $error = "Error submitting article: " . $e->getMessage();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_article'])) {
        $article_id = $_POST['article_id'] ?? 0;
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? '';

        if (empty($title)) { $errors['title'] = 'Article title is required.'; }
        if (empty($content)) { $errors['content'] = 'Article content is required.'; }
        if (empty($status)) { $errors['status'] = 'Please select a status.'; }

        if (empty($errors)) {
            try {
                $stmt = $dbHandler->prepare("UPDATE articles SET title = :title, content = :content, status = :status WHERE article_id = :article_id");
                $stmt->execute([
                    'title' => $title,
                    'content' => $content,
                    'status' => $status,
                    'article_id' => $article_id
                ]);
                header('Location: editorial.php?success=updated');
                exit();
            } catch(PDOException $e) {
                $error = "Error updating article: " . $e->getMessage();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article'])) {
        $article_id = $_POST['article_id'] ?? 0;
        try {
            $stmt = $dbHandler->prepare("DELETE FROM articles WHERE article_id = :article_id");
            $stmt->execute(['article_id' => $article_id]);
            $success = "Article deleted successfully!";
        } catch(PDOException $e) {
            $error = "Error deleting article: " . $e->getMessage();
        }
    }

    $editArticle = null;
    if (isset($_GET['edit'])) {
        $edit_id = $_GET['edit'];
        try {
            $stmt = $dbHandler->prepare("SELECT * FROM articles WHERE article_id = :article_id");
            $stmt->execute(['article_id' => $edit_id]);
            $editArticle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $currentUserId = $_SESSION['user_id'] ?? 0;
            if ($editArticle && $editArticle['author_id'] != $currentUserId && $userRole !== 'Editor in Chief') {
                $editArticle = null;
                $error = "You don't have permission to edit this article.";
            }
        } catch(PDOException $e) {
            $error = "Error fetching article: " . $e->getMessage();
        }
    }

    try {
        $stmt = $dbHandler->query("
            SELECT a.*, u.username, u.fname, u.lname 
            FROM articles a 
            LEFT JOIN users u ON a.author_id = u.id 
            ORDER BY a.submission_date DESC
        ");
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $articles = [];
        $error = "Error fetching articles: " . $e->getMessage();
    }

    if (isset($_GET['success'])) {
        if ($_GET['success'] === 'updated') {
            $success = "Article updated successfully!";
        } elseif ($_GET['success'] === 'submitted') {
            $success = "Article successfully submitted!";
        }
    }

    $faviconPath = "../assets/sidebar/editorial.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemorskos - <?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo $faviconPath; ?>">

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../style_editorial.css">
</head>
<body>
    <?php
    renderHeader($pageTitle);
    renderSidebar($navigation, $navigationLink, $navigationLogo, $currentPage);
    ?>

    <div class="herobox">
        <div class="editorial-container">
            <div class="editorial-header">
                <h1>Editorial Dashboard</h1>
                <p style="color: #999999; font-size: 16px;">Manage and publish articles</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Article Submission Form -->
            <div class="article-form">
                <h3><?php echo $editArticle ? 'Edit Article' : 'Submit New Article'; ?></h3>
                <form method="POST" action="">
                    <?php if ($editArticle): ?>
                        <input type="hidden" name="article_id" value="<?php echo $editArticle['article_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Article Title</label>
                        <input type="text" id="title" name="title" value="<?php echo $editArticle ? htmlspecialchars($editArticle['title']) : (isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''); ?>">
                        <?php if (isset($errors['title'])): ?>
                            <div class="field-error"><?php echo htmlspecialchars($errors['title']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content"><?php echo $editArticle ? htmlspecialchars($editArticle['content']) : (isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''); ?></textarea>
                        <?php if (isset($errors['content'])): ?>
                            <div class="field-error"><?php echo htmlspecialchars($errors['content']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">-- Select Status --</option>
                            <?php
                            $currentStatus = $editArticle ? $editArticle['status'] : (isset($_POST['status']) ? $_POST['status'] : '');
                            $statusOptions = [
                                'draft' => 'Draft',
                                'review' => 'Pending Review',
                                'published' => 'Published'
                            ];
                            foreach ($statusOptions as $value => $label) {
                                $selected = ($currentStatus === $value) ? 'selected' : '';
                                echo "<option value='$value' $selected>$label</option>";
                            }
                            ?>
                        </select>
                        <?php if (isset($errors['status'])): ?>
                            <div class="field-error"><?php echo htmlspecialchars($errors['status']); ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if ($editArticle): ?>
                        <button type="submit" name="update_article" class="submit-btn">Update Article</button>
                        <a href="editorial.php" class="cancel-btn" style="margin-left: 15px; padding: 15px 40px; display: inline-block;">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="submit_article" class="submit-btn">Submit Article</button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="section-divider"></div>

            <!-- Articles List -->
            <div class="articles-section">
                <h2>All Articles (<?php echo count($articles); ?>)</h2>
                
                <?php if (empty($articles)): ?>
                    <div class="articles-table">
                        <div class="no-articles">No articles found. Submit your first article above!</div>
                    </div>
                <?php else: ?>
                    <div class="articles-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Submission Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($articles as $article): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($article['article_id']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($article['title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($article['fname'] . ' ' . $article['lname']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo htmlspecialchars($article['status']); ?>">
                                                <?php echo htmlspecialchars(ucfirst($article['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($article['submission_date'])); ?></td>
                                        <td>
                                            <?php 
                                            $currentUserId = $_SESSION['user_id'] ?? 0;
                                            $canEdit = ($article['author_id'] == $currentUserId || $userRole === 'Editor in Chief');
                                            if ($canEdit): 
                                            ?>
                                                <a href="?edit=<?php echo $article['article_id']; ?>" class="edit-btn">Edit</a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                                    <input type="hidden" name="article_id" value="<?php echo $article['article_id']; ?>">
                                                    <button type="submit" name="delete_article" class="delete-btn">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
