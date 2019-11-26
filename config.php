<?php
$general = [
    'src' => '/home/Disk/priv/Photo/',
    'dst' => ['/home/Disk/priv/','/home/nas/priv/',],
    'preview' => [1920,],
    'list' => [
        'Очень личные альбомы',
        'Семейные альбомы',
        'Фотовстречи',
        'Ксюшкины фотки',
        'Танюшка эксклюзив',
        'Мобильное'
    ],
];

return $general;

return  [
    'Очень личные альбомы' => [
        'src' => '/home/Disk/priv/Photo/Очень личные альбомы/DUMP',
        'jpg_dst' => [
            [
                '/home/Disk/priv/Photo/Очень личные альбомы',
                [
                    ['/home/Disk/priv/Photo/Очень личные альбомы/ph1920', 1920],
                ],
            ],
            [
                '/home/nas/priv/Photo/Очень личные альбомы',
                [
                    ['/home/nas/priv/Photo/Очень личные альбомы/ph1920', 1920],
                ],
            ],
        ],
        'raw_dst' => [
            ['/home/Disk/priv/Photo/RAW/Очень личные альбомы', null,],
            ['/home/nas/priv/Photo/RAW/Очень личные альбомы', null,],
        ],
        'video_dst' => [
            ['/home/Disk/priv/Video/Очень личные альбомы', null,],
            ['/home/nas/priv/Video/Очень личные альбомы', null,],
        ],
    ],
    'Семейные альбомы' => [
        'src' => '/home/Disk/priv/Photo/Семейные альбомы/DUMP',
        'jpg_dst' => [
            [
                '/home/Disk/priv/Photo/Семейные альбомы',
                [
                    ['/home/Disk/priv/Photo/Семейные альбомы/ph1920', 1920],
                ],
            ],
            [
                '/home/nas/priv/Photo/Семейные альбомы',
                [
                    ['/home/nas/priv/Photo/Семейные альбомы/ph1920', 1920],
                ],
            ],
        ],
        'raw_dst' => [
            ['/home/Disk/priv/Photo/RAW/Семейные альбомы', null,],
            ['/home/nas/priv/Photo/RAW/Семейные альбомы', null,],
        ],
        'video_dst' => [
            ['/home/Disk/priv/Video/Семейные альбомы', null,],
            ['/home/nas/priv/Video/Семейные альбомы', null,],
        ],
    ],
    'Фотовстречи' => [
        'src' => '/home/Disk/priv/Photo/Фотовстречи/DUMP',
        'jpg_dst' => [
            [
                '/home/Disk/priv/Photo/Фотовстречи',
                [
                    ['/home/Disk/priv/Photo/Фотовстречи/ph1920', 1920],
                ],
            ],
            [
                '/home/nas/priv/Photo/Фотовстречи',
                [
                    ['/home/nas/priv/Photo/Фотовстречи/ph1920', 1920],
                ],
            ],
        ],
        'raw_dst' => [
            ['/home/Disk/priv/Photo/RAW/Фотовстречи', null,],
            ['/home/nas/priv/Photo/RAW/Фотовстречи', null,],
        ],
        'video_dst' => [
            ['/home/Disk/priv/Video/Фотовстречи', null,],
            ['/home/nas/priv/Video/Фотовстречи', null,],
        ],
    ],
];
