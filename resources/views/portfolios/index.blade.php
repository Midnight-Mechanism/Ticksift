@extends('layouts.app')

@section('template_title')
    Portfolios
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="chart col-12 text-center pb-3">
                <h3 class="table-title">Your Portfolios</h3>
                <div id="table-portfolios"></div>
            </div>
        </div>
    </div>
    @foreach($portfolios as $portfolio)
        @include('modals.delete-portfolio', [
            'portfolio' => $portfolio,
        ])
    @endforeach
@endsection

@section('footer_scripts')
    <script>
        let portfolios = {!! $portfolios !!};
        var portfoliosTable = new Tabulator("#table-portfolios", {
            columns: [
                {
                    title: "Name",
                    field: "name",
                    sorter: "string",
                    cellClick: function(event, cell) {
                       window.location = "{{ route('securities.explorer') }}?add_portfolios=" + cell._cell.row.data.id;
                    }
                },
                {
                    title: "Securities",
                    field: "tickers",
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
                    },
                },
            ],
            layout: "fitColumns",
            placeholder: "You don't have any saved portfolios. Try saving a new portfolio in the <a href='{{ route('securities.explorer') }}'>Security Explorer</a>!",
        });
        portfoliosTable.setData(portfolios);
    </script>
@endsection
