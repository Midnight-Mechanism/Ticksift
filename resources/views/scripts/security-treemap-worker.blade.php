<script id="security-treemap-worker" type="javascript/worker">
    importScripts("{{ env('APP_URL') }}/js/worker-deps.js");

    function formatCurrency(number, code) {
        if (!code) {
            return new Intl.NumberFormat("en-US", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(number);
        }
        return new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: code,
        }).format(number);
    }

    onmessage = function(e) {
        // Parse message data
        var data = e.data.data;
        var fileSlug = e.data.fileSlug;
        eval("var calculateSecuritySize = " + JSON.parse(e.data.calculateSecuritySize));

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

                    text += "<br><br><b><span style='font-size: 150%'>";
                    if (securityData.change < 0) {
                        text += "-";
                        colors.push(Color("#1A0000").lighten(colorChange).hex());
                    } else {
                        text += "+";
                        colors.push(Color("#001A04").lighten(colorChange).hex());
                    }
                    text += percent.toLocaleString(undefined, {
                        maximumFractionDigits: 2,
                    }) + "%</span></b>";
                    text += "<br>" + formatCurrency(securityData.latest_close, securityData.currency_code) + " (";

                    if (securityData.change < 0) {
                        text += "➘"
                    } else {
                        text += "➚"
                    }

                    text += formatCurrency(
                        Math.abs(securityData.latest_close - securityData.earliest_close),
                        securityData.currency_code
                    ) + ")";

                    labels.push(label);
                    texts.push(text);
                    values.push(calculateSecuritySize(securityData));
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
