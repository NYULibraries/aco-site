  <div class="header-wrapper">
      <header class="header-main container-fluid" role="banner">
          <div lang="en">
              <h1 class="sitename" lang="en">
                  <a href="{{ config('app.url') }}">{{ env('APP_NAME_EN') }}</a>
              </h1>
          </div>
          <div lang="ar">
              <h1 class="sitename" lang="ar">
                  <a href="{{ config('app.url') }}">{{ env('APP_NAME_AR') }}</a>
              </h1>
          </div>
      </header>
  </div>
  <nav class="navbar navbar-default" role="navigation">
      <div class="container-fluid">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed widget" data-toggle="collapse"
                  aria-label="Toggle Navigation" data-name="navButton">
                  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0" y="0" width="18" height="18"
                      viewBox="0 0 18 18">
                      <rect y="3" fill="#FFFFFF" width="18" height="3"></rect>
                      <rect y="8" fill="#FFFFFF" width="18" height="3"></rect>
                      <rect y="13" fill="#FFFFFF" width="18" height="3"></rect>
                  </svg>
              </button>
          </div>
          <div class="navbar-collapse">
              @include('partials.nav')
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>
