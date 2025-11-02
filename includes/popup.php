<!-- Popup Container -->
<div class="popup-overlay" id="popupOverlay">
    <div class="popup" id="popupBox">
        <div class="popup-icon" id="popupIcon"></div>
        <h3 id="popupTitle"></h3>
        <p id="popupMessage"></p>
        <div id="popupButtons"></div>
    </div>
</div>

<!-- Full Image Preview Popup -->
<div id="imagePopupOverlay" style="display:none; position:fixed; 
        top:0; left:0; right:0; bottom:0; 
        background:rgba(0,0,0,0.8); 
        justify-content:center; 
        align-items:center;
        z-index:9999; ">
        
    <img id="fullImagePreview" src="" style="max-width:90%; 
        max-height:90%; 
        border-radius:10px; 
        box-shadow:0 0 15px rgba(255,255,255,0.3); 
        cursor:pointer;">
</div>
