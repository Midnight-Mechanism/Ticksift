<script type="text/javascript">

    var security = $("#select-ticker").val();
    var ticker = $("#select-ticker option:selected").text();

    var layout = {
        autosize: true,
        font: {
            family: "Lato",
            color: "white",
        },
        dragmode: "zoom",
        xaxis: {
            title: "Date",
            matches: "x2",
            gridcolor: "#154B4B",
        },
        yaxis: {
            title: "Price",
            tickprefix: "$",
            gridcolor: "#154B4B",
        },
        paper_bgcolor: "#000F0F",
        plot_bgcolor: "#000F0F",
    };

    var config = {
        collaborate: false,
        displaylogo: false,
        responsive: true,
    };

    var prices = [];
    var candlestickChart = document.getElementById('candlestick-chart')
    function redrawPlots() {

        layout.title = ticker;

        let dates = prices.map(a => a.date);
        let open = prices.map(a => a.open);
        let high = prices.map(a => a.high);
        let low = prices.map(a => a.low);
        let close = prices.map(a => a.close);

        let candleData = [
            {
                name: "Candlestick",
                type: "candlestick",
                x: dates,
                open: open,
                high: high,
                low: low,
                close: close,
            },
        ];

        Plotly.react(candlestickChart, candleData, layout, config);
    }

    function getSecurityData() {
        security = $("#select-ticker").val();
        ticker = $("#select-ticker option:selected").text();
        $("body").addClass("waiting");
        $.get("/securities/" + security + "/prices", function(msg) {
            prices = msg;
            redrawPlots();
            let lastPoint = prices[prices.length - 1];
            $("#last-date").text("Last Date: " + lastPoint.date);
            $("#last-open").text("Last Open Price: $" + lastPoint.open);
            $("#last-high").text("Last High Price: $" + lastPoint.high);
            $("#last-low").text("Last Low Price: $" + lastPoint.low);
            $("#last-close").text("Last Close Price: $" + lastPoint.close);
            $(".sim-card").css("display", "flex");
            $("body").removeClass("waiting");
        });
    }

    $("#select-ticker").select2(({
        placeholder: "Please enter a ticker symbol (e.g. AAPL)...",
        ajax: {
            url: "/securities/search",
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.ticker + " - " + item.name,
                            id: item.id
                        }
                    })
                };
            },
        },
    })).on("select2:selecting", function(event) {
        // clear results to prevent option list getting too large
        $("#select-ticker").find("option").remove();
    });

    $("#select-ticker").change(getSecurityData);

</script>
