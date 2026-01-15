<?php
    session_start();
    
    // All logged-in users can access this page
    if (!isset($_SESSION['user'])) {
        header('Location: ../index.php');
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

    // Handle client creation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_client'])) {
        $name = trim($_POST['name'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($name)) {
            $errors['name'] = 'Client name is required.';
        }
        if (empty($company_name)) {
            $errors['company_name'] = 'Company name is required.';
        }
        if (empty($phone_number)) {
            $errors['phone_number'] = 'Phone number is required.';
        }
        if (empty($email)) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        }

        if (empty($errors)) {
            try {
                $stmt = $dbHandler->prepare("INSERT INTO clients (name, company_name, phone_number, email) VALUES (:name, :company_name, :phone_number, :email)");
                $stmt->execute([
                    'name' => $name,
                    'company_name' => $company_name,
                    'phone_number' => $phone_number,
                    'email' => $email
                ]);
                header('Location: clients.php?success=created');
                exit();
            } catch(PDOException $e) {
                $error = "Error creating client: " . $e->getMessage();
            }
        }
    }

    // Handle client update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_client'])) {
        $client_id = $_POST['client_id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($name)) {
            $errors['name'] = 'Client name is required.';
        }
        if (empty($company_name)) {
            $errors['company_name'] = 'Company name is required.';
        }
        if (empty($phone_number)) {
            $errors['phone_number'] = 'Phone number is required.';
        }
        if (empty($email)) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        }

        if (empty($errors)) {
            try {
                $stmt = $dbHandler->prepare("UPDATE clients SET name = :name, company_name = :company_name, phone_number = :phone_number, email = :email WHERE client_id = :client_id");
                $stmt->execute([
                    'name' => $name,
                    'company_name' => $company_name,
                    'phone_number' => $phone_number,
                    'email' => $email,
                    'client_id' => $client_id
                ]);
                header('Location: clients.php?success=updated');
                exit();
            } catch(PDOException $e) {
                $error = "Error updating client: " . $e->getMessage();
            }
        }
    }

    // Handle client deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_client'])) {
        $client_id = $_POST['client_id'] ?? 0;
        try {
            $stmt = $dbHandler->prepare("DELETE FROM clients WHERE client_id = :client_id");
            $stmt->execute(['client_id' => $client_id]);
            header('Location: clients.php?success=deleted');
            exit();
        } catch(PDOException $e) {
            $error = "Error deleting client: " . $e->getMessage();
        }
    }

    // Handle edit mode
    $editClient = null;
    if (isset($_GET['edit']) || (!empty($errors) && isset($_POST['client_id']))) {
        if (!empty($errors) && isset($_POST['client_id'])) {
            $editClient = [
                'client_id' => $_POST['client_id'],
                'name' => $_POST['name'] ?? '',
                'company_name' => $_POST['company_name'] ?? '',
                'phone_number' => $_POST['phone_number'] ?? '',
                'email' => $_POST['email'] ?? ''
            ];
        } else {
            $edit_id = $_GET['edit'];
            try {
                $stmt = $dbHandler->prepare("SELECT client_id, name, company_name, phone_number, email FROM clients WHERE client_id = :client_id");
                $stmt->execute(['client_id' => $edit_id]);
                $editClient = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                $error = "Error fetching client: " . $e->getMessage();
            }
        }
    }

    // Get all clients
    try {
        $stmt = $dbHandler->prepare("SELECT client_id, name, company_name, phone_number, email FROM clients ORDER BY client_id ASC");
        $stmt->execute();
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error = "Error fetching clients: " . $e->getMessage();
        $clients = [];
    }

    // Success messages
    if (isset($_GET['success'])) {
        if ($_GET['success'] === 'created') {
            $success = "Client created successfully!";
        } elseif ($_GET['success'] === 'updated') {
            $success = "Client updated successfully!";
        } elseif ($_GET['success'] === 'deleted') {
            $success = "Client deleted successfully!";
        }
    }

    $pageTitle = "Client Management";
    $faviconPath = "../assets/sidebar/client.png";

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
                <h1>Client Management</h1>
                <p>Manage all clients and their information</p>
            </div>

            <?php if ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($editClient): ?>
                <div class="edit-form">
                    <h3>Edit Client</h3>
                    <form method="POST" action="clients.php">
                        <input type="hidden" name="client_id" value="<?php echo $editClient['client_id']; ?>">
                        
                        <div class="form-group">
                            <label for="name">Client Name</label>
                            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($editClient['name']); ?>">
                            <?php if (isset($errors['name'])): ?>
                                <div class="field-error"><?php echo $errors['name']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="company_name">Company Name</label>
                            <input type="text" name="company_name" id="company_name" value="<?php echo htmlspecialchars($editClient['company_name']); ?>">
                            <?php if (isset($errors['company_name'])): ?>
                                <div class="field-error"><?php echo $errors['company_name']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($editClient['phone_number']); ?>">
                            <?php if (isset($errors['phone_number'])): ?>
                                <div class="field-error"><?php echo $errors['phone_number']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($editClient['email']); ?>">
                            <?php if (isset($errors['email'])): ?>
                                <div class="field-error"><?php echo $errors['email']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_client" class="submit-btn">Update Client</button>
                            <a href="clients.php" class="cancel-btn">Cancel</a>
                        </div>
                    </form>
                </div>
                <div class="section-divider"></div>
            <?php else: ?>
                <div class="edit-form">
                    <h3>Add New Client</h3>
                    <form method="POST" action="clients.php">
                        <div class="form-group">
                            <label for="name">Client Name</label>
                            <input type="text" name="name" id="name">
                            <?php if (isset($errors['name'])): ?>
                                <div class="field-error"><?php echo $errors['name']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="company_name">Company Name</label>
                            <input type="text" name="company_name" id="company_name">
                            <?php if (isset($errors['company_name'])): ?>
                                <div class="field-error"><?php echo $errors['company_name']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" name="phone_number" id="phone_number">
                            <?php if (isset($errors['phone_number'])): ?>
                                <div class="field-error"><?php echo $errors['phone_number']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" name="email" id="email">
                            <?php if (isset($errors['email'])): ?>
                                <div class="field-error"><?php echo $errors['email']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="create_client" class="submit-btn">Add Client</button>
                        </div>
                    </form>
                </div>
                <div class="section-divider"></div>
            <?php endif; ?>

            <div class="users-section">
                <h2>All Clients (<?php echo count($clients); ?>)</h2>
                
                <?php if (count($clients) > 0): ?>
                    <div class="users-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client Name</th>
                                    <th>Company</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($client['client_id']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($client['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($client['company_name']); ?></td>
                                        <td><?php echo htmlspecialchars($client['phone_number']); ?></td>
                                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                                        <td>
                                            <a href="clients.php?edit=<?php echo $client['client_id']; ?>" class="edit-btn">Edit</a>
                                            <form method="POST" action="clients.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                                <input type="hidden" name="client_id" value="<?php echo $client['client_id']; ?>">
                                                <button type="submit" name="delete_client" class="delete-btn">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-users">
                        <p>No clients found in the system.</p>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>

</body>
</html>
