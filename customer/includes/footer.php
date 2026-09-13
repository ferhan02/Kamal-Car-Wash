
<!-- FOOTER -->
<footer class="customer-footer">
    <p>&copy; <?php echo date("Y"); ?> CARWASH KAMAL</p>
    <p>More Than a Wash, It's a Revival.</p>
</footer>

<!-- NOTIFLIX JS -->
<script src="../js/notiflix-aio-3.2.8.min.js"></script>

<?php if(isset($_SESSION['success'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Notiflix.Notify.success("<?= $_SESSION['success']; ?>");
});
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Notiflix.Notify.failure("<?= $_SESSION['error']; ?>");
});
</script>
<?php unset($_SESSION['error']); endif; ?>

</body>
</html>