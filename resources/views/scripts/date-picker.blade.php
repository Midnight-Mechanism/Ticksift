<script>

    minDate = "{{ Cache::get('min-sep-date', '1997-12-31') }}";
    maxDate = "{{ \App\Models\Price::max('date') }}";

    var calendar = $("#input-dates").flatpickr({
        mode: "range",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "M j, Y",
        defaultDate: [
            @if(Session::has('security_dates'))
                "{{ Session::get('security_dates')[0] }}",
                "{{ Session::get('security_dates')[1] }}",
            @else
                moment(maxDate).subtract(1, "month").format("YYYY-MM-DD"),
                maxDate,
            @endif
        ],
        minDate: minDate,
        maxDate: maxDate,
    });
    $("#button-day").click(function() {
        calendar.setDate([
            maxDate,
        ], true);
    });
    $("#button-week").click(function() {
        calendar.setDate([
            moment(maxDate).subtract(1, "week").format("YYYY-MM-DD"),
            maxDate,
        ], true);
    });
    $("#button-month").click(function() {
        calendar.setDate([
            moment(maxDate).subtract(1, "month").format("YYYY-MM-DD"),
            maxDate,
        ], true);
    });
    $("#button-ytd").click(function() {
        calendar.setDate([
            moment(maxDate).startOf("year").format("YYYY-MM-DD"),
            maxDate,
        ], true);
    });
    $("#button-1yr").click(function() {
        calendar.setDate([
            moment(maxDate).subtract(1, "year").format("YYYY-MM-DD"),
            maxDate,
        ], true);
    });
    $("#button-all").click(function() {
        calendar.setDate([
            minDate,
            maxDate,
        ], true);
    });
</script>
