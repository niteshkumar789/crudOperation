<!-- ================ PHP ================ -->
<?php
    session_start();
    include 'includes/db.php';
    include 'includes/functions.php';

    // Redirect to login if not logged in
    // if (!isset($_SESSION['user_id']) && (!isset($_GET['page']) || $_GET['page'] !== 'login')) {
    //     header("Location: index.php?page=login");
    //     exit;
    // }
?>

<!-- ================ HTML ================ -->
<!DOCTYPE html>
<html>
<head>
    <title>User Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="index.php?page=home">Home</a>
            <?php if ($_SESSION['user_role'] == 1): ?>
                <a href="index.php?page=add_user">Add User</a>
                <a href="index.php?page=bulk_upload">Bulk Upload</a>
            <?php endif; ?>
            <a href="index.php?page=list_users">List Users</a>
            <a href="#" id="logoutBtn">Logout</a>
        
        <?php else: ?>
            <a href="index.php?page=login">Login</a>
            <a href="index.php?page=register">Create Account</a>
        <?php endif; ?>
    </nav>

    <main>
        <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'home';
            switch ($page) {
                case 'login': include 'tasks/login.php'; break;
                case 'add_user': include 'tasks/add_user.php'; break;
                case 'list_users': include 'tasks/list_users.php'; break;
                case 'edit_user': include 'tasks/edit_user.php'; break;
                case 'register': include 'tasks/register.php'; break;
                case 'bulk_upload': include 'tasks/bulk_upload.php'; break;
                default: include 'tasks/home.php'; break;
            }
        ?>
    </main>
    
    <?php include 'includes/popup.php'; ?> <!-- ✅ Added here -->
    <script src="assets/js/main.js"></script>
</body>

</html>

<!-- 
        project/
        │
        ├── index.php
        ├── assets/
        │   ├── js/
        │   │   └── main.js
        │   └── css/
        │       └── style.css
        │
        ├── includes/
        │   ├── db.php
        │   ├── excel_reader.php
        |   ├── functions.php
        │   └── popup.php
        │
        ├── tasks/
        │   ├── home.php
        │   ├── add_user.php
        │   ├── list_users.php
        │   ├── edit_user.php
        │   ├── login.php
        │   ├── register.php      ← 🆕 NEW FILE
        │   ├── logout.php
        │   └── bulk_upload.php        ← 🆕 NEW PAGE for admin upload
        │
        ├── ajax/
        │   └── user_actions.php
        │
        └── uploads/
            └── bulk_files/     ← 🆕 folder to temporarily store uploaded Excel/CSV

-->

<!-- Total: 15

index.php
add_user.php
bulk_upload.php
edit_user.php
home.php
list_users.php
login.php
logout.php 
register.php
db.php
excel_reader.php
functions.php
user_actions.php
style.css
main.js
-->