<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">

    <!-- Sidebar user panel (optional) -->
    <div class="user-panel">
      <div class="pull-left image">
        <img src="{{ $avatar }}" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p>{{ Auth::user()->name }}</p>
        <!-- Status -->
        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div>

    <!-- search form (Optional) -->
    <form action="#" method="get" class="sidebar-form">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Search...">
        <span class="input-group-btn">
            <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
            </button>
          </span>
      </div>
    </form>
    <!-- /.search form -->

    <!-- Sidebar Menu -->
    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MENU</li>
      <!-- Optionally, you can add icons to the links -->
      <li {{ (str_is('dashboard', Route::currentRouteName()) ? 'class=active' : '') }}><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
      @if(Auth::user()->type == config('constants.userType.superadmin'))
      <li {{ (str_is('user.*', Route::currentRouteName()) ? 'class=active' : '') }}><a href="{{ route('user.index') }}"><i class="fa fa-users"></i> <span>Users</span></a></li>
      @endif
      <li {{ (str_is('upload.*', Route::currentRouteName()) ? 'class=active' : '') }}><a href="{{ route('upload.index') }}"><i class="fa fa-edit"></i> <span>Upload Transaction</span></a></li>
      <li {{ (str_is('hincomecal.*', Route::currentRouteName()) ? 'class=active' : '') }}><a href="{{ route('hincomecal.index') }}"><i class="fa fa-credit-card"></i> <span>Transactions</span></a></li>
      <li {{ (str_is('report.*', Route::currentRouteName()) ? 'class=active' : '') }}><a href="{{ route('report.index') }}"><i class="fa fa-pie-chart"></i> <span>Report</span></a></li>
    </ul>
    <!-- /.sidebar-menu -->
  </section>
  <!-- /.sidebar -->
</aside>
