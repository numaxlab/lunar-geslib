<div>
    @foreach($collectionGroups as $group)
        @foreach ($group->collections as $collection)
            <section class="org-tier mb-12">
                <header class="tier-header">
                    <h2 class="at-heading is-2">
                        {{ $collection->translateAttribute('name') }}
                        <span class="at-small">({{ $group->name }})</span>
                    </h2>
                </header>
                <div class="tier-content">
                    Products list...
                </div>
            </section>
        @endforeach
    @endforeach
</div>
