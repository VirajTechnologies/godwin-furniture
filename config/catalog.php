<?php

/*
 * Product Manager catalogue config.
 * Mirrors _reference/Godwin Industries – Product Manager (2).html.
 */

return [

    'business_group' => 'Industries',

    // Project category => room/space => product categories
    'tree' => [
        'Furniture' => [
            'Living Room' => ['Sofa', 'Recliner', 'Lounge Chair', 'Accent Chair', 'Coffee Table', 'Centre Table', 'Side Table', 'TV Unit', 'TV Console', 'Bookshelf', 'Display Cabinet', 'Shoe Rack', 'Pouffe', 'Ottoman'],
            'Bedroom' => ['King Size Bed', 'Queen Size Bed', 'Single Bed', 'Bunk Bed', 'Bedside Table', 'Nightstand', 'Wardrobe', 'Dresser', 'Dressing Table', 'Chest of Drawers', 'Storage Bench', 'Headboard'],
            'Dining Room' => ['Dining Table', 'Dining Chair', 'Dining Bench', 'Bar Stool', 'Bar Counter', 'Crockery Unit', 'Buffet Cabinet', 'Sideboard'],
            'Office' => ['Office Chair', 'Executive Chair', 'Visitor Chair', 'Office Desk', 'Workstation', 'Conference Table', 'Filing Cabinet', 'Office Storage', 'Reception Desk'],
            'Outdoor' => ['Outdoor Sofa', 'Garden Chair', 'Patio Table', 'Outdoor Bench', 'Swing Chair', 'Outdoor Lounger'],
            'Kids Room' => ['Kids Bed', 'Bunk Bed', 'Kids Study Table', 'Kids Chair', 'Toy Storage', 'Kids Wardrobe'],
            'Study Room' => ['Study Desk', 'Study Chair', 'Computer Table', 'Bookshelf', 'Storage Cabinet'],
            'Entryway' => ['Console Table', 'Shoe Cabinet', 'Entryway Bench', 'Coat Stand', 'Mirror Unit'],
        ],
        'Interiors' => [
            'Living Room' => ['TV Wall Unit', 'TV Back Panel', 'Decorative Wall Panel', 'Partition', 'False Ceiling Elements', 'Wall Shelving', 'Display Unit', 'Feature Wall'],
            'Bedroom' => ['Modular Wardrobe', 'Sliding Wardrobe', 'Walk-in Closet', 'Bed Back Panel', 'Wall Panelling', 'Dressing Unit', 'Storage Solutions'],
            'Kitchen' => ['Modular Kitchen', 'Base Cabinet', 'Wall Cabinet', 'Tall Unit', 'Pantry Unit', 'Kitchen Island', 'Breakfast Counter', 'Kitchen Storage'],
            'Dining Room' => ['Crockery Display', 'Bar Unit', 'Dining Wall Panel', 'Storage Cabinet'],
            'Bathroom' => ['Vanity Unit', 'Mirror Cabinet', 'Bathroom Storage', 'Wall Shelves'],
            'Office' => ['Office Partitions', 'Modular Workstations', 'Reception Interiors', 'Conference Room Interiors', 'Storage Systems'],
            'Commercial Spaces' => ['Retail Display', 'Reception Interiors', 'Restaurant Interiors', 'Hotel Interiors', 'Commercial Partitions', 'Display Shelving'],
            'Entertainment Room' => ['Entertainment Wall Unit', 'Media Cabinet', 'Acoustic Wall Panels', 'Gaming Storage'],
        ],
    ],

    // First matching regex wins (tested against the product category name).
    'types' => [
        ['pattern' => '/Sofa/', 'types' => ['Chester', 'Royal', 'Fusion', 'Contemporary', 'Modern', 'Minimalist', 'Scandinavian', 'Industrial', 'Luxury', 'Modular']],
        ['pattern' => '/Bed|Headboard/', 'types' => ['Royal', 'Chester', 'Platform', 'Contemporary', 'Minimalist', 'Upholstered', 'Storage Bed', 'Floating Bed', 'Industrial']],
        ['pattern' => '/Chair|Recliner|Stool/', 'types' => ['Royal', 'Executive', 'Contemporary', 'Minimalist', 'Industrial', 'Scandinavian', 'Ergonomic', 'Lounge']],
        ['pattern' => '/TV|Media|Entertainment/', 'types' => ['Floating', 'Wall Mounted', 'Floor Standing', 'Modular', 'Contemporary', 'Minimalist', 'Luxury', 'Industrial']],
        ['pattern' => '/Dining Table/', 'types' => ['Modern', 'Royal', 'Industrial', 'Minimalist', 'Contemporary', 'Extendable', 'Round', 'Rectangular']],
        ['pattern' => '/Table|Desk|Counter|Workstation/', 'types' => ['Modern', 'Industrial', 'Minimalist', 'Contemporary', 'Extendable', 'Round', 'Rectangular', 'Classic']],
        ['pattern' => '/Wardrobe|Closet/', 'types' => ['Sliding', 'Hinged', 'Walk-in', 'Modular', 'Contemporary', 'Luxury', 'Minimalist']],
        ['pattern' => '/Kitchen|Pantry|Island|Base|Wall Cabinet|Tall/', 'types' => ['L-Shaped', 'U-Shaped', 'Parallel', 'Island', 'Straight', 'Modern', 'Classic']],
        ['pattern' => '/Panel|Partition|Ceiling|Feature/', 'types' => ['Fluted', 'Louvered', 'Veneer', 'Fabric', 'Backlit', 'Contemporary', 'Luxury', 'Minimalist']],
        ['pattern' => '/.*/', 'types' => ['Contemporary', 'Modern', 'Minimalist', 'Luxury', 'Industrial', 'Classic', 'Scandinavian', 'Modular']],
    ],

    // Prefixes combined with the product category to suggest model names.
    'model_names' => ['Oslo', 'Milano', 'Monaco', 'Windsor', 'Florence', 'Verona', 'Imperial', 'Monarch', 'Nova', 'Metro', 'Urban', 'Elite', 'Majesty', 'Leo', 'Royal', 'Aurora'],

    // Family detection. Interiors is always "int"; the rest use the first matching pattern.
    'family_patterns' => [
        'bed' => '/Bed|Headboard/',
        'seat' => '/Sofa|Recliner|Chair|Stool|Pouffe|Ottoman|Bench|Swing|Lounger/',
        'table' => '/Table|Desk|Counter|Workstation/',
    ],
    'default_family' => 'store',
    'interiors_family' => 'int',

    // type: s = select (options), n = number (default)
    'families' => [
        'seat' => [
            'label' => 'seating',
            'fields' => [
                ['label' => 'Seating capacity', 'type' => 'n', 'default' => '3'],
                ['label' => 'Frame material', 'type' => 's', 'options' => ['Sheesham wood', 'Teak', 'Mango wood', 'Metal', 'Engineered wood']],
                ['label' => 'Foam density (kg/m³)', 'type' => 'n', 'default' => '32'],
                ['label' => 'Armrest style', 'type' => 's', 'options' => ['Straight', 'Rolled', 'Sloped', 'None']],
            ],
        ],
        'bed' => [
            'label' => 'beds',
            'fields' => [
                ['label' => 'Mattress size', 'type' => 's', 'options' => ['King 72×78 in', 'Queen 60×78 in', 'Single 36×72 in']],
                ['label' => 'Under-bed storage', 'type' => 's', 'options' => ['None', 'Hydraulic', 'Drawer']],
                ['label' => 'Headboard height (cm)', 'type' => 'n', 'default' => '110'],
                ['label' => 'Frame material', 'type' => 's', 'options' => ['Sheesham wood', 'Teak', 'Engineered wood', 'Metal']],
            ],
        ],
        'table' => [
            'label' => 'tables and desks',
            'fields' => [
                ['label' => 'Top material', 'type' => 's', 'options' => ['Solid wood', 'Marble', 'Glass', 'Laminate', 'Veneer']],
                ['label' => 'Base material', 'type' => 's', 'options' => ['Solid wood', 'Metal', 'Engineered wood']],
                ['label' => 'Seating capacity', 'type' => 'n', 'default' => '6'],
                ['label' => 'Extendable', 'type' => 's', 'options' => ['No', 'Yes']],
            ],
        ],
        'store' => [
            'label' => 'storage furniture',
            'fields' => [
                ['label' => 'Doors', 'type' => 'n', 'default' => '2'],
                ['label' => 'Shelves / drawers', 'type' => 'n', 'default' => '4'],
                ['label' => 'Body material', 'type' => 's', 'options' => ['Engineered wood', 'Plywood', 'Solid wood', 'MDF']],
                ['label' => 'Hardware', 'type' => 's', 'options' => ['Soft-close', 'Standard', 'Push-to-open']],
            ],
        ],
        'int' => [
            'label' => 'interior installations',
            'fields' => [
                ['label' => 'Core material', 'type' => 's', 'options' => ['BWP plywood', 'MDF', 'HDHMR', 'Solid wood']],
                ['label' => 'Surface finish', 'type' => 's', 'options' => ['Laminate', 'Veneer', 'Acrylic', 'PU paint', 'Membrane']],
                ['label' => 'Pricing basis', 'type' => 's', 'options' => ['Per sq ft', 'Per running ft', 'Per unit']],
                ['label' => 'Installation time (days)', 'type' => 'n', 'default' => '7'],
            ],
        ],
    ],

    'dimension_defaults' => ['width_cm' => 200, 'depth_cm' => 90, 'height_cm' => 85, 'weight_kg' => 45],

    'colours' => [
        'Walnut' => '#5c3d2a',
        'Natural oak' => '#c9a574',
        'Charcoal' => '#3b4247',
        'Ivory' => '#e8e2d3',
        'Forest green' => '#2f5d4a',
        'Navy' => '#23395b',
        'Tan leather' => '#a8693a',
        'Ash grey' => '#9aa1a3',
    ],

    'finishes' => ['Matte', 'Glossy', 'Textured', 'Natural wood', 'Duco paint'],

    'upholstery' => ['Velvet', 'Leatherette', 'Genuine leather', 'Linen blend', 'Boucle'],

    // Families that carry an upholstery option on their variants.
    'upholstery_families' => ['seat', 'bed'],

    'default_base_price' => 25000,
    'default_variant_stock' => 10,
];
