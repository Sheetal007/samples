<div class="apl-section">
    <div class="view-content">
        <div class="apl-header">
            <h1>Agent Listing</h1>
            <div class="search-wrap">
                <label class="label-title">Search by Login Name</label>
                <div class="search-container">
                    <div class="apl-form search">
                        <input type="text" autocomplete="off" placeholder="Agency/Member Name" class="filter-username form-control" >
                    </div>
                </div>
            </div>
            <div class="additional-options">
                <button type="button" name="button" class="export-btn apl-btn apl-btn-secondary">Download CSV</button>
                <a href="{{route('agency-management-create-user')}}" class="apl-btn apl-btn-primary">New Agent</a>
            </div>
        </div>
        <div class="expandable-table-wrap">
            <div class="breadcrumbs">
                <ul>
                    <li> <span>Downline Listing</span> </li>
                </ul>
            </div>
            <div class="downline-container custom-scrollbar">
                <div class="table-responsive">
                    <table class="table apl-table apl-table-striped apl-table-hover">
                        <thead>
                            <tr>
                                <th class="align-left">Login Name</th>
                                <th class="align-left">ID</th>
                                <th class="align-center">Downline</th>
                                <th class="align-center">Locked</th>
                                <th class="align-center">Status</th>
                                <th class="align-center">Details</th>
                                <th class="align-right">Net Exposure</th>
                                <th class="align-right">Take</th>
                                <th class="align-right">Give</th>
                                <th class="align-right">Credit Limit</th>
                                <th class="align-right">PT (%) C/F/T/H/G/X</th>
                                <th class="align-right">PT (%) C/F/T/H/G/X</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($data) && count($data) > 0 )
                            @foreach($data as $user)
                            <tr>
                                <td class="align-left">
                                    <a href="#" class="theme-green" data-toggle="modal" data-target="#infoModal">{{$user->username}}</a>
                                </td>
                                <td class="align-left">
                                    <a href="#" class="theme-green" data-toggle="modal" data-target="#infoModal">{{$user->id}}</a>
                                </td>
                                <td class="align-center">
                                    <a href="javascript:void(0);" class="theme-green loadchild" data-pid="{{$user->id}}"><i class="fa fa-sitemap" aria-hidden="true"></i></a>
                                </td>
                                <td class="align-center">
                                    <button type="button" name="button" class="lock-btn">
                                        <i class="fa fa-unlock-alt" aria-hidden="true"></i>
                                    </button>
                                </td>
                                <td class="align-center"> {{ucfirst($user->status)}}</td>
                                <td class="align-center">
                                    <a href="#" class="theme-green"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                </td>
                                <td class="align-right positive">0.00</td>
                                <td class="align-right"> </td>
                                <td class="align-right"> </td>
                                <td class="align-right">{{ $user->credit_limit }}</td>
                                <td class="align-right">{{ $user->pt_own }}</td>
                                <td class="align-right">{{ $user->pt_down }}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td class="align-center" colspan="12"> No record found </td>
                            </tr>
                            @endif


                        </tbody>
                    </table>
                </div>
            </div>
            <div class="expandable-table-control-bg inverted"></div>

        </div>
    </div>
</div>
<!-- center contbx ends-->
