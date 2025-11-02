<?php
    // list_users.php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $user_id   = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'];

    $limit = 5; // <- page size
    $page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
    $offset = ($page - 1) * $limit;

    // Build base WHERE for count & fetch
    $where = "WHERE STATUS = 1";
    if ($user_role != 1) {
        $where = "WHERE ID = $user_id AND STATUS = 1";
    }

    // Get total count for pagination (only needed for admins but safe to run)
    $countSql = "SELECT COUNT(*) as total FROM users $where";
    $countRes = $conn->query($countSql);
    $totalRows = ($countRes && $countRes->num_rows) ? intval($countRes->fetch_assoc()['total']) : 0;
    $totalPages = max(1, intval(ceil($totalRows / $limit)));

    // Fetch paginated rows
    $sql = ($user_role == 1)
        ? "SELECT * FROM users WHERE STATUS = 1 ORDER BY ID DESC LIMIT $limit OFFSET $offset"
        : "SELECT * FROM users WHERE ID = $user_id AND STATUS = 1 ORDER BY ID DESC LIMIT $limit OFFSET $offset";

    $result = $conn->query($sql);
?>

<h2>User List</h2>

<?php
    if ($user_role == 1) {
        echo '
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <div>
                <input type="text" id="searchInput" placeholder="Search by name or email..." style="padding:5px;">
                <select id="roleFilter" style="padding:5px;">
                    <option value="">All Roles</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                <button id="searchBtn" style="padding:5px 10px;">Search</button>
            </div>
            <div>
                <button id="exportBtn">Export to CSV</button>
                <button id="recycleBinBtn" style="margin-left:8px;">Recycle Bin</button>
            </div>
        </div>';
    }
?>

<div id="userTableContainer">
    <?php
        if ($result && $result->num_rows > 0) {
            echo "<table>";
            echo "<tr>
                    <th>S.No</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Photo</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>";

            $serial = $offset + 1; // serial continues across pages

            while ($row = $result->fetch_assoc()) {
                $photoPath = $row['photo'] ? "uploads/" . $row['photo'] : "assets/img/default.png";
                $roleLabel = $row['role'] == 1 ? "Admin" : "User";

                echo "<tr>";
                echo "<td>" . $serial++ . "</td>";
                echo "<td>" . htmlspecialchars($row['NAME']) . "</td>";
                echo "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
                echo "<td><img src='$photoPath' width='50' height='50' style='cursor:pointer' onclick=\"openFullImage('$photoPath')\"></td>";
                echo "<td>$roleLabel</td>";

                echo "<td>";
                echo "<a href='index.php?page=edit_user&ID={$row['ID']}'>Edit</a>";
                if ($user_role == 1) {
                    echo " | <button class='deleteBtn' data-id='{$row['ID']}'>Delete</button>";
                }
                echo "</td></tr>";
            }
            echo "</table>";

            // ---------- Pagination controls ----------
            if ($totalPages > 1) {
                echo '<div style="margin-top:12px; text-align:center;">';
                // Previous
                $prev = max(1, $page - 1);
                echo "<button class='paginationBtn' data-page='$prev' " . ($page==1 ? "disabled" : "") . ">Prev</button> ";

                // show pages (for simplicity show all pages; for very large counts you'd want a compact UI)
                for ($p = 1; $p <= $totalPages; $p++) {
                    $cls = $p == $page ? "style='font-weight:bold; text-decoration:underline;'" : "";
                    echo "<button class='paginationBtn' data-page='$p' $cls>$p</button> ";
                }

                // Next
                $next = min($totalPages, $page + 1);
                echo " <button class='paginationBtn' data-page='$next' " . ($page==$totalPages ? "disabled" : "") . ">Next</button>";
                echo '</div>';
            }

        } else {
            echo "<p>No users found.</p>";
        }
    ?>
</div>
