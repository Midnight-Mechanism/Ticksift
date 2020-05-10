<script id="security-treemap-worker" type="javascript/worker">
    importScripts("{{ env('APP_URL') }}/js/worker-deps.js");

    function formatCurrency(number, code) {
        return new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: code,
        }).format(number);
    }

    onmessage = function(e) {
        // Parse message data
        var data = e.data.data;
        var fileSlug = e.data.fileSlug;

        // Prepare momentum graph data objects
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

                    let label = "<b><span style='font-size: 200%'>" + securityData.short_name + "</span></b>";
                    let text = securityData.short_name == securityData.name ? "" : securityData.name;

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
                    values.push(securityData.latest_close * securityData.volume);
                    lineColors.push(null);
                }
            }
        }
        // Return momentum data for graphing
        postMessage({
            labels: labels,
            parents: parents,
            texts: texts,
            colors: colors,
            lineColors: lineColors,
            values: values,
            customdata: customdata
        });
    }

</script>
