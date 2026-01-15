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
    $sender_id = $_SESSION['user_id'] ?? 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_instruction'])) {
        $recipient_role = $_POST['recipient_role'] ?? '';
        $message_text = trim($_POST['message_text'] ?? '');
        $priority = $_POST['priority'] ?? '';
        $due_date = $_POST['due_date'] ?? '';

        if (empty($recipient_role)) { $errors['recipient_role'] = 'Please select a role.'; }
        if (empty($message_text)) { $errors['message_text'] = 'Message is required.'; }
        if (empty($priority)) { $errors['priority'] = 'Please select a priority.'; }
        if (empty($due_date)) { $errors['due_date'] = 'Due date is required.'; }

        if (empty($errors)) {
            try {
                $stmt = $dbHandler->prepare("INSERT INTO instructions (sender_id, recipient_role, message_text, priority, due_date, created_at) VALUES (:sender_id, :recipient_role, :message_text, :priority, :due_date, NOW())");
                $stmt->execute([
                    'sender_id' => $sender_id,
                    'recipient_role' => $recipient_role,
                    'message_text' => $message_text,
                    'priority' => $priority,
                    'due_date' => $due_date
                ]);
                header('Location: instructions.php?success=created');
                exit();
            } catch(PDOException $e) {
                $error = "Error creating instruction: " . $e->getMessage();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_instruction'])) {
        $instruction_id = $_POST['instruction_id'] ?? 0;
        $recipient_role = $_POST['recipient_role'] ?? '';
        $message_text = trim($_POST['message_text'] ?? '');
        $priority = $_POST['priority'] ?? '';
        $due_date = $_POST['due_date'] ?? '';

        if (empty($recipient_role)) { $errors['recipient_role'] = 'Please select a role.'; }
        if (empty($message_text)) { $errors['message_text'] = 'Message is required.'; }
        if (empty($priority)) { $errors['priority'] = 'Please select a priority.'; }
        if (empty($due_date)) { $errors['due_date'] = 'Due date is required.'; }

        if (empty($errors)) {
            try {
                $stmt = $dbHandler->prepare("UPDATE instructions SET recipient_role = :recipient_role, message_text = :message_text, priority = :priority, due_date = :due_date WHERE instructions_id = :instruction_id");
                $stmt->execute([
                    'recipient_role' => $recipient_role,
                    'message_text' => $message_text,
                    'priority' => $priority,
                    'due_date' => $due_date,
                    'instruction_id' => $instruction_id
                ]);
                header('Location: instructions.php?success=updated');
                exit();
            } catch(PDOException $e) {
                $error = "Error updating instruction: " . $e->getMessage();
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_instruction'])) {
        $instruction_id = $_POST['instruction_id'] ?? 0;
        try {
            $stmt = $dbHandler->prepare("DELETE FROM instructions WHERE instructions_id = :instruction_id");
            $stmt->execute(['instruction_id' => $instruction_id]);
            header('Location: instructions.php?success=deleted');
            exit();
        } catch(PDOException $e) {
            $error = "Error deleting instruction: " . $e->getMessage();
        }
    }

    $editInstruction = null;
    if (isset($_GET['edit']) || (!empty($errors) && isset($_POST['instruction_id']))) {
        if (!empty($errors) && isset($_POST['instruction_id'])) {
            $editInstruction = [
                'instructions_id' => $_POST['instruction_id'],
                'recipient_role' => $_POST['recipient_role'] ?? '',
                'message_text' => $_POST['message_text'] ?? '',
                'priority' => $_POST['priority'] ?? '',
                'due_date' => $_POST['due_date'] ?? ''
            ];
        } else {
            $edit_id = $_GET['edit'];
            try {
                $stmt = $dbHandler->prepare("SELECT instructions_id, recipient_role, message_text, priority, due_date FROM instructions WHERE instructions_id = :instruction_id");
                $stmt->execute(['instruction_id' => $edit_id]);
                $editInstruction = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                $error = "Error fetching instruction: " . $e->getMessage();
            }
        }
    }

    $availableRoles = [
        'Editor in Chief',
        'Editor',
        'Administration',
        'Web Designer',
        'Journalist/Photographer'
    ];

    try {
        $stmt = $dbHandler->prepare("
            SELECT i.instructions_id, i.message_text, i.priority, i.due_date, i.created_at,
                   i.recipient_role,
                   s.fname as sender_fname, s.lname as sender_lname
            FROM instructions i
            LEFT JOIN users s ON i.sender_id = s.id
            ORDER BY i.created_at DESC
        ");
        $stmt->execute();
        $instructions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error = "Error fetching instructions: " . $e->getMessage();
        $instructions = [];
    }

    // Success messages
    if (isset($_GET['success'])) {
        if ($_GET['success'] === 'created') {
            $success = "Instruction created successfully!";
        } elseif ($_GET['success'] === 'updated') {
            $success = "Instruction updated successfully!";
        } elseif ($_GET['success'] === 'deleted') {
            $success = "Instruction deleted successfully!";
        }
    }

    $pageTitle = "Instructions Management";
    $faviconPath = "../assets/sidebar/instructions.png";

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
    <link rel="stylesheet" href="../style_instructions.css">
</head>
<body>
    <?php
    renderHeader($pageTitle);
    renderSidebar($navigation, $navigationLink, $navigationLogo, $currentPage);
    ?>
    
    <div class="herobox">
        <div class="instructions-container">
            
            <div class="instructions-header">
                <h1>Instructions Management</h1>
                <p>Create and manage instructions for team members</p>
            </div>

            <?php if ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="instruction-form">
                <h3><?php echo $editInstruction ? 'Edit Instruction' : 'Create New Instruction'; ?></h3>
                <form method="POST" action="instructions.php">
                    <?php if ($editInstruction): ?>
                        <input type="hidden" name="instruction_id" value="<?php echo $editInstruction['instructions_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="recipient_role">Assign To Role</label>
                        <select name="recipient_role" id="recipient_role">
                            <option value="">Select Role</option>
                            <?php foreach ($availableRoles as $role): ?>
                                <option value="<?php echo $role; ?>" 
                                    <?php echo ($editInstruction && $editInstruction['recipient_role'] == $role) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['recipient_role'])): ?>
                            <div class="field-error"><?php echo $errors['recipient_role']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="message_text">Message</label>
                        <textarea name="message_text" id="message_text" rows="4"><?php echo $editInstruction ? htmlspecialchars($editInstruction['message_text']) : ''; ?></textarea>
                        <?php if (isset($errors['message_text'])): ?>
                            <div class="field-error"><?php echo $errors['message_text']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select name="priority" id="priority">
                                <option value="">Select Priority</option>
                                <option value="normal" <?php echo ($editInstruction && $editInstruction['priority'] === 'normal') ? 'selected' : ''; ?>>Normal</option>
                                <option value="urgent" <?php echo ($editInstruction && $editInstruction['priority'] === 'urgent') ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                            <?php if (isset($errors['priority'])): ?>
                                <div class="field-error"><?php echo $errors['priority']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" name="due_date" id="due_date" 
                                value="<?php echo $editInstruction ? htmlspecialchars($editInstruction['due_date']) : ''; ?>">
                            <?php if (isset($errors['due_date'])): ?>
                                <div class="field-error"><?php echo $errors['due_date']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <?php if ($editInstruction): ?>
                            <button type="submit" name="update_instruction" class="submit-btn">Update Instruction</button>
                            <a href="instructions.php" class="cancel-btn">Cancel</a>
                        <?php else: ?>
                            <button type="submit" name="create_instruction" class="submit-btn">Create Instruction</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="section-divider"></div>

            <div class="instructions-section">
                <h2>All Instructions (<?php echo count($instructions); ?>)</h2>
                
                <?php if (count($instructions) > 0): ?>
                    <div class="instructions-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Recipient Role</th>
                                    <th>Message</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($instructions as $instruction): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($instruction['instructions_id']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($instruction['recipient_role']); ?></strong></td>
                                        <td class="message-cell"><?php echo htmlspecialchars($instruction['message_text']); ?></td>
                                        <td>
                                            <span class="priority-badge priority-<?php echo $instruction['priority']; ?>">
                                                <?php echo htmlspecialchars(ucfirst($instruction['priority'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($instruction['due_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($instruction['sender_fname'] . ' ' . $instruction['sender_lname']); ?></td>
                                        <td>
                                            <a href="instructions.php?edit=<?php echo $instruction['instructions_id']; ?>" class="edit-btn">Edit</a>
                                            <form method="POST" action="instructions.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this instruction?');">
                                                <input type="hidden" name="instruction_id" value="<?php echo $instruction['instructions_id']; ?>">
                                                <button type="submit" name="delete_instruction" class="delete-btn">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-instructions">
                        <p>No instructions found.</p>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</body>
</html>