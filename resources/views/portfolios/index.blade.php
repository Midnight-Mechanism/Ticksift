@extends('layouts.app')

@section('template_title')
    Portfolios
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials.date-picker')
        <div class="row">
            <div class="col-12 text-center pb-3">
                <h3 class="table-title">Your Portfolios</h3>
                <div id="table-portfolios"></div>
            </div>
        </div>
        @include('partials.security-picker')
        <div class="chart col-12 text-center pb-3">
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
                    title: "Created",
                    field: "created_at",
                    formatter: "datetimediff",
                    formatterParams: {
                        inputFormat: "YYYY-MM-DDTHH:mm:ssZ",
                        humanize: true,
                        suffix: true,
                    },
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
            rowSelected: function(row) {
                fetchPortfolioData([row._row.data.id]);
            },
            rowDeselected: function(row) {
                $("#select-securities").empty().trigger("change");
            },
        });
        portfoliosTable.setData(portfolios);

        function updateMomentum() {
            if ($("#select-securities").val().length) {
                $("body").addClass("waiting");
                $(".chart").addClass("outdated");
                $.get("{{ route('securities.get-momentum') }}", data = {
                    dates: $("#input-dates").val(),
                    security_ids: $("#select-securities").val(),
                }).done(function(data) {
                    const mergedData = [].concat.apply([], Object.values(_.cloneDeep(data)));
                    buildTreemap(mergedData, function(security) {
                        return security.latest_close;
                    });
                    $("body").removeClass("waiting");
                    $(".chart").removeClass("outdated");
                    if (portfoliosTable.getSelectedData().length > 0) {
                        $("#create-portfolio-button").addClass("d-none");
                        $("#update-portfolio-button").removeClass("d-none");
                    } else {
                        $("#create-portfolio-button").removeClass("d-none");
                        $("#update-portfolio-button").addClass("d-none");
                    }
                });
            } else {
                $("#update-portfolio-button").addClass("d-none");
                $("#create-portfolio-button").addClass("d-none");
                Plotly.purge("treemap-chart");
            }
        }

        $("#input-dates").change(updateMomentum);
        $("#select-securities").change(updateMomentum);

        updateMomentum();
    </script>
@endsection
