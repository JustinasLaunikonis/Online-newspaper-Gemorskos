<?php
    session_start();
    
    if (($_SESSION['user_role'] ?? '') !== 'Editor in Chief') {
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
    $success = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'] ?? 0;
        $username = trim($_POST['username'] ?? '');
        $fname = trim($_POST['fname'] ?? '');
        $lname = trim($_POST['lname'] ?? '');
        $new_role = $_POST['role'] ?? '';
        
        if (empty($username)) { $errors['username'] = 'Username is required.'; }
        if (empty($fname)) { $errors['fname'] = 'First name is required.'; }
        if (empty($lname)) { $errors['lname'] = 'Last name is required.'; }
        if (empty($new_role)) { $errors['role'] = 'Please select a role.'; }

        if (empty($errors['username'])) {
            try {
                $stmt = $dbHandler->prepare("SELECT id FROM users WHERE username = :username AND id != :user_id");
                $stmt->execute(['username' => $username, 'user_id' => $user_id]);
                if ($stmt->fetch()) {
                    $errors['username'] = 'Username is already taken.';
                }
            } catch(PDOException $e) {
                $error = "Error checking username: " . $e->getMessage();
            }
        }

        if (empty($errors)) {
            try {
                $stmt = $dbHandler->prepare("UPDATE users SET username = :username, fname = :fname, lname = :lname, role = :role WHERE id = :user_id");
                $stmt->execute([
                    'username' => $username,
                    'fname' => $fname,
                    'lname' => $lname,
                    'role' => $new_role,
                    'user_id' => $user_id
                ]);
                header('Location: users.php?success=updated');
                exit();
            } catch(PDOException $e) {
                $error = "Error updating user: " . $e->getMessage();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'] ?? 0;
        $current_user_id = $_SESSION['user_id'] ?? 0;
        
        if ($user_id == $current_user_id) {
            $error = "You cannot delete your own account.";
        } else {
            try {
                $stmt = $dbHandler->prepare("DELETE FROM articles WHERE author_id = :user_id");
                $stmt->execute(['user_id' => $user_id]);
                
                $stmt = $dbHandler->prepare("DELETE FROM users WHERE id = :user_id");
                $stmt->execute(['user_id' => $user_id]);
                
                header('Location: users.php?success=deleted');
                exit();
            } catch(PDOException $e) {
                $error = "Error deleting user: " . $e->getMessage();
            }
        }
    }

    $editUser = null;
    if (isset($_GET['edit']) || (!empty($errors) && isset($_POST['user_id']))) {
        if (!empty($errors) && isset($_POST['user_id'])) {
            $editUser = [
                'id' => $_POST['user_id'],
                'username' => $_POST['username'] ?? '',
                'fname' => $_POST['fname'] ?? '',
                'lname' => $_POST['lname'] ?? '',
                'role' => $_POST['role'] ?? ''
            ];
        } else {
            $edit_id = $_GET['edit'];
            try {
                $stmt = $dbHandler->prepare("SELECT id, username, fname, lname, role FROM users WHERE id = :user_id");
                $stmt->execute(['user_id' => $edit_id]);
                $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                $error = "Error fetching user: " . $e->getMessage();
            }
        }
    }

    try {
        $stmt = $dbHandler->prepare("
            SELECT u.id, u.username, u.fname, u.lname, u.role, COUNT(a.article_id) as article_count
            FROM users u
            LEFT JOIN articles a ON u.id = a.author_id
            GROUP BY u.id, u.username, u.fname, u.lname, u.role
            ORDER BY u.id ASC
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error = "Error fetching users: " . $e->getMessage();
        $users = [];
    }

    if (isset($_GET['success'])) {
        if ($_GET['success'] === 'updated') {
            $success = "User role updated successfully!";
        } elseif ($_GET['success'] === 'deleted') {
            $success = "User deleted successfully!";
        }
    }

    $pageTitle = "User Management";
    $faviconPath = "../assets/sidebar/gemorskos.png";

    require_once '../components/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemorskos - <?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo $faviconPath; ?>">

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../style_users.css">
</head>
<body>
    <?php
    renderHeader($pageTitle);
    renderSidebar($navigation, $navigationLink, $navigationLogo, $currentPage);
    ?>
    
    <div class="herobox">
        <div class="users-container">
            
            <div class="users-header">
                <h1>User Management</h1>
                <p>Manage all registered users and their roles</p>
            </div>

            <?php if ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($editUser): ?>
                <div class="edit-form">
                    <h3>Edit User</h3>
                    <form method="POST" action="users.php">
                        <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($editUser['username']); ?>">
                            <?php if (isset($errors['username'])): ?>
                                <div class="field-error"><?php echo $errors['username']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="fname">First Name</label>
                            <input type="text" name="fname" id="fname" value="<?php echo htmlspecialchars($editUser['fname']); ?>">
                            <?php if (isset($errors['fname'])): ?>
                                <div class="field-error"><?php echo $errors['fname']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="lname">Last Name</label>
                            <input type="text" name="lname" id="lname" value="<?php echo htmlspecialchars($editUser['lname']); ?>">
                            <?php if (isset($errors['lname'])): ?>
                                <div class="field-error"><?php echo $errors['lname']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select name="role" id="role">
                                <option value="Editor in Chief" <?php echo $editUser['role'] === 'Editor in Chief' ? 'selected' : ''; ?>>Editor in Chief</option>
                                <option value="Editor" <?php echo $editUser['role'] === 'Editor' ? 'selected' : ''; ?>>Editor</option>
                                <option value="Administration" <?php echo $editUser['role'] === 'Administration' ? 'selected' : ''; ?>>Administration</option>
                                <option value="Web Designer" <?php echo $editUser['role'] === 'Web Designer' ? 'selected' : ''; ?>>Web Designer</option>
                                <option value="Journalist/Photographer" <?php echo $editUser['role'] === 'Journalist/Photographer' ? 'selected' : ''; ?>>Journalist/Photographer</option>
                                </select>
                            <?php if (isset($errors['role'])): ?>
                                <div class="field-error"><?php echo $errors['role']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_role" class="submit-btn">Update User</button>
                            <a href="users.php" class="cancel-btn">Cancel</a>
                        </div>
                    </form>
                </div>
                <div class="section-divider"></div>
            <?php endif; ?>

            <div class="users-section">
                <h2>All Users (<?php echo count($users); ?>)</h2>
                
                <?php if (count($users) > 0): ?>
                    <div class="users-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Articles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $currentUserId = $_SESSION['user_id'] ?? 0;
                                foreach ($users as $user): 
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></td>
                                        <td>
                                            <span class="role-badge role-<?php echo strtolower(str_replace(['/', ' '], ['_', '-'], $user['role'])); ?>">
                                                <?php echo htmlspecialchars($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['article_count']; ?></td>
                                        <td>
                                            <a href="users.php?edit=<?php echo $user['id']; ?>" class="edit-btn">Edit</a>
                                            
                                            <?php if ($user['id'] != $currentUserId): ?>
                                                <form method="POST" action="users.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? This will also delete all their articles.');">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="delete_user" class="delete-btn">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-users">
                        <p>No users found in the system.</p>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>

</body>
</html>
