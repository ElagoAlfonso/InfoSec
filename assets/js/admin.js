function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent =
        now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) +
        '  ' + now.toLocaleTimeString('en-US', { hour12: false });
}
updateClock();
setInterval(updateClock, 1000);

document.getElementById('searchInput').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#userTable tr[data-name]').forEach(row => {
        const match = row.dataset.name.includes(q) || row.dataset.email.includes(q);
        row.style.display = match ? '' : 'none';
    });
});