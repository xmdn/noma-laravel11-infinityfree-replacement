<x-layouts.auth title="Administration">
    <section class="page-heading">
        <p class="eyebrow">Administration</p>
        <h1>Quiet control.</h1>
        <p class="lede">Manage access today; catalog, order, and customer workspaces can build on these permissions.</p>
    </section>

    <div class="summary-grid">
        @foreach ($modules as $module)
            <article>
                <span>{{ strtoupper($module['label']) }}</span>
                <strong>{{ $module['value'] }}</strong>
                @isset($module['link'])
                    <a href="{{ $module['link'] }}">{{ $module['action'] }} -></a>
                @endisset
            </article>
        @endforeach
    </div>
</x-layouts.auth>
