<script>
    $("#select-securities").select2({
        placeholder: "Please enter a security ticker or name (AAPL, Apple, etc.)...",
        allowClear: true,
        minimumInputLength: 1,
        escapeMarkup: function(text) {
            return text;
        },
        ajax: {
            url: "{{ route('securities.search') }}",
            delay: 250,
            processResults: function(data) {
                return {"results": data};
            },
        },
    });

    $("#create-portfolio-form").submit(function(event) {
        $("<input>", {
            type: "hidden",
            name: "security_ids",
            value: $("#select-securities").val(),
        }).appendTo(this);
        return true;
    });

    $("#update-portfolio-form").submit(function(event) {
        this.action = this.action.replace(
            /(?!.*\/).*$/,
            portfoliosTable.getSelectedData()[0].id
        );

        $("<input>", {
            type: "hidden",
            name: "security_ids",
            value: $("#select-securities").val(),
        }).appendTo(this)

        return true;
    });

    function appendSecurities(securities) {
        for (let security of securities) {
            let existing_option = $("#select-securities option[value="+security.id+"]");
            if (!existing_option.length) {
                $("#select-securities").append(new Option(
                    security.ticker ? security.ticker + " - " + security.name : security.name,
                    security.id,
                    false,
                    true
                ));
            } else {
                existing_option.prop("selected", true);
            }
        }
        $("#select-securities").trigger("change");
    }
</script>
