<nav class="navbar presto-navbar navbar-expand-lg sticky-top py-2 shadow-sm">
    <div class="container">
        <a class="navbar-brand d-inline-flex align-items-center p-0 me-3" href="{{ route('homepage') }}"
            aria-label="{{ __('ui.home_link_aria') }}">
            <img class="presto-brand-logo" src="/media/Logo2.png" alt="{{ __('ui.logo_alt') }}">
            <span class="d-none d-sm-inline ms-2 lh-1">
                <span class="presto-brand-title d-block fw-bold fs-4">Presto</span>
                <small class="presto-brand-tagline fw-semibold">{{__('ui.slogan')}}</small>
            </span>
        </a>

        <x-_locale lang="it" />
        <x-_locale lang="uk" />
        <x-_locale lang="es" />

        <button class="presto-toggler navbar-toggler border-0 shadow-none rounded-circle p-2" type="button"
            data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation"
            aria-expanded="false" aria-label="{{ __('ui.open_navigation_aria') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavigation">
            <ul class="navbar-nav mx-lg-auto align-items-lg-center gap-1 gap-lg-2 py-3 py-lg-0">
                @auth
                @if (Auth::user()->is_revisor)
                <li class="nav-item">
                    <a @class([
                        'presto-interactive presto-nav-link nav-link d-flex align-items-center gap-2 rounded-pill px-3 py-2 fw-semibold',
                        'presto-nav-link-active' => request()->routeIs('revisor_index', 'revisor.*'),
                    ]) href="{{ route('revisor_index') }}"
                        @if (request()->routeIs('revisor_index', 'revisor.*')) aria-current="page" @endif>
                        <x-presto-check-verified-02 style="width: 1.1rem; height: 1.1rem;" aria-hidden="true"
                            focusable="false" />
                        {{__('ui.revisor_area')}}
                        <span
                            class="position-absolute top-5 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ \App\Models\Article::toBeRevisedCount() }}
                        </span>
                    </a>
                </li>
                @endif
                @endauth
            </ul>

            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 pb-2 pb-lg-0">
                <a class="btn presto-interactive presto-btn-dark d-inline-flex align-items-center justify-content-center gap-2 rounded-pill px-3 fw-semibold"
                    href="{{ route('article.index') }}">

                    <x-presto-cart-plus style="width: 1.1rem; height: 1.1rem;" />
                    {{__('ui.articles')}}
                </a>

                @auth
                <a class="btn presto-interactive presto-btn-dark d-inline-flex align-items-center justify-content-center gap-2 rounded-pill px-3 fw-semibold"
                    href="{{ route('create.article') }}">

                    <x-presto-add style="width: 1.1rem; height: 1.1rem;" />
                      {{__('ui.publish_article')}}
                </a>

                <div class="dropdown">
                    <button
                        class="btn presto-interactive presto-btn-light w-100 d-inline-flex align-items-center justify-content-center gap-2 rounded-pill px-3 fw-semibold"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <x-presto-arrow-right-circle style="width: 1.1rem; height: 1.1rem;" />
                        {{__('ui.profile')}}
                        <x-presto-chevron-down style="width: .9rem; height: .9rem;" />
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 p-2 rounded-4">
                        {{--
                            <li><a class="dropdown-item rounded-3 py-2" href="#">Il mio profilo</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                        --}}
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item rounded-3 py-2" type="submit"> {{__('ui.logout')}}</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @else
                <a class="btn presto-interactive presto-btn-light d-inline-flex align-items-center justify-content-center gap-2 rounded-pill px-3 fw-semibold"
                    href="{{ route('login') }}">
                    <x-presto-arrow-right-circle style="width: 1.1rem; height: 1.1rem;" />
                     {{__('ui.login')}}
                </a>
                <a class="btn presto-interactive presto-btn-red presto-navbar-register d-inline-flex align-items-center gap-2 justify-content-center rounded-pill px-3 fw-semibold"
                    href="{{ route('register') }}"><x-presto-person-add
                        style="width: 1.1rem; height: 1.1rem;" /> {{__('ui.register')}}</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
