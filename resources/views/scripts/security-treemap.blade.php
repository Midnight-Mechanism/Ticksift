@include('scripts.security-treemap-worker')
<script>
    const chartColor = "#000F0F";
    var treemapChart = document.getElementById("treemap-chart");
    var treemapLoader = document.getElementById("treemap-loader");
    var selectedTreemapLevel = null;

    function buildTreemap(data, fileSlug, calculateSecuritySize) {

        let treemapLoader = document.getElementById("treemap-loader");
        treemapLoader.classList.add("loader");

        // Create treemap treemap worker
        var blob = new Blob([document.querySelector("#security-treemap-worker").textContent])
        var treemapWorker = new Worker(window.URL.createObjectURL(blob));

        // Handle worker's return message
        treemapWorker.onmessage = function(e) {
            var labels = e.data.labels;
            var parents = e.data.parents;
            var texts = e.data.texts;
            var colors = e.data.colors;
            var lineColors = e.data.colors;
            var values = e.data.values;
            var customdata = e.data.customdata;

            var treemapChart = document.getElementById("treemap-chart");
            const chartColor = "#000F0F";
            Plotly.react(
                treemapChart,
                traces = [{
                    type: "treemap",
                    labels: labels,
                    parents: parents,
                    text: texts,
                    values: values,
                    level: selectedTreemapLevel,
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
                            "sectors",
                            $("#input-dates").val(),
                        ].join("_").split(" ").join("_"),
                    },
                },
            ).then(function() {
                treemapChart.classList.remove("loading");
                treemapLoader.classList.remove("loader");
            });
            treemapChart.on("plotly_treemapclick", function(data) {
                ticker = data.points[0].customdata;
                if (ticker) {
                    window.location = "{{ route('securities.explorer') }}?add_tickers=" + ticker;
                    return false;
                }
                selectedTreemapLevel = data.nextLevel;
            });
            treemapLoader.classList.remove("loader");
            treemapChart.classList.remove("loading");
        }

        // Handle worker errors
        treemapWorker.addEventListener("error", function(e) {
            console.log("Error returned from treemap worker...");
            console.table(e);
        });

        // Send message to treemap worker to calculate security treemaps for graph
        let mergedData = [].concat.apply([], Object.values(_.cloneDeep(data)));
        mergedData = _(mergedData).map(function (securityData) {
            if (!securityData.sector) {
                securityData.sector = "No Sector"
                securityData.industry = "No Industry"
            }
            // consolidate "increase" and "decrease" into "change"
            if ("increase" in securityData) {
                securityData.change = securityData.increase;
                delete securityData["increase"];
            } else if ("decrease" in securityData) {
                securityData.change = securityData.decrease * -1;
                delete securityData["decrease"];
            }
            return(securityData);
        }).without(undefined).groupBy("sector").mapValues(function(securities) {
            sector_color = securities[0].sector_color;
            return {
                "color": sector_color ? "#" + sector_color : null,
                "industries": _.groupBy(securities, "industry"),
            };
        }).value();
        treemapWorker.postMessage({
            data: mergedData,
            fileSlug: fileSlug,
            calculateSecuritySize: JSON.stringify(calculateSecuritySize.toString()),
        });
    }

</script>
