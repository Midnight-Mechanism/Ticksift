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
        <div class="row">
            <div class="col-12 text-center pb-3">
                <h3 id="securities-chart-title" class="chart-title">New Portfolio</h3>
                <div id="table-portfolio-securities"></div>
            </div>
        </div>
        <div class="row pb-3">
            <div class="col-12 d-flex flex-column flex-md-row">
                <select id="select-securities" multiple="multiple" class="invisible"></select>
                @auth
                    <button
                        id="create-portfolio-button"
                        class="btn btn-primary d-none"
                        data-toggle="modal"
                        data-target="#create-portfolio">
                        Create Portfolio
                    </button>
                    @include('modals.create-portfolio')
                    {!! Form::open([
                        'id' => 'update-portfolio-form',
                        'route' => [
                            'portfolios.update',
                            ':id',
                        ],
                        'method' => 'PUT',
                        'role' => 'form',
                        'class' => 'needs-validation',
                    ]) !!}
                    <button
                        id="update-portfolio-button"
                        class="btn btn-primary d-none h-100 w-100">
                        Update Portfolio
                    </button>
                    {!! Form::close() !!}
                @endauth
            </div>
        </div>
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
    @include('scripts.security-treemap')
    <script>
        let portfolios = {!! $portfolios !!};
        let activePortfolioId = 0;
        let securities = [];

        // Initialize Securities picker
        $("#select-securities").select2({
            placeholder: "Please enter a security ticker or name (AAPL, Apple, etc.)...",
            allowClear: true,
            minimumInputLength: 1,
            escapeMarkup: function(text) {
                return text;
            },
            ajax: {
                url: "{{ route('securities.search') }}",
                delay: 250,
                processResults: function(data) {
                    return {"results": data};
                },
            },
        });
        function appendSecurities(securities) {
            for (let security of securities) {
                let existing_option = $("#select-securities option[value="+security.id+"]");
                if (!existing_option.length) {
                    $("#select-securities").append(new Option(
                        security.ticker ? security.ticker + " - " + security.name : security.name,
                        security.id,
                        false,
                        true
                    ));
                } else {
                    existing_option.prop("selected", true);
                }
            }
            $("#select-securities").trigger("change");
        }

        // Get portfolio data with securities when selected
        function fetchPortfolioData(ids) {
            $.get("{{ route('portfolios.securities') }}", data = {
                portfolio_ids: ids,
            }).done(function(securities) {
                $("#select-securities").empty();
                appendSecurities(securities);
            });
        }

        // Initialize Securities Table
        var securitiesTable = new Tabulator("#table-portfolio-securities", {
            columns: [
                {
                    title: "Name",
                    field: "name",
                    sorter: "string",
                },
                {
                    title: "Ticker",
                    field: "ticker",
                    sorter: "string",
                },
                {
                    title: "Weight",
                    field: "weight",
                    editor: "number"
                },
                {
                    formatter: "buttonCross",
                    width: 10,
                    align: "center",
                    headerSort: false,
                    cellClick: function(event, cell) {
                        cell.getRow().delete();
                        securities = securitiesTable.getData()
                        updateTreemap();
                        event.stopPropagation();
                    },
                },
            ],
            layout: "fitColumns",
            placeholder: "Try adding a security to the portfolio below!",
        });
        // Initialize portfolios table
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
                    title: "Last Updated",
                    field: "updated_at",
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
            rowSelectionChanged: function(data, rows) {
                if(data.length > 0) {
                    // Update portfolio securities table
                    fetchPortfolioData([data[0].id]);
                    activePortfolioId = data[0].id;
                    $('#securities-chart-title').html(data[0].name ?? 'Selected Portfolio');
                } else {
                    // Clear Portfolio Securities Table and Treemap
                    activePortfolioId = 0;
                    securities = [];
                    securitiesTable.setData([]);
                    $("#select-securities").empty();
                    $('#securities-chart-title').html('New Portfolio')
                    updateTreemap();
                }
            },
        });
        portfoliosTable.setData(portfolios);

        function updateTreemap() {
            if (securitiesTable.getData().length) {
                $("body").addClass("waiting");
                $(".chart").addClass("outdated");
                $.get("{{ route('securities.get-momentum') }}", data = {
                    dates: $("#input-dates").val(),
                    security_ids: securitiesTable.getData().map(s => s.id),
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

        function updateSecuritiesTable() {
            if ($("#select-securities").val().length) {
                $.get("{{ route('portfolios.security-weights') }}", data = {
                    portfolio_id: activePortfolioId,
                    security_ids: $("#select-securities").val(),
                }).done(function(data) {
                    for (s of data) {
                        securities.push(s);
                    }
                    securitiesTable.setData(securities);
                    $("#select-securities").empty();
                    updateTreemap()
                });
            }
        }//end updateSecuritiesTable()

        $("#input-dates").change(updateTreemap);
        $("#select-securities").change(updateSecuritiesTable);

        // Create new portfolio with securities from New Portfolio table
        $("#create-portfolio-form").submit(function(event) {
            // Add Security IDs
            $("<input>", {
                type: "hidden",
                name: "security_ids",
                value: securitiesTable.getData().map(s => s.id),
            }).appendTo(this);
            // Add Security Weights
            $("<input>", {
                type: "hidden",
                name: "security_weights",
                value: securitiesTable.getData().map(s => s.weight),
            }).appendTo(this);
            return true;
        });
        // Update Portfolio with associated securities and weights
        $("#update-portfolio-form").submit(function(event) {
            this.action = this.action.replace(
                /(?!.*\/).*$/,
                portfoliosTable.getSelectedData()[0].id
            );
            // Add Security IDs
            $("<input>", {
                type: "hidden",
                name: "security_ids",
                value: securitiesTable.getData().map(s => s.id),
            }).appendTo(this)
            // Add Security Weights
            $("<input>", {
                type: "hidden",
                name: "security_weights",
                value: securitiesTable.getData().map(s => s.weight),
            }).appendTo(this);
            return true;
        });

        updateTreemap();
    </script>
@endsection
