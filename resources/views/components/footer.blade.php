<footer class="presto-footer mt-auto pt-5">
    @php
        $isGuest = Auth::guest();
        $showRevisorRequest = $isGuest || (Auth::check() && ! Auth::user()->is_revisor);
    @endphp

    <div class="container">
        <div @class([
            'row align-items-stretch g-4 pb-5',
            'justify-content-center' => $showRevisorRequest,
        ])>
            <div @class([
                'col-lg-4' => !$showRevisorRequest,
                'col-lg-5 order-2 d-flex flex-column justify-content-center align-items-start' => $showRevisorRequest,
            ])>
                <a class="d-inline-flex align-items-center gap-3 text-decoration-none mb-3"
                    href="{{ route('homepage') }}" aria-label="{{ __('ui.home_link_aria') }}">
                    <img class="presto-footer-logo" src="{{ asset('media/Logo2.png') }}" alt="{{ __('ui.logo_alt') }}">
                    <span>
                        <span class="d-block fs-3 fw-bold text-white lh-1">Presto</span>
                        <small class="presto-footer-tagline"> {{__('ui.give_value')}}</small>
                    </span>
                </a>
                <p class="mb-4 text-white-50 pe-lg-4"> {{__('ui.marketplace_description')}}</p>
                <div class="d-flex align-items-center gap-2 text-white-50 small">
                    <x-presto-map-pin class="presto-icon presto-icon-nav presto-location-icon" />
                    <span> {{__('ui.near_you')}}</span>
                </div>
            </div>

            <div @class([
                'col-12 order-1' => $showRevisorRequest,
                'col-lg-8' => !$showRevisorRequest,
            ])>
                <section class="presto-footer-cta presto-footer-cta-inline rounded-4 p-4 shadow-lg h-100">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center h-100 gap-4">
                        <div class="d-flex align-items-start gap-3 flex-grow-1">
                            <span
                                class="presto-cta-icon d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0">
                                <x-presto-recycle-line class="presto-icon presto-icon-cta" />
                            </span>
                            <div>
                                <p class="presto-eyebrow text-uppercase small fw-bold mb-1"> {{__('ui.next_opportunity')}}
                                </p>
                                <h2 class="h2 mb-1"> {{__('ui.second_life')}}</h2>
                                <p class="mb-0">{{__('ui.publish_description')}}</p>
                            </div>
                        </div>
                        <a class="btn presto-interactive presto-btn-dark presto-start-now btn-lg d-inline-flex align-items-center justify-content-center gap-2 rounded-pill px-4 fw-bold flex-shrink-0"
                            href="{{ Auth::check() ? route('create.article') : route('register') }}">
                             {{__('ui.start_now')}}
                            <x-presto-arrow-right-circle class="presto-icon presto-icon-arrow" />
                        </a>
                    </div>
                </section>
            </div>

            {{-- Link per la candidatura a revisore --}}
            @if ($showRevisorRequest)
                <div class="col-lg-5 order-3 d-flex flex-column justify-content-center">

                    <h2 class="h5 mb-2 text-white"> {{__('ui.work_with_us')}}</h2>
                    <p class="text-white-50 small mb-3"> {{__('ui.become_revisor')}}</p>
                    @if ($isGuest)
                        <button type="button"
                            class="btn presto-btn-dark presto-revisor-request fw-bold rounded-pill w-100 px-3 mt-2"
                            data-bs-toggle="modal" data-bs-target="#guestRevisorModal"
                            aria-controls="guestRevisorModal" aria-haspopup="dialog">
                            {{__('ui.send_application')}}
                            <x-presto-arrow-right-circle class="presto-icon presto-icon-arrow" />
                        </button>
                    @else
                        <a href="{{ route('become.revisor') }}"
                            class="btn presto-btn-dark presto-revisor-request fw-bold rounded-pill w-100 px-3 mt-2">
                            {{__('ui.send_application')}}
                            <x-presto-arrow-right-circle class="presto-icon presto-icon-arrow" />
                        </a>
                    @endif

                </div>
            @endif
        </div>
    </div>

    {{-- <div class="presto-footer-bottom"> --}}
        <div
            class="container py-4 d-flex flex-column flex-md-row justify-content-center align-items-md-center gap-2 small text-white-50">
            <span>&copy; {{ now()->year }}  {{__('ui.copyright')}}</span>
            {{-- <span class="presto-footer-signature-wrap d-inline-flex align-items-center gap-2">
                <x-presto-person-fill-check class="presto-icon presto-icon-bottom presto-icon-creators" />
                <span class="presto-footer-signature">
                    <span><span>Fatto</span></span>
                    <span><span>da</span></span>
                    <span><span>Manuel</span></span>
                    <span><span>Pierangeli,</span></span>
                    <span><span>Raffaele</span></span>
                    <span><span>Guacci,</span></span>
                    <span><span>Adamo</span></span>
                    <span><span>Junior</span></span>
                    <span><span>Mizzoni</span></span>
                    <span><span>e</span></span>
                    <span><span>Daniele</span></span>
                    <span><span>Pigliacelli.</span></span>
                </span>
            </span> --}}
        </div>
    </div>
</footer>

@if ($isGuest)
    <div class="modal fade presto-guest-revisor-modal" id="guestRevisorModal" tabindex="-1"
        aria-labelledby="guestRevisorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="presto-guest-revisor-modal-close" data-bs-dismiss="modal"
                    aria-label="{{ __('ui.close') }}">
                    <x-presto-x-circle-fill aria-hidden="true" focusable="false" />
                </button>

                <div class="modal-body">
                    <span class="presto-guest-revisor-modal-icon" aria-hidden="true">
                        <x-presto-person-fill-check focusable="false" />
                    </span>

                    <h2 id="guestRevisorModalLabel">{{ __('ui.guest_revisor_title') }}</h2>
                    <p>{{ __('ui.guest_revisor_description') }}</p>

                    <div class="presto-guest-revisor-modal-actions">
                        <a href="{{ route('login') }}"
                            class="presto-guest-revisor-modal-action presto-guest-revisor-modal-login">
                            {{ __('ui.login') }}
                        </a>
                        <a href="{{ route('register') }}"
                            class="presto-guest-revisor-modal-action presto-guest-revisor-modal-register">
                            {{ __('ui.register') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
