<script type="text/javascript">

    Dropzone.options.dropzone = {
        maxFiles: 1,
        maxFilesize: 2, // MB
        acceptedFiles: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        dictDefaultMessage: 'Drop file here or click to upload',
        init: function() {

            this.on("maxfilesexceeded", function(file) {
                this.removeAllFiles();
                this.addFile(file);
            });

            this.on("success", function(file, response) {
                location.reload();
            })
        },
    };

</script>
