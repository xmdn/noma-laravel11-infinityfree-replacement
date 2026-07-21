<x-layouts.app title="NOMA - Commerce platform">
    <div class="platform-shell">
        <header class="platform-nav">
            <a class="platform-mark" href="{{ route('home') }}">NOMA</a>
            <nav aria-label="Platform navigation">
                <a href="#shops">Shops</a>
                <a href="#platform">Platform</a>
                @auth
                    <a href="{{ route('dashboard') }}">Account</a>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                    <a class="nav-cta" href="{{ route('owner.register') }}">Start a shop</a>
                @endauth
            </nav>
        </header>

        <main>
            <section class="platform-hero">
                <div class="platform-hero-copy">
                    <p class="platform-eyebrow">Multi-tenant commerce for independent sellers</p>
                    <h1>Create a shop, publish products, and run your catalog from one account.</h1>
                    <p>NOMA is moving toward the same operating model used by modern commerce platforms: owner onboarding, shop identity, catalog management, staff access, storefronts, and tenant isolation.</p>
                    <div class="hero-actions">
                        <a class="primary-link" href="{{ route('owner.register') }}">Create your shop</a>
                        <a class="secondary-link" href="#shops">Browse active shops</a>
                    </div>
                </div>
                <div class="platform-hero-media" aria-label="Commerce dashboard preview">
                    <img src="https://images.unsplash.com/photo-1556742502-ec7c0e9f34b1?auto=format&fit=crop&w=1400&q=85" alt="Small business owner packing ecommerce orders">
                    <div class="dashboard-preview">
                        <span>Today</span>
                        <strong>{{ $shops->sum('products_count') }} products live</strong>
                        <p>{{ $shops->count() }} shops are already configured in this local environment.</p>
                    </div>
                </div>
            </section>

            <section class="platform-band" id="platform">
                <article>
                    <span>01</span>
                    <h2>Shop identity</h2>
                    <p>Each owner gets a verified shop with its own slug address, settings, staff access, and catalog boundary.</p>
                </article>
                <article>
                    <span>02</span>
                    <h2>Catalog operations</h2>
                    <p>Products, categories, images, prices, badges, and publishing status are managed from the shop admin area.</p>
                </article>
                <article>
                    <span>03</span>
                    <h2>Tenant roadmap</h2>
                    <p>The current build uses shop-scoped tables. Dedicated tenant databases remain the next infrastructure phase.</p>
                </article>
            </section>

            <section class="shop-directory" id="shops">
                <div class="section-heading">
                    <p class="platform-eyebrow">Existing shops</p>
                    <h2>Open storefronts</h2>
                </div>

                <div class="shop-grid">
                    @forelse ($shops as $shop)
                        <a class="shop-tile" href="{{ $shop->publicUrl() }}">
                            <span>{{ $shop->slug }}</span>
                            <strong>{{ $shop->name }}</strong>
                            <small>{{ $shop->products_count }} active products</small>
                        </a>
                    @empty
                        <article class="empty-directory">
                            <strong>No shops yet.</strong>
                            <p>Register as an owner, verify email, then the first shop will appear here.</p>
                            <a href="{{ route('owner.register') }}">Create the first shop</a>
                        </article>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
</x-layouts.app>
