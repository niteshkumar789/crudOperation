document.addEventListener("DOMContentLoaded", function () {

    // ===== POPUP HANDLER FUNCTION =====
    function showPopup(type, title, message, buttons = [{ text: "OK", class: "ok", callback: closePopup }]) {
        const overlay = document.getElementById("popupOverlay");
        const popupBox = document.getElementById("popupBox");
        const popupTitle = document.getElementById("popupTitle");
        const popupMsg = document.getElementById("popupMessage");
        const popupBtns = document.getElementById("popupButtons");
        const popupIcon = document.getElementById("popupIcon");

        popupBox.className = "popup " + type;
        popupBtns.innerHTML = "";

        let icon = "";
        if (type === "success") icon = "✓";
        else if (type === "error") icon = "❌";
        else if (type === "warning") icon = "⚠️";
        else icon = "ℹ️"; 

        popupIcon.textContent = icon;
        popupTitle.textContent = title;
        popupMsg.textContent = message;

        buttons.forEach(btn => {
            const button = document.createElement("button");
            button.textContent = btn.text;
            button.classList.add(btn.class || "ok");
            button.onclick = btn.callback;
            popupBtns.appendChild(button);
        });

        overlay.style.display = "flex";
    }
    function closePopup() {
        document.getElementById("popupOverlay").style.display = "none";
    }

    // ========================================== ADD USER EVENT ==========================================
    const addForm = document.getElementById("userForm");
    if (addForm) {
        addForm.addEventListener("submit", function (e) {
            e.preventDefault();

            // _______________ PHP _______________
            const formData = new FormData(addForm);
            formData.append("action", "insert");
            fetch("ajax/user_actions.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {

                  
                    // show popups
                    if (data.status === "success") {
                        showPopup("success", "Success", data.message);
                        addForm.reset();
                    } else {
                        showPopup("error", "Error", data.message);
                    }
                })
                .catch(err => showPopup("error", "Error", "Insert error: " + err));
        });
    }

    // ========================================== DELETE USER EVENT ==========================================
    document.querySelectorAll(".deleteBtn").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.getAttribute("data-id");

            showPopup(
                "warning",
                "Confirm Delete",
                "Are you sure you want to delete this user?",
                [
                    { text: "Cancel", class: "cancel", callback: closePopup },
                    {
                        text: "Delete",
                        class: "delete",
                        callback: function () {

                            // _______________ PHP _______________
                            const formData = new FormData();
                            formData.append("action", "delete");
                            formData.append("id", id);
                            fetch("ajax/user_actions.php", {
                                method: "POST",
                                body: formData
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.status === "success") {
                                        showPopup("success", "Deleted", data.message, [{
                                            text: "OK",
                                            class: "ok",
                                            callback: function () {
                                                closePopup();
                                                location.reload();
                                            }
                                        }]);
                                    } else {
                                        showPopup("error", "Error", data.message);
                                    }
                                })
                                .catch(err => showPopup("error", "Error", "Delete error: " + err));
                        }
                    }
                ]
            );
        });
    });

    // ========================================== UPDATE USER EVENT ==========================================
    const editForm = document.getElementById("editUserForm");
    if (editForm) {
        editForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(editForm);
            formData.append("action", "update");

            fetch("ajax/user_actions.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        showPopup("success", "Updated", data.message, [
                            {
                                text: "OK",
                                class: "ok",
                                callback: function () {
                                    closePopup();
                                    window.location.href = "index.php?page=list_users";
                                }
                            }
                        ]);
                    } else {
                        showPopup("error", "Error", data.message);
                    }
                })
                .catch(err => showPopup("error", "Error", "Update error: " + err));
        });
    }


    // ========================================== LOGIN EVENT ==========================================
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();
            
            // _______________ PHP _______________
            const formData = new FormData(loginForm);
            formData.append("action", "login");

            fetch("ajax/user_actions.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        showPopup("success", "Login Successful", data.message, [
                            {
                                text: "OK",
                                class: "ok",
                                callback: function () {
                                    closePopup();
                                    window.location.href = "index.php?page=home";
                                }
                            }
                        ]);
                    } else {
                        showPopup("error", "Login Failed", data.message);
                    }
                })
                .catch(err => showPopup("error", "Error", "Login error: " + err));
        });
    }

    // ========================================== REGISTER EVENT ==========================================
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(registerForm);
            formData.append("action", "register");

            fetch("ajax/user_actions.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        showPopup("success", "Registration Successful", data.message, [
                            {
                                text: "OK",
                                class: "ok",
                                callback: function () {
                                    closePopup();
                                    window.location.href = "index.php?page=login";
                                }
                            }
                        ]);
                    } else {
                        showPopup("error", "Error", data.message);
                    }
                })
                .catch(err => showPopup("error", "Error", "Registration error: " + err));
        });
    }

    // ========================================== BULK UPLOAD EVENT ==========================================
    const bulkUploadForm = document.getElementById("bulkUploadForm");
    if (bulkUploadForm) {
        bulkUploadForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(bulkUploadForm);
            formData.append("action", "bulk_upload");

            fetch("ajax/user_actions.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        showPopup("success", "Upload Successful", data.message);
                        bulkUploadForm.reset();
                    } else {
                        showPopup("error", "Error", data.message);
                    }
                })
                .catch(err => showPopup("error", "Error", "Bulk upload failed: " + err));
        });
    }

    // ========================================== EXPORT USERS EVENT ==========================================
    const exportBtn = document.getElementById("exportBtn");
    if (exportBtn) {
        exportBtn.addEventListener("click", function () {
            fetch("ajax/user_actions.php", {
                method: "POST",
                body: new URLSearchParams({ action: "export_users" })
            })
            .then(res => {
                if (!res.ok) throw new Error("Network error");
                return res.blob(); // CSV as blob
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = "users_export.csv";
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            })
            .catch(err => {
                showPopup("error", "Export Failed", err.message);
            });
        });
    }

    // ========================================== RECYCLE BIN EVENT ==========================================
    const recycleBinBtn = document.getElementById("recycleBinBtn");
    if (recycleBinBtn) {
        recycleBinBtn.addEventListener("click", function () {
            fetch("ajax/user_actions.php", {
                method: "POST",
                body: new URLSearchParams({ action: "get_recycle_bin" })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success" ) {
                    let html = `
                        <h2>Recycle Bin - Deactivated Users</h2>
                        <table>
                            <tr>
                                <th>S.No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Photo</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                    `;
                    data.users.forEach((u, i) => {
                        html += `
                            <tr>
                                <td>${i + 1}</td>
                                <td>${u.NAME}</td>
                                <td>${u.EMAIL}</td>
                                <td><img src="uploads/${u.photo || 'assets/img/default.png'}" width="50" height="50"></td>
                                <td>${u.role == 1 ? 'Admin' : 'User'}</td>
                                <td><button class="activateBtn" data-id="${u.ID}">Activate</button></td>
                            </tr>
                        `;
                    });
                    html += `</table>`;
                    document.querySelector("main").innerHTML = html;

                    // Rebind activation buttons
                    document.querySelectorAll(".activateBtn").forEach(btn => {
                        btn.addEventListener("click", function () {
                            const id = this.getAttribute("data-id");

                            showPopup(
                                "warning",
                                "Reactivate User",
                                "Are you sure you want to activate this user?",
                                [
                                    { text: "Cancel", class: "cancel", callback: closePopup },
                                    {
                                        text: "Activate",
                                        class: "ok",
                                        callback: function () {
                                            const formData = new FormData();
                                            formData.append("action", "activate_user");
                                            formData.append("id", id);

                                            fetch("ajax/user_actions.php", {
                                                method: "POST",
                                                body: formData
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.status === "success") {
                                                    showPopup("success", "Activated", data.message, [{
                                                        text: "OK",
                                                        class: "ok",
                                                        callback: function () {
                                                            closePopup();
                                                            recycleBinBtn.click(); // reload recycle bin table
                                                        }
                                                    }]);
                                                } else {
                                                    showPopup("error", "Error", data.message);
                                                }
                                            })
                                            .catch(err => showPopup("error", "Error", "Activation failed: " + err));
                                        }
                                    }
                                ]
                            );
                        });
                    });
                } else {
                    showPopup("info", "Recycle Bin", data.message);
                }
            })
            .catch(err => {
                showPopup("error", "Error", "Failed to fetch recycle bin: " + err);
            });
        });
    }

    // ========================================== SEARCH + FILTER EVENT ==========================================
    // const searchBtn = document.getElementById("searchBtn");
    // if (searchBtn) {
    //     searchBtn.addEventListener("click", function () {
    //         const search = document.getElementById("searchInput").value.trim();
    //         const role = document.getElementById("roleFilter").value.trim();

    //         // Send search + filter to PHP
    //         fetch("ajax/user_actions.php", {
    //             method: "POST",
    //             body: new URLSearchParams({
    //                 action: "search_users",
    //                 search: search,
    //                 role: role
    //             })
    //         })
    //         .then(res => res.text())
    //         .then(html => {
    //             // Replace only the user table container content
    //             document.getElementById("userTableContainer").innerHTML = html;
    //         })
    //         .catch(err => {
    //             showPopup("error", "Error", "Search failed: " + err);
    //         });
    //     });
    // }

    // ----------------- Delegated delete handler (works for dynamic rows) -----------------
document.addEventListener("click", function (e) {
    if (e.target && e.target.classList.contains("deleteBtn")) {
        const id = e.target.getAttribute("data-id");

        showPopup(
            "warning",
            "Confirm Delete",
            "Are you sure you want to delete this user?",
            [
                { text: "Cancel", class: "cancel", callback: closePopup },
                {
                    text: "Delete",
                    class: "delete",
                    callback: function () {
                        const formData = new FormData();
                        formData.append("action", "delete");
                        formData.append("id", id);
                        fetch("ajax/user_actions.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === "success") {
                                showPopup("success", "Deleted", data.message, [{
                                    text: "OK",
                                    class: "ok",
                                    callback: function () {
                                        closePopup();
                                        // reload current table page by triggering search (keeps filters)
                                        const search = document.getElementById("searchInput")?.value || "";
                                        const role = document.getElementById("roleFilter")?.value || "";
                                        loadUsers(1, search, role);
                                    }
                                }]);
                            } else {
                                showPopup("error", "Error", data.message);
                            }
                        })
                        .catch(err => showPopup("error", "Error", "Delete error: " + err));
                    }
                }
            ]
        );
    }
});

// ----------------- pagination click (delegated) -----------------
document.addEventListener("click", function (e) {
    if (e.target && e.target.classList.contains("paginationBtn")) {
        e.preventDefault();
        const page = e.target.getAttribute("data-page") || 1;
        const search = document.getElementById("searchInput")?.value || "";
        const role = document.getElementById("roleFilter")?.value || "";
        loadUsers(page, search, role);
    }
});

// ----------------- helper: loadUsers (page, search, role) -----------------
function loadUsers(page = 1, search = "", role = "") {
    const body = new URLSearchParams();
    body.append("action", "search_users");
    body.append("page", page);
    body.append("search", search);
    body.append("role", role);

    fetch("ajax/user_actions.php", {
        method: "POST",
        body: body
    })
    .then(res => res.text())
    .then(html => {
        const container = document.getElementById("userTableContainer");
        if (container) {
            container.innerHTML = html;
            // re-enable image click -> openFullImage works because server HTML uses onclick to call it
            // No need to rebind delete/pagination because we use delegated handlers
        }
    })
    .catch(err => {
        showPopup("error", "Error", "Failed to load users: " + err);
    });
}

// Modify search button to use loadUsers(1)
const searchBtn = document.getElementById("searchBtn");
if (searchBtn) {
    searchBtn.addEventListener("click", function () {
        const search = document.getElementById("searchInput").value.trim();
        const role = document.getElementById("roleFilter").value.trim();
        loadUsers(1, search, role);
    });
}

// Optionally, load first page via AJAX on page load to ensure consistent behavior:
// Only if you want AJAX-initialized table. If you prefer server-rendered initial table, comment out the next lines.
// const initialSearch = document.getElementById("searchInput")?.value || "";
// const initialRole = document.getElementById("roleFilter")?.value || "";
// loadUsers(1, initialSearch, initialRole);

    
    
    // ========================================== IMAGE PREVIEW EVENT ==========================================
    // For Add User Form
    const photoInput = document.getElementById("photoInput");
    const photoPreviewContainer = document.getElementById("photoPreviewContainer");
    const photoPreview = document.getElementById("photoPreview");

    if (photoInput) {
        photoInput.addEventListener("change", function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    photoPreview.src = e.target.result;
                    photoPreviewContainer.style.display = "block";
                };
                reader.readAsDataURL(file);
            } else {
                photoPreviewContainer.style.display = "none";
                photoPreview.src = "";
            }
        });
    }

    // For Edit User Form
    const editPhotoInput = document.getElementById("editPhotoInput");
    const editPhotoPreview = document.getElementById("editPhotoPreview");

    if (editPhotoInput) {
        editPhotoInput.addEventListener("change", function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    editPhotoPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ========================================== CLICKABLE FULL IMAGE PREVIEW EVENT ==========================================
    const imagePopupOverlay = document.getElementById("imagePopupOverlay");
    const fullImagePreview = document.getElementById("fullImagePreview");

    function openFullImage(src) {
        fullImagePreview.src = src;
        imagePopupOverlay.style.display = "flex";
    }

    function closeFullImage() {
        imagePopupOverlay.style.display = "none";
        fullImagePreview.src = "";
    }

    // Close when clicking anywhere on the overlay
    if (imagePopupOverlay) {
        imagePopupOverlay.addEventListener("click", closeFullImage);
    }

    // Make add form preview clickable
    if (photoPreview) {
        photoPreview.addEventListener("click", function () {
            openFullImage(this.src);
        });
    }

    // Make edit form preview clickable
    if (editPhotoPreview) {
        editPhotoPreview.addEventListener("click", function () {
            openFullImage(this.src);
        });
    }

});
