<script>
    var calendar = $("#input-dates").flatpickr({
        mode: "range",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "M j, Y",
        defaultDate: [
            @if(Session::has('security_dates'))
                "{{ Session::get('security_dates')[0] }}",
            "{{ Session::get('security_dates')[1] }}"
        @else
            moment().subtract(1, "month").format("YYYY-MM-DD"),
            moment().format("YYYY-MM-DD")
        @endif
        ],
        maxDate: moment().format("YYYY-MM-DD"),
    });
    $("#button-week").click(function() {
        calendar.setDate([
            moment().subtract(1, "week").format("YYYY-MM-DD"),
            moment().format("YYYY-MM-DD")
        ], true);
    });
    $("#button-1mo").click(function() {
        calendar.setDate([
            moment().subtract(1, "month").format("YYYY-MM-DD"),
            moment().format("YYYY-MM-DD")
        ], true);
    });
    $("#button-ytd").click(function() {
        calendar.setDate([
            moment().startOf("year").format("YYYY-MM-DD"),
            moment().format("YYYY-MM-DD")
        ], true);
    });
    $("#button-1yr").click(function() {
        calendar.setDate([
            moment().subtract(1, "year").format("YYYY-MM-DD"),
            moment().format("YYYY-MM-DD")
        ], true);
    });
    $("#button-5yr").click(function() {
        calendar.setDate([
            moment().subtract(5, "year").format("YYYY-MM-DD"),
            moment().format("YYYY-MM-DD")
        ], true);
    });
    $("#button-all").click(function() {
        calendar.setDate([
            "{{ \App\Models\Price::min('date') }}",
            moment().format("YYYY-MM-DD")
        ], true);
    });
</script>
