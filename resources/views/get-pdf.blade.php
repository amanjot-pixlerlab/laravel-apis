@extends('layouts.pdf')

@section('content')

@php
if(Request::input('campaign_id'))
{
$campaign_id = Request::input('campaign_id');
}
else
{
$campaign_id ="";
}
@endphp



@php
$content = "";
$campaignId = 0;
$prevCampaignId = 0;
$prevAdgroupId = 0;
$campaignColSpan = [];
$adColSpan = [];

$adIds = [];

foreach ($ads as $ad):

if(!array_key_exists( $ad->campaign_id, $campaignColSpan ))
{
$campaignColSpan[$ad->campaign_id] = 0;
}

if(!array_key_exists( $ad->adgroup_id, $adColSpan ))
{
$adColSpan[$ad->adgroup_id] = 0;
}

array_push($adIds, $ad->ad_id);

$campaignColSpan[$ad->campaign_id]++;
$adColSpan[$ad->adgroup_id]++;
endforeach;


@endphp

 
<div class="content-wrapper">
    <div class="main-content">
        <div class="content-row">
            <div class="content-col">
                <div class="campaign-data-wrap">
                    <div class="card">
                        <div class="card-header">
                            <h5>Campaign Data</h5>
                            <div class="search-wrap">
                                <button>
                                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.45932 3.80957C10.9022 5.41055 10.7498 7.85765 9.11432 9.27504C7.47881 10.6924 4.98354 10.5399 3.54068 8.93891C2.09782 7.33793 2.25018 4.89083 3.88569 3.47344C5.52119 2.05605 8.01646 2.2086 9.45932 3.80957Z" stroke="#2E3338" stroke-width="1.3" />
                                        <line x1="1" y1="-1" x2="5.07338" y2="-1" transform="matrix(0.669473 0.742836 -0.755699 0.654919 8.10498 9.67041)" stroke="#2E3338" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                </button>
                                <input type="text" name="">
                            </div>
                        </div>


                        <div class="card-body p-0">
                            <div class="campaign-table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Campaign</th>
                                            <th>Ad Group</th>
                                            <th>Ad</th>
                                            <th>Impressions</th>
                                            <th>Reach</th>
                                            <th>Clicks</th>
                                            <th>CTR</th>
                                        </tr>
                                    </thead>
                                    <tbody>



                                        @foreach ($ads as $ad)

                                        @php
                                        $createTime = (isset($ad->create_time) && ($ad->create_time != null)) ? date('m.y', strtotime($ad->create_time)). "– now" : "N/A";
                                        @endphp

                                        @if( $campaign_id == $ad->campaign_id)

                                        <tr>

                                            @if($ad->campaign_id !=$prevCampaignId)
                                            <td rowspan="{{$campaignColSpan[$ad->campaign_id]}}">{{$ad->campaign_name}}</td>
                                            @endif

                                            @if($ad->adgroup_id !=$prevAdgroupId)
                                            <td rowspan="{{$adColSpan[$ad->adgroup_id]}}">{{$ad->adgroup_name}}</td>
                                            @endif


                                            <td>{{$ad->ad_name}}</td>
                                            <td>{{$createTime}}</td>
                                            <td>{{number_format($ad->impressions)}}</td>
                                            <td>{{number_format($ad->reach)}}</td>
                                            <td>{{number_format($ad->clicks)}}</td>
                                            <td>{{$ad->ctr}}%</td>
                                        </tr>
                                        @endif


                                        @if( $campaign_id == "")

                                        <tr>

                                            @if($ad->campaign_id !=$prevCampaignId)
                                            <td rowspan="{{$campaignColSpan[$ad->campaign_id]}}">{{$ad->campaign_name}}</td>
                                            @endif

                                            @if($ad->adgroup_id !=$prevAdgroupId)
                                            <td rowspan="{{$adColSpan[$ad->adgroup_id]}}">{{$ad->adgroup_name}}</td>
                                            @endif

                                            <td>{{$ad->ad_name}}</td>
                                            <td> {{$createTime}}</td>
                                            <td>{{number_format($ad->impressions)}}</td>
                                            <td>{{number_format($ad->reach)}}</td>
                                            <td>{{number_format($ad->clicks)}}</td>
                                            <td>{{$ad->ctr}}%</td>
                                        </tr>


                                        @endif

                                        @php
                                        $prevCampaignId = $ad->campaign_id;
                                        $prevAdgroupId = $ad->adgroup_id;
                                        @endphp

                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<style>
    body {
        background: #fff;
        font-size: 13px;
    }
</style>
@endsection