<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        // sendResponse("unauthorized", "You must be logged in to perform this action.");
        exit;
    }
    $ID = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
    include 'includes/db.php';

    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];

    // Restrict normal user to only edit themselves
    if ($user_role == 0 && $ID != $user_id) {
        echo "<p>Access Denied!</p>";
        exit;
    }

    // Fetch user details
    $query = $conn->query("SELECT * FROM users WHERE ID = $ID");
    if ($query->num_rows == 0) {
        echo "<p>User not found!</p>";
        exit;
    }
    $user = $query->fetch_assoc();
?>

<!-- ================== EDIT USER FORM ================== -->
<h2>Edit User</h2>
<form id="editUserForm" enctype="multipart/form-data">
    <!-- Hidden id -->
    <input type="hidden" name="ID" value="<?= $user['ID'] ?>">

    <!-- name -->
    <label>Name:</label><br>
    <input type="text" name="NAME" value="<?= htmlspecialchars($user['NAME']) ?>"><br><br>

    <!-- email -->
    <label>Email:</label><br>
    <input type="text" 
        name="EMAIL" 
        value="<?= htmlspecialchars($user['EMAIL']) ?>" 
        <?= ($user_role == 0 ? 'readonly' : '') ?>
    >
    <br><br>

    
    <!-- Role (Visible only to Admins) -->
    <?php if ($user_role == 1): ?>
        <label>Role:</label><br>
        <select name="ROLE">
            <option value="0" <?= ($user['role'] == 0 ? 'selected' : '') ?>>User</option>
            <option value="1" <?= ($user['role'] == 1 ? 'selected' : '') ?>>Admin</option>
        </select><br><br>
    <?php endif; ?>

    <!-- change photo -->
    <label>Change Photo:</label><br>
    <input type="file" name="photo" id="editPhotoInput" accept="image/*"><br><br>

    <!-- change password -->
    <label>Change Password:</label><br>
    <input type="password" name="password"><br><br>

    <!-- Image preview -->
    <div id="editPhotoPreviewContainer">
        <img id="editPhotoPreview" src="uploads/<?= htmlspecialchars($user['photo']) ?>" 
        alt="Current Photo" 
        style="max-width:150px; border-radius:8px; box-shadow:0 0 5px rgba(0,0,0,0.2);">
    </div>
    
    <br>

    <!-- submit button -->
    <button type="submit">Update User</button>
</form>
