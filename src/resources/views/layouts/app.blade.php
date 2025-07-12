<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>attendance-management</title>
  <link rel="stylesheet" href="{{ asset('css/common.css')}}">
  @yield('css')
</head>

<body>
  <div class="app">
    <header class="header">
      <div class="header__inner">
        <a href="/attendance/list" class="header__logo">
          <img src="{{ asset('image/logo.svg') }}" alt="COACHTECH">
        </a>
        @empty($HeaderParts)
          <div class="header-nav__group">
            <nav>
              <ul class="header-nav">
                <li class="header-nav__item">
                  <a class="header-nav__link" href="/attendance">勤怠</a>
                </li>
                <li class="header-nav__item">
                  <a class="header-nav__link" href="/attendance/list">勤怠一覧</a>
                </li>
                <li class="header-nav__item">
                  <a class="header-nav__link" href="/request">申請</a>
                </li>
                <li class="header-nav__item">
                  <form class="header-nav__form" action="{{ route('logout') }}" method="post">
                      @csrf  
                      <button class="header-nav__link logout-button" type="submit">ログアウト</button>
                  </form>
                </li>
              </ul>
            </nav>
          </div>
        @endempty
      </div>
    </header>
    <div class="content">
      @yield('content')
    </div>
  </div>
</body>
</html>