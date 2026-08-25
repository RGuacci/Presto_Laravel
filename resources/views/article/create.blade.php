<x-layout>

    <section class="presto-create-hero py-5">
        <div class="container py-lg-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <span class="presto-eyebrow text-uppercase small fw-bold">{{ __('ui.make_room') }}</span>
                    <h1 class="display-3 mt-3 mb-3">{{ __('ui.publish_your') }}
                        <br>
                        <span class="presto-accent">{{ __('ui.ad_accent') }}</span>
                    </h1>
                    <p class="lead mb-0">{{ __('ui.describe_what_you_sell') }}</p>
                </div>
                <div class="presto-create-timeline-column col-lg-6">
                    <ol class="presto-create-steps list-unstyled mb-0" aria-label="{{ __('ui.how_to_publish_aria') }}">
                        <li class="presto-create-step-item">
                            <span class="presto-create-step-number" data-step="1" aria-hidden="true"></span>
                            <span class="presto-create-step-copy">
                                <small>{{ __('ui.step_1') }}</small>
                                <strong>{{ __('ui.step_fill') }}</strong>
                            </span>
                        </li>
                        <li class="presto-create-step-item">
                            <span class="presto-create-step-number" data-step="2" aria-hidden="true"></span>
                            <span class="presto-create-step-copy">
                                <small>{{ __('ui.step_2') }}</small>
                                <strong>{{ __('ui.step_publish') }}</strong>
                            </span>
                        </li>
                        <li class="presto-create-step-item">
                            <span class="presto-create-step-number" data-step="3" aria-hidden="true"></span>
                            <span class="presto-create-step-copy">
                                <small>{{ __('ui.step_3') }}</small>
                                <strong>{{ __('ui.step_complete') }}</strong>
                            </span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <livewire:create-article-form />
            </div>
        </div>
    </section>
</x-layout>