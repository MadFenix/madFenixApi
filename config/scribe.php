<?php

return [
    'title' => 'weLore API',
    'description' => 'The WeLore API allows creators, brands, and communities to integrate a gamified loyalty system without writing code. It handles users, missions, digital rewards, collectibles, custom currencies, and seasonal narratives — all designed to turn every interaction into part of your brand story.',
    'intro_text' => <<<INTRO
Welcome to the WeLore API — a powerful and flexible system that transforms engagement into legacy. With WeLore, you don't just reward your audience with points; you invite them to co-write your universe. Through customizable tokens, unique items, missions, tiers, and lore-driven seasons, the API helps you retain and grow your community authentically. Whether you're a content creator, indie brand, or digital educator, WeLore gives you full control to build a living ecosystem around your values and audience.

<aside>
Let’s turn your fans into part of your legend.
</aside>
INTRO,
    'base_url' => 'https://api-weafinity.madfenix.com',
    'logo' => 'https://welore.io/logos/welore_logo_white.png',
    'auth' => [
        'enabled' => true,
        'in' => 'bearer',
        'name' => 'Authorization', // <-- This value is ignored, as the header name is always "Authorization"
        'placeholder' => '{ACCESS_TOKEN}',
        'extra_info' => 'You can retrieve your token by visiting your dashboard and clicking <b>Generate API token</b>.',
    ],
];
