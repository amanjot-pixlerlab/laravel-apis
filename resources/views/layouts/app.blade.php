<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Rankzoo Ad Publisher') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/7jj548itjbvl8mtep56dpskx9276inypwzh0qea62u2ke145/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://kit.fontawesome.com/9503eda1da.js" crossorigin="anonymous"></script>
</head>

<body @if($darkMode) class="dark-mode" @endif>

    <div class="main-wrapper">
        <header class="site-header desktop-header">
            <div class="header-left">
                <a href="#"><img src="{{asset('images/logo.svg')}}" alt=""></a>
            </div>
            <div class="header-right">
                <ul class="list-unstyled m-0">
                    <!-- <li>
                        <a href="javascript:;" class="share-btn">Share</a>
                        <ol>
                            <li>
                                <h6>Share</h6>
                            </li>
                            <li><a href="#"><span>Client Password & Link</span> <span class="arrow-icon"></span></a></li>
                            <li><a href="#"><span>Reset Client Password</span> <span class="arrow-icon"></span></a></li>
                            <li class="separator"></li>
                            <li><a href="#"><span> Admin Password</span> <span class="arrow-icon"></span></a></li>
                            <li><a href="#"><span>Reset Admin Password</span> <span class="arrow-icon"></span></a></li>
                            <li><a href="#" class="action-btn"><span>Copy Client Link & Password</span></a></li>
                        </ol>
                    </li> -->
                    <li>
                        <a href="#" class="upload-btn">
                            <svg width="26" height="29" viewBox="0 0 26 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M25.037 24.1108H0.962963C0.244993 24.1108 0 24.3612 0 25.0606V27.91C0 28.6095 0.244993 28.8598 0.962963 28.8598H25.037C25.755 28.8598 26 28.6095 26 27.91V25.0606C26 24.3612 25.755 24.1108 25.037 24.1108Z" fill="#3A3F45" />
                                <path d="M10.5923 6.42964L10.5923 21.2611C10.5923 21.9875 10.737 22.2109 11.5552 22.2109H14.4441C15.2623 22.2109 15.4071 21.9875 15.4071 21.2611V6.42964C15.4071 5.70332 14.7438 5.11453 13.9256 5.11453H12.0738C11.2556 5.11453 10.5923 5.70332 10.5923 6.42964Z" fill="#3A3F45" />
                                <path d="M2.50342 9.45666L12.7983 0.365723L15.9849 3.17958L6.6704 11.4047C6.1289 11.8829 5.25098 11.8829 4.70948 11.4047L2.50342 9.45666Z" fill="#3A3F45" />
                                <path d="M20.9053 11.4047C20.3638 11.8829 19.4858 11.8829 18.9443 11.4047L9.62988 3.17958L12.8164 0.365723L23.1113 9.45664L20.9053 11.4047Z" fill="#3A3F45" />
                            </svg>
                        </a>

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

                        <ol>
                            <li>
                                <h6>Choose format</h6>
                            </li>
                            <li>
                                <a href="{{ route('getcsv', $advertiserId) }}?campaign_id={{$campaign_id}}"><span>Export as .xlsx</span> <span class="arrow-icon"></span></a>
                            </li>
                            <!-- <li>
                                <a href="{{ route('getdoc', $advertiserId) }}?campaign_id={{$campaign_id}}"><span>Export as .doc</span> <span class="arrow-icon"></span></a>
                            </li>
                            <li>
                                <a href="{{ route('getpdf', $advertiserId) }}?campaign_id={{$campaign_id}}"><span>Export as .pdf</span> <span class="arrow-icon"></span></a>
                            </li> -->
                        </ol>
                    </li>
                    <li>
                        <a href="#" class="settings-btn"><svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M26.5812 16.1237C24.6893 15.3379 24.6893 12.6602 26.5812 11.8763C26.8601 11.7608 27.1135 11.5916 27.3269 11.3782C27.5404 11.1647 27.7097 10.9114 27.8252 10.6325C27.9407 10.3536 28.0001 10.0548 28 9.75292C27.9999 9.45109 27.9404 9.15223 27.8248 8.87341L27.4004 7.84986C27.1673 7.28663 26.7199 6.83909 26.1568 6.60569C25.5936 6.37229 24.9608 6.37215 24.3976 6.60529C22.5057 7.39113 20.6108 5.49432 21.3947 3.60242C21.6278 3.03918 21.6277 2.40638 21.3943 1.84324C21.1609 1.2801 20.7134 0.832734 20.1501 0.599553L19.1266 0.175203C18.8478 0.0595984 18.5489 6.36948e-05 18.2471 5.10813e-08C17.9452 -6.35926e-05 17.6464 0.059345 17.3675 0.174832C17.0886 0.290318 16.8353 0.459619 16.6218 0.673062C16.4084 0.886504 16.2392 1.13991 16.1237 1.41879C15.3379 3.31068 12.6602 3.31068 11.8763 1.41879C11.7608 1.13991 11.5916 0.886504 11.3782 0.673062C11.1647 0.459619 10.9114 0.290318 10.6325 0.174832C10.3536 0.059345 10.0548 -6.35926e-05 9.75292 5.10813e-08C9.45109 6.36948e-05 9.15223 0.0595984 8.87341 0.175203L7.84986 0.599553C7.28663 0.832734 6.83909 1.2801 6.60569 1.84324C6.37229 2.40638 6.37215 3.03918 6.60529 3.60242C7.39113 5.49432 5.49432 7.38916 3.60242 6.60529C3.03918 6.37215 2.40638 6.37229 1.84324 6.60569C1.2801 6.83909 0.832734 7.28663 0.599553 7.84986L0.175203 8.87341C0.0595984 9.15223 6.36948e-05 9.45109 5.10813e-08 9.75292C-6.35926e-05 10.0548 0.059345 10.3536 0.174832 10.6325C0.290318 10.9114 0.459619 11.1647 0.673062 11.3782C0.886504 11.5916 1.13991 11.7608 1.41879 11.8763C3.31068 12.6621 3.31068 15.3398 1.41879 16.1237C1.13991 16.2392 0.886504 16.4084 0.673062 16.6218C0.459619 16.8353 0.290318 17.0886 0.174832 17.3675C0.059345 17.6464 -6.35926e-05 17.9452 5.10813e-08 18.2471C6.36948e-05 18.5489 0.0595984 18.8478 0.175203 19.1266L0.599553 20.1501C0.832734 20.7134 1.2801 21.1609 1.84324 21.3943C2.40638 21.6277 3.03918 21.6278 3.60242 21.3947C5.49432 20.6089 7.38916 22.5057 6.60529 24.3976C6.37215 24.9608 6.37229 25.5936 6.60569 26.1568C6.83909 26.7199 7.28663 27.1673 7.84986 27.4004L8.87341 27.8248C9.15223 27.9404 9.45109 27.9999 9.75292 28C10.0548 28.0001 10.3536 27.9407 10.6325 27.8252C10.9114 27.7097 11.1647 27.5404 11.3782 27.3269C11.5916 27.1135 11.7608 26.8601 11.8763 26.5812C12.6621 24.6893 15.3398 24.6893 16.1237 26.5812C16.2392 26.8601 16.4084 27.1135 16.6218 27.3269C16.8353 27.5404 17.0886 27.7097 17.3675 27.8252C17.6464 27.9407 17.9452 28.0001 18.2471 28C18.5489 27.9999 18.8478 27.9404 19.1266 27.8248L20.1501 27.4004C20.7134 27.1673 21.1609 26.7199 21.3943 26.1568C21.6277 25.5936 21.6278 24.9608 21.3947 24.3976C20.6089 22.5057 22.5057 20.6108 24.3976 21.3947C24.9608 21.6278 25.5936 21.6277 26.1568 21.3943C26.7199 21.1609 27.1673 20.7134 27.4004 20.1501L27.8248 19.1266C27.9404 18.8478 27.9999 18.5489 28 18.2471C28.0001 17.9452 27.9407 17.6464 27.8252 17.3675C27.7097 17.0886 27.5404 16.8353 27.3269 16.6218C27.1135 16.4084 26.8601 16.2392 26.5812 16.1237ZM13.4588 19.4262C12.3856 19.4262 11.3365 19.1079 10.4441 18.5117C9.55179 17.9155 8.85631 17.068 8.44561 16.0765C8.03491 15.085 7.92746 13.994 8.13683 12.9414C8.3462 11.8888 8.86299 10.922 9.62186 10.1631C10.3807 9.40424 11.3476 8.88744 12.4002 8.67807C13.4527 8.4687 14.5438 8.57616 15.5353 8.98685C16.5268 9.39755 17.3742 10.093 17.9705 10.9854C18.5667 11.8777 18.8849 12.9268 18.8849 14C18.8849 15.4391 18.3133 16.8193 17.2957 17.8369C16.278 18.8545 14.8979 19.4262 13.4588 19.4262Z" fill="#3A3F45" />
                            </svg>
                        </a>
                        <ol>
                            <li>
                                <a href="javascript:;" onclick="dark();">Dark Mode <span class="switch"><span class="switch-btn"></span></span></a>
                            </li>

                            <li>
                                <a href="{{ route('logout') }}" class="action-btn"><span>Sign Out</span></a>
                            </li>
                        </ol>
                    </li>
                </ul>
            </div>
        </header>
        <header class="site-header mobile-header">
            <ul class="mobile-main-nav">
                <li>
                    <button class="toggle-mobile-menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </li>
                <li>
                    <a href="#"><img src="{{asset('/images/logo.svg')}}" alt=""></a>
                </li>
                <li>
                    <a href="#" class="settings-btn">
                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M26.5812 16.1237C24.6893 15.3379 24.6893 12.6602 26.5812 11.8763C26.8601 11.7608 27.1135 11.5916 27.3269 11.3782C27.5404 11.1647 27.7097 10.9114 27.8252 10.6325C27.9407 10.3536 28.0001 10.0548 28 9.75292C27.9999 9.45109 27.9404 9.15223 27.8248 8.87341L27.4004 7.84986C27.1673 7.28663 26.7199 6.83909 26.1568 6.60569C25.5936 6.37229 24.9608 6.37215 24.3976 6.60529C22.5057 7.39113 20.6108 5.49432 21.3947 3.60242C21.6278 3.03918 21.6277 2.40638 21.3943 1.84324C21.1609 1.2801 20.7134 0.832734 20.1501 0.599553L19.1266 0.175203C18.8478 0.0595984 18.5489 6.36948e-05 18.2471 5.10813e-08C17.9452 -6.35926e-05 17.6464 0.059345 17.3675 0.174832C17.0886 0.290318 16.8353 0.459619 16.6218 0.673062C16.4084 0.886504 16.2392 1.13991 16.1237 1.41879C15.3379 3.31068 12.6602 3.31068 11.8763 1.41879C11.7608 1.13991 11.5916 0.886504 11.3782 0.673062C11.1647 0.459619 10.9114 0.290318 10.6325 0.174832C10.3536 0.059345 10.0548 -6.35926e-05 9.75292 5.10813e-08C9.45109 6.36948e-05 9.15223 0.0595984 8.87341 0.175203L7.84986 0.599553C7.28663 0.832734 6.83909 1.2801 6.60569 1.84324C6.37229 2.40638 6.37215 3.03918 6.60529 3.60242C7.39113 5.49432 5.49432 7.38916 3.60242 6.60529C3.03918 6.37215 2.40638 6.37229 1.84324 6.60569C1.2801 6.83909 0.832734 7.28663 0.599553 7.84986L0.175203 8.87341C0.0595984 9.15223 6.36948e-05 9.45109 5.10813e-08 9.75292C-6.35926e-05 10.0548 0.059345 10.3536 0.174832 10.6325C0.290318 10.9114 0.459619 11.1647 0.673062 11.3782C0.886504 11.5916 1.13991 11.7608 1.41879 11.8763C3.31068 12.6621 3.31068 15.3398 1.41879 16.1237C1.13991 16.2392 0.886504 16.4084 0.673062 16.6218C0.459619 16.8353 0.290318 17.0886 0.174832 17.3675C0.059345 17.6464 -6.35926e-05 17.9452 5.10813e-08 18.2471C6.36948e-05 18.5489 0.0595984 18.8478 0.175203 19.1266L0.599553 20.1501C0.832734 20.7134 1.2801 21.1609 1.84324 21.3943C2.40638 21.6277 3.03918 21.6278 3.60242 21.3947C5.49432 20.6089 7.38916 22.5057 6.60529 24.3976C6.37215 24.9608 6.37229 25.5936 6.60569 26.1568C6.83909 26.7199 7.28663 27.1673 7.84986 27.4004L8.87341 27.8248C9.15223 27.9404 9.45109 27.9999 9.75292 28C10.0548 28.0001 10.3536 27.9407 10.6325 27.8252C10.9114 27.7097 11.1647 27.5404 11.3782 27.3269C11.5916 27.1135 11.7608 26.8601 11.8763 26.5812C12.6621 24.6893 15.3398 24.6893 16.1237 26.5812C16.2392 26.8601 16.4084 27.1135 16.6218 27.3269C16.8353 27.5404 17.0886 27.7097 17.3675 27.8252C17.6464 27.9407 17.9452 28.0001 18.2471 28C18.5489 27.9999 18.8478 27.9404 19.1266 27.8248L20.1501 27.4004C20.7134 27.1673 21.1609 26.7199 21.3943 26.1568C21.6277 25.5936 21.6278 24.9608 21.3947 24.3976C20.6089 22.5057 22.5057 20.6108 24.3976 21.3947C24.9608 21.6278 25.5936 21.6277 26.1568 21.3943C26.7199 21.1609 27.1673 20.7134 27.4004 20.1501L27.8248 19.1266C27.9404 18.8478 27.9999 18.5489 28 18.2471C28.0001 17.9452 27.9407 17.6464 27.8252 17.3675C27.7097 17.0886 27.5404 16.8353 27.3269 16.6218C27.1135 16.4084 26.8601 16.2392 26.5812 16.1237ZM13.4588 19.4262C12.3856 19.4262 11.3365 19.1079 10.4441 18.5117C9.55179 17.9155 8.85631 17.068 8.44561 16.0765C8.03491 15.085 7.92746 13.994 8.13683 12.9414C8.3462 11.8888 8.86299 10.922 9.62186 10.1631C10.3807 9.40424 11.3476 8.88744 12.4002 8.67807C13.4527 8.4687 14.5438 8.57616 15.5353 8.98685C16.5268 9.39755 17.3742 10.093 17.9705 10.9854C18.5667 11.8777 18.8849 12.9268 18.8849 14C18.8849 15.4391 18.3133 16.8193 17.2957 17.8369C16.278 18.8545 14.8979 19.4262 13.4588 19.4262Z" fill="#3A3F45" />
                        </svg>
                    </a>
                    <ol>
                        <li>
                            <div class="mode-wrapper">
                                <button class="light-btn"><span>Light Mode</span></button>
                                <button class="dark-btn"><span>Dark Mode</span></button>
                            </div>

                            <!-- <a href="javascript:;" onclick="dark();">Dark Mode <span class="switch"><span class="switch-btn"></span></span></a> -->
                        </li>

                        <li>
                            <a href="{{ route('logout') }}" class="action-btn"><span>Sign Out</span></a>
                        </li>
                    </ol>
                </li>
            </ul>
        </header>
        @yield('content')
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/no-data-to-display.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <script src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <script>
        // Maintain cookies for dark mode
        function setCookie(cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            var expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }

        function getCookie(cname) {
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }
        // Maintain cookies for dark mode

        $(document).ready(function() {
            $('.light-btn').click(function() {
                setCookie('darkMode', 0);
                $('body').removeClass('dark-mode');
            });
            $('.dark-btn').click(function() {
                setCookie('darkMode', 1);
                $('body').addClass('dark-mode');
            });


            $('.mb-done').click(function() {
                // if (($(e.target).hasClass('export-options') || $(e.target).parents().hasClass('export-options'))) {

                // } else {
                $('.sidebar').removeClass('active')
                //}
            });
        });

        function dark() {
            var body = document.body;
            if (document.body.classList.contains('dark-mode')) {
                body.classList = '';
                setCookie('darkMode', 0);
            } else {
                body.classList = 'dark-mode';
                setCookie('darkMode', 1);
            }
        }
        $('.toggle-mobile-menu').click(function(e) {
            e.stopPropagation();
            $('.sidebar').removeClass('small');
            $('.sidebar').addClass('active')

        })
        $(document).click(function(e) {

            if (($(e.target).hasClass('sidebar') || $(e.target).parents().hasClass('sidebar'))) {

            } else {
                $('.sidebar').removeClass('active')
            }
        })

        $('.sidebar-collape').click(function() {
            $('.sidebar').toggleClass('small');
        })


        $('.owl-carousel').owlCarousel({
            loop: false,
            rewind: true,
            margin: 14,
            nav: true,
            dots: false,
            stagePadding: 15,
            items: 3,
            responsive: {
                600: {
                    items: 3
                },
                1000: {
                    items: 4
                }
            }
        })


        var dateAndMonths = [];
        var reachData = [];
        // Highcharts
        charts = Highcharts.chart('container', {
            chart: {
                type: 'areaspline'
            },
            title: {
                text: '',
                align: 'left'
            },
            lang: {
                noData: "No data for selected time range. <br>Please select another option."
            },
            legend: {
                enabled: false
            },
            xAxis: {
                categories: dateAndMonths
            },
            yAxis: {
                title: {
                    text: ''
                }
            },
            tooltip: {
                shared: true,
                valueSuffix: ''
            },
            credits: {
                enabled: false
            },
            series: [{
                name: '',
                data: reachData,
                fillColor: {
                    linearGradient: [0, 0, 0, 300],
                    stops: [
                        [0, '#f7323f'],
                        [1, Highcharts.color('#f7323f').setOpacity(0).get('rgba')]
                    ]
                }
            }],
            colors: ['#f7323f'],
            exporting: {
                enabled: false
            },
        });

        jQuery(document).ready(function() {
            jQuery('#campaignSelect').change(function() {
                var campaignId = jQuery(this).val();
                if (campaignId == "") {
                    window.location = '{{ Request::url() }}';
                } else {
                    window.location = '{{ Request::url() }}?campaign_id=' + campaignId;
                }
            });

            jQuery('.sort_by_rank').change(function() {
                var filterValue = jQuery(this).val();
                var topAdsContainer = $('.owl-carousel').find('div.owl-stage'),
                    topAds = topAdsContainer.find('div.owl-item');
                topAds.detach().sort(function(a, b) {
                    var astts = $(a).find('.item').data(filterValue);
                    var bstts = $(b).find('.item').data(filterValue);

                    return (astts < bstts) ? (astts < bstts) ? 1 : 0 : -1;
                });

                topAdsContainer.append(topAds);
            });

            jQuery('.sort_by_rank').val('impressions').trigger('change');;

        });
    </script>

    <script>
        jQuery(document).ready(function() {
            var campaignStartDate = "@php echo $campaignStartDate @endphp";
            //campaignStartDate = moment().subtract(500, 'days');
            console.log(campaignStartDate);
            var start = moment(campaignStartDate);
            var end = moment();
          

            function cb(start, end) {
                $('#reportrange span').html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY')); 
            }

            $('#reportrange').daterangepicker({
                opens:'left',
                startDate: start,
                endDate: moment(),
                autoApply: false,
                format: 'DD/MM/YYYY h:mm A',
                alwaysShowCalendars: true,
                maxDate: moment(),
                ranges: {
                    'All': [moment(campaignStartDate), moment()],
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);

            //cb(start, end);

           
            setTimeout(function(){
                displayDataInChart();
            }, 100);
                

        });

        // All time campaign reach

        $('#reportrange').on('apply.daterangepicker', function(ev, picker) {

            var startDate = picker.startDate.format('YYYY-MM-DD');
            var endDate = picker.endDate.format('YYYY-MM-DD');
            displayDataInChart(startDate, endDate, picker);
            console.log(picker);
        });



        function displayDataInChart(startDate="", endDate="", picker = "") {
            if(picker=="")
            {   
                $('#reportrange span').html("All");
            }
            else if(picker  != "" && picker.chosenLabel != "Custom Range")
            {
                $('#reportrange span').html(picker.chosenLabel);
            }
            

            var url = "{{ route('campaign.reach.data', [$advertiserId, $campaign_id]) }}";
            $.ajax({
                    method: "GET",
                    url: url,
                    data: {
                        startDate: startDate,
                        endDate: endDate
                    },
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    }
                })
                .done(function(response) {
                    response = JSON.parse(response);
                    var campaignReach = response.data;
                    dateAndMonths = [];
                    reachData = [];

                    $.each(campaignReach, function(index, value) {
                     
                        dateAndMonths.push(value.dateAndMonth);
                        reachData.push(value.campaignReach);
                    });

                    //console.log(dateAndMonths, reachData);
                    charts.update({
                        xAxis: {
                            categories: dateAndMonths
                        },
                        series: [{
                            data: reachData,
                        }]
                    });


                }).fail(function(data) {
                    toastr.error("Server is not responding. Please try agian!");
                });
        }


    </script>

    <style>
        .owl-stage {
            left: -15px;
        }
    </style>
</body>

</html>