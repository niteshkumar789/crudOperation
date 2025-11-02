<div class="add-user-page">
    <div class="add-user-container">
        <h2>Add New User</h2>

        <form id="userForm" enctype="multipart/form-data">
            <!-- name -->
            <label>Name:</label><br>
            <input type="text" name="NAME"><br><br>

            <!-- email -->
            <label>Email:</label><br>
            <input type="text" name="EMAIL"><br><br>

            <!-- password -->
            <label>Password:</label><br>
            <input type="password" name="PASSWORD"><br><br>

            <!-- role selection -->
            <label>Role:</label><br>
            <select name="ROLE">
                <option value="0">User</option>
                <option value="1">Admin</option>
            </select><br><br>
            
            <!-- photo -->
            <label>Photo:</label><br>
            <input type="file" name="photo" id="photoInput" accept="image/*"><br><br>

            <!-- preview box -->
            <div id="photoPreviewContainer" style="display:none;">
                <img id="photoPreview" src="" alt="Image Preview">
            </div><br>
            
            <!-- submit button -->
            <button type="submit" class="add-user-btn">Add User</button>
        </form>
    </div>
</div>
