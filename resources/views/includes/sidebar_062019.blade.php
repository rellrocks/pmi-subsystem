<!-- BEGIN SIDEBAR -->
    <div class="page-sidebar-wrapper">
        <div class="page-sidebar navbar-collapse collapse">
            <!-- BEGIN SIDEBAR MENU -->
            <!-- DOC: Apply "page-sidebar-menu-light" class right after "page-sidebar-menu" to enable light sidebar menu style(without borders) -->
            <!-- DOC: Apply "page-sidebar-menu-hover-submenu" class right after "page-sidebar-menu" to enable hoverable(hover vs accordion) sub menu mode -->
            <!-- DOC: Apply "page-sidebar-menu-closed" class right after "page-sidebar-menu" to collapse("page-sidebar-closed" class must be applied to the body element) the sidebar sub menu mode -->
            <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
            <!-- DOC: Set data-keep-expand="true" to keep the submenues expanded -->
            <!-- DOC: Set data-auto-speed="200" to adjust the sub menu slide up/down speed -->
            <ul class="page-sidebar-menu page-sidebar-menu-light page-sidebar-menu-closed" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
                <!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
                <li class="sidebar-toggler-wrapper">
                    <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
                    <div class="sidebar-toggler"></div>
                    <!-- END SIDEBAR TOGGLER BUTTON -->
                </li>
                <!-- DOC: To remove the search box from the sidebar you just need to completely remove the below "sidebar-search-wrapper" LI element -->
                <li class="sidebar-search-wrapper"></li>
                <?php
                    $json  = json_encode($userProgramAccess);
                    $array = json_decode($json, true);
                    $progclass = array_column($array, 'program_class');
                    $progcode = array_column($array, 'program_code');
                    $url = ""; $icon = "";
                ?>

                @if (in_array("Master Management",$progclass))
                    <li>
                        <a href="javascript:;">
                        <i class="fa fa-folder-open-o"></i>
                        <span class="title">Master Management</span>
                        <span class="arrow "></span>
                        </a>
                        <ul class="sub-menu">
                            <?php $url = ""; $icon = ""; ?>
                            @foreach ($userProgramAccess as $access)
                                @if ($access->program_code == "2001")
                                    <?php $url = "/usermaster"; $icon = "fa fa-user"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "2003")
                                    <?php $url = "/productlines"; $icon = "fa fa-cart-plus"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "2002")
                                    <?php $url = "/suppliermaster"; $icon = "fa fa-cubes"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "2004")
                                    <?php $url = "/justificationmaster"; $icon = "fa fa-comment"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "2005")
                                    <?php $url = "/dropdown"; $icon = "fa fa-th-list"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "2006")
                                    <?php $url = "/sold-to"; $icon = "fa fa-tag"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "2007")
                                    <?php $url = "/invoicing-markup"; $icon = "fa fa-line-chart"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif

                @if (in_array("Operational Management",$progclass))
                    <li>
                        <a href="javascript:;">
                        <i class="fa fa-refresh"></i>
                        <span class="title">Subsystems</span>
                        <span class="arrow "></span>
                        </a>
                        <ul class="sub-menu">
                            @foreach ($userProgramAccess as $access)
                                @if ($access->program_code == "3001")
                                    <?php $url = "/orderdatacheck"; $icon = "fa fa-clipboard";?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3002")
                                    <?php $url = "/ypicsr3"; $icon = "fa fa-area-chart"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3003")
                                    <?php $url = "/mra"; $icon = "fa fa-puzzle-piece"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3004")
                                    <?php $url = "/partsrejectionrate"; $icon = "fa fa-refresh"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3005")
                                    <?php $url = "/invoicedatacheck"; $icon = "fa fa-file"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3006")
                                    <?php $url = "/materiallist"; $icon = "fa fa-list-ol"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3007")
                                    <?php $url = "/mrpcalculation"; $icon = "fa fa-calculator"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3014")
                                    <?php $url = "/prchange"; $icon = "fa fa-file-o"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3015")
                                    <?php $url = "/prbalance"; $icon = "fa fa-clipboard"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3016")
                                    <?php $url = "/inventoryquery"; $icon = "fa fa-cubes"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                
                                @elseif ($access->program_code == "3028")
                                    <?php $url = "/packinglistsystem"; $icon = "fa fa-bars"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif

                                @elseif ($access->program_code == "3035")
                                    <?php $url = "/yieldperformance"; $icon = "fa fa-line-chart"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3036")
                                    <?php $url = "/ypicsinvoicing"; $icon = "fa fa-file-text"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif
                
                @if (in_array("SSS",$progclass))
                    <li>
                        <a href="javascript:;" ><i class="fa fa-calendar" ></i> <span class="title">SSS</span><span class="arrow "></span></a>
                        <ul class="sub-menu">
                            @foreach ($userProgramAccess as $access)
                                @if ($access->program_code == "3008")
                                    <?php $url = "/postatus"; $icon = "fa fa-line-chart";?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3009")
                                    <?php $url = "/partsstatus"; $icon = "fa fa-certificate"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3010")
                                    <?php $url = "/deliverywarning"; $icon = "fa fa-truck"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3011")
                                    <?php $url = "/dataupdate"; $icon = "fa fa-edit"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3012")
                                    <?php $url = "/answerinputmanagement"; $icon = "fa fa-clipboard"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3013")
                                    <?php $url = "/sampledoujiinput"; $icon = "fa fa-clipboard"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif
                
                @if (in_array("WBS",$progclass))
                    <li>
                        <a href="javascript:;" ><i class="fa fa-cube" ></i> <span class="title">WBS</span><span class="arrow "></span></a>
                        <ul class="sub-menu">
                            @foreach ($userProgramAccess as $access)
                                @if ($access->program_code == "3017")
                                    <?php $url = "/materialreceiving"; $icon = "fa fa-qrcode";?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3037")
                                    <?php $url = "/wbslocmat"; $icon = "fa fa-qrcode"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3018")
                                    <?php $url = "/iqc"; $icon = "fa fa-search"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3019")
                                    <?php $url = "/material-kitting"; $icon = "fa fa-clipboard"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3020")
                                    <?php $url = "/sakidashi-issuance"; $icon = "glyphicon glyphicon-paste"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3022")
                                    <?php $url = "/wbsphysicalinventory"; $icon = "glyphicon glyphicon-list-alt"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3023")
                                    <?php $url = "/wbsprodmatrequest"; $icon = "glyphicon glyphicon-save-file"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3024")
                                    <?php $url = "/wbsprodmatreturn"; $icon = "fa fa-exchange"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3025")
                                    <?php $url = "/whs-issuance"; $icon = "fa fa-cubes"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url('/whs-issuance') }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3026")
                                    <?php $url = "/wbsmaterialdisposition"; $icon = "fa fa-history"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3027")
                                    <?php $url = "/wbsreports"; $icon = "fa fa-file-text-o"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3021")
                                    <?php $url = "/wbsemailsettings"; $icon = "fa fa-envelope"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif

                @if (in_array("QCDB",$progclass))
                    <li>
                        <a href="javascript:;" ><i class="fa fa-search" ></i> <span class="title">QC Database</span><span class="arrow "></span></a>
                        <ul class="sub-menu">
                            @foreach ($userProgramAccess as $access)
                                @if ($access->program_code == "3029")
                                    <?php $url = "/iqcinspection"; $icon = "fa fa-search";?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3038")
                                    <?php $url = "/iqc-matrix"; $icon = "fa fa-cogs"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3030")
                                    <?php $url = "/oqcinspection"; $icon = "fa fa-search"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3031")
                                    <?php $url = "/fgs"; $icon = "fa fa-line-chart"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "3032")
                                    <?php $url = "/packinginspection"; $icon = "fa fa-cube"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @endif
                            @endforeach
                            
                        </ul>
                    </li>
                @endif

                @if (in_array("Engineering Yielding Performance",$progclass))
                    <li>
                        <a href="javascript:;" ><i class="fa fa-road" ></i> <span class="title">Engineering Yielding Performance</span><span class="arrow "></span></a>
                        <ul class="sub-menu">
                        @foreach ($userProgramAccess as $access)
                            @if ($access->program_code == "6001")
                                <?php $url = "/addnewYieldperformance"; $icon = "fa fa-pencil-square-o";?>
                                @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                @endif
                            @elseif ($access->program_code == "6002")
                                <?php $url = "/poregistration"; $icon = "fa fa-cube"; ?>
                                @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                @endif
                             @elseif ($access->program_code == "6003")
                                <?php $url = "/targetyieldperformance"; $icon = "fa fa-bullseye"; ?>
                                @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                @endif
                            @elseif ($access->program_code == "6004")
                                <?php $url = "/ReportYieldPerformance"; $icon = "fa fa-area-chart"; ?>
                                @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                        </ul>
                    </li>
                @endif
                
                @if (in_array("QCMLD",$progclass))
                    <li>
                        <a href="javascript:;" ><i class="fa fa-search" ></i> <span class="title">QC Database Molding</span><span class="arrow "></span></a>
                        <ul class="sub-menu">
                        @foreach ($userProgramAccess as $access)
                            @if ($access->program_code == "3033")
                                <?php $url = "/oqcmolding"; $icon = "fa fa-search";?>
                                @if ($access->read_write != "0")
                                <li>
                                    <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                </li>
                                @endif
                            @elseif ($access->program_code == "3034")
                                <?php $url = "/packingmolding"; $icon = "fa fa-cube"; ?>
                                @if ($access->read_write != "0")
                                <li>
                                    <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                </li>
                                @endif
                            @endif
                        @endforeach
                        </ul>
                    </li>
                @endif

                @if (in_array("YPICS",$progclass))
                    <li>
                        <a href="javascript:;">
                        <i class="fa fa-cubes"></i>
                        <span class="title">YPICS</span>
                        <span class="arrow "></span>
                        </a>
                        <ul class="sub-menu">
                            @foreach ($userProgramAccess as $access)
                                @if ($access->program_code == "5001")
                                    <?php $url = "/withdrawal-detail"; $icon = "fa fa-mobile";?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "5002")
                                    <?php $url = "/ypics-dispatch"; $icon = "fa fa-file-excel-o";?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif

                @if (in_array("Security Management",$progclass))
                    <li>
                        <a href="javascript:;">
                        <i class="fa fa-folder-open-o"></i>
                        <span class="title">Security Management</span>
                        <span class="arrow "></span>
                        </a>
                        <ul class="sub-menu">
                            @foreach ($userProgramAccess as $access)
                                @if ($access->program_code == "4001")
                                    <?php $url = "/changepassword"; $icon = "fa fa-lock";?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "4002")
                                    <?php $url = "/wbssetiing"; $icon = "fa fa-barcode"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "4003")
                                    <?php $url = "/transactionsetting"; $icon = "fa fa-exchange"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "4004")
                                    <?php $url = "/companysetting"; $icon = "fa fa-building"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @elseif ($access->program_code == "4005")
                                    <?php $url = "/plsetting"; $icon = "fa fa-wrench"; ?>
                                    @if ($access->read_write != "0")
                                    <li>
                                        <a href="{{ url($url) }}"><i class="{{ $icon }}" ></i> {{ $access->program_name }}</a>
                                    </li>
                                    @endif
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif
                
            </ul>
            <!-- END SIDEBAR MENU -->
        </div>
    </div>
<!-- END SIDEBAR -->