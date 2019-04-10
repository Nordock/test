<!-- Main Header -->
<header class="main-header">

  <!-- Logo -->
  <a href="{{ route('dashboard') }}" class="logo">
    <!-- mini logo for sidebar mini 50x50 pixels -->
    <span class="logo-mini">{{ config('constants.app.app_letter') }}</span>
    <!-- logo for regular state and mobile devices -->
    <span class="logo-lg">{{ config('constants.app.app_name') }}</span>
  </a>

  <!-- Header Navbar -->
  <nav class="navbar navbar-static-top" role="navigation">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
      <span class="sr-only">Toggle navigation</span>
    </a>
    <!-- Navbar Right Menu -->
    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        <!-- User Account Menu -->
        <li class="dropdown user user-menu">
          <!-- Menu Toggle Button -->
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <!-- The user image in the navbar-->
            <img src="{{ $avatar }}" class="user-image" alt="User Image">
            <!-- hidden-xs hides the username on small devices so only the image appears. -->
            <span class="hidden-xs">{{ Auth::user()->name }}</span>
          </a>
          <ul class="dropdown-menu">
            <!-- The user image in the menu -->
            <li class="user-header">
              <img src="{{ $avatar }}" class="img-circle" alt="User Image">

              <p>
                {{ Auth::user()->name }}
                <small>Member since {{ \Carbon\Carbon::createFromTimeStamp(strtotime(Auth::user()->created_at))->diffForHumans() }}</small>
              </p>
            </li>
            <!-- Menu Footer-->
            <li class="user-footer">
              <div class="pull-left">
                <a href="{{ route('profile.edit') }}" class="btn btn-default btn-flat">Profile</a>
              </div>
              <div class="pull-right">
                <form method="POST" action="{{ route('logout') }}">
  								{{ csrf_field() }}
  								<button class="btn btn-default btn-flat" id="logout">Sign out</button>
  							</form>
              </div>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</header>
