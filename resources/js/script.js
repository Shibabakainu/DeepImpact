function toggleSidebar(button) {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
    
    // ボタンを左にずらす
    button.classList.toggle('move-left');
}
