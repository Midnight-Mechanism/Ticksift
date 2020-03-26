<script>
    const chartColor = "#000F0F";
    var treemapChart = document.getElementById("treemap-chart");

    function formatCurrency(number, code) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: code,
        }).format(number);
    }

    function buildTreemap(data, calculateSecuritySize) {

        data = _(data).map(function (securityData) {
            // consolidate "increase" and "decrease" into "change"
            if ("increase" in securityData) {
                securityData.change = securityData.increase;
                delete securityData["increase"];
            } else if ("decrease" in securityData) {
                securityData.change = securityData.decrease * -1;
                delete securityData["decrease"];
            }
            return(securityData);
        }).without(undefined).groupBy('sector').mapValues(function(securities) {
            sector_color = securities[0].sector_color;
            return {
                'color': sector_color ? "#" + sector_color : null,
                'industries': _.groupBy(securities, 'industry'),
            };
        }).value();

        let labels = [];
        let parents = [];
        let texts = [];
        let colors = [];
        let lineColors = [];
        let values = [];
        let customdata = [];

        for (const[sector, sectorData] of Object.entries(data)) {
            let sectorLabel = "<b><span style='text-transform: uppercase'>" +
                sector +
                "</span></b>";
            labels.push(sectorLabel);
            parents.push("");
            texts.push(null);
            colors.push(Color(sectorData.color).darken(0.75).hex());
            lineColors.push(sectorData.color);
            values.push(0);
            customdata.push(null);
            for (const[industry, industryData] of Object.entries(sectorData.industries)) {
                let industryLabel = "<span style='text-transform: uppercase'>" +
                    industry +
                    "</span>";
                labels.push(industryLabel);
                parents.push(sectorLabel);
                texts.push(null);
                colors.push(null);
                lineColors.push(sectorData.color);
                values.push(0);
                customdata.push(null);
                for (const securityData of Object.values(industryData)) {
                    parents.push(industryLabel);
                    customdata.push(securityData.ticker);

                    let label = "<b><span style='font-size: 200%'>" + securityData.ticker + "</span></b>";
                    let text = securityData.name;

                    let absChange = Math.abs(securityData.change);
                    let colorChange = Math.min(absChange * 15, 8.5);
                    let percent = absChange * 100;

                    text += "<b><span style='font-size: 150%;";
                    if (securityData.change < 0) {
                        text += "color: #FFE6E6'><br><br>-";
                        colors.push(Color("#1A0000").lighten(colorChange).hex());
                    } else {
                        text += "color: #E6FFEA'><br><br>+";
                        colors.push(Color("#001A04").lighten(colorChange).hex());
                    }
                    text += percent.toFixed(2) + "%</span></b>";
                    text += "<br>" + formatCurrency(securityData.latest_close, securityData.currency_code);

                    labels.push(label);
                    texts.push(text);
                    values.push(calculateSecuritySize(securityData));
                    lineColors.push(null);
                }
            }
        }

        Plotly.newPlot(
            treemapChart,
            traces = [{
                type: "treemap",
                labels: labels,
                parents: parents,
                text: texts,
                values: values,
                customdata: customdata,
                hoverinfo: "label+text",
                textposition: "middle center",
                marker: {
                    colors: colors,
                    line: {
                        color: lineColors,
                    },
                },
            }],
            layout = {
                autosize: true,
                height: 800,
                paper_bgcolor: chartColor,
                plot_bgcolor: chartColor,
                margin: {
                    l: 0,
                    r: 0,
                    b: 0,
                    t: 20,
                    pad: 0,
                },
            },
            config = {
                collaborate: false,
                displaylogo: false,
                responsive: true,
                toImageButtonOptions: {
                    format: "png",
                    height: 1080,
                    width: 1920,
                    filename: [
                        "ticksift",
                        "treemap",
                        $("#input-dates").val(),
                    ].join("_").split(" ").join("_"),
                },
            },
        );

        treemapChart.on('plotly_treemapclick', function(data) {
            ticker = data.points[0].customdata;
            if (ticker) {
                window.location = "{{ route('securities.explorer') }}?add_tickers=" + ticker;
                return false;
            }
        });
    }
</script>
