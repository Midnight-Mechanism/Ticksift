@php

$drilldownStatus = config('LaravelLogger.enableDrillDown');
$prependUrl = '/activity/log/';

if (isset($hoverable) && $hoverable === true) {
    $hoverable = true;
} else {
    $hoverable = false;
}

if (Request::is('activity/cleared')) {
    $prependUrl = '/activity/cleared/log/';
}

@endphp

<div class="table-responsive activity-table">
    <table class="table table-striped table-condensed table-sm @if(config('LaravelLogger.enableDrillDown') && $hoverable) table-hover @endif data-table">
        <thead>
            <tr>
                <th>
                    <i class="fas fa-envelope fa-fw" aria-hidden="true"></i>
                    <span class="hidden-sm hidden-xs">
                        {!! trans('LaravelLogger::laravel-logger.drilldown.list-group.labels.userEmail') !!}
                    </span>
                </th>
                <th>
                    <i class="fas fa-clock fa-fw" aria-hidden="true"></i>
                    {!! trans('LaravelLogger::laravel-logger.dashboard.labels.time') !!}
                </th>
                <th>
                    <i class="fas fa-file-alt fa-fw" aria-hidden="true"></i>
                    {!! trans('LaravelLogger::laravel-logger.dashboard.labels.description') !!}
                </th>
                <th>
                    <i class="fas fa-user fa-fw" aria-hidden="true"></i>
                    {!! trans('LaravelLogger::laravel-logger.dashboard.labels.user') !!}
                </th>
                <th>
                    <i class="fas fa-truck fa-fw" aria-hidden="true"></i>
                    <span class="hidden-sm hidden-xs">
                        {!! trans('LaravelLogger::laravel-logger.dashboard.labels.method') !!}
                    </span>
                </th>
                <th>
                    <i class="fas fa-map fa-fw" aria-hidden="true"></i>
                    {!! trans('LaravelLogger::laravel-logger.dashboard.labels.route') !!}
                </th>
                <th>
                    <i class="fas fa-map-marker-alt fa-fw" aria-hidden="true"></i>
                    {!! trans('LaravelLogger::laravel-logger.dashboard.labels.ipAddress') !!}
                </th>
                <th>
                    <i class="fas fa-laptop fa-fw" aria-hidden="true"></i>
                    {!! trans('LaravelLogger::laravel-logger.dashboard.labels.agent') !!}
                </th>
                @if(Request::is('activity/cleared'))
                    <th>
                        <i class="fas fa-trash fa-fw" aria-hidden="true"></i>
                        {!! trans('LaravelLogger::laravel-logger.dashboard.labels.deleteDate') !!}
                    </th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $activity)
                <tr @if($drilldownStatus && $hoverable) class="clickable-row" data-href="{{$prependUrl . $activity->id}}" data-toggle="tooltip" title="{{trans('LaravelLogger::laravel-logger.tooltips.viewRecord')}}" @endif >
                    <td>
                        <small>
                            {{ $activity->userDetails->email }}
                        </small>
                    </td>
                    <td>
                        {{ $activity->timePassed }}
                    </td>
                    <td>
                        {{ $activity->description }}
                    </td>
                    <td>
                        @php
                            switch ($activity->userType) {
                                case trans('LaravelLogger::laravel-logger.userTypes.registered'):
                                    $userTypeClass = 'success';
                                    $userLabel = $activity->userDetails['name'];
                                    break;

                                case trans('LaravelLogger::laravel-logger.userTypes.crawler'):
                                    $userTypeClass = 'danger';
                                    $userLabel = $activity->userType;
                                    break;

                                case trans('LaravelLogger::laravel-logger.userTypes.guest'):
                                default:
                                    $userTypeClass = 'warning';
                                    $userLabel = $activity->userType;
                                    break;
                            }

                        @endphp
                        <span class="badge badge-{{$userTypeClass}}">
                            {{$userLabel}}
                        </span>
                    </td>
                    <td>
                        @php
                            switch (strtolower($activity->methodType)) {
                                case 'get':
                                    $methodClass = 'info';
                                    break;

                                case 'post':
                                    $methodClass = 'warning';
                                    break;

                                case 'put':
                                    $methodClass = 'warning';
                                    break;

                                case 'delete':
                                    $methodClass = 'danger';
                                    break;

                                default:
                                    $methodClass = 'info';
                                    break;
                            }
                        @endphp
                        <span class="badge badge-{{ $methodClass }}">
                            {{ $activity->methodType }}
                        </span>
                    </td>
                    <td class="ellipsis">
                        @if($hoverable)
                            {{ showCleanRoutUrl($activity->route) }}
                        @else
                            <a href="{{ $activity->route }}">
                                {{$activity->route}}
                            </a>
                        @endif
                    </td>
                    <td>
                        {{ $activity->ipAddress }}
                    </td>
                    <td>
                        @php
                            $platform       = $activity->userAgentDetails['platform'];
                            $browser        = $activity->userAgentDetails['browser'];
                            $browserVersion = $activity->userAgentDetails['version'];

                            switch ($platform) {

                                case 'Windows':
                                    $platformIcon = 'fa-windows';
                                    break;

                                case 'iPad':
                                    $platformIcon = 'fa-';
                                    break;

                                case 'iPhone':
                                    $platformIcon = 'fa-';
                                    break;

                                case 'Macintosh':
                                    $platformIcon = 'fa-apple';
                                    break;

                                case 'Android':
                                    $platformIcon = 'fa-android';
                                    break;

                                case 'BlackBerry':
                                    $platformIcon = 'fa-';
                                    break;

                                case 'Unix':
                                case 'Linux':
                                    $platformIcon = 'fa-linux';
                                    break;

                                default:
                                    $platformIcon = 'fa-';
                                    break;
                            }

                            switch ($browser) {

                                case 'Chrome':
                                    $browserIcon  = 'fa-chrome';
                                    break;

                                case 'Firefox':
                                    $browserIcon  = 'fa-';
                                    break;

                                case 'Opera':
                                    $browserIcon  = 'fa-opera';
                                    break;

                                case 'Safari':
                                    $browserIcon  = 'fa-safari';
                                    break;

                                case 'Internet Explorer':
                                    $browserIcon  = 'fa-edge';
                                    break;

                                default:
                                    $browserIcon  = 'fa-';
                                    break;
                            }
                        @endphp
                        <i class="fab {{ $browserIcon }} fa-fw" aria-hidden="true">
                            <span class="sr-only">
                                {{ $browser }}
                            </span>
                        </i>
                        <sup>
                            <small>
                                {{ $browserVersion }}
                            </small>
                        </sup>
                        <i class="fab {{ $platformIcon }} fa-fw" aria-hidden="true">
                            <span class="sr-only">
                                {{ $platform }}
                            </span>
                        </i>
                        <sup>
                            <small>
                                {{ $activity->langDetails }}
                            </small>
                        </sup>
                    </td>
                    @if(Request::is('activity/cleared'))
                        <td>
                            {{ $activity->deleted_at }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if(config('LaravelLogger.loggerPaginationEnabled'))
    <div class="text-center">
        <div>
            {!! $activities->render() !!}
        </div>
        <p>
            {!! trans('LaravelLogger::laravel-logger.pagination.countText', ['firstItem' => $activities->firstItem(), 'lastItem' => $activities->lastItem(), 'total' => $activities->total(), 'perPage' => $activities->perPage()]) !!}
        </p>
    </div>
@endif
