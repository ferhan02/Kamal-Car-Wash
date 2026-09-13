<?php
if(isset($_SESSION['success'])):
?>

<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '<?= $_SESSION['success']; ?>'
});
</script>

<?php
unset($_SESSION['success']);
endif;
?>


<?php
if(isset($_SESSION['error'])):
?>

<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '<?= $_SESSION['error']; ?>'
});
</script>

<?php
unset($_SESSION['error']);
endif;
?>