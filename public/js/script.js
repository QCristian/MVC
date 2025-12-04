document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', () => {
        const id = link.dataset.target;
        const content = document.getElementById(id).innerHTML;
        document.getElementById('dashboard-main').innerHTML = content;
    });
});
