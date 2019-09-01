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

    var securityPrices = [];
    var candlestickChart = document.getElementById('candlestick-chart')
    function redrawPlots() {

        layout.title = "Prices: " + Object.keys(securityPrices).sort().join(" - ");

        let candleData = [];
        let lastDates = [];

        for (const security of Object.entries(securityPrices)) {
            let ticker = security[0];
            let prices = security[1];

            let dates = prices.map(a => a.date);
            let open = prices.map(a => a.open);
            let high = prices.map(a => a.high);
            let low = prices.map(a => a.low);
            let close = prices.map(a => a.close);

            lastDates.push(moment(dates[dates.length - 1]));

            candleData.push({
                name: ticker,
                type: "candlestick",
                x: dates,
                open: open,
                high: high,
                low: low,
                close: close,
            });
        }

        if (lastDates.length > 0) {
            lastDate = moment.max(lastDates);
            layout.xaxis.range = [
                lastDate.subtract(6, "month").format("YYYY-MM-DD"),
                lastDate,
            ];
        }

        Plotly.react(candlestickChart, candleData, layout, config);
    }

    function getSecurityData() {
        ids = $("#select-ticker").val();
        $("body").addClass("waiting");
        $.post("{{ route('securities.prices') }}", data = {
            ids: ids
        }).done(function(msg) {
            securityPrices = msg;
            redrawPlots();
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
		$(".select2-results__option").remove();
    });

    $("#select-ticker").change(getSecurityData);

</script>
