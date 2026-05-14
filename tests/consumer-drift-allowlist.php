<?php

declare(strict_types=1);

return [
    '_releaseGate' => [
        'modules' => ['evo-ui', 'sSeo', 'sLang', 'sSettings'],
        'scanned_consumers' => ['sSeo', 'sLang', 'sSettings'],
        'package_checks' => [
            'evo-ui' => [
                'composer test',
                'composer validate --strict --no-check-publish',
                'node --check resources/js/evo-ui.js',
            ],
        ],
        'allowed_exceptions' => [
            'sSeo' => [
                'src/sSeo.php' => [
                    'inline-script' => 'Public site-content analytics output, not manager UI drift.',
                ],
                'src/Livewire/RobotsEditor.php' => [
                    'inline-script' => 'Temporary editor bridge until the shared editor/media adapter owns CodeMirror lifecycle.',
                ],
                'views/module/shell.blade.php' => [
                    'inline-script' => 'Temporary manager bridge for parent Evolution tab-title synchronization.',
                ],
            ],
            'sLang' => [
                'src/sLang.php' => [
                    'cdn-asset' => 'Google Translate API endpoint used by translation logic, not a manager UI asset loaded by evo-ui screens.',
                ],
                'src/Controllers/sLangController.php' => [
                    'inline-script' => 'Evolution resource TV TabPane bridge documented in sLang resource-bridge docs.',
                ],
                'views/resourceGeneralTab.blade.php' => [
                    'inline-script' => 'Evolution resource General tab registration bridge documented in sLang resource-bridge docs.',
                ],
                'views/resourceSettingsTab.blade.php' => [
                    'inline-script' => 'Evolution resource Settings tab registration bridge documented in sLang resource-bridge docs.',
                ],
                'views/resourceTemplateVariablesTab.blade.php' => [
                    'inline-script' => 'Evolution resource Template Variables tab registration bridge documented in sLang resource-bridge docs.',
                ],
                'views/tabs.blade.php' => [
                    'inline-style' => 'Scoped Evolution resource form CSS documented in sLang resource-bridge docs.',
                    'inline-script' => 'Scoped Evolution resource form adapter documented in sLang resource-bridge docs.',
                    'local-evo-ui-style' => 'Scoped resource-tab button bridge, not an evo-ui-owned module screen.',
                ],
            ],
            'sSettings' => [
                'views/index.blade.php' => [
                    'inline-script' => 'Accepted first-release manager config bridge after visual QA; cleanup tracked for the next sSettings WebUI pass.',
                ],
                'views/livewire/configure-panel.blade.php' => [
                    'local-evo-ui-style' => 'Accepted first-release dirty-field modal exclusion after visual QA; move to shared EvoUI form helper in follow-up cleanup.',
                ],
                'views/livewire/settings-panel.blade.php' => [
                    'local-evo-ui-style' => 'Accepted first-release dirty-field modal exclusion after visual QA; move to shared EvoUI form helper in follow-up cleanup.',
                ],
            ],
        ],
    ],
    // Keep this list small and task-owned. Use paths relative to the consumer
    // package root. Example:
    // 'sLang' => [
    //     'views/tabs.blade.php' => ['inline-style', 'inline-script'],
    // ],
    'sSeo' => [
        // Public SEO integration output, not manager UI drift.
        'src/sSeo.php' => [
            'inline-script' => 'Public site-content analytics output, not manager UI drift.',
        ],
        // Temporary manager bridge until the shared editor/media adapter owns CodeMirror lifecycle.
        'src/Livewire/RobotsEditor.php' => [
            'inline-script' => 'Temporary editor bridge until the shared editor/media adapter owns CodeMirror lifecycle.',
        ],
        // Temporary manager bridge for parent Evolution tab-title synchronization.
        'views/module/shell.blade.php' => [
            'inline-script' => 'Temporary manager bridge for parent Evolution tab-title synchronization.',
        ],
    ],
    'sLang' => [
        'src/sLang.php' => [
            'cdn-asset' => 'Google Translate API endpoint used by translation logic, not a manager UI asset loaded by evo-ui screens.',
        ],
        'src/Controllers/sLangController.php' => [
            'inline-script' => 'Evolution resource TV TabPane bridge documented in sLang resource-bridge docs.',
        ],
        'views/resourceGeneralTab.blade.php' => [
            'inline-script' => 'Evolution resource General tab registration bridge documented in sLang resource-bridge docs.',
        ],
        'views/resourceSettingsTab.blade.php' => [
            'inline-script' => 'Evolution resource Settings tab registration bridge documented in sLang resource-bridge docs.',
        ],
        'views/resourceTemplateVariablesTab.blade.php' => [
            'inline-script' => 'Evolution resource Template Variables tab registration bridge documented in sLang resource-bridge docs.',
        ],
        'views/tabs.blade.php' => [
            'inline-style' => 'Scoped Evolution resource form CSS documented in sLang resource-bridge docs.',
            'inline-script' => 'Scoped Evolution resource form adapter documented in sLang resource-bridge docs.',
            'local-evo-ui-style' => 'Scoped resource-tab button bridge, not an evo-ui-owned module screen.',
        ],
    ],
    'sSettings' => [
        'views/index.blade.php' => [
            'inline-script' => 'Accepted first-release manager config bridge after visual QA; cleanup tracked for the next sSettings WebUI pass.',
        ],
        'views/livewire/configure-panel.blade.php' => [
            'local-evo-ui-style' => 'Accepted first-release dirty-field modal exclusion after visual QA; move to shared EvoUI form helper in follow-up cleanup.',
        ],
        'views/livewire/settings-panel.blade.php' => [
            'local-evo-ui-style' => 'Accepted first-release dirty-field modal exclusion after visual QA; move to shared EvoUI form helper in follow-up cleanup.',
        ],
    ],
    'sArticles' => [
        'builder/accordion/config.php' => [
            'inline-script' => 'Public builder block editor script; not an evo-ui manager primitive consumer.',
        ],
        'builder/file/template.blade.php' => [
            'inline-script' => 'Public builder block template script; not an evo-ui manager primitive consumer.',
        ],
        'builder/imgandtext/template.blade.php' => [
            'inline-script' => 'Public builder block template script; not an evo-ui manager primitive consumer.',
        ],
        'builder/poll/render.blade.php' => [
            'inline-script' => 'Public article render output; not an evo-ui manager primitive consumer.',
        ],
        'builder/quote/template.blade.php' => [
            'inline-script' => 'Public builder block template script; not an evo-ui manager primitive consumer.',
        ],
        'builder/singleimg/template.blade.php' => [
            'inline-script' => 'Public builder block template script; not an evo-ui manager primitive consumer.',
        ],
        'builder/slider/config.php' => [
            'inline-script' => 'Public builder block editor script; not an evo-ui manager primitive consumer.',
        ],
        'builder/slider/template.blade.php' => [
            'inline-script' => 'Public builder block template script; not an evo-ui manager primitive consumer.',
        ],
    ],
    'dIssues' => [
        'views/client/index.blade.php' => [
            'inline-style' => 'Public client issue portal styling; outside evo-ui manager shell.',
        ],
        'views/client/show.blade.php' => [
            'inline-style' => 'Public client issue portal styling; outside evo-ui manager shell.',
        ],
    ],
    'dDocs' => [
        'views/docs/shell.blade.php' => [
            'inline-script' => 'dDocs documentation shell adapter; tracked as a dDocs primitive before promotion to evo-ui.',
        ],
        'views/livewire/module-panel.blade.php' => [
            'inline-style' => 'dDocs documentation panel layout adapter; tracked as dDocs-specific shell work.',
            'inline-script' => 'dDocs documentation panel behavior adapter; tracked as dDocs-specific shell work.',
            'local-evo-ui-style' => 'dDocs currently owns its doc-tree panel skin; promotion to evo-ui is a separate primitive task.',
        ],
    ],
];
