@extends('layouts.app')

@section('template_title')
    Portfolios
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials.date-picker')
        <div class="row">
            <div class="col-12 text-center pb-3">
                <h3 class="chart-title">Your Portfolios</h3>
                <div id="table-portfolios"></div>
            </div>
        </div>
        @include('partials.security-picker')
        <div class="col-12 text-center pb-3">
            <div id="treemap-loader"></div>
            <div id="treemap-chart" class="chart"></div>
        </div>
    </div>
    @foreach($portfolios as $portfolio)
        @include('modals.delete-portfolio', [
            'portfolio' => $portfolio,
        ])
    @endforeach
@endsection

@section('footer_scripts')
    @include('scripts.date-picker')
    @include('scripts.security-picker')
    @include('scripts.security-treemap')
    <script>
        function fetchPortfolioData(ids) {
            $.get("{{ route('portfolios.securities') }}", data = {
                portfolio_ids: ids,
            }).done(function(securities) {
                $("#select-securities").empty();
                appendSecurities(securities);
            });
        }

        let portfolios = {!! $portfolios !!};
        var portfoliosTable = new Tabulator("#table-portfolios", {
            selectable: 1,
            columns: [
                {
                    title: "Name",
                    field: "name",
                    sorter: "string",
                },
                {
                    title: "Securities",
                    field: "securities",
                    headerSort: false,
                },
                {
                    formatter: "buttonCross",
                    width: 10,
                    align: "center",
                    headerSort: false,
                    cellClick: function(event, cell) {
                        $("#delete-portfolio-" + cell._cell.row.data.id).modal("show");
                        event.stopPropagation();
                    },
                },
            ],
            layout: "fitColumns",
            placeholder: "Try saving a portfolio below!",
            rowSelectionChanged: function(data, rows) {
                if(data.length > 0) {
                    fetchPortfolioData([data[0].id]);
                } else {
                    updateTreemap();
                }
            },
        });
        portfoliosTable.setData(portfolios);

        function updateTreemap() {
            if ($("#select-securities").val().length) {
                $("body").addClass("waiting");
                $(".chart").addClass("outdated");
                $.get("{{ route('securities.get-momentum') }}", data = {
                    dates: $("#input-dates").val(),
                    security_ids: $("#select-securities").val(),
                }).done(function(data) {
                    let exportFilename = "treemap";
                    if (portfoliosTable.getSelectedData().length > 0) {
                        exportFilename = _.kebabCase(portfoliosTable.getSelectedData()[0].name);
                        $("#create-portfolio-button").addClass("d-none");
                        $("#update-portfolio-button").removeClass("d-none");
                    } else {
                        $("#create-portfolio-button").removeClass("d-none");
                        $("#update-portfolio-button").addClass("d-none");
                    }
                    const mergedData = [].concat.apply([], Object.values(_.cloneDeep(data)));
                    buildTreemap(
                        mergedData,
                        exportFilename,
                        function(security) {
                            return 1;
                        }
                    );
                    $("body").removeClass("waiting");
                    $(".chart").removeClass("outdated");

                });
            } else {
                $("#update-portfolio-button").addClass("d-none");
                $("#create-portfolio-button").addClass("d-none");
                Plotly.purge("treemap-chart");
            }
        }

        $("#input-dates").change(updateTreemap);
        $("#select-securities").change(updateTreemap);

        updateTreemap();
    </script>
@endsection
