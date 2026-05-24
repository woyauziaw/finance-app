if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}
function toggleDarkMode() {
    if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    } else {
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark';
    }
}
function formatRupiahInput(el) {
    let val = el.value.replace(/[^,\d]/g, "").toString();
    let split = val.split(",");
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    if (ribuan) { rupiah += (sisa ? "." : "") + ribuan.join("."); }
    el.value = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
}
window.addEventListener('load', () => {
    const loader = document.getElementById('global-loader');
    if(loader) loader.classList.add('opacity-0', 'pointer-events-none');
});