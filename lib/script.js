document.getElementById("sidebarToggle").addEventListener("click", function() {
    const sidebar = document.getElementById("sidebar");
    
    // Toggle class "visible" untuk menampilkan sidebar dari atas pada layar kecil
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle("visible");
    } else {
        // Mode biasa tetap gunakan toggle class "collapsed"
        sidebar.classList.toggle("collapsed");
    }
});