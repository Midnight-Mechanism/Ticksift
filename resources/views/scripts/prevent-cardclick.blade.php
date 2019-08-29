<script type="text/javascript">
    // prevents button click from firing card link
    $("a").click(function(e) {
        var senderElementName = e.target.tagName.toLowerCase();
        var senderElementType = e.target.type;
        if (
            e.target.tagName.toLowerCase() === 'button' &&
            e.target.type === 'button'
        ) {
            e.preventDefault();
        }
    });
</script>
