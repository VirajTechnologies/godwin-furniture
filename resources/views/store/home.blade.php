@extends('layouts.store')

@section('title', 'GODWIN — Metal Furniture & Interiors')
@section('body_class', 'gw-home-theme')

@push('scripts')
    <script>
        function scrollCarouselTrack(trackId, direction) {
            var track = document.getElementById(trackId);
            if (!track) return;
            var card = track.querySelector('.gw-card, .gw-cat-card');
            var step = card ? (card.offsetWidth + 16) * 2 : 560;
            track.scrollBy({ left: direction === 'prev' ? -step : step, behavior: 'smooth' });
        }
        function scrollBestSellers(direction) {
            scrollCarouselTrack('bestSellersTrack', direction);
        }
        document.addEventListener('DOMContentLoaded', function () {
            var messages = [
                'Discover the new era of metal furniture.',
                'Premium designs. Precision manufacturing.',
                'Explore our latest collections.'
            ];
            var idx = 0;
            var el = document.getElementById('gwAnnText');
            if (el) {
                setInterval(function () {
                    idx = (idx + 1) % messages.length;
                    el.textContent = messages[idx];
                }, 4000);
            }
        });
    </script>
@endpush

@section('content')
    @include('store.partials.header')

    @php
        $roomModels = collect($storeMenu ?? [])->keyBy('name');
        $allLeafSlugs = collect($storeMenu ?? [])
            ->flatMap(fn ($room) => $room->children)
            ->pluck('slug', 'name');

        $spacesShowcase = [
            [
                'name' => 'Living Room',
                'description' => 'Sofas, lounge chairs and tables built on slim, strong metal frames.',
                'slug' => $roomModels->get('Living Room')?->slug,
                'query' => 'Sofa',
                'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&q=80&w=1400',
            ],
            [
                'name' => 'Bedroom',
                'description' => 'Metal bed frames and storage with a quiet, warm finish.',
                'slug' => $roomModels->get('Bedroom')?->slug,
                'query' => 'Bed',
                'image' => 'https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=1400',
            ],
            [
                'name' => 'Dining Room',
                'description' => 'Tables and chairs made for long dinners and daily use.',
                'slug' => $roomModels->get('Dining Room')?->slug,
                'query' => 'Dining',
                'image' => 'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=1400',
            ],
            [
                'name' => 'Office',
                'description' => 'Desks, chairs and workstations for focused work.',
                'slug' => $roomModels->get('Office')?->slug,
                'query' => 'Office',
                'image' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=1400',
            ],
            [
                'name' => 'Outdoor Living',
                'description' => 'Powder-coated frames that handle sun, rain and company.',
                'slug' => $roomModels->get('Outdoor')?->slug,
                'query' => 'Outdoor',
                'image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&q=80&w=1400',
            ],
        ];

        $shopByCategories = [
            [
                'label' => 'Sofas',
                'slug' => $allLeafSlugs['Sofa'] ?? ($roomModels->get('Living Room')?->slug),
                'query' => 'Sofa',
                'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=700',
            ],
            [
                'label' => 'Chairs',
                'slug' => $allLeafSlugs['Accent Chair'] ?? $allLeafSlugs['Lounge Chair'] ?? null,
                'query' => 'Chair',
                'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&q=80&w=700',
            ],
            [
                'label' => 'Tables',
                'slug' => $allLeafSlugs['Coffee Table'] ?? $allLeafSlugs['Centre Table'] ?? null,
                'query' => 'Table',
                'image' => 'https://images.unsplash.com/photo-1532372576444-dda954194ad0?auto=format&fit=crop&q=80&w=700',
            ],
            [
                'label' => 'Beds',
                'slug' => $allLeafSlugs['King Size Bed'] ?? $allLeafSlugs['Queen Size Bed'] ?? ($roomModels->get('Bedroom')?->slug),
                'query' => 'Bed',
                'image' => 'https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=700',
            ],
            [
                'label' => 'Dining',
                'slug' => $roomModels->get('Dining Room')?->slug ?? $allLeafSlugs['Dining Table'] ?? null,
                'query' => 'Dining',
                'image' => 'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=700',
            ],
            [
                'label' => 'Office',
                'slug' => $roomModels->get('Office')?->slug ?? $allLeafSlugs['Office Desk'] ?? null,
                'query' => 'Office',
                'image' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=700',
            ],
            [
                'label' => 'Outdoor',
                'slug' => $roomModels->get('Outdoor')?->slug ?? $allLeafSlugs['Outdoor Sofa'] ?? null,
                'query' => 'Outdoor',
                'image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&q=80&w=700',
            ],
        ];

        $ratingsCycle = ['4.6', '4.4', '4.7', '4.5', '4.3', '4.2', '4.8', '4.6'];

        $mapCarouselCards = function ($collection, bool $forceNew = false) use ($ratingsCycle): array {
            $cards = [];
            foreach (collect($collection)->values() as $idx => $prod) {
                $firstVariant = $prod->activeVariants->sortBy('price')->first() ?? $prod->activeVariants->first();
                $price = $firstVariant ? (float) $firstVariant->storePrice() : (float) $prod->fromPrice();
                $mrp = $firstVariant && $firstVariant->mrp() ? (float) $firstVariant->mrp() : null;
                $discount = ($mrp && $mrp > $price) ? (int) round((($mrp - $price) / $mrp) * 100) : null;
                $colourName = $firstVariant?->colour ?: 'Grey';
                $colourHex = $firstVariant?->colour_hex ?: \App\Support\Catalog::colourHex($colourName);
                $image = $prod->primaryImageUrl() ?: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=700';

                $cards[] = [
                    'name' => $prod->name,
                    'price' => $price,
                    'mrp' => $mrp,
                    'discount' => $discount,
                    'rating' => $ratingsCycle[$idx % count($ratingsCycle)],
                    'colour' => $colourName,
                    'colour_hex' => $colourHex,
                    'subtitle' => $prod->material ?: ($prod->product_category ?: 'GODWIN'),
                    'is_new' => $forceNew || ($idx % 2 === 0),
                    'image' => $image,
                    'url' => route('store.product', $prod->slug),
                    'variant_id' => $firstVariant?->id,
                ];
            }
            return $cards;
        };

        $bestSellerPool = collect($featuredProducts ?? [])->concat($newProducts ?? [])->unique('id')->take(8);
        $bestSellerCards = $mapCarouselCards($bestSellerPool, false);
        $newArrivalCards = $mapCarouselCards(collect($newProducts ?? [])->take(8), true);
        $bestDealCards = collect($bestSellerCards)->filter(fn ($c) => ! empty($c['discount']))->values()->all();

        if ($bestSellerCards === []) {
            $bestSellerCards = [
                ['name' => 'Arden 3-Seater Sofa', 'price' => 48900, 'mrp' => 58900, 'discount' => 17, 'rating' => '4.6', 'colour' => 'Grey', 'colour_hex' => '#8d8d8d', 'subtitle' => 'Metal + Upholstery', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Sofa']), 'variant_id' => null],
                ['name' => 'Linea L-Shaped Sofa', 'price' => 72900, 'mrp' => null, 'discount' => null, 'rating' => '4.4', 'colour' => 'Beige', 'colour_hex' => '#d8c7a8', 'subtitle' => 'Metal + Upholstery', 'is_new' => false, 'image' => 'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Sofa']), 'variant_id' => null],
                ['name' => 'Kern Lounge Chair', 'price' => 18900, 'mrp' => 21900, 'discount' => 14, 'rating' => '4.5', 'colour' => 'Brown', 'colour_hex' => '#6b4a33', 'subtitle' => 'Metal + Upholstery', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Chair']), 'variant_id' => null],
                ['name' => 'Rolo Dining Chair', 'price' => 5900, 'mrp' => 7500, 'discount' => 21, 'rating' => '4.2', 'colour' => 'Black', 'colour_hex' => '#222224', 'subtitle' => 'Mild Steel', 'is_new' => false, 'image' => 'https://images.unsplash.com/photo-1592078615290-033ee584e267?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Chair']), 'variant_id' => null],
                ['name' => 'Orla Coffee Table', 'price' => 12900, 'mrp' => 15900, 'discount' => 19, 'rating' => '4.5', 'colour' => 'Gold', 'colour_hex' => '#b8975a', 'subtitle' => 'Metal + Glass', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1532372576444-dda954194ad0?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Table']), 'variant_id' => null],
                ['name' => 'Hale Metal Bed (Queen)', 'price' => 28900, 'mrp' => 34900, 'discount' => 17, 'rating' => '4.7', 'colour' => 'Black', 'colour_hex' => '#222224', 'subtitle' => 'Mild Steel', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['q' => 'Bed']), 'variant_id' => null],
            ];
        }

        if ($newArrivalCards === []) {
            $newArrivalCards = [
                ['name' => 'Vale 4-Seater Dining Set', 'price' => 39900, 'mrp' => null, 'discount' => null, 'rating' => '4.4', 'colour' => 'Grey', 'colour_hex' => '#8d8d8d', 'subtitle' => 'Metal + Glass', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
                ['name' => 'Pia Accent Chair', 'price' => 14500, 'mrp' => null, 'discount' => null, 'rating' => '4.3', 'colour' => 'Beige', 'colour_hex' => '#d8c7a8', 'subtitle' => 'Mild Steel', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
                ['name' => 'Sol Outdoor Chair', 'price' => 8900, 'mrp' => 10900, 'discount' => 18, 'rating' => '4.4', 'colour' => 'Green', 'colour_hex' => '#4d6b57', 'subtitle' => 'Aluminium', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1580480055273-228ff5388ef8?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
                ['name' => 'Arden 3-Seater Sofa', 'price' => 48900, 'mrp' => 58900, 'discount' => 17, 'rating' => '4.6', 'colour' => 'Grey', 'colour_hex' => '#8d8d8d', 'subtitle' => 'Metal + Upholstery', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
                ['name' => 'Hale Metal Bed (Queen)', 'price' => 28900, 'mrp' => 34900, 'discount' => 17, 'rating' => '4.7', 'colour' => 'Black', 'colour_hex' => '#222224', 'subtitle' => 'Mild Steel', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
                ['name' => 'Axis Office Chair', 'price' => 11900, 'mrp' => 14900, 'discount' => 20, 'rating' => '4.5', 'colour' => 'Black', 'colour_hex' => '#222224', 'subtitle' => 'Metal + Upholstery', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1506439773649-6e0eb8cfb237?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'newest']), 'variant_id' => null],
            ];
        }

        if ($bestDealCards === []) {
            $bestDealCards = [
                ['name' => 'Nest Side Table', 'price' => 4900, 'mrp' => 6500, 'discount' => 25, 'rating' => '4.1', 'colour' => 'Black', 'colour_hex' => '#222224', 'subtitle' => 'Mild Steel', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1532372576444-dda954194ad0?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'price_asc']), 'variant_id' => null],
                ['name' => 'Rolo Dining Chair', 'price' => 5900, 'mrp' => 7500, 'discount' => 21, 'rating' => '4.2', 'colour' => 'Black', 'colour_hex' => '#222224', 'subtitle' => 'Mild Steel', 'is_new' => false, 'image' => 'https://images.unsplash.com/photo-1592078615290-033ee584e267?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'price_asc']), 'variant_id' => null],
                ['name' => 'Ferro Single Bed', 'price' => 14900, 'mrp' => 18900, 'discount' => 21, 'rating' => '4.0', 'colour' => 'White', 'colour_hex' => '#f4f4f2', 'subtitle' => 'Powder Coated Iron', 'is_new' => false, 'image' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'price_asc']), 'variant_id' => null],
                ['name' => 'Axis Office Chair', 'price' => 11900, 'mrp' => 14900, 'discount' => 20, 'rating' => '4.5', 'colour' => 'Black', 'colour_hex' => '#222224', 'subtitle' => 'Metal + Upholstery', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1506439773649-6e0eb8cfb237?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'price_asc']), 'variant_id' => null],
                ['name' => 'Orla Coffee Table', 'price' => 12900, 'mrp' => 15900, 'discount' => 19, 'rating' => '4.5', 'colour' => 'Gold', 'colour_hex' => '#b8975a', 'subtitle' => 'Metal + Glass', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1532372576444-dda954194ad0?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'price_asc']), 'variant_id' => null],
                ['name' => 'Voss 2-Seater Sofa', 'price' => 36900, 'mrp' => 44900, 'discount' => 18, 'rating' => '4.7', 'colour' => 'Green', 'colour_hex' => '#4d6b57', 'subtitle' => 'Metal + Upholstery', 'is_new' => true, 'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&q=80&w=700', 'url' => route('store.catalog', ['sort' => 'price_asc']), 'variant_id' => null],
            ];
        }

        $shopTheLooks = [
            ['label' => 'Living Room Look', 'slug' => $roomModels->get('Living Room')?->slug, 'query' => 'Sofa', 'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&q=80&w=800'],
            ['label' => 'Bedroom Look', 'slug' => $roomModels->get('Bedroom')?->slug, 'query' => 'Bed', 'image' => 'https://images.unsplash.com/photo-1505693314120-0d443867891c?auto=format&fit=crop&q=80&w=800'],
            ['label' => 'Dining Look', 'slug' => $roomModels->get('Dining Room')?->slug, 'query' => 'Dining', 'image' => 'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&q=80&w=800'],
            ['label' => 'Office Look', 'slug' => $roomModels->get('Office')?->slug, 'query' => 'Office', 'image' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=800'],
            ['label' => 'Outdoor Look', 'slug' => $roomModels->get('Outdoor')?->slug, 'query' => 'Outdoor', 'image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&q=80&w=800'],
        ];

        $whyGodwin = [
            ['title' => 'Manufacturer direct', 'desc' => 'Crafted in our own facilities with zero middleman markup.'],
            ['title' => 'Precision metal engineering', 'desc' => 'Heavy-gauge CRCA steel and laser-accurate joinery.'],
            ['title' => 'Premium upholstery', 'desc' => 'Curated velvets, bouclé, linen blends and full-grain leathers.'],
            ['title' => 'Durable construction', 'desc' => '7-tank anti-rust treatment and oven-baked powder coating.'],
            ['title' => 'Modern designs', 'desc' => 'Slim architectural silhouettes balanced for contemporary homes.'],
            ['title' => 'Custom manufacturing', 'desc' => 'Tailored dimensions, finishes and fabrics built to order.'],
        ];
    @endphp

    <main>
        <!-- 3. Hero Section -->
        <section class="gw-hero">
            <div class="gw-hero-media">
                <img src="https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&q=80&w=1800" alt="The future of furniture is metal">
                <div class="gw-hero-scrim"></div>
            </div>
            <div class="gw-hero-tx">
                <h1>The future of furniture is metal.</h1>
                <p>Discover thoughtfully designed metal furniture that brings strength, sophistication and timeless style into every space.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="gw-btn" href="{{ route('store.catalog') }}">Explore collections</a>
                    <a class="gw-btn gw-btn-outline" href="#why-godwin">Discover GODWIN</a>
                </div>
            </div>
        </section>

        <!-- 4. Spaces That Define Your Lifestyle (Alternating Split Rows) -->
        <section class="gw-spaces-section">
            <div class="gw-wrap" style="padding-bottom: 1.5rem;">
                <h2>Spaces that define your lifestyle.</h2>
                <p class="m-0">Discover thoughtfully designed furniture for every corner of your world.</p>
            </div>

            @foreach ($spacesShowcase as $space)
                @php
                    $exploreUrl = $space['slug']
                        ? route('store.catalog', ['category' => $space['slug']])
                        : route('store.catalog', ['q' => $space['query']]);
                    $shopUrl = $space['slug']
                        ? route('store.catalog', ['category' => $space['slug'], 'sort' => 'popular'])
                        : route('store.catalog', ['q' => $space['query'], 'sort' => 'popular']);
                @endphp
                <div class="gw-room">
                    <div class="gw-room-media">
                        <img src="{{ $space['image'] }}" alt="{{ $space['name'] }}" loading="lazy">
                    </div>
                    <div class="gw-room-tx">
                        <h2>{{ $space['name'] }}</h2>
                        <p>{{ $space['description'] }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="gw-btn gw-btn-outline" href="{{ $exploreUrl }}">Explore room</a>
                            <a class="gw-btn" href="{{ $shopUrl }}">Shop this space</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        <!-- 5. Shop by Category -->
        <section class="gw-wrap">
            <h2>Shop by category</h2>
            <div class="gw-row" id="shopCatTrack">
                @foreach ($shopByCategories as $cat)
                    @php
                        $catUrl = $cat['slug']
                            ? route('store.catalog', ['category' => $cat['slug']])
                            : route('store.catalog', ['q' => $cat['query']]);
                    @endphp
                    <a class="gw-cat-card" href="{{ $catUrl }}">
                        <div class="gw-cat-media">
                            <img src="{{ $cat['image'] }}" alt="{{ $cat['label'] }}" loading="lazy">
                        </div>
                        <b>{{ $cat['label'] }}</b>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- 6. Best Sellers -->
        <section class="gw-wrap">
            <div class="gw-bar">
                <h2 class="m-0">Best sellers</h2>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('store.catalog', ['sort' => 'popular']) }}" class="gw-view-link">View all</a>
                    <div class="d-none d-sm-flex align-items-center gap-1">
                        <button type="button" class="gw-arrow-btn" onclick="scrollCarouselTrack('bestSellersTrack', 'prev')" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" class="gw-arrow-btn" onclick="scrollCarouselTrack('bestSellersTrack', 'next')" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
            <div class="gw-row" id="bestSellersTrack">
                @foreach ($bestSellerCards as $item)
                    <div class="gw-card">
                        @if ($item['is_new'])
                            <span class="gw-tag">New</span>
                        @endif
                        <button type="button" class="gw-hb" aria-label="Wishlist" onclick="this.textContent = this.textContent.trim() === '♥' ? '♡' : '♥'">♡</button>
                        <a href="{{ $item['url'] }}" class="gw-card-media">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">
                        </a>
                        <div class="gw-card-in">
                            <div class="gw-card-nm">
                                <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                            </div>
                            <div class="gw-card-pr">
                                ₹{{ number_format($item['price'], 0) }}
                                @if ($item['mrp'] && $item['mrp'] > $item['price'])
                                    <s>₹{{ number_format($item['mrp'], 0) }}</s>
                                @endif
                                @if ($item['discount'])
                                    <span class="gw-off">{{ $item['discount'] }}% off</span>
                                @endif
                            </div>
                            <div class="gw-card-meta">
                                ★ {{ $item['rating'] }} · <span class="gw-sw" style="background: {{ $item['colour_hex'] }};"></span>{{ $item['colour'] }} · {{ $item['subtitle'] }}
                            </div>
                            <div class="gw-card-actions">
                                @if ($item['variant_id'])
                                    <form method="POST" action="{{ route('store.cart.add') }}" class="d-inline m-0">
                                        @csrf
                                        <input type="hidden" name="product_variant_id" value="{{ $item['variant_id'] }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="gw-btn gw-btn-sm">Add to cart</button>
                                    </form>
                                @else
                                    <a href="{{ $item['url'] }}" class="gw-btn gw-btn-sm">Add to cart</a>
                                @endif
                                <a class="gw-btn gw-btn-sm gw-btn-outline" href="{{ $item['url'] }}">Quick view</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- 7. New Arrivals -->
        <section class="gw-wrap">
            <div class="gw-bar">
                <h2 class="m-0">New arrivals</h2>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('store.catalog', ['sort' => 'newest']) }}" class="gw-view-link">View all</a>
                    <div class="d-none d-sm-flex align-items-center gap-1">
                        <button type="button" class="gw-arrow-btn" onclick="scrollCarouselTrack('newArrivalsTrack', 'prev')" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" class="gw-arrow-btn" onclick="scrollCarouselTrack('newArrivalsTrack', 'next')" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
            <div class="gw-row" id="newArrivalsTrack">
                @foreach ($newArrivalCards as $item)
                    <div class="gw-card">
                        @if ($item['is_new'])
                            <span class="gw-tag">New</span>
                        @endif
                        <button type="button" class="gw-hb" aria-label="Wishlist" onclick="this.textContent = this.textContent.trim() === '♥' ? '♡' : '♥'">♡</button>
                        <a href="{{ $item['url'] }}" class="gw-card-media">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">
                        </a>
                        <div class="gw-card-in">
                            <div class="gw-card-nm">
                                <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                            </div>
                            <div class="gw-card-pr">
                                ₹{{ number_format($item['price'], 0) }}
                                @if ($item['mrp'] && $item['mrp'] > $item['price'])
                                    <s>₹{{ number_format($item['mrp'], 0) }}</s>
                                @endif
                                @if ($item['discount'])
                                    <span class="gw-off">{{ $item['discount'] }}% off</span>
                                @endif
                            </div>
                            <div class="gw-card-meta">
                                ★ {{ $item['rating'] }} · <span class="gw-sw" style="background: {{ $item['colour_hex'] }};"></span>{{ $item['colour'] }} · {{ $item['subtitle'] }}
                            </div>
                            <div class="gw-card-actions">
                                @if ($item['variant_id'])
                                    <form method="POST" action="{{ route('store.cart.add') }}" class="d-inline m-0">
                                        @csrf
                                        <input type="hidden" name="product_variant_id" value="{{ $item['variant_id'] }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="gw-btn gw-btn-sm">Add to cart</button>
                                    </form>
                                @else
                                    <a href="{{ $item['url'] }}" class="gw-btn gw-btn-sm">Add to cart</a>
                                @endif
                                <a class="gw-btn gw-btn-sm gw-btn-outline" href="{{ $item['url'] }}">Quick view</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- 9. Best Deals -->
        <section class="gw-wrap" id="deals">
            <div class="gw-bar">
                <h2 class="m-0">Best deals</h2>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('store.catalog', ['sort' => 'price_asc']) }}" class="gw-view-link">View all</a>
                    <div class="d-none d-sm-flex align-items-center gap-1">
                        <button type="button" class="gw-arrow-btn" onclick="scrollCarouselTrack('bestDealsTrack', 'prev')" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" class="gw-arrow-btn" onclick="scrollCarouselTrack('bestDealsTrack', 'next')" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
            <div class="gw-row" id="bestDealsTrack">
                @foreach ($bestDealCards as $item)
                    <div class="gw-card">
                        @if ($item['is_new'])
                            <span class="gw-tag">New</span>
                        @endif
                        <button type="button" class="gw-hb" aria-label="Wishlist" onclick="this.textContent = this.textContent.trim() === '♥' ? '♡' : '♥'">♡</button>
                        <a href="{{ $item['url'] }}" class="gw-card-media">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">
                        </a>
                        <div class="gw-card-in">
                            <div class="gw-card-nm">
                                <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                            </div>
                            <div class="gw-card-pr">
                                ₹{{ number_format($item['price'], 0) }}
                                @if ($item['mrp'] && $item['mrp'] > $item['price'])
                                    <s>₹{{ number_format($item['mrp'], 0) }}</s>
                                @endif
                                @if ($item['discount'])
                                    <span class="gw-off">{{ $item['discount'] }}% off</span>
                                @endif
                            </div>
                            <div class="gw-card-meta">
                                ★ {{ $item['rating'] }} · <span class="gw-sw" style="background: {{ $item['colour_hex'] }};"></span>{{ $item['colour'] }} · {{ $item['subtitle'] }}
                            </div>
                            <div class="gw-card-actions">
                                @if ($item['variant_id'])
                                    <form method="POST" action="{{ route('store.cart.add') }}" class="d-inline m-0">
                                        @csrf
                                        <input type="hidden" name="product_variant_id" value="{{ $item['variant_id'] }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="gw-btn gw-btn-sm">Add to cart</button>
                                    </form>
                                @else
                                    <a href="{{ $item['url'] }}" class="gw-btn gw-btn-sm">Add to cart</a>
                                @endif
                                <a class="gw-btn gw-btn-sm gw-btn-outline" href="{{ $item['url'] }}">Quick view</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- 10. Shop the Look -->
        <section class="gw-wrap">
            <h2>Shop the look</h2>
            <div class="gw-row">
                @foreach ($shopTheLooks as $look)
                    @php
                        $lookUrl = $look['slug']
                            ? route('store.catalog', ['category' => $look['slug']])
                            : route('store.catalog', ['q' => $look['query']]);
                    @endphp
                    <a class="gw-cat-card gw-look-card" href="{{ $lookUrl }}">
                        <div class="gw-cat-media gw-look-media">
                            <img src="{{ $look['image'] }}" alt="{{ $look['label'] }}" loading="lazy">
                        </div>
                        <b>{{ $look['label'] }}</b>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- 11. Built for Business. Designed for Excellence. -->
        <section class="gw-full">
            <div class="gw-full-media">
                <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=1600" alt="Built for business" loading="lazy">
            </div>
            <div class="gw-full-tx">
                <h2>Built for business. Designed for excellence.</h2>
                <p>From hospitality and workplaces to large-scale commercial projects, GODWIN delivers furniture solutions engineered for performance and designed for impact.</p>
                <a class="gw-btn" href="https://wa.me/917418759171?text=Hi%20GODWIN,%20I%20would%20like%20to%20enquire%20about%20a%20Business%20%26%20Commercial%20project." target="_blank" rel="noopener">Explore business &amp; commercial</a>
            </div>
        </section>

        <!-- 12. Furniture That Fits Your Space. And Your Stay. (Stone Banner) -->
        <section class="gw-banner gw-banner-stone">
            <div class="gw-banner-tx">
                <h2>Furniture that fits your space. And your stay.</h2>
                <p>Flexible furniture rental solutions for homes, offices, events and commercial spaces.</p>
                <a class="gw-btn" href="{{ route('store.catalog') }}">Explore furniture on rent</a>
            </div>
            <div class="gw-banner-media">
                <img src="https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?auto=format&fit=crop&q=80&w=1200" alt="Furniture on rent" loading="lazy">
            </div>
        </section>

        <!-- 13. Engineered With Precision. Designed to Last. -->
        <section class="gw-full">
            <div class="gw-full-media">
                <img src="https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&q=80&w=1600" alt="Engineered with precision" loading="lazy">
            </div>
            <div class="gw-full-tx">
                <h2>Engineered with precision. Designed to last.</h2>
                <p>Fabrication, welding, powder coating, finishing, upholstery and assembly, all under one roof.</p>
                <a class="gw-btn" href="{{ route('store.catalog') }}">Explore our manufacturing</a>
            </div>
        </section>

        <!-- 14. Why GODWIN? -->
        <section class="gw-wrap" id="why-godwin">
            <h2>Why GODWIN?</h2>
            <div class="gw-cols">
                @foreach ($whyGodwin as $pillar)
                    <div class="gw-pillar-item">
                        <h3>{{ $pillar['title'] }}</h3>
                        <p class="m-0">{{ $pillar['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- 15. Give the Gift of Great Design (Gift Card Banner) -->
        <section class="gw-banner gw-banner-stone">
            <div class="gw-banner-media" style="min-height: 300px;">
                <img src="https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&q=80&w=1200" alt="GODWIN Gift Cards" loading="lazy">
            </div>
            <div class="gw-banner-tx">
                <h2>Give the gift of great design.</h2>
                <p>Make every space special with a GODWIN Gift Card.</p>
                <a class="gw-btn" href="{{ route('store.catalog') }}">Explore gift cards</a>
            </div>
        </section>

        <!-- 16. Customer Reviews -->
        <section class="gw-wrap">
            <h2>Customer reviews</h2>
            <p class="gw-reviews-note">Verified buyer reviews from homes, studios and commercial spaces furnished by GODWIN across India.</p>
        </section>
    </main>

    @include('store.partials.footer')

    <!-- Floating WhatsApp Action Button -->
    <div class="designer-floating-widget">
        <a href="https://wa.me/917418759171" target="_blank" class="rounded-circle text-white d-flex align-items-center justify-content-center shadow-lg text-decoration-none" style="width: 52px; height: 52px; background-color: #25D366; font-size: 26px;" title="Chat with GODWIN on WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <!-- Mobile Bottom App Bar -->
    <nav class="mobile-bottom-nav d-lg-none">
        <a href="{{ route('store.home') }}" class="mobile-nav-item active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('store.catalog') }}" class="mobile-nav-item">
            <i class="fas fa-compass"></i>
            <span>Explore</span>
        </a>
        <a href="{{ route('store.cart') }}" class="mobile-nav-item">
            <i class="fas fa-shopping-bag"></i>
            <span>Cart</span>
            @if (($cartCount ?? 0) > 0)
                <span class="mobile-nav-badge">{{ $cartCount }}</span>
            @endif
        </a>
        <a href="#" class="mobile-nav-item" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
            <i class="far fa-user"></i>
            <span>Menu</span>
        </a>
    </nav>

    <!-- Mobile Offcanvas Menu Drawer -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav" style="background: var(--bg);">
        <div class="offcanvas-header p-4" style="background: var(--ink); color: var(--bg);">
            <div class="d-flex align-items-center gap-3">
                <div>
                    <h5 class="m-0 text-white" style="font-family: 'Cormorant Garamond', serif; letter-spacing: .16em;">GODWIN</h5>
                    <span class="small" style="color: var(--stone);">Metal Furniture &amp; Interiors</span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-4">
            <h6 class="text-uppercase small mb-3" style="color: var(--mut); letter-spacing: .08em;">Browse Categories</h6>
            <ul class="nav flex-column gap-2">
                @forelse ($storeMenu ?? [] as $room)
                    <li class="nav-item">
                        <a class="nav-link px-0 py-1 fw-medium" style="color: var(--ink);" href="{{ route('store.catalog', ['category' => $room->slug]) }}">
                            {{ $room->name }}
                        </a>
                        @if ($room->children->isNotEmpty())
                            <ul class="list-unstyled ps-3 mb-2">
                                @foreach ($room->children->take(6) as $child)
                                    <li>
                                        <a class="nav-link px-0 py-1 small" style="color: var(--mut);" href="{{ route('store.catalog', ['category' => $child->slug]) }}">{{ $child->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @empty
                    <li class="nav-item"><a class="nav-link px-0" style="color: var(--ink);" href="{{ route('store.catalog') }}">Furniture Catalog</a></li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
