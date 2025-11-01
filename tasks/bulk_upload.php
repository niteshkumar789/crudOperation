<div style="display:flex;justify-content:center;align-items:center;">
    <div style="
        background:#fff;
        padding:40px;
        border-radius:12px;
        box-shadow:0 6px 20px rgba(0,0,0,0.25);
        width:400px;
        text-align:center;
    ">
        <h2>Bulk Upload Users</h2>
        <form id="bulkUploadForm" enctype="multipart/form-data">
            <label>Select Excel/CSV File:</label><br><br>
            <input type="file" name="file" accept=".xlsx,.xls,.csv,.XLSX,.XLS,.CSV"><br><br>

            <button type="submit" style="
                width:100%;
                background:#007bff;
                color:white;
                padding:10px;
                border:none;
                border-radius:8px;
                font-size:16px;
                cursor:pointer;
            ">Upload File</button>
        </form>
    </div>
</div>
