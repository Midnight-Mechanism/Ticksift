<script type="text/javascript">
    $("form").submit(function () {
        $(this).find(":submit").prop('disabled', true);
        $('body').addClass('waiting');
    });
</script>
