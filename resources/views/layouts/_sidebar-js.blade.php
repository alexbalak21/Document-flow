{{-- Sidebar collapse + nav-group toggle JS --}}
<script>
const sidebar     = document.getElementById('sidebar');
const toggleIcon  = document.getElementById('toggle-icon');
const STORAGE_KEY = 'df_sidebar_collapsed';

function toggleSidebar() {
    const collapsed = sidebar.classList.toggle('collapsed');
    toggleIcon.className = collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
    localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
}

if (localStorage.getItem(STORAGE_KEY) === '1') {
    sidebar.classList.add('collapsed');
    toggleIcon.className = 'bi bi-chevron-right';
}

function toggleGroup(el) {
    el.classList.toggle('open');
    const children = el.nextElementSibling;
    if (children) children.classList.toggle('show');
}
</script>
