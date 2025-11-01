<div style="
    display: flex; 
    justify-content: center; 
    align-items: center;
">
    <div style="
        background: #fff; 
        padding: 40px 35px; 
        border-radius: 15px; 
        box-shadow: 0 8px 25px rgba(0,0,0,0.3); 
        width: 350px; 
        text-align: center;
    ">
        <h2>Create Account</h2>
        <form id="registerForm" enctype="multipart/form-data">
            <label>Name:</label><br>
            <input type="text" name="NAME" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;outline:none;"><br><br>

            <label>Email:</label><br>
            <input type="text" name="EMAIL" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;outline:none;"><br><br>

            <label>Password:</label><br>
            <input type="password" name="PASSWORD" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;outline:none;"><br><br>

            <label>Photo:</label><br>
            <input type="file" name="photo" accept="image/*"><br><br>

            <button type="submit" style="
                width:100%; 
                background:#28a745; 
                color:white; 
                border:none; 
                padding:10px; 
                border-radius:8px; 
                cursor:pointer; 
                font-size:16px;
            ">Register</button>
        </form>
        <p style="margin-top:10px;">Already have an account? 
            <a href="index.php?page=login">Login</a>
        </p>
    </div>
</div>
