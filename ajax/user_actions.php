<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    include '../includes/db.php';
    include '../includes/functions.php';
    include '../includes/excel_reader.php';
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    // ========= USER LOGIN =========
    if ($action == 'login') {
        $email = sanitize($conn, $_POST['email'] ?? '');
        $password = sanitize($conn, $_POST['password'] ?? ''); 

        // field validation
        if (empty($email) || empty($password)) {
            sendResponse("error", "Please fill in all fields!");
        }

        $query = $conn->query("SELECT * FROM users WHERE EMAIL='$email' AND STATUS = 1");
        if ($query->num_rows === 1) {
            $user = $query->fetch_assoc();

            // password verification
            if (password_verify($password, $user['password'])) {
                // session_start(); // Session already started at top, just store user info
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['user_name'] = $user['NAME'];
                $_SESSION['user_role'] = $user['role'];

                sendResponse("success", "Login successful!");
            } else {
                sendResponse("error", "Incorrect password!");
            }
        } else {
            sendResponse("error", "Email not found!");
        }
    }

    // ========= USER LOGOUT =========
    if ($action == 'logout') {
        // Clear session variables
        $_SESSION = [];
        // session_unset(); // or use this

        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Prevent browser from caching old pages
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Destroy session
        session_destroy();
        sendResponse("success", "Logged out successfully!");
    }

    // ========= USER REGISTRATION =========
    if ($action == 'register') {
        $NAME = sanitize($conn, $_POST['NAME'] ?? '');
        $EMAIL = sanitize($conn, $_POST['EMAIL'] ?? '');
        $PASSWORD = sanitize($conn, $_POST['PASSWORD'] ?? '');
        $ROLE = 0; // default role = User

        if (empty($NAME) || empty($EMAIL) || empty($PASSWORD))
            sendResponse("error", "All fields are required!");

        if (!filter_var($EMAIL, FILTER_VALIDATE_EMAIL))
            sendResponse("error", "Invalid email format!");

        if (empty($_FILES['photo']['name']))
            sendResponse("error", "Photo is required!");

        $photo = $_FILES['photo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($photo['type'], $allowedTypes))
            sendResponse("error", "Only JPG, JPEG, and PNG formats are allowed!");
        if ($photo['size'] > 2 * 1024 * 1024)
            sendResponse("error", "Image must be 2MB or smaller!");

        $photoName = time() . "_" . basename($photo['name']);
        $target = "../uploads/" . $photoName;
        if (!move_uploaded_file($photo['tmp_name'], $target))
            sendResponse("error", "Failed to upload image!");

        // check if email already exists
        $check = $conn->query("SELECT * FROM users WHERE EMAIL='$EMAIL' LIMIT 1");
        
        $hashedPassword = password_hash($PASSWORD, PASSWORD_DEFAULT);

        if ($check->num_rows > 0) {
            $existingUser = $check->fetch_assoc();
            
            if ($existingUser['STATUS'] == 1) {
                sendResponse("error", "Email already registered!");
            } else {
                // Reactivate existing inactive account
                $update = "UPDATE users 
                        SET NAME='$NAME', password='$hashedPassword', role=$ROLE, 
                            photo='$photoName', STATUS=1 
                        WHERE EMAIL='$EMAIL'";
                
                if ($conn->query($update)) {
                    sendResponse("success", "Account reactivated successfully! Please login.");
                } else {
                    sendResponse("error", "Failed to reactivate account: " . $conn->error);
                }
            }
        } else {
            // Create new account
            $query = "INSERT INTO users (NAME, EMAIL, password, role, photo, STATUS)
                    VALUES ('$NAME', '$EMAIL', '$hashedPassword', $ROLE, '$photoName', 1)";

            if ($conn->query($query)) {
                sendResponse("success", "Account created successfully! Please login.");
            } else {
                sendResponse("error", "Registration failed: " . $conn->error);
            }
        }
    }

    //  ========= PROTECT ALL ACTIONS BELOW (Require Login) =========
    if (!isset($_SESSION['user_id'])) {
        sendResponse("unauthorized", "You must be logged in to perform this action.");
        exit;
    }

    // ========= INSERT USER =========
    if ($action == 'insert') {
        // Sanitize: Trim extra spaces
        $NAME = sanitize($conn, $_POST['NAME'] ?? '');
        $EMAIL = sanitize($conn, $_POST['EMAIL'] ?? '');
        $PASSWORD = sanitize($conn, $_POST['PASSWORD'] ?? '');
        $ROLE = intval($_POST['ROLE'] ?? 0);

        // Individual backend "empty field" validation
        if (empty($NAME)) sendResponse("error", "Name field is empty!");
        if (empty($EMAIL)) sendResponse("error", "Email field is empty!");
        if (empty($PASSWORD)) sendResponse("error", "Password is required!");
        // if (empty($_FILES['photo']['name'])) sendResponse("error", "Photo is missing!"); // make photo optional

        // Backend valid email validation (cannot be bypassed)
        if (!filter_var($EMAIL, FILTER_VALIDATE_EMAIL)) { // use rajx
            sendResponse("error", "Invalid email format!");
        }
        // hashing of password
        $hashedPassword = password_hash($PASSWORD, PASSWORD_DEFAULT);

        // Handle optional image upload
        $photoFinal = "";
        if (!empty($_FILES['photo']['name'])) {
            $photo = $_FILES['photo'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

            if (!in_array($photo['type'], $allowedTypes))
                sendResponse("error", "Only JPG, JPEG, and PNG formats are allowed!");
            if ($photo['size'] > 2 * 1024 * 1024)
                sendResponse("error", "Image must be 2MB or smaller!");

            $photoName = time() . "_" . basename($photo['name']);
            $target = "../uploads/" . $photoName;

            if (!move_uploaded_file($photo['tmp_name'], $target))
                sendResponse("error", "Failed to upload image!");

            $photoFinal = $photoName; 
        }
        
        // Insert into database
        $query = "INSERT INTO users (NAME, EMAIL, password, role, photo)
            VALUES ('$NAME', '$EMAIL', '$hashedPassword', '$ROLE', '$photoFinal')";

        if ($conn->query($query) === TRUE) {
            sendResponse("success", "User added successfully!");
        } else {
            sendResponse("error", "Database error: " . $conn->error);
        }
    }

    // ========= DELETE USER =========
    if ($action == 'delete') {
        $ID = intval($_POST['id'] ?? 0);
        if ($ID <= 0) sendResponse("error", "Invalid user ID!");

        // Delete photo from folder
        $res = $conn->query("SELECT photo FROM users WHERE ID=$ID");
        if ($res->num_rows > 0) {
            $photo = $res->fetch_assoc()['photo'];
            $path = "../uploads/" . $photo;
            if ($photo && file_exists($path)) unlink($path);
        }
        
        // Delete from database
        $query = "UPDATE users SET STATUS = 0 WHERE ID=$ID";
        if ($conn->query($query)) {
            sendResponse("success", "User deleted successfully!");
        } else {
            sendResponse("error", "Delete failed: " . $conn->error);
        }
    }

    // ========= UPDATE USER =========
    if ($action == 'update') {
        $userRole = $_SESSION['user_role'];
        $userId   = $_SESSION['user_id'];

        // Sanitize: Trim extra spaces
        $ID = intval($_POST['ID'] ?? 0);
        $NAME = sanitize($conn, $_POST['NAME'] ?? '');
        $EMAIL = sanitize($conn, $_POST['EMAIL'] ?? '');

        if ($ID <= 0) sendResponse("error", "Invalid user ID!");
        if (empty($NAME)) sendResponse("error", "Name field is empty!");

        // Restrict: Normal user can only edit themselves
        if ($userRole == 0 && $userId != $ID) {
            sendResponse("error", "Access denied! You can only edit your own profile.");
        }

        // Build update query dynamically
        $updateFields = "NAME='$NAME'";

        // Only admin can update email
        if ($userRole == 1) {
            if (empty($EMAIL)) sendResponse("error", "Email cannot be empty!");
            if (!filter_var($EMAIL, FILTER_VALIDATE_EMAIL)) sendResponse("error", "Invalid email format!");
            $updateFields .= ", EMAIL='$EMAIL'";
        }

        // Handle optional image upload
        $photoUpdate = "";
        if (!empty($_FILES['photo']['name'])) {
            $photo = $_FILES['photo'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

            if (!in_array($photo['type'], $allowedTypes))
                sendResponse("error", "Only JPG, JPEG, and PNG formats are allowed!");
            if ($photo['size'] > 2 * 1024 * 1024)
                sendResponse("error", "Image must be 2MB or smaller!");

            $photoName = time() . "_" . basename($photo['name']);
            $target = "../uploads/" . $photoName;

            if (!move_uploaded_file($photo['tmp_name'], $target))
                sendResponse("error", "Failed to upload image!");

            $photoUpdate = ", photo='$photoName'";
        }

        // Handle optional password update
        $passwordUpdate = "";
        if (!empty($_POST['password'])) {
            $newPass = password_hash(sanitize($conn, $_POST['password']), PASSWORD_DEFAULT);
            $passwordUpdate = ", password='$newPass'";
        }

        // Only admin can update role (from edit form)
        if ($userRole == 1 && isset($_POST['ROLE'])) {
            $newRole = intval($_POST['ROLE']);
            $updateFields .= ", role=$newRole";
        }

        // Combine all updates
        $query = "UPDATE users SET $updateFields $photoUpdate $passwordUpdate WHERE ID=$ID";

        if ($conn->query($query)) {
            sendResponse("success", "User updated successfully!");
        } else {
            sendResponse("error", "Update failed: " . $conn->error);
        }
    }

    // ========= BULK USER UPLOAD =========
    if ($action == 'bulk_upload') {    
        // empty field check
        if (empty($_FILES['file']['name'])) {
            sendResponse("error", "Please select a file!");
        }
        
        // file validation
        $file = $_FILES['file'];
        $allowed = ['application/vnd.ms-excel', 'text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (!in_array($file['type'], $allowed) && !in_array($ext, ['csv', 'xlsx'])) {
            sendResponse("error", "Invalid file type! Only Excel or CSV allowed.");
        }

        // move in to temporary folder
        $target = "../uploads/bulk_files/" . time() . "_" . basename($file['name']);
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            sendResponse("error", "Failed to upload file!");
        }

        $rows = [];

        if ($ext === "csv") {
            // Read CSV
            if (($handle = fopen($target, "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            // Read Excel using PhpSpreadsheet (if available)
            require_once "../includes/excel_reader.php"; // custom lightweight reader we'll add below
            $rows = readExcelFile($target);
        }

        // Remove header if present
        if (isset($rows[0]) && str_contains(strtolower(implode(',', $rows[0])), 'name'))
            array_shift($rows);

        if (empty($rows))
            sendResponse("error", "File is empty or invalid format!");

        $invalidFound = false;
        $validUsers = [];

        foreach ($rows as $row) {
            $NAME = trim($row[0] ?? '');
            $EMAIL = trim($row[1] ?? '');
            $PASSWORD = trim($row[2] ?? '');
            $PHOTO = trim($row[3] ?? ''); // optional

            if (empty($NAME) || empty($EMAIL) || empty($PASSWORD)) {
                $invalidFound = true;
                break;
            }

            $validUsers[] = [
                'NAME' => $NAME,
                'EMAIL' => $EMAIL,
                'PASSWORD' => password_hash($PASSWORD, PASSWORD_DEFAULT),
                'PHOTO' => $PHOTO,
                'ROLE' => 0
            ];
        }

        if ($invalidFound)
            sendResponse("error", "Some rows have empty fields! Please check the Excel/CSV file.");

        // Insert all valid users
        $successCount = 0;
        foreach ($validUsers as $u) {
            $NAME = $u['NAME'];
            $EMAIL = $u['EMAIL'];
            $PASSWORD = $u['PASSWORD'];
            $PHOTO = $u['PHOTO'];
            $ROLE = 0;

            $check = $conn->query("SELECT * FROM users WHERE EMAIL='$EMAIL'");
            if ($check->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO users (NAME, EMAIL, password, role, photo) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssis", $NAME, $EMAIL, $PASSWORD, $ROLE, $PHOTO);
                if ($stmt->execute()) $successCount++;
            }
        }

        sendResponse("success", "$successCount users uploaded successfully!");
    }

    // ========= EXPORT USERS =========
    if ($action == 'export_users') {
        // Only Admin can export
        if ($_SESSION['user_role'] != 1) {
            sendResponse("error", "Access denied! Only admin can export users.");
        }

        $sql = "SELECT * FROM users WHERE STATUS = 1 ORDER BY ID DESC";
        $result = $conn->query($sql);

        if (!$result || $result->num_rows === 0) {
            sendResponse("error", "No active users found to export.");
        }

        // Build CSV content in memory
        $csv = "S.No,Name,Email,Role,Status\n";
        $serial = 1;
        while ($row = $result->fetch_assoc()) {
            $role = $row['role'] == 1 ? 'Admin' : 'User';
            $status = $row['STATUS'] == 1 ? 'Active' : 'Inactive';
            $csv .= "$serial,{$row['NAME']},{$row['EMAIL']},$role,$status\n";
            $serial++;
        }

        // Send CSV as downloadable file
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_export.csv"');
        echo $csv;
        exit;
    }

    // ========= GET RECYCLE BIN USERS =========
    if ($action == 'get_recycle_bin') {
        // Only Admin can Activate user
        if ($_SESSION['user_role'] != 1) {
            sendResponse("error", "Access denied! Only admin can view recycle bin.");
        }

        $sql = "SELECT ID, NAME, EMAIL, role, photo FROM users WHERE STATUS = 0 ORDER BY ID DESC";
        $result = $conn->query($sql);

        if (!$result || $result->num_rows === 0) {
            sendResponse("error", "No deactivated users found.");
        }

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "users" => $users
        ]);
        exit;
    }

    // ========= ACTIVATE USER =========
    if ($action == 'activate_user') {
        // Only Admin can Activate
        $ID = intval($_POST['id'] ?? 0);
        if ($ID <= 0) sendResponse("error", "Invalid user ID!");

        $query = "UPDATE users SET STATUS = 1 WHERE ID = $ID";
        if ($conn->query($query)) {
            sendResponse("success", "User reactivated successfully!");
        } else {
            sendResponse("error", "Activation failed: " . $conn->error);
        }
    }

    // =========  PAGINATION (SEARCH USER) =========
    if ($action == 'search_users') {
        $user_role = $_SESSION['user_role'] ?? 0;
        $user_id   = $_SESSION['user_id'] ?? 0;

        $search = sanitize($conn, $_POST['search'] ?? '');
        $role   = sanitize($conn, $_POST['role'] ?? '');
        $page   = max(1, intval($_POST['page'] ?? 1));
        $limit  = 5;
        $offset = ($page - 1) * $limit;

        // Build base WHERE clause
        if ($user_role == 1) {
            $where = "WHERE STATUS = 1";
        } else {
            $where = "WHERE ID = $user_id AND STATUS = 1";
        }

        // Add search and role filters
        if (!empty($search)) {
            $s = $search;
            $where .= " AND (NAME LIKE '%$s%' OR EMAIL LIKE '%$s%')";
        }
        if (!empty($role)) {
            $roleVal = ($role == 'admin') ? 1 : 0;
            $where .= " AND role = $roleVal";
        } 

        // total count for filtered results
        $countSql = "SELECT COUNT(*) as total FROM users $where";
        $countRes = $conn->query($countSql);
        $totalRows = ($countRes && $countRes->num_rows) ? intval($countRes->fetch_assoc()['total']) : 0;
        $totalPages = max(1, intval(ceil($totalRows / $limit)));

        // fetch paginated rows
        $sql = "SELECT * FROM users $where ORDER BY ID DESC LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);

        // render HTML table (same structure as list_users)
        if ($result && $result->num_rows > 0) {
            $serial = $offset + 1;
            $html = "<table>";
            $html .= "<tr>
                        <th>S.No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Photo</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>";

            while ($row = $result->fetch_assoc()) {
                $photoPath = $row['photo'] ? "uploads/" . $row['photo'] : "assets/img/default.png";
                $roleLabel = $row['role'] == 1 ? "Admin" : "User";

                $html .= "<tr>";
                $html .= "<td>" . $serial++ . "</td>";
                $html .= "<td>" . htmlspecialchars($row['NAME']) . "</td>";
                $html .= "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
                $html .= "<td><img src='$photoPath' width='50' height='50' style='cursor:pointer' onclick=\"openFullImage('$photoPath')\"></td>";
                $html .= "<td>$roleLabel</td>";
                $html .= "<td><a href='index.php?page=edit_user&ID={$row['ID']}'>Edit</a>";
                if ($user_role == 1) {
                    $html .= " | <button class='deleteBtn' data-id='{$row['ID']}'>Delete</button>";
                }
                $html .= "</td></tr>";
            }

            $html .= "</table>";

            // pagination HTML
            if ($totalPages > 1) {
                $html .= '<div style="margin-top:12px; text-align:center;">';
                $prev = max(1, $page - 1);
                $html .= "<button class='paginationBtn' data-page='$prev' " . ($page==1 ? "disabled" : "") . ">Prev</button> ";

                for ($p = 1; $p <= $totalPages; $p++) {
                    $cls = $p == $page ? "style='font-weight:bold; text-decoration:underline;'" : "";
                    $html .= "<button class='paginationBtn' data-page='$p' $cls>$p</button> ";
                }

                $next = min($totalPages, $page + 1);
                $html .= " <button class='paginationBtn' data-page='$next' " . ($page==$totalPages ? "disabled" : "") . ">Next</button>";
                $html .= '</div>';
            }

            echo $html;
        } else {
            echo "<p>No users found.</p>";
        }
        exit;
    }

    sendResponse("error", "Invalid action request!");
?>
