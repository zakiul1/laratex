<?php
// config/dynamicgrid.php
return [
    // available layouts per type
    'layouts' => [
        'single_post' => [
            'layout1' => 'Catalog Grid Layout Price Button',
            'layout2' => 'Single Layout 2',
        ],
        'feature_post' => [
            'layout1' => 'Feature Layout 1',
            'layout2' => 'Feature Layout 2',
        ],
        'widget_post' => [
            'layout1' => 'Widget Layout 1',
        ],
    ],

    // defaults
    'type' => 'single_post',
    'layout' => 'layout1',
    'post_id' => null,
    'category_id' => null,
    'columns' => [
        'mobile' => 1,
        'tablet' => 2,
        'medium' => 3,
        'desktop' => 4,
        'large' => 4,
    ],
    'excerpt_words' => 20,
    'show_image' => true,
    'button_type' => 'read_more',
    'heading' => '',
    'product_amount' => 5,
    // NEW: whether to show the description/excerpt text
    'show_description' => false,
];