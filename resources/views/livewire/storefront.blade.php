<div class="store" x-data="{ menu: false }" @keydown.escape.window="$wire.cartOpen = false; menu = false">
    <div class="announcement"><span>Complimentary shipping on orders over $200</span><span class="announcement-side">Designed with intention · Shipped worldwide</span></div>

    <header class="nav-shell">
        <button class="icon-button mobile-menu" @click="menu = !menu" aria-label="Toggle menu"><span></span><span></span></button>
        <a class="wordmark" href="#top">NOMA<span>®</span></a>
        <nav :class="menu && 'open'" aria-label="Main navigation">
            <a href="#shop" @click="menu = false">New arrivals</a>
            <a href="#shop" wire:click="$set('category', 'Living')" @click="menu = false">Living</a>
            <a href="#shop" wire:click="$set('category', 'Carry')" @click="menu = false">Carry</a>
            <a href="#journal" @click="menu = false">Journal</a>
        </nav>
        <div class="nav-actions">
            <button class="search-toggle" onclick="document.querySelector('#catalog-search').focus()" aria-label="Search">Search</button>
            <button class="bag-button" wire:click="$toggle('cartOpen')">Bag <span>{{ $this->cart['count'] }}</span></button>
        </div>
    </header>

    <main id="top">
        <section class="hero-store">
            <div class="hero-media"><img src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=1800&q=90" alt="Quiet modern interior with considered furniture"></div>
            <div class="hero-overlay"></div>
            <div class="hero-content"><p class="kicker">The quiet collection · 2026</p><h1>Objects for<br>a considered life.</h1><p>Essential forms, honest materials, and enduring function. Designed to live with you—not around you.</p><a class="shop-link" href="#shop">Explore the collection <span>↘</span></a></div>
            <div class="hero-caption"><span>01</span><span>Form Lounge Chair<br>Natural ash / Wool</span></div>
        </section>

        <section class="manifesto">
            <span class="section-number">01 / Our point of view</span>
            <p>We make fewer, better things. Each piece is selected for its ability to bring <em>clarity, utility, and quiet character</em> to the everyday.</p>
            <div class="manifesto-meta"><span>Independent since 2018</span><span>London · Copenhagen · Online</span></div>
        </section>

        <section class="catalog" id="shop">
            <div class="catalog-head">
                <div><span class="section-number">02 / The collection</span><h2>Shop all objects</h2></div>
                <p>Purposeful essentials for your space, your work, and everywhere in between.</p>
            </div>

            <div class="catalog-tools">
                <div class="category-tabs" role="group" aria-label="Product category">
                    @foreach (['All', 'Living', 'Carry', 'Wear', 'Objects'] as $item)
                        <button wire:click="$set('category', '{{ $item }}')" @class(['active' => $category === $item])>{{ $item }}</button>
                    @endforeach
                </div>
                <div class="tool-right">
                    <label class="catalog-search"><span>⌕</span><input id="catalog-search" wire:model.live.debounce.250ms="query" placeholder="Search objects" aria-label="Search products"></label>
                    <label class="sort"><span>Sort</span><select wire:model.live="sort"><option value="featured">Featured</option><option value="newest">Newest</option><option value="price-asc">Price: low</option><option value="price-desc">Price: high</option></select></label>
                </div>
            </div>

            <div class="product-grid" wire:loading.class="loading">
                @forelse ($this->products as $product)
                    <article class="product-card" wire:key="product-{{ $product['id'] }}">
                        <div class="product-image">
                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                            <div class="badges">@if($product['new'])<span>New</span>@endif @if($product['featured'])<span>Selected</span>@endif</div>
                            <button class="favorite @if(in_array($product['id'], $favorites, true)) active @endif" wire:click="toggleFavorite('{{ $product['id'] }}')" aria-label="Toggle favorite">♡</button>
                            <button class="quick-add" wire:click="addToCart('{{ $product['id'] }}')"><span>Add to bag</span><b>+</b></button>
                        </div>
                        <div class="product-info"><div><span class="product-category">{{ $product['category'] }}</span><h3>{{ $product['name'] }}</h3></div><strong>{{ $product['price'] }}</strong></div>
                        <div class="product-detail"><p>{{ $product['description'] }}</p><div class="swatches">@foreach($product['colors'] as $color)<i style="--swatch: {{ $color }}"></i>@endforeach</div></div>
                    </article>
                @empty
                    <div class="no-results"><span>Nothing found</span><p>Try another search or browse all objects.</p><button wire:click="$set('query', ''); $set('category', 'All')">Reset filters</button></div>
                @endforelse
            </div>
        </section>

        <section class="editorial" id="journal">
            <div class="editorial-image"><img src="https://images.unsplash.com/photo-1494438639946-1ebd1d20bf85?auto=format&fit=crop&w=1600&q=90" alt="Calm home workspace"></div>
            <div class="editorial-copy"><span class="section-number">Journal / Issue 08</span><h2>The art of<br>living with less</h2><p>Designer Mae Engelgeer on texture, restraint, and why the objects we keep should earn their place.</p><a href="#shop">Read the story <span>→</span></a><div class="quote">“Good design leaves room for life to happen.”</div></div>
        </section>

        <section class="service-grid">
            <div><span>01</span><h3>Made to last</h3><p>Materials chosen for character, repairability, and a long useful life.</p></div>
            <div><span>02</span><h3>Considered delivery</h3><p>Plastic-free packaging and carbon-conscious shipping as standard.</p></div>
            <div><span>03</span><h3>Here to help</h3><p>Real people, thoughtful advice, and 30 days to live with your order.</p></div>
        </section>

        <section class="newsletter">
            <div><span class="section-number">Private notes, occasionally</span><h2>Stay in the know.</h2><p>New objects, studio visits, and useful ideas. Never noise.</p></div>
            @if($subscribed)
                <div class="subscribed"><span>✓</span><p>You're on the list.<br><small>We'll write when there's something worth saying.</small></p></div>
            @else
                <form wire:submit="subscribe"><div><input type="email" wire:model="email" placeholder="Email address" aria-label="Email address"><button>Join us <span>→</span></button></div>@error('email')<small>{{ $message }}</small>@enderror</form>
            @endif
        </section>
    </main>

    <footer>
        <div class="footer-main"><a class="footer-mark" href="#top">NOMA<span>®</span></a><div class="footer-links"><div><b>Shop</b><a href="#shop">New arrivals</a><a href="#shop">All objects</a><a href="#shop">Gift cards</a></div><div><b>About</b><a href="#journal">Our story</a><a href="#journal">Journal</a><a href="#journal">Materials</a></div><div><b>Help</b><a href="#top">Delivery</a><a href="#top">Returns</a><a href="#top">Contact</a></div></div></div>
        <div class="footer-bottom"><span>© 2026 NOMA Supply Co.</span><span>Built with care in small batches.</span><div><a href="#top">Privacy</a><a href="#top">Terms</a><a href="#top">Instagram</a></div></div>
    </footer>

    @if($cartOpen)<button class="cart-backdrop" wire:click="$set('cartOpen', false)" aria-label="Close bag"></button>@endif
    <aside @class(['cart-drawer', 'open' => $cartOpen]) aria-label="Shopping bag" aria-hidden="{{ $cartOpen ? 'false' : 'true' }}">
        <div class="cart-head"><div><span>Your bag</span><b>{{ $this->cart['count'] }} {{ Str::plural('item', $this->cart['count']) }}</b></div><button wire:click="$set('cartOpen', false)" aria-label="Close bag">×</button></div>
        @if($this->cart['count'] > 0)
            <div class="shipping-progress"><p>@if($this->cart['free_shipping_remaining'] > 0)Spend <b>${{ number_format($this->cart['free_shipping_remaining'] / 100, 2) }}</b> more for complimentary shipping.@else Complimentary shipping unlocked. @endif</p><i><span style="width: {{ min(100, $this->cart['subtotal_cents'] / 200) }}%"></span></i></div>
            <div class="cart-lines">
                @foreach($this->cart['lines'] as $line)
                    <article wire:key="cart-{{ $line['id'] }}"><img src="{{ $line['image'] }}" alt=""><div class="cart-line-info"><div><h3>{{ $line['name'] }}</h3><button wire:click="removeFromCart('{{ $line['id'] }}')">Remove</button></div><span>{{ $line['price'] }}</span><div class="quantity"><button wire:click="updateQuantity('{{ $line['id'] }}', {{ $line['quantity'] - 1 }})">−</button><span>{{ $line['quantity'] }}</span><button wire:click="updateQuantity('{{ $line['id'] }}', {{ $line['quantity'] + 1 }})">+</button></div></div></article>
                @endforeach
            </div>
            <div class="cart-summary"><div><span>Subtotal</span><b>{{ $this->cart['subtotal'] }}</b></div>@if($this->cart['discount_cents'] > 0)<div class="discount"><span>{{ $this->cart['promotion'] }}</span><b>−{{ $this->cart['discount'] }}</b></div>@endif<div><span>Shipping</span><b>{{ $this->cart['shipping'] }}</b></div><div class="cart-total"><span>Total</span><b>{{ $this->cart['total'] }}</b></div><button class="checkout">Secure checkout <span>→</span></button><small>Taxes calculated at checkout · 30-day returns</small></div>
        @else
            <div class="empty-cart"><div>○</div><h2>Your bag is quiet.</h2><p>Start with something made to stay.</p><button wire:click="$set('cartOpen', false)">Explore the collection</button></div>
        @endif
    </aside>
    <div class="toast" x-data="{ show: false }" @cart-updated.window="show = true; setTimeout(() => show = false, 1800)" x-show="show" x-transition>Added to your bag <span>✓</span></div>
</div>
