<?php require_once __DIR__.'/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#070a12">
    <meta name="description"
        content="Swarnim Groups in Godda, Jharkhand — batteries, inverters, solar panels, ACs, coolers, chimneys and power solutions with installation and doorstep service.">
    <meta name="keywords"
        content="Swarnim Groups, electronics shop Godda, solar panel Godda, inverter Godda, battery shop Godda, solar installation Jharkhand, cooler, AC, chimney, power solutions">
    <title>Swarnim Groups — Power Your Life</title>
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="icon" href="data/Swarnim Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pwa.css">
</head>

<body>
    <!-- Swarnim page loader -->
    <div class="sw-page-loader" id="swPageLoader" aria-label="Loading Swarnim Groups" role="status">
        <div class="sw-loader-orbit" aria-hidden="true">
            <span class="sw-dot d1"></span><span class="sw-dot d2"></span><span class="sw-dot d3"></span>
            <span class="sw-dot d4"></span><span class="sw-dot d5"></span><span class="sw-dot d6"></span>
            <span class="sw-dot d7"></span><span class="sw-dot d8"></span>
            <span class="sw-loader-logo"><img src="data/Swarnim Logo.png" alt=""></span>
        </div>
        <div class="sw-loader-name">SWARNIM GROUPS</div>
        <div class="sw-loader-line"><span></span></div>
    </div>

    <div class="grid-bg"></div>
    <div class="orb one"></div>
    <div class="orb two"></div>

    <header id="header">
        <div class="container">
            <nav class="nav">
                <a class="logo" href="#home">
                    <img src="data/Swarnim Logo.png" alt="Swarnim Groups">
                    <div><span>Swarnim Groups</span><small>Power • Comfort • Trust</small></div>
                </a>
                <div class="navlinks">
                    <a href="#home">Home</a><a href="#about">About</a><a href="#categories">Categories</a>
                    <a href="product.php">Products</a><a href="#services">Services</a><a href="#reviews">Reviews</a><a
                        href="#contact">Contact</a>
                </div>
                <div class="nav-actions">
                    <button class="icon-btn" id="themeBtn" aria-label="Change theme">☼</button>
                    <div id="authNav"><a class="cta" href="login.php">Login</a></div>
                    <button class="menu-btn" id="menuBtn">☰</button>
                </div>
            </nav>
        </div>
    </header>

    <div class="mobile-menu" id="mobileMenu">
        <a href="#home">Home</a><a href="#about">About</a><a href="#categories">Categories</a><a
            href="product.php">Products</a>
        <a href="#services">Services</a><a href="#reviews">Reviews</a><a href="#contact">Contact</a><span id="mobileAuth"><a
            href="login.php">Login</a></span>
    </div>

    <main>
        <section class="hero" id="home">
            <div class="container hero-inner">
                <div class="reveal">
                    <div class="badge"><i class="pulse"></i> Trusted Electronics Store</div>
                    <h1>Power Your <span class="gradient-text">Life</span><br>with Swarnim <span class="type"
                            id="type"></span></h1>
                    <p class="hero-copy">From heavy-duty batteries to solar energy systems — we bring you genuine,
                        high-performance electronics with expert installation, honest pricing and service you can rely
                        on, right at your doorstep.</p>
                    <div class="hero-actions">
                        <a class="cta" href="#categories">Explore Products ↗</a>
                        <a class="cta ghost" href="tel:+919955056900">Call Us Now</a>
                    </div>
                    <div class="hero-meta">
                        <div class="meta"><strong data-count="15">0+</strong><span>Years Experience</span></div>
                        <div class="meta"><strong data-count="12">0k+</strong><span>Happy Customers</span></div>
                        <div class="meta"><strong data-count="50">0+</strong><span>Top Brands</span></div>
                    </div>
                </div>
                <div class="hero-visual reveal">
                    <div class="energy-core">
                        <div class="core-logo"><img src="data/Swarnim Logo.png" alt=""></div>
                    </div>
                    <div class="float-card fc1"><b>BatteriesBatteries</b><span>100% Genuine</span></div>
                    <div class="float-card fc2"><b>☀️ Solar Plates</b><span>Save up to 40%</span></div>
                    <div class="float-card fc3"><b>⚡ Inverters</b><span>24/7 Backup</span></div>
                    <div class="float-card fc4"><b>❄️ Air Conditioner</b><span>Free Installation</span></div>
                </div>
            </div>
        </section>

        <div class="ticker">
            <div class="ticker-track">
                <span>BATTERIES <i>✦</i></span><span>AIR COOLERS <i>✦</i></span><span>AIR CONDITIONERS
                    <i>✦</i></span><span>KITCHEN CHIMNEYS <i>✦</i></span><span>SOLAR PLATES
                    <i>✦</i></span><span>INVERTERS <i>✦</i></span><span>ROOM HEATER <i>✦</i></span><span>REFRIGERATOR
                    <i>✦</i></span><span>WASHING MACHINE <i>✦</i></span>
                <span>BATTERIES <i>✦</i></span><span>AIR COOLERS <i>✦</i></span><span>AIR CONDITIONERS
                    <i>✦</i></span><span>KITCHEN CHIMNEYS <i>✦</i></span><span>SOLAR PLATES
                    <i>✦</i></span><span>INVERTERS <i>✦</i></span>
            </div>
        </div>

        <section class="section">
            <div class="container reveal">
                <div class="banner" id="banner">
                    <img id="bannerImg" src="data/banner1.png" alt="Swarnim Groups Offer">
                    <div class="banner-content">
                        <div class="eyebrow">Featured Offers</div>
                        <h3 id="bannerTitle">Power up with genuine electronics.</h3>
                        <p id="bannerText">Explore batteries, solar systems, cooling and smart home solutions from
                            trusted brands.</p>
                        <a class="cta" href="product.php">Shop Products ↗</a>
                    </div>
                    <div class="banner-dots" id="bannerDots"></div>
                </div>
            </div>
        </section>

        <section class="section" id="about">
            <div class="container about-grid">
                <div class="about-media reveal"><img src="data/about-img.png" alt="Swarnim Groups">
                    <div class="about-stamp">15+ Years<br>Trusted Service</div>
                </div>
                <div class="about-copy reveal">
                    <div class="eyebrow">About Swarnim Groups</div>
                    <h2>The Name You Trust for Power & Comfort</h2>
                    <p>Founded with a single vision — to deliver honest electronics at fair prices — Swarnim Groups has
                        grown into the region's most trusted destination for power solutions and home comfort
                        appliances. Every product we sell is sourced directly from authorized distributors, tested, and
                        backed by full warranty.</p>
                    <p>From a simple battery replacement to a complete rooftop solar setup, our certified team handles
                        it all — with free site visits, expert installation and after-sales support that never sleeps.
                    </p>
                    <div class="checks">
                        <div class="check"><b>✓</b> Authorized brand dealer</div>
                        <div class="check"><b>✓</b> Certified technicians</div>
                        <div class="check"><b>✓</b> Free doorstep delivery</div>
                        <div class="check"><b>✓</b> Hassle-free warranty</div>
                    </div>
                    <a class="cta" href="#contact">Talk to Our Team</a>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">Our Categories</div>
                    <h2>Everything Your Home Needs.</h2>
                    <p>Power, cooling, comfort and smart solutions under one roof.</p>
                </div>
                <div class="category-grid">
                    <a class="cat reveal" href="product.php?category=Battery"><img src="data/Battery.png" alt="Battery">
                        <div class="cat-info">
                            <div>
                                <h3>Batteries</h3>
                                <p>Reliable backup power</p>
                            </div><span class="arrow">↗</span>
                        </div>
                    </a>
                    <a class="cat reveal" href="product.php?category=Cooler"><img src="data/Air Cooler.png"
                            alt="Cooler">
                        <div class="cat-info">
                            <div>
                                <h3>Air Coolers</h3>
                                <p>Fast, efficient cooling</p>
                            </div><span class="arrow">↗</span>
                        </div>
                    </a>
                    <a class="cat reveal" href="product.php?category=AC"><img src="data/AC.png" alt="AC">
                        <div class="cat-info">
                            <div>
                                <h3>Air Conditioners</h3>
                                <p>Smart inverter cooling</p>
                            </div><span class="arrow">↗</span>
                        </div>
                    </a>
                    <a class="cat reveal" href="product.php?category=Chimney"><img src="data/Chimeny.png" alt="Chimney">
                        <div class="cat-info">
                            <div>
                                <h3>Kitchen Chimneys</h3>
                                <p>Cleaner, healthier kitchens</p>
                            </div><span class="arrow">↗</span>
                        </div>
                    </a>
                    <a class="cat reveal" href="product.php?category=Solar Panel"><img src="data/Solar Plates.png"
                            alt="Solar">
                        <div class="cat-info">
                            <div>
                                <h3>Solar Panels</h3>
                                <p>Turn sunlight into power</p>
                            </div><span class="arrow">↗</span>
                        </div>
                    </a>
                    <a class="cat reveal" href="product.php?category=Home Inverter"><img src="data/Inverter-c.png"
                            alt="Inverter">
                        <div class="cat-info">
                            <div>
                                <h3>Inverters</h3>
                                <p>24/7 backup solutions</p>
                            </div><span class="arrow">↗</span>
                        </div>
                    </a>
                    <a class="cat reveal" href="product.php?category=Roomheater"><img src="data/Roomheater.png"
                            alt="Room Heater">
                        <div class="cat-info">
                            <div>
                                <h3>Room Heaters</h3>
                                <p>Warmth for winter</p>
                            </div><span class="arrow">↗</span>
                        </div>
                    </a>
                    <a class="cat reveal" href="product.php?category=Refrigerator"><img src="data/Refrigerator.png"
                            alt="Refrigerator">
                        <div class="cat-info">
                            <div>
                                <h3>Refrigerators</h3>
                                <p>Freshness, every day</p>
                            </div><span class="arrow">↗</span>
                        </div>
                    </a>
                    <a class="cat reveal" href="product.php?category=Washing-Machine"><img
                            src="data/Washing Machine.png" alt="Washing Machine">
                        <div class="cat-info">
                            <div>
                                <h3>Washing Machines</h3>
                                <p>Smart laundry solutions</p>
                            </div><span class="arrow">↗</span>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <section class="section" id="products">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">Featured Products</div>
                    <h2>Built for Real Life.</h2>
                    <p>Explore our popular electronics, power solutions and smart appliances.</p>
                </div>
                <div class="product-grid">
                    <article class="product reveal">
                        <div class="product-img"><img
                                src="https://www.luminousindia.com/cdn/shop/files/invamaster_1.jpg"
                                alt="Exide InvaMaster Battery" onerror="this.style.opacity=.15"></div>
                        <div class="product-body"><span class="tag">Battery</span>
                            <h3>Exide InvaMaster Battery</h3>
                            <p>Powerful and reliable battery with long backup and excellent performance.</p>
                            <div class="price"><strong>₹10,999</strong><span class="old">₹12,999</span></div>
                        </div>
                    </article>
                    <article class="product reveal">
                        <div class="product-img"><img
                                src="https://rukminim2.flixcart.com/image/416/416/xif0q/battery/l/5/5/-original-imagp9z7g8j2wz5h.jpeg"
                                alt="Luminous Tall Tubular Battery" onerror="this.style.opacity=.15"></div>
                        <div class="product-body"><span class="tag">Battery</span>
                            <h3>Luminous Tall Tubular Battery</h3>
                            <p>High backup capacity designed for homes and commercial applications.</p>
                            <div class="price"><strong>₹12,499</strong><span class="old">₹14,500</span></div>
                        </div>
                    </article>
                    <article class="product reveal">
                        <div class="product-img"><img src="https://m.media-amazon.com/images/I/71Q3d7zQxVL._SL1500_.jpg"
                                alt="Symphony Desert Cooler" onerror="this.style.opacity=.15"></div>
                        <div class="product-body"><span class="tag">Cooler</span>
                            <h3>Symphony Desert Cooler</h3>
                            <p>Powerful air cooling with large water tank and efficient airflow.</p>
                            <div class="price"><strong>₹13,999</strong><span class="old">₹15,999</span></div>
                        </div>
                    </article>
                    <article class="product reveal">
                        <div class="product-img"><img
                                src="https://rukminim2.flixcart.com/image/416/416/xif0q/air-cooler/f/v/e/-original-imagz5s5kqf3z7hz.jpeg"
                                alt="Voltas Mega Air Cooler" onerror="this.style.opacity=.15"></div>
                        <div class="product-body"><span class="tag">Cooler</span>
                            <h3>Voltas Mega Air Cooler</h3>
                            <p>Fast cooling with powerful air throw for large rooms.</p>
                            <div class="price"><strong>₹15,999</strong><span class="old">₹18,500</span></div>
                        </div>
                    </article>
                    <article class="product reveal">
                        <div class="product-img"><img
                                src="https://aaravelectronics.com/wp-content/uploads/2024/01/1.5-Ton-Inverter-AC.jpg"
                                alt="1.5 Ton Inverter Split AC" onerror="this.style.opacity=.15"></div>
                        <div class="product-body"><span class="tag">AC</span>
                            <h3>1.5 Ton Inverter Split AC</h3>
                            <p>Energy-efficient cooling with smart inverter technology.</p>
                            <div class="price"><strong>₹36,999</strong><span class="old">₹42,999</span></div>
                        </div>
                    </article>
                    <article class="product reveal">
                        <div class="product-img"><img
                                src="https://aaravelectronics.com/wp-content/uploads/2024/01/1.5-Ton-Smart-Inverter-AC.jpg"
                                alt="1.5 Ton Smart Inverter AC" onerror="this.style.opacity=.15"></div>
                        <div class="product-body"><span class="tag">AC</span>
                            <h3>1.5 Ton Smart Inverter AC</h3>
                            <p>Smart cooling system with low power consumption and fast cooling.</p>
                            <div class="price"><strong>₹39,999</strong><span class="old">₹45,999</span></div>
                        </div>
                    </article>
                    <article class="product reveal">
                        <div class="product-img"><img
                                src="https://rukminim2.flixcart.com/image/416/416/xif0q/solar-panel/m/n/1/550w-mono-perc-original-imagq8h8m2z9j2af.jpeg"
                                alt="550W Mono Solar Panel" onerror="this.style.opacity=.15"></div>
                        <div class="product-body"><span class="tag">Solar</span>
                            <h3>550W Mono Solar Panel</h3>
                            <p>High-efficiency solar panel designed for residential and commercial use.</p>
                            <div class="price"><strong>₹15,999</strong><span class="old">₹18,999</span></div>
                        </div>
                    </article>
                    <article class="product reveal">
                        <div class="product-img"><img
                                src="https://rayzonsolar.com/wp-content/uploads/2023/10/Adani-545W.jpg"
                                alt="Adani 545W Solar Panel" onerror="this.style.opacity=.15"></div>
                        <div class="product-body"><span class="tag">Solar</span>
                            <h3>Adani 545W Solar Panel</h3>
                            <p>Premium solar module with excellent efficiency and durability.</p>
                            <div class="price"><strong>₹16,499</strong><span class="old">₹18,499</span></div>
                        </div>
                    </article>
                    <article class="product reveal">
                        <div class="product-img"><img src="https://www.vguard.in/uploads/product/auto-clean-chimney.jpg"
                                alt="Auto Clean Kitchen Chimney" onerror="this.style.opacity=.15"></div>
                        <div class="product-body"><span class="tag">Chimney</span>
                            <h3>Auto Clean Kitchen Chimney</h3>
                            <p>Powerful suction with modern design for a cleaner and healthier kitchen.</p>
                            <div class="price"><strong>₹16,999</strong><span class="old">₹19,999</span></div>
                        </div>
                    </article>
                    <article class="product reveal">
                        <div class="product-img"><img src="https://www.vguard.in/uploads/product/90cm-chimney.jpg"
                                alt="90cm Designer Auto Clean Chimney" onerror="this.style.opacity=.15"></div>
                        <div class="product-body"><span class="tag">Chimney</span>
                            <h3>90cm Designer Auto Clean Chimney</h3>
                            <p>Elegant kitchen chimney with powerful suction and easy maintenance.</p>
                            <div class="price"><strong>₹20,999</strong><span class="old">₹24,999</span></div>
                        </div>
                    </article>
                </div>
                <div style="text-align:center;margin-top:35px"><a class="cta ghost" href="product.php">View All Products
                        →</a></div>
            </div>
        </section>

        <section class="section" id="services">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">Our Promise</div>
                    <h2>Service That Goes Beyond the Sale</h2>
                    <p>We don't just sell electronics — we build long-term relationships through dependable service.</p>
                </div>
                <div class="service-grid">
                    <div class="service reveal"><span class="num">01 / TRUST</span>
                        <h3>100% Genuine Products</h3>
                        <p>Every item is sourced from authorized distributors with full bill, warranty card and original
                            seal.</p>
                    </div>
                    <div class="service reveal"><span class="num">02 / INSTALL</span>
                        <h3>Expert Installation</h3>
                        <p>Certified technicians handle AC, chimney, inverter & solar installation with precision and
                            safety.</p>
                    </div>
                    <div class="service reveal"><span class="num">03 / CARE</span>
                        <h3>Warranty Support</h3>
                        <p>Hassle-free claim processing with brand service centers — we handle the paperwork for you.
                        </p>
                    </div>
                    <div class="service reveal"><span class="num">04 / DOORSTEP</span>
                        <h3>Doorstep Service</h3>
                        <p>Free delivery, site surveys and pickup-and-drop repair service across the entire city.</p>
                    </div>
                    <div class="service reveal"><span class="num">05 / VALUE</span>
                        <h3>Best Price Promise</h3>
                        <p>Transparent, competitive pricing with easy EMI options and festive-season offers.</p>
                    </div>
                    <div class="service reveal"><span class="num">06 / SUPPORT</span>
                        <h3>24/7 Quick Support</h3>
                        <p>Call us any time — evenings, weekends, holidays. If the power's out, we're already on the
                            way.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">Special Offers</div>
                    <h2>More Power. Less Price.</h2>
                    <p>Exclusive deals on power, solar and smart home solutions.</p>
                </div>
                <div class="offer-grid">
                    <div class="offer reveal"><small>⚡ BATTERY SALE</small>
                        <h3>Battery Power Deal</h3><strong>Up to 25% OFF</strong>
                        <p>From ₹8,999</p><a class="cta ghost" href="product.php?category=Battery">Explore</a>
                    </div>
                    <div class="offer reveal"><small>HOT DEAL</small>
                        <h3>Inverter Special</h3><strong>Save up to ₹2,500</strong>
                        <p>Starting ₹6,999</p><a class="cta ghost" href="product.php?category=Home Inverter">Explore</a>
                    </div>
                    <div class="offer reveal"><small>☀️ SOLAR DEAL</small>
                        <h3>Solar Power Offer</h3><strong>Up to 15% OFF</strong>
                        <p>From ₹15,999</p><a class="cta ghost" href="product.php?category=Solar Panel">Explore</a>
                    </div>
                    <div class="offer reveal"><small>✨ HOME DEAL</small>
                        <h3>Kitchen Chimney Deal</h3><strong>Save up to ₹4,000</strong>
                        <p>From ₹14,999</p><a class="cta ghost" href="product.php?category=Chimney">Explore</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container wholesale reveal">
                <div>
                    <div class="eyebrow">Trade & Wholesale</div>
                    <h2>Need Electrical Products in Bulk?</h2>
                    <p>Get special pricing and reliable supply for large electrical requirements. Ideal for
                        electricians, contractors, builders, interior designers, hotels, restaurants, offices and shops.
                    </p>
                    <div class="hero-actions"><a class="cta" href="#contact">Contact</a><a class="cta ghost"
                            href="https://wa.me/+919955056900" target="_blank">Talk on WhatsApp</a></div>
                </div>
                <div class="feature-list">
                    <div>✓ Wholesale & contractor pricing</div>
                    <div>✓ Reliable & on-time supply</div>
                    <div>✓ GST billing & warranties</div>
                    <div>✓ Priority order handling</div>
                    <div>✓ Large stock availability</div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">Authorized Brands</div>
                    <h2>Brands You Already Trust.</h2>
                </div>
                <div class="brands">
                    <div class="brand reveal">Philips</div>
                    <div class="brand reveal">Havells</div>
                    <div class="brand reveal">Anchor</div>
                    <div class="brand reveal">Polycab</div>
                    <div class="brand reveal">Finolex</div>
                    <div class="brand reveal">Syska</div>
                    <div class="brand reveal">Crompton</div>
                    <div class="brand reveal">Bajaj</div>
                    <div class="brand reveal">Orient</div>
                    <div class="brand reveal">Wipro</div>
                    <div class="brand reveal">Legrand</div>
                    <div class="brand reveal">Schneider</div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">Why Swarnim Groups</div>
                    <h2>Why Choose Us?</h2>
                </div>
                <div class="why-grid">
                    <div class="why reveal"><b>01</b>
                        <h3>Genuine Products</h3>
                        <p>100% genuine and quality-checked electrical products.</p>
                    </div>
                    <div class="why reveal"><b>02</b>
                        <h3>Trusted Brands</h3>
                        <p>Products from trusted and authorized brands.</p>
                    </div>
                    <div class="why reveal"><b>03</b>
                        <h3>Competitive Prices</h3>
                        <p>Best pricing for retail, wholesale and bulk purchases.</p>
                    </div>
                    <div class="why reveal"><b>04</b>
                        <h3>Wide Product Range</h3>
                        <p>Batteries, inverters, solar, AC, chimney and more.</p>
                    </div>
                    <div class="why reveal"><b>05</b>
                        <h3>Expert Guidance</h3>
                        <p>Get the right product recommendation for your needs.</p>
                    </div>
                    <div class="why reveal"><b>06</b>
                        <h3>Fast Service</h3>
                        <p>Quick response and reliable service whenever you need us.</p>
                    </div>
                    <div class="why reveal"><b>07</b>
                        <h3>Warranty Support</h3>
                        <p>Proper warranty assistance and after-sales support.</p>
                    </div>
                    <div class="why reveal"><b>08</b>
                        <h3>Bulk Orders</h3>
                        <p>Special pricing and priority handling for bulk orders.</p>
                    </div>
                    <div class="why reveal"><b>09</b>
                        <h3>Home & Commercial</h3>
                        <p>Solutions for homes, shops, offices and commercial projects.</p>
                    </div>
                    <div class="why reveal"><b>10</b>
                        <h3>Easy WhatsApp Enquiry</h3>
                        <p>Quickly connect with our team for product enquiries.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">Photo Gallery</div>
                    <h2>Work That Speaks.</h2>
                    <p>A look at our products, installations and completed projects. Click any image to view fullscreen.
                    </p>
                </div>
                <div class="gallery">
                    <div class="gallery-item reveal"><img src="data/photogallery-1.png" alt="Swarnim Gallery 1"></div>
                    <div class="gallery-item reveal"><img src="data/photogallery-2.png" alt="Swarnim Gallery 2"></div>
                    <div class="gallery-item reveal"><img src="data/photogallery-3.png" alt="Swarnim Gallery 3"></div>
                    <div class="gallery-item reveal"><img src="data/photogallery-4.png" alt="Swarnim Gallery 4"></div>
                    <div class="gallery-item reveal"><img src="images/gallery/solar-1.jpg" alt="Solar Installation">
                    </div>
                    <div class="gallery-item reveal"><img src="images/gallery/solar-2.jpg" alt="Solar Project"></div>
                </div>
            </div>
        </section>

        <section class="section" id="reviews">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">Customer Stories</div>
                    <h2>What Our Customers Say</h2>
                </div>
                <div class="testimonials">
                    <div class="quote reveal">
                        <div class="stars">★★★★★</div>
                        <blockquote id="quote">“I bought an inverter and two tubular batteries from Swarnim Groups. The
                            installation was same-day, the team was professional, and we haven't had a single power-cut
                            interruption since. Truly honest people.”</blockquote><small id="author">Ramesh Thapa ·
                            Homeowner · Battisputali</small>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="contact">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">Contact Us</div>
                    <h2>Let's Power Up Your Home & Business</h2>
                    <p>Questions, quotes or installations — our team replies within minutes during store hours. Visit us
                        or drop a message below.</p>
                </div>
                <div class="contact-grid">
                    <div class="contact-stack reveal">
                        <div class="contact-card"><span>Call Us</span><strong>+91 9955056900</strong></div>
                        <div class="contact-card"><span>Email Us</span><strong>swarnimgroups@gmail.com</strong></div>
                        <div class="contact-card"><span>Main Office</span><strong>Babupara, South West Corner,
                                Mulashtank, Godda, India, 814133</strong></div>
                        <div class="contact-card"><span>Branch Office</span><strong>Satya nagar, Godda,
                                Jharkhand</strong></div>
                        <div class="contact-card"><span>Store Hours</span><strong>Sun–Fri: 9 AM – 7 PM · Sat: 10 AM – 4
                                PM</strong></div>
                    </div>
                    <form class="form reveal" id="contactForm">
                        <div class="form-grid">
                            <div class="field"><label>Full Name *</label><input id="contactName" name="name" required type="text"
                                    maxlength="100" placeholder="Your name"></div>
                            <div class="field"><label>Phone Number *</label><input id="contactPhone" name="phone" required type="tel"
                                    maxlength="20" placeholder="+91"></div>
                        </div>
                        <div class="form-grid">
                            <div class="field"><label>Email Address</label><input type="email"
                                    placeholder="you@example.com"></div>
                            <div class="field"><label>Interested In *</label><select id="contactInterest" name="interest" required>
                                    <option value="">Choose category</option>
                                    <option>Battery</option>
                                    <option>Cooler</option>
                                    <option>AC</option>
                                    <option>Chimney</option>
                                    <option>Solar Plates</option>
                                    <option>Inverter</option>
                                    <option>Other / Multiple</option>
                                </select></div>
                        </div>
                        <div class="field"><label>Your Message</label><textarea id="contactMessage" name="message" maxlength="5000"
                                placeholder="Tell us what you need..."></textarea></div>
                        <label
                            style="display:flex;gap:8px;align-items:center;color:var(--muted);font-size:.75rem"><input id="contactConsent" name="consent" value="1"
                                type="checkbox" required> By submitting, you agree to be contacted about your
                            enquiry.</label>
                        <button class="cta" type="submit">Send Message ↗</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div class="eyebrow">FAQ</div>
                    <h2>Questions, Answered.</h2>
                </div>
                <div class="faq">
                    <div class="faq-item"><button class="faq-q">What electrical products do you sell?<span
                                class="plus">+</span></button>
                        <div class="faq-a">We sell batteries, inverters, solar panels, ACs, coolers, kitchen chimneys,
                            room heaters, refrigerators, washing machines and other electrical products.</div>
                    </div>
                    <div class="faq-item"><button class="faq-q">Do you provide home delivery?<span
                                class="plus">+</span></button>
                        <div class="faq-a">Yes, doorstep delivery is available. Our team can also coordinate site visits
                            and installation where required.</div>
                    </div>
                    <div class="faq-item"><button class="faq-q">Can I enquire about prices on WhatsApp?<span
                                class="plus">+</span></button>
                        <div class="faq-a">Yes. You can contact our team on WhatsApp for product enquiries, pricing and
                            availability.</div>
                    </div>
                    <div class="faq-item"><button class="faq-q">Do you provide installation services?<span
                                class="plus">+</span></button>
                        <div class="faq-a">Yes. Certified technicians handle AC, chimney, inverter and solar
                            installation with precision and safety.</div>
                    </div>
                    <div class="faq-item"><button class="faq-q">Do you sell products in bulk?<span
                                class="plus">+</span></button>
                        <div class="faq-a">Yes. Wholesale and contractor pricing is available for large electrical
                            requirements, builders, offices, hotels, shops and commercial projects.</div>
                    </div>
                    <div class="faq-item"><button class="faq-q">Which brands are available?<span
                                class="plus">+</span></button>
                        <div class="faq-a">Authorized brands include Philips, Havells, Anchor, Polycab, Finolex, Syska,
                            Crompton, Bajaj, Orient, Wipro, Legrand and Schneider.</div>
                    </div>
                    <div class="faq-item"><button class="faq-q">Are your products genuine?<span
                                class="plus">+</span></button>
                        <div class="faq-a">Products are sourced from authorized distributors and are supplied with
                            applicable bills, warranty cards and original seals.</div>
                    </div>
                    <div class="faq-item"><button class="faq-q">Do you provide warranty support?<span
                                class="plus">+</span></button>
                        <div class="faq-a">Yes. We provide assistance with brand warranty claims and after-sales
                            support.</div>
                    </div>
                    <div class="faq-item"><button class="faq-q">How can I check product availability?<span
                                class="plus">+</span></button>
                        <div class="faq-a">Contact the Swarnim Groups team by phone, email or WhatsApp with the product
                            name and required quantity.</div>
                    </div>
                    <div class="faq-item"><button class="faq-q">Can I request a quotation for a large order?<span
                                class="plus">+</span></button>
                        <div class="faq-a">Yes. Large and bulk orders can be discussed with the team for special pricing
                            and supply arrangements.</div>
                    </div>
                    <div class="faq-item"><button class="faq-q">What payment options are available?<span
                                class="plus">+</span></button>
                        <div class="faq-a">Payment options can be confirmed with the team for your selected product or
                            order.</div>
                    </div>
                    <div class="faq-item"><button class="faq-q">How can I contact Swarnim Groups?<span
                                class="plus">+</span></button>
                        <div class="faq-a">Call +91 9955056900, email swarnimgroups@gmail.com, visit the Godda office,
                            or contact the team through WhatsApp.</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Guest login reminder: shown once per browser session after 60 seconds -->
    <div class="sw-login-reminder" id="swLoginReminder" aria-hidden="true">
        <div class="sw-login-backdrop" data-close-login></div>
        <section class="sw-login-card" role="dialog" aria-modal="true" aria-labelledby="swLoginTitle">
            <button class="sw-login-close" type="button" data-close-login aria-label="Close login reminder">×</button>
            <div class="sw-login-mark"><img src="data/Swarnim Logo.png" alt="Swarnim Groups"></div>
            <span class="sw-login-kicker">SWARNIM MEMBERS</span>
            <h2 id="swLoginTitle">Keep your Swarnim journey connected.</h2>
            <p>Login to save your wishlist, cart, orders and profile across devices. You can continue browsing as a guest.</p>
            <div class="sw-login-actions">
                <a class="cta" href="login.php">Login / Register</a>
                <button class="ghost" type="button" data-close-login>Continue Browsing</button>
            </div>
            <div class="sw-login-note"><span></span> Your account data stays protected.</div>
        </section>
    </div>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div><a class="logo" href="#home"><img src="data/Swarnim Logo.png" alt="">
                        <div><span>Swarnim Groups</span><small>Power • Comfort • Trust</small></div>
                    </a>
                    <p style="margin-top:18px;max-width:390px">One-stop destination for batteries, coolers, ACs,
                        chimneys, solar systems and inverters — with service you can trust since 2009.</p>
                </div>
                <div>
                    <h3>Quick Links</h3>
                    <div class="footer-links"><a href="#home">Home</a><a href="#about">About Us</a><a
                            href="product.php">All Products</a><a href="#services">Services</a><a
                            href="#reviews">Reviews</a><a href="#contact">Contact</a></div>
                </div>
                <div>
                    <h3>Contact</h3>
                    <div class="footer-links"><span>Babupara, South West Corner, Mulashtank, Godda, India,
                            814133</span><a href="tel:+919955056900">+91 9955056900</a><a
                            href="mailto:swarnimgroups@gmail.com">swarnimgroups@gmail.com</a><span>Sun–Fri: 9 AM – 7 PM
                            · Sat: 10 AM – 4 PM</span></div>
                </div>
            </div>
            <div class="footer-bottom"><span>© 2026 Swarnim Groups. All rights reserved.</span><span>Crafted with ⚡ for
                    uninterrupted power & comfort.</span></div>
        </div>
    </footer>

    <a class="float-call" href="tel:+919955056900" aria-label="Call Swarnim Groups">☎</a>
    <button class="top" id="topBtn">↑</button>

    <div class="modal" id="galleryModal"><button class="modal-close" id="modalClose">×</button><img id="modalImg" src=""
            alt=""></div>
    <div class="toast" id="toast">Message Sent! We'll get back to you within 15 minutes.</div>

    <script src="js/pwa.js"></script>
    <script src="js/script.js"></script>
</body>

</html>